<?php

namespace Database\Factories;

use App\Models\AppointmentStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\AppointmentStatus>
 */
class AppointmentStatusFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = AppointmentStatus::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // El estado genérico no debe competir con los estados canónicos
            // creados explícitamente por cada prueba mediante sus states.
            'name' => 'test_'.$this->faker->unique()->bothify('????????????'),
            'display_name' => $this->faker->words(2, true),
            'description' => $this->faker->optional(0.7)->sentence(),
            'color' => $this->faker->hexColor(),
            'is_active' => $this->faker->boolean(90), // 90% probability of being active
        ];
    }

    /**
     * Indicate that the status is scheduled.
     */
    public function scheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'scheduled',
            'display_name' => 'Programada',
            'description' => 'Cita programada pero no confirmada',
            'color' => '#0d6efd',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the status is confirmed.
     */
    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'confirmed',
            'display_name' => 'Confirmada',
            'description' => 'Cita confirmada por el paciente',
            'color' => '#28a745',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the status is in progress.
     */
    public function inProgress(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'in_progress',
            'display_name' => 'En Progreso',
            'description' => 'Cita en curso',
            'color' => '#ffc107',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the status is completed.
     */
    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'completed',
            'display_name' => 'Completada',
            'description' => 'Cita completada exitosamente',
            'color' => '#6c757d',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the status is cancelled.
     */
    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'cancelled',
            'display_name' => 'Cancelada',
            'description' => 'Cita cancelada',
            'color' => '#dc3545',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the status is no show.
     */
    public function noShow(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'no_show',
            'display_name' => 'No se Presentó',
            'description' => 'Paciente no se presentó a la cita',
            'color' => '#fd7e14',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the status is rescheduled.
     */
    public function rescheduled(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'rescheduled',
            'display_name' => 'Reprogramada',
            'description' => 'Cita reprogramada para otra fecha',
            'color' => '#8b5cf6',
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the status is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }
}
