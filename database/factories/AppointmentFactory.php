<?php

namespace Database\Factories;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use App\Models\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Appointment>
 */
class AppointmentFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Appointment::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $appointmentDate = $this->faker->dateTimeBetween('now', '+3 months');
        $startTime = $this->faker->time('H:i');
        $duration = $this->faker->randomElement([30, 45, 60, 90, 120]);
        
        // Calcular end_time basado en start_time y duration
        $startDateTime = Carbon::parse($appointmentDate->format('Y-m-d') . ' ' . $startTime);
        $endDateTime = $startDateTime->copy()->addMinutes($duration);
        
        $appointmentTypes = [
            'consultation',
            'treatment',
            'cleaning',
            'emergency',
            'follow_up',
            'orthodontics',
            'whitening',
            'bridge',
            'implant',
            'extraction',
            'endodontics'
        ];

        $reasons = [
            'Routine dental checkup',
            'Tooth pain',
            'Dental cleaning',
            'Cavity treatment',
            'Tooth extraction',
            'Root canal treatment',
            'Braces adjustment',
            'Dental implant placement',
            'Oral surgery',
            'Emergency dental care',
            'Follow-up appointment',
            'Teeth whitening',
            'Crown placement',
            'Denture repair',
            'Cosmetic consultation'
        ];

        $treatmentPlans = [
            'Complete dental examination and cleaning',
            'Fill composite resin in affected tooth',
            'Extract wisdom tooth with local anesthesia',
            'Perform root canal treatment',
            'Install dental braces for orthodontic treatment',
            'Place dental implant in lower jaw',
            'Perform oral surgery for impacted tooth',
            'Apply professional teeth whitening',
            'Install dental crown on treated tooth',
            'Create and install dental bridge'
        ];

        return [
            'appointment_code' => 'APT-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'patient_id' => Patient::factory(),
            'staff_id' => Staff::factory(),
            'appointment_status_id' => AppointmentStatus::factory(),
            'appointment_date' => $appointmentDate->format('Y-m-d'),
            'start_time' => $startTime,
            'end_time' => $endDateTime->format('H:i'),
            'duration' => $duration,
            'type' => $this->faker->randomElement($appointmentTypes),
            'reason' => $this->faker->randomElement($reasons),
            'notes' => $this->faker->optional(0.7)->sentence(),
            'treatment_plan' => $this->faker->optional(0.6)->randomElement($treatmentPlans),
            'estimated_cost' => $this->faker->optional(0.8)->randomFloat(2, 50, 500),
            'is_urgent' => $this->faker->boolean(10), // 10% probability
            'is_follow_up' => $this->faker->boolean(15), // 15% probability
            'is_recurring' => $this->faker->boolean(5), // 5% probability
            'reminder_sent' => $this->faker->boolean(60), // 60% probability
            'parent_appointment_id' => null, // Will be set in follow_up state
            'confirmed_at' => $this->faker->optional(0.7)->dateTimeBetween('-1 week', 'now'),
            'cancelled_at' => null, // Will be set in cancelled state
            'cancellation_reason' => null, // Will be set in cancelled state
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the appointment is urgent.
     */
    public function urgent(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_urgent' => true,
            'type' => 'emergency',
            'reason' => 'Dental emergency - severe pain',
        ]);
    }

    /**
     * Indicate that the appointment is a follow-up.
     */
    public function followUp(): static
    {
        return $this->state(function (array $attributes) {
            $parentAppointment = Appointment::factory()->create();
            
            return [
                'is_follow_up' => true,
                'parent_appointment_id' => $parentAppointment->id,
                'type' => 'follow_up',
                'reason' => 'Follow-up appointment for previous treatment',
            ];
        });
    }

    /**
     * Indicate that the appointment is recurring.
     */
    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'type' => 'cleaning',
            'reason' => 'Regular dental cleaning',
        ]);
    }

    /**
     * Indicate that the appointment is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'reminder_sent' => true,
        ]);
    }

    /**
     * Indicate that the appointment is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'cancelled_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'cancellation_reason' => $this->faker->randomElement([
                'Patient request',
                'Doctor unavailable',
                'Emergency situation',
                'Weather conditions',
                'Rescheduled by patient'
            ]),
        ]);
    }

    /**
     * Indicate that the appointment is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'confirmed_at' => $this->faker->dateTimeBetween('-1 week', 'now'),
            'appointment_date' => $this->faker->dateTimeBetween('-1 month', '-1 week'),
            'start_time' => $this->faker->time('H:i'),
        ]);
    }

    /**
     * Indicate that the appointment is scheduled for today.
     */
    public function today(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => Carbon::today()->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the appointment is scheduled for tomorrow.
     */
    public function tomorrow(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => Carbon::tomorrow()->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the appointment is scheduled for this week.
     */
    public function thisWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => $this->faker->dateTimeBetween(Carbon::now(), Carbon::now()->addWeek())->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the appointment is scheduled for next week.
     */
    public function nextWeek(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => $this->faker->dateTimeBetween(Carbon::now()->addWeek(), Carbon::now()->addWeeks(2))->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the appointment is scheduled for this month.
     */
    public function thisMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => $this->faker->dateTimeBetween(Carbon::now(), Carbon::now()->addMonth())->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the appointment is scheduled for next month.
     */
    public function nextMonth(): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_date' => $this->faker->dateTimeBetween(Carbon::now()->addMonth(), Carbon::now()->addMonths(2))->format('Y-m-d'),
        ]);
    }

    /**
     * Indicate that the appointment has a high estimated cost.
     */
    public function expensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_cost' => $this->faker->randomFloat(2, 300, 1000),
            'type' => $this->faker->randomElement(['implant', 'orthodontics', 'surgery']),
        ]);
    }

    /**
     * Indicate that the appointment has a low estimated cost.
     */
    public function inexpensive(): static
    {
        return $this->state(fn (array $attributes) => [
            'estimated_cost' => $this->faker->randomFloat(2, 50, 150),
            'type' => $this->faker->randomElement(['consultation', 'cleaning', 'follow_up']),
        ]);
    }

    /**
     * Indicate that the appointment is for a specific type.
     */
    public function ofType(string $type): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => $type,
        ]);
    }

    /**
     * Indicate that the appointment is for a specific patient.
     */
    public function forPatient(Patient $patient): static
    {
        return $this->state(fn (array $attributes) => [
            'patient_id' => $patient->id,
        ]);
    }

    /**
     * Indicate that the appointment is with a specific staff member.
     */
    public function withStaff(Staff $staff): static
    {
        return $this->state(fn (array $attributes) => [
            'staff_id' => $staff->id,
        ]);
    }

    /**
     * Indicate that the appointment has a specific status.
     */
    public function withStatus(AppointmentStatus $status): static
    {
        return $this->state(fn (array $attributes) => [
            'appointment_status_id' => $status->id,
        ]);
    }

    /**
     * Indicate that the appointment was created by a specific user.
     */
    public function createdBy(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'created_by' => $user->id,
        ]);
    }
}