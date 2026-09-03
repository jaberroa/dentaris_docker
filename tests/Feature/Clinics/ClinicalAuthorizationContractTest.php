<?php

namespace Tests\Feature\Clinics;

use App\Models\Patient;
use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class ClinicalAuthorizationContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_clinic_membership_role_grants_permissions_without_a_global_role(): void
    {
        $fixture = $this->fixture(['view_patients', 'manage_patients']);
        $this->useContext($fixture['context']);

        $this->assertFalse($fixture['user']->roles()->exists());
        $this->assertTrue(Gate::forUser($fixture['user'])->allows('viewAny', Patient::class));
        $this->assertTrue(Gate::forUser($fixture['user'])->allows('create', Patient::class));
    }

    public function test_global_role_never_replaces_a_clinic_membership_permission(): void
    {
        $fixture = $this->fixture([]);
        $globalRole = Role::create([
            'name' => 'global-'.uniqid(),
            'display_name' => 'Rol global',
            'permissions' => ['view_patients', 'manage_patients', 'view_staff', 'manage_staff'],
            'is_active' => true,
        ]);
        $fixture['user']->roles()->attach($globalRole);
        $this->useContext($fixture['context']);

        $this->assertFalse(Gate::forUser($fixture['user'])->allows('viewAny', Patient::class));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('viewAny', Staff::class));
    }

    public function test_authorization_fails_closed_without_a_validated_context(): void
    {
        $fixture = $this->fixture(['view_patients', 'manage_patients']);
        $this->useContext(null);

        $this->assertFalse(Gate::forUser($fixture['user'])->allows('viewAny', Patient::class));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('create', Patient::class));
    }

    public function test_context_cannot_be_reused_by_another_user(): void
    {
        $fixture = $this->fixture(['view_patients']);
        $otherUser = User::factory()->create(['is_active' => true]);
        $this->useContext($fixture['context']);

        $this->assertFalse(Gate::forUser($otherUser)->allows('viewAny', Patient::class));
    }

    public function test_patient_policy_allows_local_resource_and_denies_cross_clinic_resource(): void
    {
        $fixture = $this->fixture(['view_patients', 'manage_patients']);
        $otherClinicId = $this->clinicId();
        $local = $this->patient($fixture['context']->clinicId, $fixture['user']->id);
        $foreign = $this->patient($otherClinicId, $fixture['user']->id);
        $this->useContext($fixture['context']);

        $this->assertTrue(Gate::forUser($fixture['user'])->allows('view', $local));
        $this->assertTrue(Gate::forUser($fixture['user'])->allows('update', $local));
        $this->assertTrue(Gate::forUser($fixture['user'])->allows('delete', $local));
        $this->assertTrue(Gate::forUser($fixture['user'])->allows('export', $local));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('view', $foreign));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('update', $foreign));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('delete', $foreign));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('export', $foreign));
    }

    public function test_staff_policy_uses_clinic_ownership_and_separate_read_and_manage_permissions(): void
    {
        $fixture = $this->fixture(['view_staff']);
        $local = $this->staff($fixture['context']->clinicId);
        $foreign = $this->staff($this->clinicId());
        $this->useContext($fixture['context']);

        $this->assertTrue(Gate::forUser($fixture['user'])->allows('viewAny', Staff::class));
        $this->assertTrue(Gate::forUser($fixture['user'])->allows('view', $local));
        $this->assertTrue(Gate::forUser($fixture['user'])->allows('export', $local));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('create', Staff::class));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('update', $local));
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('view', $foreign));
    }

    public function test_inactive_role_or_suspended_membership_revokes_permission_immediately(): void
    {
        $fixture = $this->fixture(['view_patients']);
        $this->useContext($fixture['context']);

        DB::table('roles')->where('id', $fixture['role_id'])->update(['is_active' => false]);
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('viewAny', Patient::class));

        DB::table('roles')->where('id', $fixture['role_id'])->update(['is_active' => true]);
        DB::table('clinic_memberships')->where('id', $fixture['context']->membershipId)->update([
            'suspended_at' => now(),
        ]);
        $this->assertFalse(Gate::forUser($fixture['user'])->allows('viewAny', Patient::class));
    }

    /**
     * @param  list<string>  $permissions
     * @return array{user: User, context: ClinicContext, role_id: int}
     */
    private function fixture(array $permissions): array
    {
        $now = now();
        $user = User::factory()->create(['is_active' => true]);
        $clinicId = $this->clinicId();
        $membershipId = DB::table('clinic_memberships')->insertGetId([
            'clinic_id' => $clinicId,
            'user_id' => $user->id,
            'status' => 'active',
            'activated_at' => $now,
            'suspended_at' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
        $roleId = DB::table('roles')->insertGetId([
            'name' => 'clinical-'.uniqid(),
            'display_name' => 'Rol clínico',
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

        return [
            'user' => $user,
            'context' => new ClinicContext($user->id, $clinicId, $membershipId),
            'role_id' => $roleId,
        ];
    }

    private function clinicId(): int
    {
        $now = now();

        return DB::table('clinics')->insertGetId([
            'name' => 'Clínica Autorización '.uniqid(),
            'code' => 'AUTH-'.uniqid(),
            'is_active' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function staff(int $clinicId): Staff
    {
        $staff = new Staff([
            'user_id' => User::factory()->create()->id,
            'employee_id' => 'EMP-'.uniqid(),
            'experience_years' => 0,
            'is_available' => true,
            'is_active' => true,
        ]);
        $staff->setAttribute('clinic_id', $clinicId);
        $staff->save();

        return $staff;
    }

    private function patient(int $clinicId, int $creatorId): Patient
    {
        $patient = Patient::factory()->make(['created_by' => $creatorId]);
        $patient->setAttribute('clinic_id', $clinicId);
        $patient->save();

        return $patient;
    }

    private function useContext(?ClinicContext $context): void
    {
        $request = Request::create('/clinical-authorization-contract', 'GET');

        if ($context !== null) {
            $request->attributes->set(ClinicContext::class, $context);
            $request->attributes->set('clinic.context', $context);
        }

        $this->app->instance('request', $request);
    }
}
