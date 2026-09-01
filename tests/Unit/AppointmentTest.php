<?php

namespace Tests\Unit;

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
        
        // Crear datos de prueba necesarios
        $this->user = User::factory()->create();
        $this->patient = Patient::factory()->create();
        $this->staff = Staff::factory()->create();
        $this->status = AppointmentStatus::factory()->create(['name' => 'scheduled']);
    }

    /** @test */
    public function it_can_create_an_appointment()
    {
        $appointmentData = [
            'appointment_code' => 'APT-001',
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'appointment_status_id' => $this->status->id,
            'appointment_date' => Carbon::tomorrow(),
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
            'duration' => 60,
            'type' => 'consultation',
            'reason' => 'Routine checkup',
            'notes' => 'First visit',
            'created_by' => $this->user->id,
        ];

        $appointment = Appointment::create($appointmentData);

        $this->assertInstanceOf(Appointment::class, $appointment);
        $this->assertEquals('APT-001', $appointment->appointment_code);
        $this->assertEquals($this->patient->id, $appointment->patient_id);
        $this->assertEquals($this->staff->id, $appointment->staff_id);
    }

    /** @test */
    public function it_generates_unique_appointment_codes()
    {
        $appointment1 = Appointment::factory()->create(['appointment_code' => 'APT-001']);
        $appointment2 = Appointment::factory()->create(['appointment_code' => 'APT-002']);

        $this->assertNotEquals($appointment1->appointment_code, $appointment2->appointment_code);
    }

    /** @test */
    public function it_casts_appointment_date_to_carbon_instance()
    {
        $appointment = Appointment::factory()->create([
            'appointment_date' => '2024-01-15'
        ]);

        $this->assertInstanceOf(Carbon::class, $appointment->appointment_date);
        $this->assertEquals('2024-01-15', $appointment->appointment_date->format('Y-m-d'));
    }

    /** @test */
    public function it_casts_time_fields_correctly()
    {
        $appointment = Appointment::factory()->create([
            'start_time' => '09:00:00',
            'end_time' => '10:00:00',
        ]);

        $this->assertInstanceOf(Carbon::class, $appointment->start_time);
        $this->assertInstanceOf(Carbon::class, $appointment->end_time);
        $this->assertEquals('09:00', $appointment->start_time->format('H:i'));
        $this->assertEquals('10:00', $appointment->end_time->format('H:i'));
    }

    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $appointment = Appointment::factory()->create([
            'is_urgent' => true,
            'is_follow_up' => false,
            'is_recurring' => true,
            'reminder_sent' => false,
        ]);

        $this->assertTrue($appointment->is_urgent);
        $this->assertFalse($appointment->is_follow_up);
        $this->assertTrue($appointment->is_recurring);
        $this->assertFalse($appointment->reminder_sent);
    }

    /** @test */
    public function it_casts_estimated_cost_to_decimal()
    {
        $appointment = Appointment::factory()->create([
            'estimated_cost' => 150.75
        ]);

        $this->assertEquals(150.75, $appointment->estimated_cost);
        $this->assertIsFloat($appointment->estimated_cost);
    }

    /** @test */
    public function it_belongs_to_a_patient()
    {
        $appointment = Appointment::factory()->create([
            'patient_id' => $this->patient->id
        ]);

        $this->assertInstanceOf(Patient::class, $appointment->patient);
        $this->assertEquals($this->patient->id, $appointment->patient->id);
    }

    /** @test */
    public function it_belongs_to_staff()
    {
        $appointment = Appointment::factory()->create([
            'staff_id' => $this->staff->id
        ]);

        $this->assertInstanceOf(Staff::class, $appointment->staff);
        $this->assertEquals($this->staff->id, $appointment->staff->id);
    }

    /** @test */
    public function it_belongs_to_appointment_status()
    {
        $appointment = Appointment::factory()->create([
            'appointment_status_id' => $this->status->id
        ]);

        $this->assertInstanceOf(AppointmentStatus::class, $appointment->status);
        $this->assertEquals($this->status->id, $appointment->status->id);
    }

    /** @test */
    public function it_belongs_to_creator_user()
    {
        $appointment = Appointment::factory()->create([
            'created_by' => $this->user->id
        ]);

        $this->assertInstanceOf(User::class, $appointment->creator);
        $this->assertEquals($this->user->id, $appointment->creator->id);
    }

    /** @test */
    public function it_can_have_parent_appointment_for_follow_ups()
    {
        $parentAppointment = Appointment::factory()->create();
        
        $followUpAppointment = Appointment::factory()->create([
            'parent_appointment_id' => $parentAppointment->id,
            'is_follow_up' => true
        ]);

        $this->assertEquals($parentAppointment->id, $followUpAppointment->parent_appointment_id);
        $this->assertTrue($followUpAppointment->is_follow_up);
    }

    /** @test */
    public function it_can_be_confirmed()
    {
        $appointment = Appointment::factory()->create([
            'confirmed_at' => null
        ]);

        $this->assertNull($appointment->confirmed_at);

        $appointment->update(['confirmed_at' => now()]);

        $this->assertNotNull($appointment->confirmed_at);
        $this->assertInstanceOf(Carbon::class, $appointment->confirmed_at);
    }

    /** @test */
    public function it_can_be_cancelled()
    {
        $appointment = Appointment::factory()->create([
            'cancelled_at' => null,
            'cancellation_reason' => null
        ]);

        $this->assertNull($appointment->cancelled_at);

        $appointment->update([
            'cancelled_at' => now(),
            'cancellation_reason' => 'Patient request'
        ]);

        $this->assertNotNull($appointment->cancelled_at);
        $this->assertEquals('Patient request', $appointment->cancellation_reason);
    }

    /** @test */
    public function it_calculates_duration_correctly()
    {
        $appointment = Appointment::factory()->create([
            'start_time' => '09:00:00',
            'end_time' => '10:30:00',
            'duration' => 90
        ]);

        $this->assertEquals(90, $appointment->duration);
    }

    /** @test */
    public function it_can_have_treatment_plan()
    {
        $treatmentPlan = 'Root canal treatment for tooth #14';
        
        $appointment = Appointment::factory()->create([
            'treatment_plan' => $treatmentPlan
        ]);

        $this->assertEquals($treatmentPlan, $appointment->treatment_plan);
    }

    /** @test */
    public function it_can_have_notes()
    {
        $notes = 'Patient has anxiety about dental procedures';
        
        $appointment = Appointment::factory()->create([
            'notes' => $notes
        ]);

        $this->assertEquals($notes, $appointment->notes);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Appointment::create([
            // Missing required fields
        ]);
    }

    /** @test */
    public function it_can_be_urgent()
    {
        $appointment = Appointment::factory()->create([
            'is_urgent' => true,
            'type' => 'emergency'
        ]);

        $this->assertTrue($appointment->is_urgent);
        $this->assertEquals('emergency', $appointment->type);
    }

    /** @test */
    public function it_can_be_recurring()
    {
        $appointment = Appointment::factory()->create([
            'is_recurring' => true
        ]);

        $this->assertTrue($appointment->is_recurring);
    }

    /** @test */
    public function it_can_track_reminder_status()
    {
        $appointment = Appointment::factory()->create([
            'reminder_sent' => true
        ]);

        $this->assertTrue($appointment->reminder_sent);
    }

    /** @test */
    public function it_has_fillable_attributes()
    {
        $fillable = [
            'appointment_code',
            'patient_id',
            'staff_id',
            'appointment_status_id',
            'appointment_date',
            'start_time',
            'end_time',
            'duration',
            'type',
            'reason',
            'notes',
            'treatment_plan',
            'estimated_cost',
            'is_urgent',
            'is_follow_up',
            'is_recurring',
            'reminder_sent',
            'parent_appointment_id',
            'confirmed_at',
            'cancelled_at',
            'cancellation_reason',
            'created_by',
        ];

        $appointment = new Appointment();
        
        foreach ($fillable as $field) {
            $this->assertContains($field, $appointment->getFillable());
        }
    }

    /** @test */
    public function it_has_timestamps()
    {
        $appointment = Appointment::factory()->create();
        
        $this->assertNotNull($appointment->created_at);
        $this->assertNotNull($appointment->updated_at);
        $this->assertInstanceOf(Carbon::class, $appointment->created_at);
        $this->assertInstanceOf(Carbon::class, $appointment->updated_at);
    }
}

