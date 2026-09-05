<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Models\AppointmentStatus;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;
use Tests\Concerns\InteractsWithClinicalContext;

class AppointmentApiTest extends TestCase
{
    use InteractsWithClinicalContext;
    use RefreshDatabase;

    private ClinicContext $clinicContext;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario autenticado
        $this->user = User::factory()->create(['is_active' => true]);
        $this->clinicContext = $this->clinicalContextFor($this->user, ['view_appointments', 'manage_appointments']);
        $this->actingAs($this->user, 'sanctum');
        $this->withHeader('X-Clinic-Id', (string) $this->clinicContext->clinicId);
        
        // Crear datos de prueba necesarios
        $this->patient = Patient::factory()->forClinic($this->clinicContext)->create(['created_by' => $this->user->id]);
        $this->staff = Staff::factory()->forClinic($this->clinicContext)->create();
        $this->status = AppointmentStatus::factory()->create(['name' => 'scheduled']);
    }

    /** @test */
    public function it_can_get_appointments_list_via_api()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/appointments');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'appointment_code',
                                'appointment_date',
                                'start_time',
                                'end_time',
                                'type',
                                'patient',
                                'staff',
                                'status'
                            ]
                        ],
                        'links',
                        'meta'
                    ]
                ]);
    }

    /** @test */
    public function it_can_create_appointment_via_api()
    {
        $appointmentData = [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
            'duration' => 60,
            'type' => 'consultation',
            'reason' => 'Routine checkup',
            'notes' => 'First visit',
        ];

        $response = $this->postJson('/api/appointments', $appointmentData);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'appointment_code',
                        'appointment_date',
                        'start_time',
                        'end_time',
                        'type',
                        'patient',
                        'staff',
                        'status'
                    ]
                ]);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'type' => 'consultation',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_creating_via_api()
    {
        $response = $this->postJson('/api/appointments', []);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ])
                ->assertJsonValidationErrors([
                    'patient_id',
                    'staff_id',
                    'appointment_date',
                    'start_time',
                    'duration',
                    'type'
                ]);
    }

    /** @test */
    public function it_validates_appointment_date_is_not_in_past_via_api()
    {
        $appointmentData = [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_date' => Carbon::yesterday()->format('Y-m-d'),
            'start_time' => '09:00',
            'duration' => 60,
            'type' => 'consultation',
        ];

        $response = $this->postJson('/api/appointments', $appointmentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['appointment_date']);
    }

    /** @test */
    public function it_validates_duration_limits_via_api()
    {
        $appointmentData = [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
            'duration' => 5, // Too short
            'type' => 'consultation',
        ];

        $response = $this->postJson('/api/appointments', $appointmentData);

        $response->assertStatus(422)
                ->assertJsonValidationErrors(['duration']);
    }

    /** @test */
    public function it_can_get_single_appointment_via_api()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data' => [
                        'id',
                        'appointment_code',
                        'appointment_date',
                        'start_time',
                        'end_time',
                        'type',
                        'patient',
                        'staff',
                        'status'
                    ]
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'id' => $appointment->id,
                        'appointment_code' => $appointment->appointment_code,
                    ]
                ]);
    }

    /** @test */
    public function it_can_update_appointment_via_api()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
            'type' => 'consultation',
        ]);

        $updateData = [
            'type' => 'treatment',
            'reason' => 'Updated reason',
            'notes' => 'Updated notes',
        ];

        $response = $this->putJson("/api/appointments/{$appointment->id}", $updateData);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'data'
                ])
                ->assertJson([
                    'success' => true,
                    'data' => [
                        'type' => 'treatment',
                        'reason' => 'Updated reason',
                    ]
                ]);

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'type' => 'treatment',
            'reason' => 'Updated reason',
        ]);
    }

    /** @test */
    public function it_can_delete_appointment_via_api()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->deleteJson("/api/appointments/{$appointment->id}");

        $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'Appointment deleted successfully'
                ]);

        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);
    }

    /** @test */
    public function it_can_filter_appointments_by_date_via_api()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
            'appointment_date' => Carbon::tomorrow(),
        ]);

        $response = $this->getJson('/api/appointments?date=' . Carbon::tomorrow()->format('Y-m-d'));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'appointment_date'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_appointments_by_staff_via_api()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/appointments?staff_id={$this->staff->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'staff'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_appointments_by_patient_via_api()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson("/api/appointments?patient_id={$this->patient->id}");

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'patient'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_filter_appointments_by_status_via_api()
    {
        $confirmedStatus = AppointmentStatus::factory()->create(['name' => 'confirmed']);
        
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $confirmedStatus->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/appointments?status=confirmed');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'status'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_sort_appointments_via_api()
    {
        $appointment1 = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
            'appointment_date' => Carbon::tomorrow(),
        ]);

        $appointment2 = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
            'appointment_date' => Carbon::tomorrow()->addDay(),
        ]);

        $response = $this->getJson('/api/appointments?sort_by=appointment_date&sort_order=asc');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'appointment_date'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_can_paginate_appointments_via_api()
    {
        // Crear múltiples citas
        Appointment::factory()->count(20)->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->getJson('/api/appointments?per_page=10');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data',
                        'links' => [
                            'first',
                            'last',
                            'prev',
                            'next'
                        ],
                        'meta' => [
                            'current_page',
                            'last_page',
                            'per_page',
                            'total'
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_returns_404_for_nonexistent_appointment_via_api()
    {
        $response = $this->getJson('/api/appointments/99999');

        $response->assertStatus(404)
                ->assertJson([
                    'success' => false,
                    'message' => 'Appointment not found'
                ]);
    }

    /** @test */
    public function it_requires_authentication_for_api_access()
    {
        auth()->logout();

        $response = $this->getJson('/api/appointments');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_search_staff_for_appointments_via_api()
    {
        $response = $this->getJson('/api/appointments/search-staff?search=doctor');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'success',
                    'data' => [
                        'data' => [
                            '*' => [
                                'id',
                                'display_name',
                                'specialty'
                            ]
                        ]
                    ]
                ]);
    }

    /** @test */
    public function it_handles_server_errors_gracefully_via_api()
    {
        // Simular error del servidor
        $this->withoutExceptionHandling();
        
        $response = $this->postJson('/api/appointments', [
            'patient_id' => 99999, // ID inexistente
            'staff_id' => $this->staff->id,
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
            'duration' => 60,
            'type' => 'consultation',
        ]);

        $response->assertStatus(422)
                ->assertJsonStructure([
                    'success',
                    'message',
                    'errors'
                ]);
    }
}

