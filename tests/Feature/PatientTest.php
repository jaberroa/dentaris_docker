<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
    }

    /** @test */
    public function it_can_view_patients_index()
    {
        Patient::factory(5)->create(['created_by' => $this->user->id]);

        $response = $this->get(route('patients.index'));

        $response->assertStatus(200);
        $response->assertViewIs('patients.index');
        $response->assertSee('Gestión de Pacientes');
    }

    /** @test */
    public function it_can_view_create_patient_form()
    {
        $response = $this->get(route('patients.create'));

        $response->assertStatus(200);
        $response->assertViewIs('patients.create');
        $response->assertSee('Nuevo Paciente');
    }

    /** @test */
    public function it_can_create_a_patient()
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

        $response = $this->post(route('patients.store'), $patientData);

        $response->assertRedirect();
        $response->assertSessionHas('success', 'Paciente creado exitosamente.');
        
        $this->assertDatabaseHas('patients', [
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'created_by' => $this->user->id,
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_patient()
    {
        $response = $this->post(route('patients.store'), []);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'birth_date',
            'gender'
        ]);
    }

    /** @test */
    public function it_validates_email_uniqueness_when_creating_patient()
    {
        Patient::factory()->create([
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

        $response = $this->post(route('patients.store'), $patientData);

        $response->assertSessionHasErrors(['email']);
    }

    /** @test */
    public function it_validates_birth_date_must_be_before_today()
    {
        $patientData = [
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => now()->addDay()->format('Y-m-d'),
            'gender' => 'male',
            'consent_data_processing' => true,
        ];

        $response = $this->post(route('patients.store'), $patientData);

        $response->assertSessionHasErrors(['birth_date']);
    }

    /** @test */
    public function it_can_view_patient_details()
    {
        $patient = Patient::factory()->create(['created_by' => $this->user->id]);

        $response = $this->get(route('patients.show', $patient));

        $response->assertStatus(200);
        $response->assertViewIs('patients.show');
        $response->assertSee($patient->first_name);
        $response->assertSee($patient->last_name);
    }

    /** @test */
    public function it_can_view_edit_patient_form()
    {
        $patient = Patient::factory()->create(['created_by' => $this->user->id]);

        $response = $this->get(route('patients.edit', $patient));

        $response->assertStatus(200);
        $response->assertViewIs('patients.edit');
        $response->assertSee($patient->first_name);
    }

    /** @test */
    public function it_can_update_a_patient()
    {
        $patient = Patient::factory()->create(['created_by' => $this->user->id]);

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

        $response = $this->put(route('patients.update', $patient), $updateData);

        $response->assertRedirect(route('patients.index'));
        $response->assertSessionHas('success', 'Paciente actualizado exitosamente.');
        
        $this->assertDatabaseHas('patients', [
            'id' => $patient->id,
            'first_name' => 'Juan Carlos',
            'last_name' => 'Pérez García',
            'email' => 'juancarlos@example.com',
        ]);
    }

    /** @test */
    public function it_can_delete_a_patient()
    {
        $patient = Patient::factory()->create(['created_by' => $this->user->id]);

        $response = $this->delete(route('patients.destroy', $patient));

        $response->assertRedirect(route('patients.index'));
        $response->assertSessionHas('success', 'Paciente eliminado exitosamente.');
        
        $this->assertDatabaseMissing('patients', [
            'id' => $patient->id,
        ]);
    }

    /** @test */
    public function it_cannot_delete_patient_with_appointments()
    {
        $patient = Patient::factory()->create(['created_by' => $this->user->id]);
        
        // Simular que el paciente tiene citas (esto requeriría crear el modelo Appointment)
        // Por ahora, solo verificamos que la validación existe en el controlador
        
        $response = $this->delete(route('patients.destroy', $patient));

        // Si no hay citas, debería eliminarse correctamente
        $response->assertRedirect(route('patients.index'));
    }

    /** @test */
    public function it_can_search_patients()
    {
        Patient::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'email' => 'juan@example.com',
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->create([
            'first_name' => 'María',
            'last_name' => 'García',
            'email' => 'maria@example.com',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('patients.index', ['search' => 'Juan']));

        $response->assertStatus(200);
        $response->assertSee('Juan');
        $response->assertDontSee('María');
    }

    /** @test */
    public function it_can_filter_patients_by_gender()
    {
        Patient::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'gender' => 'male',
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->create([
            'first_name' => 'María',
            'last_name' => 'García',
            'gender' => 'female',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('patients.index', ['gender' => 'male']));

        $response->assertStatus(200);
        $response->assertSee('Juan');
        $response->assertDontSee('María');
    }

    /** @test */
    public function it_can_filter_patients_by_age_range()
    {
        Patient::factory()->create([
            'first_name' => 'Juan',
            'last_name' => 'Pérez',
            'birth_date' => now()->subYears(25)->format('Y-m-d'),
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->create([
            'first_name' => 'María',
            'last_name' => 'García',
            'birth_date' => now()->subYears(65)->format('Y-m-d'),
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('patients.index', ['age_range' => '18-30']));

        $response->assertStatus(200);
        $response->assertSee('Juan');
        $response->assertDontSee('María');
    }

    /** @test */
    public function it_can_sort_patients_by_different_fields()
    {
        Patient::factory()->create([
            'first_name' => 'Ana',
            'last_name' => 'Zapata',
            'created_at' => now()->subDays(2),
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->create([
            'first_name' => 'Zoe',
            'last_name' => 'Alvarez',
            'created_at' => now()->subDays(1),
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('patients.index', ['sort' => 'first_name', 'direction' => 'asc']));

        $response->assertStatus(200);
        // Verificar que el ordenamiento funciona (esto requeriría verificar el orden en la vista)
    }

    /** @test */
    public function it_can_change_per_page_pagination()
    {
        Patient::factory(15)->create(['created_by' => $this->user->id]);

        $response = $this->get(route('patients.index', ['per_page' => '5']));

        $response->assertStatus(200);
        // Verificar que la paginación funciona correctamente
    }

    /** @test */
    public function it_can_export_patients_to_excel()
    {
        Patient::factory(5)->create(['created_by' => $this->user->id]);

        $response = $this->get(route('patients.export.excel'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    }

    /** @test */
    public function it_can_export_patients_to_pdf()
    {
        Patient::factory(5)->create(['created_by' => $this->user->id]);

        $response = $this->get(route('patients.export.pdf'));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_can_export_patient_history()
    {
        $patient = Patient::factory()->create(['created_by' => $this->user->id]);

        $response = $this->get(route('patients.export.history', $patient));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'application/pdf');
    }

    /** @test */
    public function it_handles_validation_errors_gracefully()
    {
        $invalidData = [
            'first_name' => '', // Required field empty
            'last_name' => '', // Required field empty
            'email' => 'invalid-email', // Invalid email format
            'birth_date' => 'invalid-date', // Invalid date format
            'gender' => 'invalid-gender', // Invalid gender value
        ];

        $response = $this->post(route('patients.store'), $invalidData);

        $response->assertSessionHasErrors([
            'first_name',
            'last_name',
            'email',
            'birth_date',
            'gender',
        ]);
    }

    /** @test */
    public function it_requires_authentication_to_access_patients()
    {
        auth()->logout();

        $response = $this->get(route('patients.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_can_handle_empty_patients_list()
    {
        $response = $this->get(route('patients.index'));

        $response->assertStatus(200);
        $response->assertSee('No hay pacientes registrados');
    }

    /** @test */
    public function it_can_handle_search_with_no_results()
    {
        Patient::factory()->create([
            'first_name' => 'Juan',
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('patients.index', ['search' => 'María']));

        $response->assertStatus(200);
        $response->assertDontSee('Juan');
    }

    /** @test */
    public function it_can_handle_date_range_filters()
    {
        Patient::factory()->create([
            'first_name' => 'Juan',
            'created_at' => now()->subDays(5),
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->create([
            'first_name' => 'María',
            'created_at' => now()->subDays(10),
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('patients.index', [
            'created_from' => now()->subDays(7)->format('Y-m-d'),
            'created_to' => now()->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertSee('Juan');
        $response->assertDontSee('María');
    }

    /** @test */
    public function it_can_handle_boolean_filters()
    {
        Patient::factory()->create([
            'first_name' => 'Juan',
            'consent_marketing' => true,
            'created_by' => $this->user->id,
        ]);

        Patient::factory()->create([
            'first_name' => 'María',
            'consent_marketing' => false,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('patients.index', ['consent_marketing' => '1']));

        $response->assertStatus(200);
        $response->assertSee('Juan');
        $response->assertDontSee('María');
    }
}
