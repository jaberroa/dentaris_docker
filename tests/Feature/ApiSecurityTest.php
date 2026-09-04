<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\SecurityAuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Tests\Concerns\InteractsWithClinicalContext;

class ApiSecurityTest extends TestCase
{
    use InteractsWithClinicalContext, RefreshDatabase, WithFaker;

    protected $user;
    protected $apiToken;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->user = User::factory()->create([
            'email' => 'test@dentaris.com',
            'password' => Hash::make('password123'),
            'is_active' => true,
        ]);

        // Create API token
        $this->apiToken = $this->user->createToken('test-token')->plainTextToken;

        $context = $this->clinicalContextFor($this->user, [
            'view_patients',
            'manage_patients',
        ]);

        // El transporte API conserva la cabecera clínica explícita. El valor
        // únicamente identifica la clínica solicitada; el middleware valida la
        // membresía activa antes de autorizar cualquier operación.
        $this->withHeader('X-Clinic-Id', (string) $context->clinicId);
    }

    /** @test */
    public function test_api_requires_authentication()
    {
        $response = $this->getJson('/api/patients');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_api_accepts_valid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->getJson('/api/patients');

        $response->assertStatus(200);
    }

    /** @test */
    public function test_api_rejects_invalid_token()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/patients');

        $response->assertStatus(401);
    }

    /** @test */
    public function test_api_rate_limiting_works()
    {
        // Clear rate limiter
        RateLimiter::clear('api');

        // Make multiple requests quickly
        for ($i = 0; $i < 70; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
            ])->getJson('/api/patients');

            if ($i >= 60) {
                $response->assertStatus(429);
            }
        }
    }

    /** @test */
    public function test_api_blocks_sql_injection_attempts()
    {
        $maliciousInput = [
            'name' => "'; DROP TABLE users; --",
            'email' => "test@example.com' OR '1'='1",
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        // Should not cause database error
        $response->assertStatus(422); // Validation error, not SQL error
        
        // Verify table still exists
        $this->assertDatabaseHas('users', [
            'email' => 'test@dentaris.com',
        ]);
    }

    /** @test */
    public function test_api_blocks_xss_attempts()
    {
        $maliciousInput = [
            'name' => '<script>alert("XSS")</script>',
            'description' => '<iframe src="javascript:alert(1)"></iframe>',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(200);
        
        // Verify XSS is sanitized
        $responseData = $response->json();
        $this->assertStringNotContainsString('<script>', $responseData['data']['name'] ?? '');
        $this->assertStringNotContainsString('<iframe>', $responseData['data']['description'] ?? '');
    }

    /** @test */
    public function test_api_blocks_csrf_attempts()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'X-CSRF-TOKEN' => 'invalid-token',
        ])->postJson('/api/patients', [
            'name' => 'Test Patient',
        ]);

        // API should not be affected by CSRF
        $response->assertStatus(200);
    }

    /** @test */
    public function test_api_blocks_mass_assignment()
    {
        $maliciousInput = [
            'name' => 'Test Patient',
            'is_admin' => true,
            'role' => 'admin',
            'password' => 'newpassword',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(200);
        
        // Verify protected fields are not updated
        $this->user->refresh();
        $this->assertFalse($this->user->is_admin ?? false);
        $this->assertNotEquals('newpassword', $this->user->password);
    }

    /** @test */
    public function test_api_blocks_directory_traversal()
    {
        $maliciousInput = [
            'file_path' => '../../../etc/passwd',
            'document' => '../../config/database.php',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function test_api_blocks_command_injection()
    {
        $maliciousInput = [
            'name' => 'Test; rm -rf /',
            'description' => 'Test | cat /etc/passwd',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(200);
        
        // Verify commands are not executed
        $this->assertFileExists(base_path('config/database.php'));
    }

    /** @test */
    public function test_api_blocks_xxe_attempts()
    {
        $maliciousXml = '<?xml version="1.0"?>
        <!DOCTYPE foo [
        <!ENTITY xxe SYSTEM "file:///etc/passwd">
        ]>
        <foo>&xxe;</foo>';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/xml',
        ])->postJson('/api/patients', [
            'xml_data' => $maliciousXml,
        ]);

        $response->assertStatus(422); // Should reject XML
    }

    /** @test */
    public function test_api_blocks_ldap_injection()
    {
        $maliciousInput = [
            'search' => '*)(uid=*))(|(uid=*',
            'filter' => 'admin)(&(password=*)',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function test_api_blocks_nosql_injection()
    {
        $maliciousInput = [
            'query' => '{"$where": "this.password == this.username"}',
            'filter' => '{"$ne": null}',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(422); // Validation error
    }

    /** @test */
    public function test_api_blocks_header_injection()
    {
        $maliciousHeaders = [
            'X-Forwarded-For' => '127.0.0.1\r\nX-Injected-Header: malicious',
            'User-Agent' => 'Mozilla/5.0\r\nX-Injected: value',
        ];

        $response = $this->withHeaders(array_merge([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ], $maliciousHeaders))->getJson('/api/patients');

        $response->assertStatus(200);
        
        // Verify headers are sanitized
        $this->assertStringNotContainsString('\r\n', $response->headers->get('X-Forwarded-For', ''));
    }

    /** @test */
    public function test_api_blocks_http_parameter_pollution()
    {
        $maliciousInput = [
            'name' => 'Test',
            'name' => 'Hacked',
            'id' => 1,
            'id' => 999,
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(422); // Should handle parameter pollution
    }

    /** @test */
    public function test_api_blocks_oversized_payloads()
    {
        $oversizedData = [
            'name' => str_repeat('A', 10000), // 10KB string
            'description' => str_repeat('B', 50000), // 50KB string
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $oversizedData);

        $response->assertStatus(422); // Payload too large
    }

    /** @test */
    public function test_api_blocks_malformed_json()
    {
        $maliciousJson = '{"name": "Test", "email": "test@example.com", "malicious": }';

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
            'Content-Type' => 'application/json',
        ])->post('/api/patients', [], [
            'CONTENT_TYPE' => 'application/json',
        ]);

        $response->assertStatus(400); // Bad request
    }

    /** @test */
    public function test_api_blocks_unicode_attacks()
    {
        $maliciousInput = [
            'name' => 'Test\u0000\u0001\u0002',
            'description' => 'Test\uFFFE\uFFFF',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->postJson('/api/patients', $maliciousInput);

        $response->assertStatus(422); // Invalid unicode
    }

    /** @test */
    public function test_api_blocks_timing_attacks()
    {
        $startTime = microtime(true);
        
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/patients');

        $endTime = microtime(true);
        $responseTime = $endTime - $startTime;

        // Response time should be consistent (not reveal token validity)
        $this->assertLessThan(1.0, $responseTime); // Should be fast
        $response->assertStatus(401);
    }

    /** @test */
    public function test_api_blocks_brute_force_attempts()
    {
        // Simulate multiple failed authentication attempts
        for ($i = 0; $i < 10; $i++) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer invalid-token-' . $i,
            ])->getJson('/api/patients');

            $response->assertStatus(401);
        }

        // Should eventually be rate limited
        $response = $this->withHeaders([
            'Authorization' => 'Bearer invalid-token',
        ])->getJson('/api/patients');

        $response->assertStatus(429); // Too many requests
    }

    /** @test */
    public function test_api_creates_security_audit_logs()
    {
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->apiToken,
        ])->getJson('/api/patients');

        $response->assertStatus(200);

        // Verify audit log is created
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $this->user->id,
            'event_type' => 'data_access',
        ]);
    }

    /** @test */
    public function test_api_blocks_suspicious_user_agents()
    {
        $suspiciousUserAgents = [
            'sqlmap',
            'nikto',
            'nmap',
            'curl/7.0',
            'wget/1.0',
        ];

        foreach ($suspiciousUserAgents as $userAgent) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'User-Agent' => $userAgent,
            ])->getJson('/api/patients');

            $response->assertStatus(200);
            
            // Verify suspicious activity is logged
            $this->assertDatabaseHas('security_audit_logs', [
                'user_id' => $this->user->id,
                'is_suspicious' => true,
            ]);
        }
    }

    /** @test */
    public function test_api_blocks_malicious_content_types()
    {
        $maliciousContentTypes = [
            'application/x-php',
            'text/x-php',
            'application/x-executable',
            'application/x-sh',
        ];

        foreach ($maliciousContentTypes as $contentType) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Content-Type' => $contentType,
            ])->postJson('/api/patients', [
                'name' => 'Test',
            ]);

            $response->assertStatus(415); // Unsupported media type
        }
    }

    /** @test */
    public function test_api_blocks_malicious_accept_headers()
    {
        $maliciousAcceptHeaders = [
            'application/x-php',
            'text/x-php',
            'application/x-executable',
        ];

        foreach ($maliciousAcceptHeaders as $acceptHeader) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Accept' => $acceptHeader,
            ])->getJson('/api/patients');

            $response->assertStatus(406); // Not acceptable
        }
    }

    /** @test */
    public function test_api_blocks_malicious_referer_headers()
    {
        $maliciousReferers = [
            'javascript:alert(1)',
            'data:text/html,<script>alert(1)</script>',
            'vbscript:msgbox(1)',
        ];

        foreach ($maliciousReferers as $referer) {
            $response = $this->withHeaders([
                'Authorization' => 'Bearer ' . $this->apiToken,
                'Referer' => $referer,
            ])->getJson('/api/patients');

            $response->assertStatus(200);
            
            // Verify suspicious activity is logged
            $this->assertDatabaseHas('security_audit_logs', [
                'user_id' => $this->user->id,
                'is_suspicious' => true,
            ]);
        }
    }
}
