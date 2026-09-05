<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Patient;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Concerns\InteractsWithClinicalContext;

class PatientApiTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    private ClinicContext $clinicContext;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create(['is_active' => true]);
        $this->clinicContext = $this->clinicalContextFor($this->user, ['view_patients', 'manage_patients']);
    }

    /**
     * Helper method to create authenticated API request headers
     */
    protected function authHeaders(): array
    {
        $token = $this->user->createToken('test-token')->plainTextToken;
        
        return [
            'Authorization' => 'Bearer ' . $token,
            'Accept' => 'application/json',
            'X-Clinic-Id' => (string) $this->clinicContext->clinicId,
        ];
    }

    /** @test */
    public function it_can_get_patients_api_with_authentication()
    {
        Patient::factory(5)->forClinic($this->clinicContext)->create(['created_by' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'patient_code',
                    'first_name',
                    'last_name',
                    'full_name',
                    'email',
                    'phone',
                    'birth_date',
                    'age',
                    'gender',
                    'is_active',
                    'created_at',
                    'updated_at'
                ]
            ],
            'current_page',
            'last_page',
            'per_page',
            'total'
        ]);
    }

    /** @test */
    public function it_requires_authentication_for_patients_api()
    {
        $response = $this->getJson('/api/patients');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_search_patients_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'María',
            'last_name' => 'García',
            'email' => 'maria@example.com',
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?search=Juan');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.first_name', 'Juan');
    }

    /** @test */
    public function it_can_filter_patients_by_gender_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'María',
            'gender' => 'female',
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?gender=male');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.gender', 'male');
    }

    /** @test */
    public function it_can_filter_patients_by_age_range_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'birth_date' => now()->subYears(25)->format('Y-m-d'),
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'María',
            'birth_date' => now()->subYears(65)->format('Y-m-d'),
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?age_min=20&age_max=30');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.first_name', 'Juan');
    }

    /** @test */
    public function it_can_filter_patients_by_allergies_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'allergies' => 'Penicilina',
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'María',
            'allergies' => null,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?has_allergies=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.first_name', 'Juan');
    }

    /** @test */
    public function it_can_filter_patients_by_marketing_consent_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'consent_marketing' => true,
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'María',
            'consent_marketing' => false,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?consent_marketing=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.first_name', 'Juan');
    }

    /** @test */
    public function it_can_filter_patients_by_date_range_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'created_at' => now()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'María',
            'created_at' => now()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?created_from=' . now()->subDays(7)->format('Y-m-d') . '&created_to=' . now()->format('Y-m-d'));

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.first_name', 'Juan');
    }

    /** @test */
    public function it_can_handle_pagination_api()
    {
        Patient::factory(25)->forClinic($this->clinicContext)->create(['created_by' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?per_page=10&page=1');

        $response->assertStatus(200);
        $response->assertJsonCount(10, 'data');
        $response->assertJsonPath('per_page', 10);
        $response->assertJsonPath('current_page', 1);
        $response->assertJsonPath('total', 25);
    }

    /** @test */
    public function it_can_get_patient_details_api()
    {
        $patient = Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/patients/{$patient->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('data.first_name', 'Juan');
        $response->assertJsonPath('data.last_name', 'Pérez');
        $response->assertJsonPath('data.email', 'juan@example.com');
    }

    /** @test */
    public function it_returns_404_for_nonexistent_patient_api()
    {
        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_create_patient_via_api()
    {
        $patientData = [
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'phone' => '555-1234',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Calle 123',
            'city' => 'Ciudad',
            'state' => 'Estado',
            'postal_code' => '12345',
            'country' => 'México',
            'blood_type' => 'O+',
            'occupation' => 'Ingeniero',
            'marital_status' => 'single',
            'medical_history' => 'Sin antecedentes',
            'dental_history' => 'Limpieza regular',
            'allergies' => 'Ninguna',
            'medications' => 'Ninguno',
            'emergency_contact_name' => 'María Pérez',
            'emergency_contact_phone' => '555-9999',
            'emergency_contact_relationship' => 'spouse',
            'emergency_contact_address' => 'Calle 456',
            'notes' => 'Paciente nuevo',
            'consent_marketing' => false,
            'consent_data_processing' => true,
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/patients', $patientData);

        $response->assertStatus(201);
        $response->assertJsonPath('data.first_name', 'Juan');
        $response->assertJsonPath('data.last_name', 'Pérez');
        $response->assertJsonPath('data.email', 'juan@example.com');
    }

    /** @test */
    public function it_validates_required_fields_when_creating_patient_via_api()
    {
        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/patients', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors([
            'first_name',
            'last_name',
            'birth_date',
            'gender',
            'consent_data_processing'
        ]);
    }

    /** @test */
    public function it_validates_email_uniqueness_when_creating_patient_via_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'email' => 'juan@example.com',
            'created_by' => $this->user->id,
        ]);

        $patientData = [
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'consent_data_processing' => true,
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->postJson('/api/patients', $patientData);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_can_update_patient_via_api()
    {
        $patient = Patient::factory()->forClinic($this->clinicContext)->create(['created_by' => $this->user->id]);

        $updateData = [
            'first_name' => 'Juan Carlos',
            'last_name' => 'Pérez García',
            'email' => 'juancarlos@example.com',
            'phone' => '555-5678',
            'birth_date' => '1985-05-15',
            'gender' => 'male',
            'address' => 'Nueva Dirección 456',
            'city' => 'Nueva Ciudad',
            'state' => 'Nuevo Estado',
            'postal_code' => '54321',
            'country' => 'México',
            'blood_type' => 'A+',
            'occupation' => 'Doctor',
            'marital_status' => 'married',
            'medical_history' => 'Historial actualizado',
            'dental_history' => 'Historial dental actualizado',
            'allergies' => 'Nuevas alergias',
            'medications' => 'Nuevos medicamentos',
            'emergency_contact_name' => 'Ana García',
            'emergency_contact_phone' => '555-8888',
            'emergency_contact_relationship' => 'sibling',
            'emergency_contact_address' => 'Nueva dirección de emergencia',
            'notes' => 'Notas actualizadas',
            'consent_marketing' => true,
            'consent_data_processing' => true,
            'is_active' => true,
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->putJson("/api/patients/{$patient->id}", $updateData);

        $response->assertStatus(200);
        $response->assertJsonPath('data.first_name', 'Juan Carlos');
        $response->assertJsonPath('data.last_name', 'Pérez García');
        $response->assertJsonPath('data.email', 'juancarlos@example.com');
    }

    /** @test */
    public function it_can_delete_patient_via_api()
    {
        $patient = Patient::factory()->forClinic($this->clinicContext)->create(['created_by' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson("/api/patients/{$patient->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('message', 'Paciente eliminado exitosamente.');
        
        $this->assertDatabaseMissing('patients', [
            'id' => $patient->id,
        ]);
    }

    /** @test */
    public function it_returns_404_when_updating_nonexistent_patient_via_api()
    {
        $updateData = [
            'first_name' => 'Juan Carlos',
            'last_name' => 'Pérez García',
            'birth_date' => '1985-05-15',
            'gender' => 'male',
            'consent_data_processing' => true,
        ];

        $response = $this->withHeaders($this->authHeaders())
            ->putJson('/api/patients/999', $updateData);

        $response->assertStatus(404);
    }

    /** @test */
    public function it_returns_404_when_deleting_nonexistent_patient_via_api()
    {
        $response = $this->withHeaders($this->authHeaders())
            ->deleteJson('/api/patients/999');

        $response->assertStatus(404);
    }

    /** @test */
    public function it_can_handle_empty_search_results_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?search=María');

        $response->assertStatus(200);
        $response->assertJsonCount(0, 'data');
    }

    /** @test */
    public function it_can_handle_combined_filters_api()
    {
        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'gender' => 'male',
            'consent_marketing' => true,
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'María',
            'gender' => 'female',
            'consent_marketing' => false,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?gender=male&consent_marketing=1');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.first_name', 'Juan');
    }

    /** @test */
    public function it_can_handle_invalid_pagination_parameters_api()
    {
        Patient::factory(5)->forClinic($this->clinicContext)->create(['created_by' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?per_page=invalid&page=invalid');

        $response->assertStatus(200);
        // Debería usar valores por defecto
        $response->assertJsonPath('per_page', 15);
        $response->assertJsonPath('current_page', 1);
    }

    /** @test */
    public function it_can_handle_large_datasets_api()
    {
        Patient::factory(100)->forClinic($this->clinicContext)->create(['created_by' => $this->user->id]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson('/api/patients?per_page=50');

        $response->assertStatus(200);
        $response->assertJsonCount(50, 'data');
        $response->assertJsonPath('total', 100);
        $response->assertJsonPath('last_page', 2);
    }

    /** @test */
    public function it_returns_correct_json_structure_for_patient_resource()
    {
        $patient = Patient::factory()->forClinic($this->clinicContext)->create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'phone' => '555-1234',
            'birth_date' => '1990-01-01',
            'gender' => 'male',
            'address' => 'Calle 123',
            'city' => 'Ciudad',
            'state' => 'Estado',
            'postal_code' => '12345',
            'country' => 'México',
            'blood_type' => 'O+',
            'occupation' => 'Ingeniero',
            'marital_status' => 'single',
            'medical_history' => 'Sin antecedentes',
            'dental_history' => 'Limpieza regular',
            'allergies' => 'Ninguna',
            'medications' => 'Ninguno',
            'emergency_contact_name' => 'María Pérez',
            'emergency_contact_phone' => '555-9999',
            'emergency_contact_relationship' => 'spouse',
            'emergency_contact_address' => 'Calle 456',
            'notes' => 'Paciente nuevo',
            'consent_marketing' => false,
            'consent_data_processing' => true,
            'is_active' => true,
            'created_by' => $this->user->id,
        ]);

        $response = $this->withHeaders($this->authHeaders())
            ->getJson("/api/patients/{$patient->id}");

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'data' => [
                'id',
                'patient_code',
                'first_name',
                'last_name',
                'full_name',
                'email',
                'phone',
                'phone_secondary',
                'birth_date',
                'age',
                'gender',
                'address',
                'city',
                'state',
                'postal_code',
                'country',
                'full_address',
                'medical_history',
                'dental_history',
                'allergies',
                'medications',
                'family_history',
                'social_history',
                'blood_type',
                'occupation',
                'marital_status',
                'has_allergies',
                'emergency_contact_name',
                'emergency_contact_phone',
                'emergency_contact_relationship',
                'emergency_contact_address',
                'emergency_contact_info',
                'notes',
                'preferences',
                'consent_marketing',
                'consent_data_processing',
                'is_active',
                'created_at',
                'updated_at'
            ]
        ]);
    }
}
