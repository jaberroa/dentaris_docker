<?php

namespace Tests\Feature\Clinics;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Role;
use App\Models\User;
use App\Modules\Clinics\Models\Clinic;
use App\Modules\Clinics\Models\ClinicMembership;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClinicalAppointmentsMedicalRecordsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_lists_searches_and_selectors_are_limited_to_the_active_clinic(): void
    {
        $fixture = $this->fixture([
            'view_appointments',
            'manage_appointments',
            'view_medical_records',
            'manage_medical_records',
        ]);
        $localPatient = $this->patientId($fixture['user'], $fixture['clinic']->id, 'LOCAL');
        $foreignPatient = $this->patientId($fixture['user'], $fixture['other_clinic']->id, 'FOREIGN');
        $localStaff = $this->staffId($fixture['clinic']->id, 'LOCAL');
        $foreignStaff = $this->staffId($fixture['other_clinic']->id, 'FOREIGN');
        $status = $this->appointmentStatusId();
        $localAppointment = $this->appointmentId($localPatient, $localStaff, $fixture['user'], $status, 'LOCAL');
        $foreignAppointment = $this->appointmentId($foreignPatient, $foreignStaff, $fixture['user'], $status, 'FOREIGN');
        $localRecord = $this->medicalRecordId($localPatient, $localStaff, $fixture['user'], $localAppointment, 'LOCAL');
        $foreignRecord = $this->medicalRecordId($foreignPatient, $foreignStaff, $fixture['user'], $foreignAppointment, 'FOREIGN');
        $client = $this->actingAs($fixture['user'])->withSession(['clinic_id' => $fixture['clinic']->id]);

        $client->get(route('appointments.index', ['search' => 'Aislamiento']))
            ->assertOk()
            ->assertViewHas('appointments', function ($appointments) use ($localAppointment, $foreignAppointment): bool {
                $ids = $appointments->getCollection()->modelKeys();

                return in_array($localAppointment, $ids, true)
                    && ! in_array($foreignAppointment, $ids, true);
            });

        $client->getJson(route('appointments.search.staff', ['search' => 'Profesional']))
            ->assertOk()
            ->assertJsonPath('total', 1)
            ->assertJsonPath('data.0.id', $localStaff);

        $client->get(route('medical-records.index', ['search' => 'Aislamiento']))
            ->assertOk()
            ->assertViewHas('records', function ($records) use ($localRecord, $foreignRecord): bool {
                $ids = $records->getCollection()->modelKeys();

                return in_array($localRecord, $ids, true)
                    && ! in_array($foreignRecord, $ids, true);
            });
    }

    public function test_appointment_writes_validate_clinic_relations_and_foreign_bindings_return_not_found(): void
    {
        $fixture = $this->fixture(['view_appointments', 'manage_appointments']);
        $localPatient = $this->patientId($fixture['user'], $fixture['clinic']->id, 'WRITE-LOCAL');
        $foreignPatient = $this->patientId($fixture['user'], $fixture['other_clinic']->id, 'WRITE-FOREIGN');
        $localStaff = $this->staffId($fixture['clinic']->id, 'WRITE-LOCAL');
        $foreignStaff = $this->staffId($fixture['other_clinic']->id, 'WRITE-FOREIGN');
        $status = $this->appointmentStatusId();
        $foreignAppointment = Appointment::query()->findOrFail(
            $this->appointmentId($foreignPatient, $foreignStaff, $fixture['user'], $status, 'WRITE-FOREIGN'),
        );
        $client = $this->actingAs($fixture['user'])->withSession(['clinic_id' => $fixture['clinic']->id]);
        $payload = $this->appointmentPayload($localPatient, $localStaff, $status);

        $client->post(route('appointments.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        $created = Appointment::query()->where('appointment_code', 'like', 'CIT-%')->firstOrFail();
        $this->assertSame($localPatient, (int) $created->patient_id);
        $this->assertSame($localStaff, (int) $created->staff_id);

        $client->put(route('appointments.update', $created), array_merge($payload, [
            'reason' => 'Cita actualizada en clínica activa',
        ]))->assertRedirect(route('appointments.show', $created));
        $this->assertDatabaseHas('appointments', [
            'id' => $created->id,
            'reason' => 'Cita actualizada en clínica activa',
        ]);

        $client->post(route('appointments.store'), array_merge($payload, [
            'staff_id' => $foreignStaff,
            'clinic_id' => $fixture['other_clinic']->id,
        ]))->assertSessionHasErrors(['staff_id', 'clinic_id']);

        $client->put(route('appointments.update', $created), array_merge($payload, [
            'patient_id' => $foreignPatient,
        ]))->assertSessionHasErrors('patient_id');

        $client->get(route('appointments.show', $foreignAppointment))->assertNotFound();
        $client->delete(route('appointments.destroy', $foreignAppointment))->assertNotFound();

        $this->assertDatabaseHas('appointments', ['id' => $foreignAppointment->id]);

        $client->delete(route('appointments.destroy', $created))
            ->assertRedirect(route('appointments.index'));
        $this->assertDatabaseMissing('appointments', ['id' => $created->id]);
    }

    public function test_medical_record_writes_require_matching_clinic_patient_staff_and_appointment(): void
    {
        $fixture = $this->fixture(['view_medical_records', 'manage_medical_records']);
        $localPatient = $this->patientId($fixture['user'], $fixture['clinic']->id, 'RECORD-LOCAL');
        $otherLocalPatient = $this->patientId($fixture['user'], $fixture['clinic']->id, 'RECORD-OTHER');
        $foreignPatient = $this->patientId($fixture['user'], $fixture['other_clinic']->id, 'RECORD-FOREIGN');
        $localStaff = $this->staffId($fixture['clinic']->id, 'RECORD-LOCAL');
        $foreignStaff = $this->staffId($fixture['other_clinic']->id, 'RECORD-FOREIGN');
        $status = $this->appointmentStatusId();
        $localAppointment = $this->appointmentId($localPatient, $localStaff, $fixture['user'], $status, 'RECORD-LOCAL');
        $foreignAppointment = $this->appointmentId($foreignPatient, $foreignStaff, $fixture['user'], $status, 'RECORD-FOREIGN');
        $foreignRecord = MedicalRecord::query()->findOrFail(
            $this->medicalRecordId($foreignPatient, $foreignStaff, $fixture['user'], $foreignAppointment, 'RECORD-FOREIGN'),
        );
        $client = $this->actingAs($fixture['user'])->withSession(['clinic_id' => $fixture['clinic']->id]);
        $payload = $this->medicalRecordPayload($localPatient, $localStaff, $localAppointment);

        $client->post(route('medical-records.store'), $payload)
            ->assertRedirect()
            ->assertSessionHas('success');

        $created = MedicalRecord::query()->where('chief_complaint', 'Historia integrada')->firstOrFail();
        $this->assertSame($localAppointment, (int) $created->appointment_id);

        $client->put(route('medical-records.update', $created), array_merge($payload, [
            'chief_complaint' => 'Historia actualizada en clínica activa',
        ]))->assertRedirect(route('patients.show', $localPatient));
        $this->assertDatabaseHas('medical_records', [
            'id' => $created->id,
            'chief_complaint' => 'Historia actualizada en clínica activa',
        ]);

        $client->post(route('medical-records.store'), array_merge($payload, [
            'patient_id' => $otherLocalPatient,
        ]))->assertSessionHasErrors('appointment_id');

        $client->post(route('medical-records.store'), array_merge($payload, [
            'staff_id' => $foreignStaff,
            'clinic_id' => $fixture['other_clinic']->id,
        ]))->assertSessionHasErrors(['staff_id', 'clinic_id']);

        $client->post(route('medical-records.store'), array_merge($payload, [
            'appointment_id' => $foreignAppointment,
        ]))->assertSessionHasErrors('appointment_id');

        $client->get(route('medical-records.show', $foreignRecord))->assertNotFound();
        $client->get(route('medical-records.export', $foreignRecord))->assertNotFound();
        $client->delete(route('medical-records.destroy', $foreignRecord))->assertNotFound();
        $client->get(route('patients.medical-records', $foreignPatient))->assertNotFound();

        $this->assertDatabaseHas('medical_records', ['id' => $foreignRecord->id]);

        $client->delete(route('medical-records.destroy', $created))
            ->assertRedirect(route('patients.show', $localPatient));
        $this->assertDatabaseMissing('medical_records', ['id' => $created->id]);
    }

    public function test_missing_invalid_or_inactive_authority_fails_closed(): void
    {
        $fixture = $this->fixture(['view_appointments', 'view_medical_records']);

        $this->actingAs($fixture['user'])
            ->get(route('appointments.index'))
            ->assertForbidden();

        $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['other_clinic']->id])
            ->get(route('medical-records.index'))
            ->assertForbidden();

        $fixture['membership']->update(['activated_at' => null]);
        $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['clinic']->id])
            ->get(route('appointments.index'))
            ->assertForbidden();

        $fixture['membership']->update(['activated_at' => now(), 'suspended_at' => now(), 'status' => 'suspended']);
        $this->actingAs($fixture['user'])
            ->withSession(['clinic_id' => $fixture['clinic']->id])
            ->get(route('medical-records.index'))
            ->assertForbidden();
    }

    public function test_clinic_membership_permission_is_required_even_when_a_global_role_grants_it(): void
    {
        $fixture = $this->fixture([]);
        $globalRole = $this->role('global-clinical', [
            'view_appointments',
            'manage_appointments',
            'view_medical_records',
            'manage_medical_records',
        ]);
        $fixture['user']->roles()->attach($globalRole);
        $client = $this->actingAs($fixture['user'])->withSession(['clinic_id' => $fixture['clinic']->id]);

        $client->get(route('appointments.index'))->assertForbidden();
        $client->get(route('medical-records.index'))->assertForbidden();
        $client->post(route('appointments.store'), [])->assertForbidden();
        $client->post(route('medical-records.store'), [])->assertForbidden();
    }

    public function test_inactive_clinic_or_user_is_rejected_without_exposing_internal_details(): void
    {
        $inactiveClinicFixture = $this->fixture(['view_appointments']);
        $inactiveClinicFixture['clinic']->update(['is_active' => false]);

        $this->actingAs($inactiveClinicFixture['user'])
            ->withSession(['clinic_id' => $inactiveClinicFixture['clinic']->id])
            ->getJson(route('appointments.index'))
            ->assertForbidden()
            ->assertJsonMissing(['exception']);

        $inactiveUserFixture = $this->fixture(['view_medical_records']);
        $inactiveUserFixture['user']->update(['is_active' => false]);

        $this->actingAs($inactiveUserFixture['user'])
            ->withSession(['clinic_id' => $inactiveUserFixture['clinic']->id])
            ->getJson(route('medical-records.index'))
            ->assertForbidden()
            ->assertJsonMissing(['exception']);
    }

    /**
     * @param  list<string>  $permissions
     * @return array{user: User, clinic: Clinic, other_clinic: Clinic, membership: ClinicMembership}
     */
    private function fixture(array $permissions): array
    {
        $user = User::factory()->create(['is_active' => true]);
        $clinic = $this->clinic('A');
        $otherClinic = $this->clinic('B');
        $role = $this->role('clinical-'.uniqid(), $permissions);
        $membership = ClinicMembership::query()->create([
            'clinic_id' => $clinic->id,
            'user_id' => $user->id,
            'status' => 'active',
            'activated_at' => now(),
            'suspended_at' => null,
        ]);
        $membership->roles()->attach($role->id);

        return [
            'user' => $user,
            'clinic' => $clinic,
            'other_clinic' => $otherClinic,
            'membership' => $membership,
        ];
    }

    private function clinic(string $suffix): Clinic
    {
        return Clinic::query()->create([
            'code' => 'M13B-'.$suffix.'-'.uniqid(),
            'name' => 'Clínica Mandato 13B '.$suffix,
            'is_active' => true,
        ]);
    }

    /** @param list<string> $permissions */
    private function role(string $name, array $permissions): Role
    {
        return Role::query()->create([
            'name' => $name,
            'display_name' => 'Rol '.$name,
            'permissions' => $permissions,
            'is_active' => true,
        ]);
    }

    private function patientId(User $creator, int $clinicId, string $suffix): int
    {
        return DB::table('patients')->insertGetId([
            'clinic_id' => $clinicId,
            'patient_code' => 'M13B-P-'.$suffix.'-'.uniqid(),
            'first_name' => 'Paciente',
            'last_name' => 'Aislamiento '.$suffix,
            'birth_date' => '1990-01-01',
            'gender' => 'other',
            'is_active' => true,
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function staffId(int $clinicId, string $suffix): int
    {
        $staffUser = User::factory()->create([
            'name' => 'Profesional '.$suffix,
            'is_active' => true,
        ]);

        return DB::table('staff')->insertGetId([
            'clinic_id' => $clinicId,
            'user_id' => $staffUser->id,
            'employee_id' => 'M13B-S-'.$suffix.'-'.uniqid(),
            'specialty' => 'Odontología',
            'is_available' => true,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function appointmentStatusId(): int
    {
        return DB::table('appointment_statuses')->insertGetId([
            'name' => 'scheduled-'.uniqid(),
            'display_name' => 'Programada',
            'is_active' => true,
            'is_final' => false,
            'sort_order' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function appointmentId(
        int $patientId,
        int $staffId,
        User $creator,
        int $statusId,
        string $suffix,
    ): int {
        return DB::table('appointments')->insertGetId([
            'appointment_code' => 'M13B-A-'.$suffix.'-'.uniqid(),
            'patient_id' => $patientId,
            'staff_id' => $staffId,
            'appointment_status_id' => $statusId,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '09:00:00',
            'end_time' => '09:30:00',
            'duration' => 30,
            'type' => 'consulta',
            'reason' => 'Aislamiento multiclínica',
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function medicalRecordId(
        int $patientId,
        int $staffId,
        User $creator,
        ?int $appointmentId,
        string $suffix,
    ): int {
        return DB::table('medical_records')->insertGetId([
            'patient_id' => $patientId,
            'appointment_id' => $appointmentId,
            'staff_id' => $staffId,
            'record_type' => 'consulta',
            'chief_complaint' => 'Aislamiento '.$suffix,
            'present_illness' => '',
            'medical_history' => '',
            'dental_history' => '',
            'family_history' => '',
            'social_history' => '',
            'clinical_examination' => '',
            'oral_examination' => '',
            'diagnostic_impression' => '',
            'treatment_plan' => '',
            'recommendations' => '',
            'is_confidential' => false,
            'created_by' => $creator->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /** @return array<string, mixed> */
    private function appointmentPayload(int $patientId, int $staffId, int $statusId): array
    {
        return [
            'patient_id' => $patientId,
            'staff_id' => $staffId,
            'appointment_status_id' => $statusId,
            'appointment_date' => now()->addDay()->toDateString(),
            'start_time' => '11:00',
            'duration' => 30,
            'type' => 'consulta',
            'reason' => 'Cita integrada',
        ];
    }

    /** @return array<string, mixed> */
    private function medicalRecordPayload(int $patientId, int $staffId, int $appointmentId): array
    {
        return [
            'patient_id' => $patientId,
            'staff_id' => $staffId,
            'appointment_id' => $appointmentId,
            'record_type' => 'consulta',
            'chief_complaint' => 'Historia integrada',
        ];
    }
}
