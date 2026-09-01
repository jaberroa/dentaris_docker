<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Models\User;
use App\Models\SecurityAuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class SimpleSecurityTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function test_data_encryption_works()
    {
        $sensitiveData = 'This is sensitive information';
        
        $encrypted = Crypt::encryptString($sensitiveData);
        $decrypted = Crypt::decryptString($encrypted);
        
        $this->assertEquals($sensitiveData, $decrypted);
        $this->assertNotEquals($sensitiveData, $encrypted);
    }

    /** @test */
    public function test_password_hashing_works()
    {
        $password = 'testpassword123';
        $hashed = Hash::make($password);
        
        $this->assertTrue(Hash::check($password, $hashed));
        $this->assertFalse(Hash::check('wrongpassword', $hashed));
        $this->assertNotEquals($password, $hashed);
    }

    /** @test */
    public function test_security_audit_log_creation()
    {
        $user = User::factory()->create();
        
        $auditLog = SecurityAuditLog::create([
            'user_id' => $user->id,
            'event_type' => 'test_event',
            'event_description' => 'Test security event',
            'ip_address' => '127.0.0.1',
            'event_time' => now(),
        ]);

        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $user->id,
            'event_type' => 'test_event',
        ]);

        $this->assertEquals($user->id, $auditLog->user_id);
        $this->assertEquals('test_event', $auditLog->event_type);
    }

    /** @test */
    public function test_security_audit_log_relationships()
    {
        $user = User::factory()->create();
        
        $auditLog = SecurityAuditLog::create([
            'user_id' => $user->id,
            'event_type' => 'test_event',
            'event_description' => 'Test security event',
            'ip_address' => '127.0.0.1',
            'event_time' => now(),
        ]);

        $this->assertInstanceOf(User::class, $auditLog->user);
        $this->assertEquals($user->id, $auditLog->user->id);
    }

    /** @test */
    public function test_security_audit_log_scopes()
    {
        $user = User::factory()->create();
        
        // Create different types of audit logs
        SecurityAuditLog::create([
            'user_id' => $user->id,
            'event_type' => 'successful_login',
            'event_description' => 'User logged in',
            'ip_address' => '127.0.0.1',
            'risk_level' => 'low',
            'is_suspicious' => false,
            'event_time' => now(),
        ]);

        SecurityAuditLog::create([
            'user_id' => $user->id,
            'event_type' => 'failed_login',
            'event_description' => 'Failed login attempt',
            'ip_address' => '127.0.0.1',
            'risk_level' => 'high',
            'is_suspicious' => true,
            'event_time' => now(),
        ]);

        // Test scopes
        $this->assertEquals(1, SecurityAuditLog::suspicious()->count());
        $this->assertEquals(1, SecurityAuditLog::highRisk()->count());
        $this->assertEquals(1, SecurityAuditLog::byEventType('successful_login')->count());
        $this->assertEquals(1, SecurityAuditLog::byUser($user->id)->count());
    }

    /** @test */
    public function test_security_audit_log_statistics()
    {
        $user = User::factory()->create();
        
        // Create multiple audit logs
        SecurityAuditLog::factory()->count(5)->create([
            'user_id' => $user->id,
            'event_type' => 'successful_login',
        ]);

        SecurityAuditLog::factory()->count(3)->create([
            'user_id' => $user->id,
            'event_type' => 'failed_login',
            'is_suspicious' => true,
        ]);

        $stats = SecurityAuditLog::getSecurityStats(30);
        
        $this->assertArrayHasKey('total_events', $stats);
        $this->assertArrayHasKey('suspicious_events', $stats);
        $this->assertArrayHasKey('failed_logins', $stats);
        $this->assertGreaterThan(0, $stats['total_events']);
    }

    /** @test */
    public function test_user_security_fields()
    {
        $user = User::factory()->create([
            'google2fa_enabled' => true,
            'is_locked' => false,
            'failed_login_attempts' => 0,
        ]);

        $this->assertTrue($user->google2fa_enabled);
        $this->assertFalse($user->is_locked);
        $this->assertEquals(0, $user->failed_login_attempts);
    }

    /** @test */
    public function test_security_audit_log_mark_as_suspicious()
    {
        $user = User::factory()->create();
        
        $auditLog = SecurityAuditLog::create([
            'user_id' => $user->id,
            'event_type' => 'test_event',
            'event_description' => 'Test event',
            'ip_address' => '127.0.0.1',
            'is_suspicious' => false,
            'event_time' => now(),
        ]);

        $auditLog->markAsSuspicious('Test reason');

        $this->assertTrue($auditLog->fresh()->is_suspicious);
        $this->assertArrayHasKey('marked_suspicious_at', $auditLog->fresh()->metadata);
        $this->assertArrayHasKey('suspicious_reason', $auditLog->fresh()->metadata);
    }
}





