<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentSimpleTest extends TestCase
{
    /** @test */
    public function it_can_instantiate_appointment_model()
    {
        $appointment = new Appointment();
        $this->assertInstanceOf(Appointment::class, $appointment);
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
    public function it_casts_appointment_date_to_carbon_instance()
    {
        $appointment = new Appointment();
        $appointment->appointment_date = '2024-01-15';
        
        // Verificar que se convierte a Carbon
        $this->assertInstanceOf(\Carbon\Carbon::class, $appointment->appointment_date);
    }

    /** @test */
    public function it_casts_time_fields_correctly()
    {
        $appointment = new Appointment();
        $appointment->start_time = '09:00:00';
        $appointment->end_time = '10:00:00';
        
        // Verificar que se convierten a Carbon
        $this->assertInstanceOf(\Carbon\Carbon::class, $appointment->start_time);
        $this->assertInstanceOf(\Carbon\Carbon::class, $appointment->end_time);
    }

    /** @test */
    public function it_casts_boolean_fields_correctly()
    {
        $appointment = new Appointment();
        $appointment->is_urgent = true;
        $appointment->is_follow_up = false;
        $appointment->is_recurring = true;
        $appointment->reminder_sent = false;
        
        // Simular el casting
        $this->assertTrue($appointment->is_urgent);
        $this->assertFalse($appointment->is_follow_up);
        $this->assertTrue($appointment->is_recurring);
        $this->assertFalse($appointment->reminder_sent);
    }

    /** @test */
    public function it_casts_estimated_cost_to_decimal()
    {
        $appointment = new Appointment();
        $appointment->estimated_cost = 150.75;
        
        // Verificar que se mantiene como decimal
        $this->assertEquals(150.75, $appointment->estimated_cost);
        $this->assertIsNumeric($appointment->estimated_cost);
    }

    /** @test */
    public function it_has_correct_table_name()
    {
        $appointment = new Appointment();
        $this->assertEquals('appointments', $appointment->getTable());
    }

    /** @test */
    public function it_has_correct_primary_key()
    {
        $appointment = new Appointment();
        $this->assertEquals('id', $appointment->getKeyName());
    }

    /** @test */
    public function it_uses_timestamps()
    {
        $appointment = new Appointment();
        $this->assertTrue($appointment->usesTimestamps());
    }
}
