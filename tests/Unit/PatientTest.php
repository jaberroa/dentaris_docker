<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    /** @test */
    public function it_can_create_a_patient()
    {
        $patientData = [
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'phone' => '555-1234',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'is_active' => true,
            'created_by' => $this->user->id,
        ];

        $patient = Patient::create($patientData);

        $this->assertInstanceOf(Patient::class, $patient);
        $this->assertEquals('Juan', $patient->first_name);
        $this->assertEquals('Pérez', $patient->last_name);
        $this->assertEquals('juan@example.com', $patient->email);
        $this->assertTrue($patient->is_active);
    }

    /** @test */
    public function it_can_generate_unique_patient_code()
    {
        $code1 = Patient::generateUniquePatientCode('Juan', 'Pérez', 1);
        $code2 = Patient::generateUniquePatientCode('María', 'García', 2);
        $code3 = Patient::generateUniquePatientCode('Juan', 'Pérez', 3);

        $this->assertNotEquals($code1, $code2);
        $this->assertNotEquals($code1, $code3);
        $this->assertStringStartsWith('JP', $code1);
        $this->assertStringStartsWith('MG', $code2);
        $this->assertStringStartsWith('JP', $code3);
    }

    /** @test */
    public function it_handles_duplicate_codes_correctly()
    {
        // Crear un paciente con código específico
        $patient = Patient::create([
            'patient_code' => 'JP00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        // Generar código para el mismo nombre pero diferente ID
        $newCode = Patient::generateUniquePatientCode('Juan', 'Pérez', $patient->id + 1);
        
        $this->assertNotEquals('JP00001', $newCode);
        $this->assertStringStartsWith('JP', $newCode);
    }

    /** @test */
    public function it_can_get_full_name_attribute()
    {
        $patient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals('Juan Pérez', $patient->full_name);
    }

    /** @test */
    public function it_can_get_age_attribute()
    {
        $birthDate = Carbon::now()->subYears(25);
        $patient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => $birthDate,
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals(25, $patient->age);
    }

    /** @test */
    public function it_can_get_full_address_attribute()
    {
        $patient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Calle 123',
            'city' => 'Ciudad',
            'state' => 'Estado',
            'postal_code' => '12345',
            'country' => 'México',
            'created_by' => $this->user->id,
        ]);

        $expectedAddress = 'Calle 123, Ciudad, Estado, 12345, México';
        $this->assertEquals($expectedAddress, $patient->full_address);
    }

    /** @test */
    public function it_returns_no_especificada_for_empty_address()
    {
        $patient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        $this->assertEquals('No especificada', $patient->full_address);
    }

    /** @test */
    public function it_can_check_has_allergies()
    {
        $patientWithAllergies = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'allergies' => 'Penicilina, polen',
            'created_by' => $this->user->id,
        ]);

        $patientWithoutAllergies = Patient::create([
            'patient_code' => 'JD00002',
            'first_name' => 'María',
            'last_name' => 'García',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($patientWithAllergies->hasAllergies());
        $this->assertFalse($patientWithoutAllergies->hasAllergies());
    }

    /** @test */
    public function it_can_check_is_active()
    {
        $activePatient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $inactivePatient = Patient::create([
            'patient_code' => 'JD00002',
            'first_name' => 'María',
            'last_name' => 'García',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'is_active' => false,
            'created_by' => $this->user->id,
        ]);

        $this->assertTrue($activePatient->isActive());
        $this->assertFalse($inactivePatient->isActive());
    }

    /** @test */
    public function it_can_get_stats_attribute()
    {
        $patient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        $stats = $patient->stats;

        $this->assertIsArray($stats);
        $this->assertArrayHasKey('total_appointments', $stats);
        $this->assertArrayHasKey('completed_appointments', $stats);
        $this->assertArrayHasKey('total_invoices', $stats);
        $this->assertArrayHasKey('total_payments', $stats);
        $this->assertArrayHasKey('pending_balance', $stats);
        $this->assertArrayHasKey('treatment_plans', $stats);
    }

    /** @test */
    public function scope_active_works()
    {
        Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        Patient::create([
            'patient_code' => 'JD00002',
            'first_name' => 'María',
            'last_name' => 'García',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'is_active' => false,
            'created_by' => $this->user->id,
        ]);

        $activePatients = Patient::active()->get();
        $this->assertCount(1, $activePatients);
        $this->assertEquals('Juan', $activePatients->first()->first_name);
    }

    /** @test */
    public function scope_search_works()
    {
        Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'phone' => '555-1234',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        Patient::create([
            'patient_code' => 'JD00002',
            'first_name' => 'María',
            'last_name' => 'García',
            'email' => 'maria@example.com',
            'phone' => '555-5678',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'created_by' => $this->user->id,
        ]);

        $searchResults = Patient::search('Juan')->get();
        $this->assertCount(1, $searchResults);
        $this->assertEquals('Juan', $searchResults->first()->first_name);

        $searchResults = Patient::search('555-1234')->get();
        $this->assertCount(1, $searchResults);
        $this->assertEquals('Juan', $searchResults->first()->first_name);
    }

    /** @test */
    public function scope_by_gender_works()
    {
        Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        Patient::create([
            'patient_code' => 'JD00002',
            'first_name' => 'María',
            'last_name' => 'García',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'created_by' => $this->user->id,
        ]);

        $malePatients = Patient::byGender('male')->get();
        $femalePatients = Patient::byGender('female')->get();

        $this->assertCount(1, $malePatients);
        $this->assertCount(1, $femalePatients);
        $this->assertEquals('male', $malePatients->first()->gender);
        $this->assertEquals('female', $femalePatients->first()->gender);
    }

    /** @test */
    public function scope_by_age_range_works()
    {
        $youngPatient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => Carbon::now()->subYears(25)->format('Y-m-d'),
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        $oldPatient = Patient::create([
            'patient_code' => 'JD00002',
            'first_name' => 'María',
            'last_name' => 'García',
            'birth_date' => Carbon::now()->subYears(65)->format('Y-m-d'),
            'gender' => 'female',
            'created_by' => $this->user->id,
        ]);

        $youngPatients = Patient::byAgeRange(20, 30)->get();
        $oldPatients = Patient::byAgeRange(60, 70)->get();

        $this->assertCount(1, $youngPatients);
        $this->assertCount(1, $oldPatients);
        $this->assertEquals('Juan', $youngPatients->first()->first_name);
        $this->assertEquals('María', $oldPatients->first()->first_name);
    }

    /** @test */
    public function scope_with_allergies_works()
    {
        Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'allergies' => 'Penicilina',
            'created_by' => $this->user->id,
        ]);

        Patient::create([
            'patient_code' => 'JD00002',
            'first_name' => 'María',
            'last_name' => 'García',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'created_by' => $this->user->id,
        ]);

        $patientsWithAllergies = Patient::withAllergies()->get();
        $this->assertCount(1, $patientsWithAllergies);
        $this->assertEquals('Juan', $patientsWithAllergies->first()->first_name);
    }

    /** @test */
    public function scope_with_marketing_consent_works()
    {
        Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'consent_marketing' => true,
            'created_by' => $this->user->id,
        ]);

        Patient::create([
            'patient_code' => 'JD00002',
            'first_name' => 'María',
            'last_name' => 'García',
            'birth_date' => '1990-01-01',
            'gender' => 'female',
            'consent_marketing' => false,
            'created_by' => $this->user->id,
        ]);

        $patientsWithConsent = Patient::withMarketingConsent()->get();
        $this->assertCount(1, $patientsWithConsent);
        $this->assertEquals('Juan', $patientsWithConsent->first()->first_name);
    }

    /** @test */
    public function it_has_creator_relationship()
    {
        $patient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        $this->assertInstanceOf(User::class, $patient->creator);
        $this->assertEquals($this->user->id, $patient->creator->id);
    }

    /** @test */
    public function it_can_get_emergency_contact_info()
    {
        $patient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'emergency_contact_name' => 'María Pérez',
            'emergency_contact_phone' => '555-9999',
            'emergency_contact_relationship' => 'spouse',
            'emergency_contact_address' => 'Calle 456',
            'created_by' => $this->user->id,
        ]);

        $emergencyInfo = $patient->emergency_contact_info;

        $this->assertIsArray($emergencyInfo);
        $this->assertEquals('María Pérez', $emergencyInfo['name']);
        $this->assertEquals('555-9999', $emergencyInfo['phone']);
        $this->assertEquals('spouse', $emergencyInfo['relationship']);
        $this->assertEquals('Calle 456', $emergencyInfo['address']);
    }

    /** @test */
    public function it_returns_null_for_emergency_contact_info_when_no_name()
    {
        $patient = Patient::create([
            'patient_code' => 'JD00001',
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        $this->assertNull($patient->emergency_contact_info);
    }
}
