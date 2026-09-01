<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\SecurityAuditLog;
use Illuminate\Support\Facades\Hash;

class PenetrationTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'user@dentaris.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_sql_injection_attack_attempts()
    {
        $sqlInjectionPayloads = [
            "' OR '1'='1",
            "'; DROP TABLE users; --",
            "' UNION SELECT * FROM users --",
            "' OR 1=1 --",
            "admin'--",
        ];

        foreach ($sqlInjectionPayloads as $payload) {
            $response = $this->postJson('/api/login', [
                'email' => $payload,
                'password' => 'password',
            ]);

            $response->assertStatus(401);
            $this->assertDatabaseHas('users', [
                'email' => 'user@dentaris.com',
            ]);
        }
    }

    /** @test */
    public function test_xss_attack_attempts()
    {
        $xssPayloads = [
            '<script>alert("XSS")</script>',
            '<img src="x" onerror="alert(1)">',
            '<iframe src="javascript:alert(1)"></iframe>',
        ];

        foreach ($xssPayloads as $payload) {
            $response = $this->actingAs($this->user)
                ->postJson('/api/patients', [
                    'name' => $payload,
                    'email' => 'test@example.com',
                ]);

            $response->assertStatus(200);
            $responseData = $response->json();
            $this->assertStringNotContainsString('<script>', $responseData['data']['name'] ?? '');
        }
    }

    /** @test */
    public function test_directory_traversal_attack_attempts()
    {
        $directoryTraversalPayloads = [
            '../../../etc/passwd',
            '..\\..\\..\\windows\\system32\\drivers\\etc\\hosts',
            '....//....//....//etc/passwd',
        ];

        foreach ($directoryTraversalPayloads as $payload) {
            $response = $this->actingAs($this->user)
                ->postJson('/api/patients', [
                    'name' => 'Test',
                    'file_path' => $payload,
                ]);

            $response->assertStatus(422);
        }
    }

    /** @test */
    public function test_command_injection_attack_attempts()
    {
        $commandInjectionPayloads = [
            '; ls -la',
            '| cat /etc/passwd',
            '&& whoami',
            '|| id',
        ];

        foreach ($commandInjectionPayloads as $payload) {
            $response = $this->actingAs($this->user)
                ->postJson('/api/patients', [
                    'name' => 'Test',
                    'description' => $payload,
                ]);

            $response->assertStatus(200);
            $this->assertFileExists(base_path('config/database.php'));
        }
    }

    /** @test */
    public function test_brute_force_attack_attempts()
    {
        $commonPasswords = [
            'password',
            '123456',
            'admin',
            'root',
            'test',
        ];

        foreach ($commonPasswords as $password) {
            $response = $this->postJson('/api/login', [
                'email' => 'user@dentaris.com',
                'password' => $password,
            ]);

            $response->assertStatus(401);
        }

        $response = $this->postJson('/api/login', [
            'email' => 'user@dentaris.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(429);
    }

    /** @test */
    public function test_all_attacks_are_logged()
    {
        $this->postJson('/api/login', [
            'email' => "'; DROP TABLE users; --",
            'password' => 'password',
        ]);

        $this->actingAs($this->user)
            ->postJson('/api/patients', [
                'name' => '<script>alert("XSS")</script>',
            ]);

        $this->assertDatabaseHas('security_audit_logs', [
            'event_type' => 'suspicious_activity',
        ]);
    }
}