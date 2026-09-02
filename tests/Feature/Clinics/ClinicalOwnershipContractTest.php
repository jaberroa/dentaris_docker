<?php

namespace Tests\Feature\Clinics;

use App\Models\Patient;
use App\Models\Staff;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use App\Modules\Clinics\Services\ClinicalOwnershipService;
use App\Modules\Clinics\Services\ClinicContextResolver;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ClinicalOwnershipContractTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_and_staff_belong_to_a_clinic_and_are_explicitly_scoped(): void
    {
        $clinic = $this->createClinic('OWN-A');
        $foreignClinic = $this->createClinic('OWN-B');
        $userId = $this->createUser('owner-a@example.test');
        $foreignUserId = $this->createUser('owner-b@example.test');

        $context = $this->contextFor($userId, $clinic);
        $foreignContext = $this->contextFor($foreignUserId, $foreignClinic);
        $ownership = app(ClinicalOwnershipService::class);
        $patient = $ownership->assignPatient(new Patient($this->patientAttributes($userId, 'PAT-A')), $context);
        $patient->save();
        $foreignPatient = $ownership->assignPatient(new Patient($this->patientAttributes($foreignUserId, 'PAT-B')), $foreignContext);
        $foreignPatient->save();
        $staff = $ownership->assignStaff(new Staff($this->staffAttributes($userId, 'EMP-A')), $context);
        $staff->save();
        $foreignStaff = $ownership->assignStaff(new Staff($this->staffAttributes($foreignUserId, 'EMP-B')), $foreignContext);
        $foreignStaff->save();

        $this->assertTrue($patient->clinic->is($clinic));
        $this->assertTrue($staff->clinic->is($clinic));
        $this->assertSame([$patient->id], Patient::query()->forClinic($context)->pluck('id')->all());
        $this->assertSame([$staff->id], Staff::query()->forClinic($context)->pluck('id')->all());
        $this->assertNotContains($foreignPatient->id, Patient::query()->forClinic($context)->pluck('id')->all());
        $this->assertNotContains($foreignStaff->id, Staff::query()->forClinic($context)->pluck('id')->all());
        $this->assertCount(1, $clinic->patients()->get());
        $this->assertCount(1, $clinic->staff()->get());
    }

    public function test_staff_user_is_unique_within_a_clinic_but_can_belong_to_another_clinic(): void
    {
        $clinic = $this->createClinic('UNIQUE-A');
        $secondClinic = $this->createClinic('UNIQUE-B');
        $userId = $this->createUser('unique@example.test');

        $ownership = app(ClinicalOwnershipService::class);
        $firstContext = $this->contextFor($userId, $clinic);
        $secondContext = $this->contextFor($userId, $secondClinic);
        $ownership->assignStaff(new Staff($this->staffAttributes($userId, 'EMP-ONE')), $firstContext)->save();
        $ownership->assignStaff(new Staff($this->staffAttributes($userId, 'EMP-TWO')), $secondContext)->save();

        $this->expectException(\Illuminate\Database\UniqueConstraintViolationException::class);
        $ownership->assignStaff(new Staff($this->staffAttributes($userId, 'EMP-THREE')), $firstContext)->save();
    }

    public function test_ownership_requires_a_valid_context_and_cannot_be_transferred_implicitly(): void
    {
        $clinic = $this->createClinic('GUARD-A');
        $foreignClinic = $this->createClinic('GUARD-B');
        $userId = $this->createUser('guard-a@example.test');
        $foreignUserId = $this->createUser('guard-b@example.test');
        $context = $this->contextFor($userId, $clinic);
        $foreignContext = $this->contextFor($foreignUserId, $foreignClinic);
        $ownership = app(ClinicalOwnershipService::class);
        $patient = $ownership->assignPatient(new Patient($this->patientAttributes($userId, 'PAT-GUARD')), $context);
        $patient->save();

        $legacyPatient = new Patient($this->patientAttributes($userId, 'PAT-LEGACY'));
        $legacyPatient->save();

        $this->assertSame(1, Patient::query()->forClinic($context)->count());
        $this->assertSame(2, Patient::query()->count());
        $this->assertNull($legacyPatient->clinic_id);
        $this->expectException(DomainException::class);
        $ownership->assignPatient($patient->fresh(), $foreignContext);
    }

    private function createClinic(string $code): Clinic
    {
        return Clinic::query()->create(['code' => $code, 'name' => 'Clínica '.$code, 'is_active' => true]);
    }

    private function createUser(string $email): int
    {
        $now = now();

        return DB::table('users')->insertGetId([
            'name' => $email,
            'email' => $email,
            'password' => bcrypt('clinical-ownership-contract'),
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function patientAttributes(int $createdBy, string $code): array
    {
        return [
            'created_by' => $createdBy,
            'patient_code' => $code,
            'first_name' => 'Paciente',
            'last_name' => $code,
            'birth_date' => '1990-01-01',
            'gender' => 'other',
        ];
    }

    private function staffAttributes(int $userId, string $employeeId): array
    {
        return [
            'user_id' => $userId,
            'employee_id' => $employeeId,
        ];
    }

    private function contextFor(int $userId, Clinic $clinic): ClinicContext
    {
        $now = now();
        DB::table('clinic_memberships')->insert([
            'user_id' => $userId,
            'clinic_id' => $clinic->id,
            'status' => 'active',
            'activated_at' => $now,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $context = app(ClinicContextResolver::class)->resolve($userId, $clinic->id);

        $this->assertInstanceOf(ClinicContext::class, $context);

        return $context;
    }
}
