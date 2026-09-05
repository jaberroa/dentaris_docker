<?php

namespace Database\Factories;

use App\Models\Patient;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Patient>
 */
class PatientFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Patient::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $firstName = $this->faker->firstName();
        $lastName = $this->faker->lastName();
        $gender = $this->faker->randomElement(['male', 'female', 'other']);
        
        // Generar fecha de nacimiento entre 18 y 80 años
        $birthDate = $this->faker->dateTimeBetween('-80 years', '-18 years');
        
        // Los estados de un lote se construyen antes de persistirse. Un número
        // Faker único evita que dos pacientes con las mismas iniciales reciban
        // el mismo código durante esa preparación.
        $initials = Str::upper(Str::substr($firstName, 0, 1).Str::substr($lastName, 0, 1));
        $patientCode = $initials.str_pad(
            (string) $this->faker->unique()->numberBetween(1, 99999),
            5,
            '0',
            STR_PAD_LEFT
        );
        
        return [
            'patient_code' => $patientCode,        
            'first_name' => $firstName,
            'last_name' => $lastName,
            'email' => $this->faker->unique()->safeEmail(),
            'phone' => $this->faker->phoneNumber(),
            'phone_secondary' => $this->faker->optional(0.3)->phoneNumber(),
            'birth_date' => $birthDate,
            'gender' => $gender,
            'address' => $this->faker->optional(0.8)->streetAddress(),
            'city' => $this->faker->optional(0.8)->city(),
            'state' => $this->faker->optional(0.8)->state(),
            'postal_code' => $this->faker->optional(0.8)->postcode(),
            'country' => 'México',
            
            // Información médica
            'medical_history' => $this->faker->optional(0.6)->sentence(),
            'dental_history' => $this->faker->optional(0.7)->sentence(),
            'allergies' => $this->faker->optional(0.4)->sentence(),
            'medications' => $this->faker->optional(0.5)->sentence(),
            'family_history' => $this->faker->optional(0.3)->sentence(),
            'social_history' => $this->faker->optional(0.2)->sentence(),
            'blood_type' => $this->faker->optional(0.6)->randomElement(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-']),
            'occupation' => $this->faker->optional(0.8)->jobTitle(),
            
            // Información de contacto de emergencia
            'emergency_contact_name' => $this->faker->optional(0.7)->name(),
            'emergency_contact_phone' => $this->faker->optional(0.7)->phoneNumber(),
            'emergency_contact_relationship' => $this->faker->optional(0.7)->randomElement(['Padre', 'Madre', 'Hijo', 'Hija', 'Cónyuge', 'Hermano', 'Hermana', 'Otro']),
            
            // Notas y consentimientos
            'notes' => $this->faker->optional(0.3)->paragraph(),
            'consent_marketing' => $this->faker->boolean(70),
            'consent_data_processing' => $this->faker->boolean(85),
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    public function forClinic(ClinicContext|Clinic|int $clinic): static
    {
        $clinicId = $clinic instanceof ClinicContext ? $clinic->clinicId
            : ($clinic instanceof Clinic ? (int) $clinic->getKey() : $clinic);

        return $this->state(fn (array $attributes): array => ['clinic_id' => $clinicId]);
    }

    /**
     * Indicate that the patient is active.
     */
    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    /**
     * Indicate that the patient is inactive.
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the patient has medical conditions.
     */
    public function withMedicalConditions(): static
    {
        return $this->state(fn (array $attributes) => [
            'medical_history' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the patient has allergies.
     */
    public function withAllergies(): static
    {
        return $this->state(fn (array $attributes) => [
            'allergies' => $this->faker->sentence(),
        ]);
    }

    /**
     * Indicate that the patient is elderly (65+ years).
     */
    public function elderly(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-90 years', '-65 years'),
        ]);
    }

    /**
     * Indicate that the patient is a minor (under 18).
     */
    public function minor(): static
    {
        return $this->state(fn (array $attributes) => [
            'birth_date' => $this->faker->dateTimeBetween('-17 years', '-1 year'),
        ]);
    }

    /**
     * Indicate that the patient has emergency contact.
     */
    public function withEmergencyContact(): static
    {
        return $this->state(fn (array $attributes) => [
            'emergency_contact_name' => $this->faker->name(),
            'emergency_contact_phone' => $this->faker->phoneNumber(),
            'emergency_contact_relationship' => $this->faker->randomElement(['Padre', 'Madre', 'Hijo', 'Hija', 'Cónyuge', 'Hermano', 'Hermana', 'Otro']),
        ]);
    }
}
