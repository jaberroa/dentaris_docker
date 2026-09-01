<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\SecurityAuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class SecurityMiddlewareTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@dentaris.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);
    }

    /** @test */
    public function test_encrypt_sensitive_data_middleware_encrypts_sensitive_fields()
    {
        $sensitiveData = [
            'email' => 'patient@example.com',
            'phone' => '+1234567890',
            'address' => '123 Main St',
            'medical_conditions' => 'Diabetes, Hypertension',
        ];

        $response = $this->postJson('/api/patients', $sensitiveData);

        // Verify that sensitive data is encrypted in the response
        $response->assertStatus(200);
        
        // Check that the response contains encrypted data
        $responseData = $response->json();
        $this->assertArrayHasKey('data', $responseData);
    }

    /** @test */
    public function test_enhanced_csrf_protection_blocks_invalid_tokens()
    {
        $response = $this->post('/patients', [
            'name' => 'Test Patient',
            'email' => 'test@example.com',
            '_token' => 'invalid_token',
        ]);

        $response->assertStatus(419); // CSRF token mismatch
    }

    /** @test */
    public function test_enhanced_csrf_protection_allows_valid_tokens()
    {
        $response = $this->actingAs($this->user)
            ->post('/patients', [
                'name' => 'Test Patient',
                'email' => 'test@example.com',
                '_token' => csrf_token(),
            ]);

        $response->assertStatus(200);
    }

    /** @test */
    public function test_xss_protection_sanitizes_malicious_input()
    {
        $maliciousInput = [
            'name' => '<script>alert("XSS")</script>',
            'description' => '<iframe src="javascript:alert(1)"></iframe>',
            'notes' => '<img src="x" onerror="alert(1)">',
        ];

        $response = $this->actingAs($this->user)
            ->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(200);
        
        // Verify that malicious scripts are sanitized
        $responseData = $response->json();
        $this->assertStringNotContainsString('<script>', $responseData['data']['name'] ?? '');
        $this->assertStringNotContainsString('<iframe>', $responseData['data']['description'] ?? '');
        $this->assertStringNotContainsString('onerror=', $responseData['data']['notes'] ?? '');
    }

    /** @test */
    public function test_xss_protection_logs_attack_attempts()
    {
        $maliciousInput = '<script>alert("XSS")</script>';

        $response = $this->actingAs($this->user)
            ->postJson('/api/patients', ['name' => $maliciousInput]);

        // Verify that XSS attempt is logged
        $this->assertDatabaseHas('security_audit_logs', [
            'event_type' => 'suspicious_activity',
            'is_suspicious' => true,
        ]);
    }

    /** @test */
    public function test_security_headers_are_present()
    {
        $response = $this->get('/dashboard');

        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-XSS-Protection', '1; mode=block');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
    }

    /** @test */
    public function test_security_headers_for_api_routes()
    {
        $response = $this->actingAs($this->user)
            ->getJson('/api/patients');

        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'DENY');
    }

    /** @test */
    public function test_performance_monitor_adds_headers()
    {
        $response = $this->get('/dashboard');

        $response->assertHeader('X-Execution-Time');
        $response->assertHeader('X-Memory-Usage');
        $response->assertHeader('X-Peak-Memory');
    }

    /** @test */
    public function test_rate_limiting_blocks_excessive_requests()
    {
        // Make multiple requests quickly
        for ($i = 0; $i < 10; $i++) {
            $response = $this->postJson('/api/login', [
                'email' => 'test@example.com',
                'password' => 'wrongpassword',
            ]);
        }

        // Should be rate limited
        $response->assertStatus(429);
    }

    /** @test */
    public function test_security_audit_logs_are_created()
    {
        $this->actingAs($this->user)
            ->postJson('/api/patients', [
                'name' => 'Test Patient',
                'email' => 'test@example.com',
            ]);

        // Verify audit log is created
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $this->user->id,
            'event_type' => 'data_access',
        ]);
    }

    /** @test */
    public function test_suspicious_activity_detection()
    {
        // Simulate suspicious activity
        $response = $this->actingAs($this->user)
            ->postJson('/api/patients', [
                'name' => '<script>alert("XSS")</script>',
                'email' => 'test@example.com',
            ]);

        // Verify suspicious activity is logged
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $this->user->id,
            'is_suspicious' => true,
        ]);
    }

    /** @test */
    public function test_data_encryption_works_correctly()
    {
        $sensitiveData = 'This is sensitive information';
        
        $encrypted = Crypt::encryptString($sensitiveData);
        $decrypted = Crypt::decryptString($encrypted);
        
        $this->assertEquals($sensitiveData, $decrypted);
        $this->assertNotEquals($sensitiveData, $encrypted);
    }

    /** @test */
    public function test_password_hashing_works_correctly()
    {
        $password = 'testpassword123';
        $hashed = Hash::make($password);
        
        $this->assertTrue(Hash::check($password, $hashed));
        $this->assertFalse(Hash::check('wrongpassword', $hashed));
        $this->assertNotEquals($password, $hashed);
    }

    /** @test */
    public function test_csrf_protection_blocks_suspicious_patterns()
    {
        $suspiciousData = [
            'name' => 'Test',
            'javascript' => 'alert("XSS")',
            'onclick' => 'malicious()',
            '_token' => csrf_token(),
        ];

        $response = $this->actingAs($this->user)
            ->post('/patients', $suspiciousData);

        // Should detect suspicious patterns and log them
        $this->assertDatabaseHas('security_audit_logs', [
            'event_type' => 'suspicious_activity',
        ]);
    }

    /** @test */
    public function test_security_middleware_order()
    {
        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        // Verify all security headers are present
        $response->assertHeader('X-Frame-Options');
        $response->assertHeader('X-Content-Type-Options');
        $response->assertHeader('X-XSS-Protection');
        $response->assertHeader('Referrer-Policy');
        
        // Verify performance headers
        $response->assertHeader('X-Execution-Time');
        $response->assertHeader('X-Memory-Usage');
    }

    /** @test */
    public function test_security_audit_log_retention()
    {
        // Create old audit log
        SecurityAuditLog::create([
            'user_id' => $this->user->id,
            'event_type' => 'test_event',
            'event_description' => 'Test event',
            'ip_address' => '127.0.0.1',
            'event_time' => now()->subYears(8), // Older than retention period
        ]);

        // Create recent audit log
        SecurityAuditLog::create([
            'user_id' => $this->user->id,
            'event_type' => 'recent_event',
            'event_description' => 'Recent event',
            'ip_address' => '127.0.0.1',
            'event_time' => now(),
        ]);

        // Verify both logs exist
        $this->assertDatabaseHas('security_audit_logs', [
            'event_type' => 'test_event',
        ]);
        $this->assertDatabaseHas('security_audit_logs', [
            'event_type' => 'recent_event',
        ]);
    }
}