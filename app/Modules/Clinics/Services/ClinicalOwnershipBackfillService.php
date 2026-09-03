<?php

namespace App\Modules\Clinics\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use RuntimeException;

/**
 * Conciliación histórica de pertenencia clínica.
 *
 * Solo modifica clinic_id de filas nulas. La operación funcional conserva
 * las transacciones y el resumen de ejecución queda libre de datos personales.
 */
final class ClinicalOwnershipBackfillService
{
    public function preview(string $clinicCode): array
    {
        return $this->inspect($clinicCode);
    }

    public function execute(string $clinicCode): array
    {
        $runId = (string) Str::uuid();

        return DB::transaction(function () use ($clinicCode, $runId): array {
            $before = $this->inspect($clinicCode, lockRows: true);
            $this->assertExecutable($before);

            $clinicId = $before['clinic']['id'];
            $patientsUpdated = DB::table('patients')
                ->whereNull('clinic_id')
                ->update(['clinic_id' => $clinicId]);
            $staffUpdated = DB::table('staff')
                ->whereNull('clinic_id')
                ->update(['clinic_id' => $clinicId]);

            $after = $this->inspect($clinicCode);
            $this->assertPostconditions($after);

            return [
                'status' => 'executed',
                'run_id' => $runId,
                'clinic_code' => $clinicCode,
                'clinic_id' => $clinicId,
                'patients_updated' => $patientsUpdated,
                'staff_updated' => $staffUpdated,
                'patients_pending_after' => $after['patients']['pending'],
                'staff_pending_after' => $after['staff']['pending'],
                'conflicts' => 0,
            ];
        });
    }

    private function inspect(string $clinicCode, bool $lockRows = false): array
    {
        $clinics = DB::table('clinics')->where('code', $clinicCode)->get(['id', 'code', 'is_active']);
        $errors = [];

        if ($clinics->count() !== 1) {
            $errors[] = 'target_clinic_must_exist_exactly_once';
        }

        $clinic = $clinics->first();

        if ($clinic !== null && ! $clinic->is_active) {
            $errors[] = 'target_clinic_must_be_active';
        }

        if ($clinic === null) {
            return [
                'clinic' => null,
                'errors' => $errors,
                'patients' => [],
                'staff' => [],
            ];
        }

        $clinicId = (int) $clinic->id;
        $patients = DB::table('patients')->whereNull('clinic_id');
        $staff = DB::table('staff')->whereNull('clinic_id');

        if ($lockRows) {
            $patients->lockForUpdate();
            $staff->lockForUpdate();
        }

        $patientPending = (clone $patients)->count();
        $staffPending = (clone $staff)->count();
        $patientEligible = $this->eligiblePatients($clinicId)->count();
        $staffEligible = $this->eligibleStaff($clinicId)->count();
        $patientNonNull = DB::table('patients')->whereNotNull('clinic_id');
        $staffNonNull = DB::table('staff')->whereNotNull('clinic_id');

        $patientState = [
            'total' => DB::table('patients')->count(),
            'pending' => $patientPending,
            'already_target' => (clone $patientNonNull)->where('clinic_id', $clinicId)->count(),
            'conflicting_owner' => (clone $patientNonNull)->where('clinic_id', '<>', $clinicId)->count(),
            'eligible' => $patientEligible,
        ];
        $staffState = [
            'total' => DB::table('staff')->count(),
            'pending' => $staffPending,
            'already_target' => (clone $staffNonNull)->where('clinic_id', $clinicId)->count(),
            'conflicting_owner' => (clone $staffNonNull)->where('clinic_id', '<>', $clinicId)->count(),
            'eligible' => $staffEligible,
            'duplicate_pending_users' => DB::table('staff')
                ->whereNull('clinic_id')
                ->groupBy('user_id')
                ->havingRaw('COUNT(*) > 1')
                ->count(),
            'target_collisions' => DB::table('staff as pending')
                ->join('staff as assigned', function ($join) use ($clinicId) {
                    $join->on('assigned.user_id', '=', 'pending.user_id')
                        ->where('assigned.clinic_id', '=', $clinicId);
                })
                ->whereNull('pending.clinic_id')
                ->count(),
        ];

        if ($patientState['conflicting_owner'] > 0 || $staffState['conflicting_owner'] > 0) {
            $errors[] = 'conflicting_existing_owner';
        }
        if ($patientEligible !== $patientPending) {
            $errors[] = 'ineligible_patient_source';
        }
        if ($staffEligible !== $staffPending) {
            $errors[] = 'ineligible_staff_source';
        }
        if ($staffState['duplicate_pending_users'] > 0 || $staffState['target_collisions'] > 0) {
            $errors[] = 'staff_uniqueness_conflict';
        }

        return [
            'clinic' => (array) $clinic,
            'errors' => $errors,
            'patients' => $patientState,
            'staff' => $staffState,
        ];
    }

    private function eligiblePatients(int $clinicId)
    {
        return DB::table('patients as p')
            ->join('users as u', 'u.id', '=', 'p.created_by')
            ->whereNull('p.clinic_id')
            ->where('u.is_active', true)
            ->whereExists(function ($query) use ($clinicId) {
                $query->select(DB::raw(1))
                    ->from('clinic_memberships as m')
                    ->whereColumn('m.user_id', 'p.created_by')
                    ->where('m.clinic_id', $clinicId)
                    ->where('m.status', 'active')
                    ->whereNotNull('m.activated_at')
                    ->whereNull('m.suspended_at');
            });
    }

    private function eligibleStaff(int $clinicId)
    {
        return DB::table('staff as s')
            ->join('users as u', 'u.id', '=', 's.user_id')
            ->whereNull('s.clinic_id')
            ->where('u.is_active', true)
            ->whereExists(function ($query) use ($clinicId) {
                $query->select(DB::raw(1))
                    ->from('clinic_memberships as m')
                    ->whereColumn('m.user_id', 's.user_id')
                    ->where('m.clinic_id', $clinicId)
                    ->where('m.status', 'active')
                    ->whereNotNull('m.activated_at')
                    ->whereNull('m.suspended_at');
            });
    }

    private function assertExecutable(array $state): void
    {
        if ($state['errors'] !== []) {
            throw new RuntimeException('Clinical ownership backfill blocked: '.implode(',', $state['errors']));
        }
    }

    private function assertPostconditions(array $state): void
    {
        $this->assertExecutable($state);

        if ($state['patients']['pending'] !== 0 || $state['staff']['pending'] !== 0) {
            throw new RuntimeException('Clinical ownership backfill did not reconcile all pending rows.');
        }
    }
}
