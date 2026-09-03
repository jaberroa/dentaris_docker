<?php

namespace App\Modules\Clinics\Services;

use App\Models\SecurityAuditLog;
use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\ClinicMembership;
use DomainException;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Orquesta la persistencia clínica de personal sin convertir al User global
 * en una entidad propiedad de una sola clínica.
 */
class StaffClinicalService
{
    public function __construct(
        private readonly ClinicalOwnershipService $ownership,
    ) {
    }

    /**
     * @param  array<string, mixed>  $userData
     * @param  array<string, mixed>  $staffData
     * @param  array<string, string|null>  $auditContext
     */
    public function create(
        array $userData,
        array $staffData,
        int $roleId,
        ClinicContext $context,
        array $auditContext = [],
    ): Staff {
        return DB::transaction(function () use ($userData, $staffData, $roleId, $context, $auditContext): Staff {
            $user = User::query()->create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
                'phone' => $userData['phone'] ?? null,
                'address' => $userData['address'] ?? null,
                'is_active' => true,
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();

            $membership = ClinicMembership::query()->create([
                'clinic_id' => $context->clinicId,
                'user_id' => $user->id,
                'status' => 'active',
                'activated_at' => now(),
                'suspended_at' => null,
            ]);
            $membership->roles()->sync([$roleId]);

            $staff = new Staff(array_merge($staffData, [
                'user_id' => $user->id,
                'employee_id' => $this->uniqueEmployeeId($context),
            ]));
            $this->ownership->assignStaff($staff, $context);
            $staff->save();

            $this->audit('staff.created', $staff, $context, $auditContext);

            return $staff->load('user');
        });
    }

    /**
     * @param  array<string, mixed>  $userData
     * @param  array<string, mixed>  $staffData
     * @param  array<string, string|null>  $auditContext
     */
    public function update(
        Staff $staff,
        array $userData,
        array $staffData,
        int $roleId,
        ClinicContext $context,
        array $auditContext = [],
    ): Staff {
        return DB::transaction(function () use ($staff, $userData, $staffData, $roleId, $context, $auditContext): Staff {
            $staff = Staff::query()
                ->forClinic($context)
                ->whereKey($staff->getKey())
                ->lockForUpdate()
                ->firstOrFail();
            $this->ownership->assignStaff($staff, $context);

            $user = User::query()->whereKey($staff->user_id)->lockForUpdate()->firstOrFail();
            $this->guardSharedIdentity($user, $userData);

            if (array_key_exists('password', $userData)) {
                $userData['password'] = Hash::make($userData['password']);
            }
            $user->update($userData);

            $membership = $this->membershipFor($user->id, $context, lock: true);
            $membership->roles()->sync([$roleId]);

            $staff->update($staffData);
            $this->audit('staff.updated', $staff, $context, $auditContext);

            return $staff->load('user');
        });
    }

    /**
     * @param  array<string, string|null>  $auditContext
     * @return array{deleted: bool, appointments: int, medical_records: int}
     */
    public function delete(
        Staff $staff,
        ClinicContext $context,
        array $auditContext = [],
    ): array {
        return DB::transaction(function () use ($staff, $context, $auditContext): array {
            $staff = Staff::query()
                ->forClinic($context)
                ->whereKey($staff->getKey())
                ->lockForUpdate()
                ->firstOrFail();

            $appointments = $staff->appointments()->count();
            $medicalRecords = $staff->medicalRecords()->count();

            if ($appointments > 0 || $medicalRecords > 0) {
                return [
                    'deleted' => false,
                    'appointments' => $appointments,
                    'medical_records' => $medicalRecords,
                ];
            }

            $membership = $this->membershipFor($staff->user_id, $context, lock: true);
            $this->audit('staff.deleted', $staff, $context, $auditContext);

            $staff->delete();
            $membership->update([
                'status' => 'suspended',
                'suspended_at' => now(),
            ]);

            return [
                'deleted' => true,
                'appointments' => 0,
                'medical_records' => 0,
            ];
        });
    }

    /**
     * Reemplaza la relación global user.roles en memoria por los roles de la
     * membresía clínica activa, sin modificar el modelo User heredado.
     *
     * @param  Collection<int, Staff>  $staff
     */
    public function loadClinicRoles(Collection $staff, ClinicContext $context): void
    {
        $userIds = $staff->pluck('user_id')->filter()->unique()->values();

        if ($userIds->isEmpty()) {
            return;
        }

        $memberships = ClinicMembership::query()
            ->with('roles')
            ->where('clinic_id', $context->clinicId)
            ->whereIn('user_id', $userIds)
            ->get()
            ->keyBy('user_id');

        $staff->each(static function (Staff $employee) use ($memberships): void {
            if ($employee->user === null) {
                return;
            }

            $employee->user->setRelation(
                'roles',
                $memberships->get($employee->user_id)?->roles ?? collect(),
            );
        });
    }

    private function membershipFor(int $userId, ClinicContext $context, bool $lock): ClinicMembership
    {
        $query = ClinicMembership::query()
            ->where('clinic_id', $context->clinicId)
            ->where('user_id', $userId);

        if ($lock) {
            $query->lockForUpdate();
        }

        $membership = $query->first();

        if ($membership === null) {
            throw new DomainException('El personal no posee una membresía en la clínica activa.');
        }

        return $membership;
    }

    /**
     * @param  array<string, mixed>  $userData
     */
    private function guardSharedIdentity(User $user, array $userData): void
    {
        $clinicCount = ClinicMembership::query()
            ->where('user_id', $user->id)
            ->distinct()
            ->count('clinic_id');

        if ($clinicCount < 2) {
            return;
        }

        $identityChanged = collect(['name', 'email', 'phone', 'address'])
            ->contains(function (string $field) use ($user, $userData): bool {
                return array_key_exists($field, $userData)
                    && (string) ($userData[$field] ?? '') !== (string) ($user->{$field} ?? '');
            });

        if ($identityChanged || array_key_exists('password', $userData)) {
            throw ValidationException::withMessages([
                'email' => 'La identidad pertenece a varias clínicas y debe actualizarse desde la gestión global de usuarios.',
            ]);
        }
    }

    private function uniqueEmployeeId(ClinicContext $context): string
    {
        for ($attempt = 0; $attempt < 20; $attempt++) {
            $candidate = sprintf(
                'STF-%d-%s',
                $context->clinicId,
                Str::upper(Str::random(12)),
            );

            if (! Staff::query()->where('employee_id', $candidate)->exists()) {
                return $candidate;
            }
        }

        throw new DomainException('No fue posible generar un código único para el personal.');
    }

    /**
     * @param  array<string, string|null>  $auditContext
     */
    private function audit(
        string $event,
        Staff $staff,
        ClinicContext $context,
        array $auditContext,
    ): void {
        SecurityAuditLog::query()->create([
            'user_id' => $context->userId,
            'event_type' => $event,
            'event_description' => 'Operación clínica de personal ejecutada.',
            'ip_address' => $auditContext['ip_address'] ?? 'unknown',
            'user_agent' => isset($auditContext['user_agent'])
                ? mb_substr($auditContext['user_agent'], 0, 255)
                : null,
            'session_id' => $auditContext['session_id'] ?? null,
            'metadata' => [
                'clinic_id' => $context->clinicId,
                'clinic_membership_id' => $context->membershipId,
                'staff_id' => $staff->id,
                'staff_user_id' => $staff->user_id,
            ],
            'risk_level' => $event === 'staff.deleted' ? 'medium' : 'low',
            'is_suspicious' => false,
            'event_time' => now(),
        ]);
    }
}
