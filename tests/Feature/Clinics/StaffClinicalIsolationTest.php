<?php

namespace Tests\Feature\Clinics;

use App\Models\Role;
use App\Models\Staff;
use App\Models\User;
use App\Modules\Clinics\Models\Clinic;
use App\Modules\Clinics\Models\ClinicMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StaffClinicalIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_context_is_required_and_the_index_cannot_escape_its_clinic_with_an_or_search(): void
    {
        $operator = User::factory()->create();
        $managerRole = $this->role('clinic-manager', ['view_staff', 'manage_staff']);
        $clinicA = $this->clinic('A');
        $clinicB = $this->clinic('B');
        $this->membership($operator, $clinicA, $managerRole);

        $local = $this->staff($clinicA, 'Local Visible', 'Coincidencia Clínica');
        $foreign = $this->staff($clinicB, 'Nombre Ajeno', 'Coincidencia Clínica');

        $this->actingAs($operator)
            ->get(route('staff.index'))
            ->assertForbidden();

        $response = $this->actingAs($operator)
            ->withSession(['clinic_id' => $clinicA->id])
            ->get(route('staff.index', ['search' => 'Coincidencia Clínica']));

        $response->assertOk();
        $response->assertViewHas('staff', function ($staff) use ($local, $foreign): bool {
            $ids = $staff->getCollection()->modelKeys();

            return in_array($local->id, $ids, true)
                && ! in_array($foreign->id, $ids, true);
        });
    }

    public function test_direct_operations_are_denied_for_staff_from_another_clinic(): void
    {
        [$operator, $clinicA] = $this->operatorFixture('cross');
        $clinicB = $this->clinic('cross-B');
        $foreign = $this->staff($clinicB, 'Personal Externo', 'Ortodoncia');
        $payload = $this->updatePayload($foreign, $this->role('doctor-cross', []));

        $client = $this->actingAs($operator)->withSession(['clinic_id' => $clinicA->id]);

        $client->get(route('staff.show', $foreign))->assertForbidden();
        $client->get(route('staff.edit', $foreign))->assertForbidden();
        $client->put(route('staff.update', $foreign), $payload)->assertForbidden();
        $client->delete(route('staff.destroy', $foreign))->assertForbidden();

        $this->assertDatabaseHas('staff', ['id' => $foreign->id, 'clinic_id' => $clinicB->id]);
    }

    public function test_store_assigns_ownership_employee_code_membership_and_clinic_role_on_the_server(): void
    {
        [$operator, $clinicA] = $this->operatorFixture('store');
        $clinicB = $this->clinic('store-B');
        $doctorRole = $this->role('doctor', ['view_patients']);

        $payload = [
            'name' => 'Dra. Clara Segura',
            'email' => 'clara.segura@example.test',
            'password' => 'Password-13!',
            'password_confirmation' => 'Password-13!',
            'phone' => '+58 212 555 0101',
            'address' => 'Dirección clínica verificada',
            'specialty' => 'Odontólogo General',
            'license_number' => 'LIC-M13-001',
            'role' => $doctorRole->name,
            'is_active' => '1',
        ];

        $this->actingAs($operator)
            ->withSession(['clinic_id' => $clinicA->id])
            ->post(route('staff.store'), $payload)
            ->assertRedirect(route('staff.index'))
            ->assertSessionHas('success');

        $createdUser = User::query()->where('email', $payload['email'])->firstOrFail();
        $staff = Staff::query()->where('user_id', $createdUser->id)->firstOrFail();
        $membership = ClinicMembership::query()
            ->where('clinic_id', $clinicA->id)
            ->where('user_id', $createdUser->id)
            ->firstOrFail();

        $this->assertSame($clinicA->id, (int) $staff->clinic_id);
        $this->assertMatchesRegularExpression('/^STF-'.$clinicA->id.'-[A-Z0-9]{12}$/', $staff->employee_id);
        $this->assertSame('active', $membership->status);
        $this->assertNotNull($membership->activated_at);
        $this->assertDatabaseHas('clinic_membership_roles', [
            'clinic_membership_id' => $membership->id,
            'role_id' => $doctorRole->id,
        ]);
        $this->assertDatabaseMissing('role_user', [
            'user_id' => $createdUser->id,
            'role_id' => $doctorRole->id,
        ]);
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $operator->id,
            'event_type' => 'staff.created',
        ]);

        $spoofed = array_merge($payload, [
            'email' => 'spoofed@example.test',
            'clinic_id' => $clinicB->id,
        ]);
        $this->actingAs($operator)
            ->withSession(['clinic_id' => $clinicA->id])
            ->post(route('staff.store'), $spoofed)
            ->assertSessionHasErrors('clinic_id');
        $this->assertDatabaseMissing('users', ['email' => 'spoofed@example.test']);
    }

    public function test_update_changes_only_the_clinic_membership_role_and_preserves_ownership(): void
    {
        [$operator, $clinic] = $this->operatorFixture('update');
        $oldRole = $this->role('old-clinic-role', []);
        $newRole = $this->role('new-clinic-role', []);
        $globalRole = $this->role('legacy-global-role', ['legacy']);
        $staff = $this->staff($clinic, 'Nombre Inicial', 'General', $oldRole);
        $staff->user->roles()->attach($globalRole->id);
        $originalEmployeeId = $staff->employee_id;

        $payload = $this->updatePayload($staff, $newRole, [
            'name' => 'Nombre Actualizado',
            'specialty' => 'Endodoncista',
        ]);

        $this->actingAs($operator)
            ->withSession(['clinic_id' => $clinic->id])
            ->put(route('staff.update', $staff), $payload)
            ->assertRedirect(route('staff.index'))
            ->assertSessionHas('success');

        $staff->refresh();
        $membership = ClinicMembership::query()
            ->where('clinic_id', $clinic->id)
            ->where('user_id', $staff->user_id)
            ->firstOrFail();

        $this->assertSame($clinic->id, (int) $staff->clinic_id);
        $this->assertSame($originalEmployeeId, $staff->employee_id);
        $this->assertSame('Endodoncista', $staff->specialty);
        $this->assertDatabaseMissing('clinic_membership_roles', [
            'clinic_membership_id' => $membership->id,
            'role_id' => $oldRole->id,
        ]);
        $this->assertDatabaseHas('clinic_membership_roles', [
            'clinic_membership_id' => $membership->id,
            'role_id' => $newRole->id,
        ]);
        $this->assertDatabaseHas('role_user', [
            'user_id' => $staff->user_id,
            'role_id' => $globalRole->id,
        ]);
    }

    public function test_shared_global_identity_cannot_be_changed_from_one_clinic(): void
    {
        [$operator, $clinicA] = $this->operatorFixture('shared');
        $clinicB = $this->clinic('shared-B');
        $staffRole = $this->role('shared-staff-role', []);
        $staff = $this->staff($clinicA, 'Identidad Compartida', 'General', $staffRole);
        $this->membership($staff->user, $clinicB, $staffRole);

        $payload = $this->updatePayload($staff, $staffRole, [
            'name' => 'Cambio No Autorizado',
            'specialty' => 'Cambio que debe revertirse',
        ]);

        $this->actingAs($operator)
            ->withSession(['clinic_id' => $clinicA->id])
            ->put(route('staff.update', $staff), $payload)
            ->assertSessionHasErrors('email');

        $this->assertSame('Identidad Compartida', $staff->user->fresh()->name);
        $this->assertSame('General', $staff->fresh()->specialty);
    }

    public function test_delete_preserves_the_global_user_and_other_clinic_membership(): void
    {
        [$operator, $clinicA] = $this->operatorFixture('delete');
        $clinicB = $this->clinic('delete-B');
        $staffRole = $this->role('deletable-staff-role', []);
        $staff = $this->staff($clinicA, 'Usuario Compartido', 'General', $staffRole);
        $membershipA = ClinicMembership::query()
            ->where('clinic_id', $clinicA->id)
            ->where('user_id', $staff->user_id)
            ->firstOrFail();
        $membershipB = $this->membership($staff->user, $clinicB, $staffRole);

        $this->actingAs($operator)
            ->withSession(['clinic_id' => $clinicA->id])
            ->delete(route('staff.destroy', $staff))
            ->assertRedirect(route('staff.index'))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('staff', ['id' => $staff->id]);
        $this->assertDatabaseHas('users', ['id' => $staff->user_id]);
        $this->assertSame('suspended', $membershipA->fresh()->status);
        $this->assertNotNull($membershipA->fresh()->suspended_at);
        $this->assertSame('active', $membershipB->fresh()->status);
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $operator->id,
            'event_type' => 'staff.deleted',
        ]);
    }

    /**
     * @return array{User, Clinic}
     */
    private function operatorFixture(string $suffix): array
    {
        $operator = User::factory()->create();
        $clinic = $this->clinic($suffix);
        $managerRole = $this->role('manager-'.$suffix, ['view_staff', 'manage_staff']);
        $this->membership($operator, $clinic, $managerRole);

        return [$operator, $clinic];
    }

    private function clinic(string $suffix): Clinic
    {
        return Clinic::query()->create([
            'code' => 'M13-'.strtoupper($suffix).'-'.uniqid(),
            'name' => 'Clínica '.$suffix.' '.uniqid(),
            'is_active' => true,
        ]);
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function role(string $name, array $permissions): Role
    {
        return Role::query()->firstOrCreate(
            ['name' => $name],
            [
                'display_name' => ucfirst(str_replace('-', ' ', $name)),
                'permissions' => $permissions,
                'is_active' => true,
            ],
        );
    }

    private function membership(User $user, Clinic $clinic, Role $role): ClinicMembership
    {
        $membership = ClinicMembership::query()->create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'status' => 'active',
            'activated_at' => now(),
            'suspended_at' => null,
        ]);
        $membership->roles()->attach($role->id);

        return $membership;
    }

    private function staff(
        Clinic $clinic,
        string $name,
        string $specialty,
        ?Role $role = null,
    ): Staff {
        $user = User::factory()->create(['name' => $name]);
        $role ??= $this->role('staff-'.$clinic->id.'-'.uniqid(), []);
        $this->membership($user, $clinic, $role);

        $staff = new Staff([
            'user_id' => $user->id,
            'employee_id' => 'FIX-'.$clinic->id.'-'.uniqid(),
            'specialty' => $specialty,
            'is_active' => true,
            'is_available' => true,
        ]);
        $staff->clinic()->associate($clinic);
        $staff->save();

        return $staff->load('user');
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function updatePayload(Staff $staff, Role $role, array $overrides = []): array
    {
        return array_merge([
            'name' => $staff->user->name,
            'email' => $staff->user->email,
            'phone' => $staff->user->phone,
            'address' => $staff->user->address,
            'specialty' => $staff->specialty,
            'license_number' => $staff->license_number,
            'role_id' => $role->id,
            'is_active' => '1',
        ], $overrides);
    }
}
