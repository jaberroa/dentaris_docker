<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Models\AppointmentStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Carbon\Carbon;

class AppointmentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Crear usuario autenticado
        $this->user = User::factory()->create();
        $this->actingAs($this->user);
        
        // Crear datos de prueba necesarios
        $this->patient = Patient::factory()->create();
        $this->staff = Staff::factory()->create();
        $this->status = AppointmentStatus::factory()->create(['name' => 'scheduled']);
    }

    /** @test */
    public function it_can_display_appointments_index()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('appointments.index'));

        $response->assertStatus(200);
        $response->assertViewIs('appointments.index');
        $response->assertViewHas('appointments');
        $response->assertSee($appointment->appointment_code);
    }

    /** @test */
    public function it_can_display_create_appointment_form()
    {
        $response = $this->get(route('appointments.create'));

        $response->assertStatus(200);
        $response->assertViewIs('appointments.create');
    }

    /** @test */
    public function it_can_store_a_new_appointment()
    {
        $appointmentData = [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
            'end_time' => '10:00',
            'duration' => 60,
            'type' => 'consultation',
            'reason' => 'Routine checkup',
            'notes' => 'First visit',
            'is_urgent' => false,
            'is_follow_up' => false,
            'is_recurring' => false,
            'reminder_sent' => false,
        ];

        $response = $this->post(route('appointments.store'), $appointmentData);

        $response->assertRedirect(route('appointments.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('appointments', [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'type' => 'consultation',
        ]);
    }

    /** @test */
    public function it_validates_required_fields_when_storing()
    {
        $response = $this->post(route('appointments.store'), []);

        $response->assertSessionHasErrors([
            'patient_id',
            'staff_id',
            'appointment_status_id',
            'appointment_date',
            'start_time',
            'duration',
            'type'
        ]);
    }

    /** @test */
    public function it_validates_appointment_date_is_not_in_past()
    {
        $appointmentData = [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'appointment_date' => Carbon::yesterday()->format('Y-m-d'),
            'start_time' => '09:00',
            'duration' => 60,
            'type' => 'consultation',
        ];

        $response = $this->post(route('appointments.store'), $appointmentData);

        $response->assertSessionHasErrors(['appointment_date']);
    }

    /** @test */
    public function it_validates_duration_is_within_limits()
    {
        $appointmentData = [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '09:00',
            'duration' => 5, // Too short
            'type' => 'consultation',
        ];

        $response = $this->post(route('appointments.store'), $appointmentData);

        $response->assertSessionHasErrors(['duration']);
    }

    /** @test */
    public function it_can_display_appointment_details()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('appointments.show', $appointment));

        $response->assertStatus(200);
        $response->assertViewIs('appointments.show');
        $response->assertViewHas('appointment', $appointment);
        $response->assertSee($appointment->appointment_code);
    }

    /** @test */
    public function it_can_display_edit_appointment_form()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('appointments.edit', $appointment));

        $response->assertStatus(200);
        $response->assertViewIs('appointments.edit');
        $response->assertViewHas('appointment', $appointment);
    }

    /** @test */
    public function it_can_update_an_appointment()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
            'type' => 'consultation',
        ]);

        $updateData = [
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
            'start_time' => '10:00',
            'end_time' => '11:00',
            'duration' => 60,
            'type' => 'treatment',
            'reason' => 'Updated reason',
            'notes' => 'Updated notes',
        ];

        $response = $this->put(route('appointments.update', $appointment), $updateData);

        $response->assertRedirect(route('appointments.show', $appointment));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'type' => 'treatment',
            'reason' => 'Updated reason',
        ]);
    }

    /** @test */
    public function it_can_delete_an_appointment()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->delete(route('appointments.destroy', $appointment));

        $response->assertRedirect(route('appointments.index'));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseMissing('appointments', [
            'id' => $appointment->id,
        ]);
    }

    /** @test */
    public function it_can_display_weekly_view()
    {
        $response = $this->get(route('appointments.weekly'));

        $response->assertStatus(200);
        $response->assertViewIs('appointments.weekly');
        $response->assertViewHas(['startOfWeek', 'endOfWeek', 'weekDays']);
    }

    /** @test */
    public function it_can_display_monthly_view()
    {
        $response = $this->get(route('appointments.monthly'));

        $response->assertStatus(200);
        $response->assertViewIs('appointments.monthly');
        $response->assertViewHas(['startOfMonth', 'calendarDays']);
    }

    /** @test */
    public function it_can_display_yearly_view()
    {
        $response = $this->get(route('appointments.yearly'));

        $response->assertStatus(200);
        $response->assertViewIs('appointments.yearly');
        $response->assertViewHas(['year', 'months']);
    }

    /** @test */
    public function it_can_confirm_an_appointment()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->post(route('appointments.confirm', $appointment));

        $response->assertRedirect(route('appointments.show', $appointment));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'confirmed_at' => now()->format('Y-m-d H:i:s'),
        ]);
    }

    /** @test */
    public function it_can_cancel_an_appointment()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $cancelData = [
            'cancellation_reason' => 'Patient request'
        ];

        $response = $this->post(route('appointments.cancel', $appointment), $cancelData);

        $response->assertRedirect(route('appointments.show', $appointment));
        $response->assertSessionHas('success');
        
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'cancelled_at' => now()->format('Y-m-d H:i:s'),
            'cancellation_reason' => 'Patient request',
        ]);
    }

    /** @test */
    public function it_can_update_appointment_status()
    {
        $newStatus = AppointmentStatus::factory()->create(['name' => 'confirmed']);
        
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->patch(route('appointments.update.status', $appointment), [
            'status_id' => $newStatus->id
        ]);

        $response->assertJson([
            'success' => true,
            'message' => 'Estado actualizado correctamente'
        ]);
        
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'appointment_status_id' => $newStatus->id,
        ]);
    }

    /** @test */
    public function it_can_search_appointments()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
            'type' => 'consultation',
        ]);

        $response = $this->get(route('appointments.index', ['search' => $this->patient->first_name]));

        $response->assertStatus(200);
        $response->assertViewHas('appointments');
    }

    /** @test */
    public function it_can_filter_appointments_by_status()
    {
        $confirmedStatus = AppointmentStatus::factory()->create(['name' => 'confirmed']);
        
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $confirmedStatus->id,
            'created_by' => $this->user->id,
        ]);

        $response = $this->get(route('appointments.index', ['status' => 'confirmed']));

        $response->assertStatus(200);
        $response->assertViewHas('appointments');
    }

    /** @test */
    public function it_can_filter_appointments_by_date_range()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'created_by' => $this->user->id,
            'appointment_date' => Carbon::tomorrow(),
        ]);

        $response = $this->get(route('appointments.index', [
            'created_from' => Carbon::today()->format('Y-m-d'),
            'created_to' => Carbon::tomorrow()->format('Y-m-d')
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('appointments');
    }

    /** @test */
    public function it_requires_authentication_to_access_appointments()
    {
        auth()->logout();

        $response = $this->get(route('appointments.index'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_can_sort_appointments()
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

        $response = $this->get(route('appointments.index', [
            'sort' => 'appointment_date',
            'direction' => 'asc'
        ]));

        $response->assertStatus(200);
        $response->assertViewHas('appointments');
    }
}

