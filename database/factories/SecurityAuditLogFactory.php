<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;
use App\Models\SecurityAuditLog;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SecurityAuditLog>
 */
class SecurityAuditLogFactory extends Factory
{
    protected $model = SecurityAuditLog::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $eventTypes = [
            'successful_login',
            'failed_login',
            'password_change',
            '2fa_enabled',
            '2fa_disabled',
            'data_access',
            'suspicious_activity',
            'system_access',
        ];

        $riskLevels = ['low', 'medium', 'high', 'critical'];

        return [
            'user_id' => User::factory(),
            'event_type' => $this->faker->randomElement($eventTypes),
            'event_description' => $this->faker->sentence(),
            'ip_address' => $this->faker->ipv4(),
            'user_agent' => $this->faker->userAgent(),
            'session_id' => $this->faker->uuid(),
            'metadata' => [
                'browser' => $this->faker->randomElement(['Chrome', 'Firefox', 'Safari', 'Edge']),
                'os' => $this->faker->randomElement(['Windows', 'macOS', 'Linux', 'iOS', 'Android']),
            ],
            'risk_level' => $this->faker->randomElement($riskLevels),
            'is_suspicious' => $this->faker->boolean(20), // 20% chance of being suspicious
            'location' => $this->faker->city() . ', ' . $this->faker->country(),
            'device_fingerprint' => $this->faker->sha256(),
            'event_time' => $this->faker->dateTimeBetween('-30 days', 'now'),
        ];
    }

    /**
     * Indicate that the audit log is suspicious.
     */
    public function suspicious(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_suspicious' => true,
            'risk_level' => $this->faker->randomElement(['high', 'critical']),
        ]);
    }

    /**
     * Indicate that the audit log is high risk.
     */
    public function highRisk(): static
    {
        return $this->state(fn (array $attributes) => [
            'risk_level' => 'high',
            'is_suspicious' => true,
        ]);
    }

    /**
     * Indicate that the audit log is a failed login.
     */
    public function failedLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'failed_login',
            'event_description' => 'Failed login attempt',
            'risk_level' => 'medium',
        ]);
    }

    /**
     * Indicate that the audit log is a successful login.
     */
    public function successfulLogin(): static
    {
        return $this->state(fn (array $attributes) => [
            'event_type' => 'successful_login',
            'event_description' => 'User logged in successfully',
            'risk_level' => 'low',
            'is_suspicious' => false,
        ]);
    }
}