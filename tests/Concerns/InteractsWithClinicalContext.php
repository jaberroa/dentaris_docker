<?php

namespace Tests\Concerns;

use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait InteractsWithClinicalContext
{
    /**
     * @param  list<string>  $permissions
     */
    protected function clinicalContextFor(
        User $user,
        array $permissions,
        ?int $clinicId = null,
        array $membershipOverrides = [],
    ): ClinicContext {
        $now = now();
        $user->forceFill(['is_active' => true])->save();

        $clinicId ??= DB::table('clinics')->insertGetId([
            'name' => 'Clínica de prueba '.uniqid(),
            'code' => 'TEST-'.uniqid(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $membershipId = DB::table('clinic_memberships')->insertGetId(array_merge([
            'clinic_id' => $clinicId,
            'user_id' => $user->id,
            'status' => 'active',
            'activated_at' => $now,
            'suspended_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ], $membershipOverrides));

        $roleId = DB::table('roles')->insertGetId([
            'name' => 'clinical-test-'.uniqid(),
            'display_name' => 'Rol clínico de prueba',
            'permissions' => json_encode($permissions, JSON_THROW_ON_ERROR),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        DB::table('clinic_membership_roles')->insert([
            'clinic_membership_id' => $membershipId,
            'role_id' => $roleId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        return new ClinicContext($user->id, $clinicId, $membershipId);
    }

    protected function bindClinicalContext(?ClinicContext $context, ?User $user = null): void
    {
        $request = Request::create('/tests/clinical-context', 'GET');

        if ($user !== null) {
            $request->setUserResolver(fn (): User => $user);
        }

        if ($context !== null) {
            $request->attributes->set(ClinicContext::class, $context);
            $request->attributes->set('clinic.context', $context);
        }

        $this->app->instance('request', $request);
    }
}
