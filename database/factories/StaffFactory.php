<?php

namespace Database\Factories;

use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Staff>
 */
class StaffFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'employee_id' => 'EMP-'.$this->faker->unique()->numerify('######'),
            'specialty' => $this->faker->randomElement([
                'Odontología General', 'Ortodoncia', 'Endodoncia', 'Periodoncia',
            ]),
            'license_number' => 'LIC-'.$this->faker->unique()->numerify('######'),
            'license_expiry' => $this->faker->dateTimeBetween('+1 year', '+8 years'),
            'university' => $this->faker->company(),
            'graduation_year' => $this->faker->numberBetween(1995, 2025),
            'consultation_fee' => $this->faker->randomFloat(2, 20, 250),
            'experience_years' => $this->faker->numberBetween(0, 30),
            'languages' => ['Español'],
            'certifications' => [],
            'is_available' => true,
            'is_active' => true,
        ];
    }

    public function forClinic(ClinicContext|Clinic|int $clinic): static
    {
        $clinicId = $clinic instanceof ClinicContext ? $clinic->clinicId
            : ($clinic instanceof Clinic ? (int) $clinic->getKey() : $clinic);

        return $this->state(fn (array $attributes): array => ['clinic_id' => $clinicId]);
    }
}
