<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Models\User;
use App\Models\SecurityAuditLog;
use Illuminate\Support\Facades\Hash;
use PragmaRX\Google2FALaravel\Support\Authenticator;
use Google2FA;

class TwoFactorAuthTest extends TestCase
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
            'google2fa_enabled' => false,
        ]);
    }

    /** @test */
    public function test_user_can_setup_2fa()
    {
        $response = $this->actingAs($this->user)
            ->get('/2fa/setup');

        $response->assertStatus(200);
        $response->assertViewIs('auth.2fa.setup');
        $response->assertViewHas(['qrCodeUrl', 'secretKey', 'backupCodes']);
    }

    /** @test */
    public function test_user_can_enable_2fa_with_valid_code()
    {
        // Generate secret key
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update(['google2fa_secret' => $secretKey]);

        // Generate valid TOTP code
        $validCode = Google2FA::getCurrentOtp($secretKey);

        $response = $this->actingAs($this->user)
            ->post('/2fa/enable', [
                'code' => $validCode,
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        // Verify 2FA is enabled
        $this->user->refresh();
        $this->assertTrue($this->user->google2fa_enabled);
        $this->assertNotNull($this->user->google2fa_enabled_at);
    }

    /** @test */
    public function test_user_cannot_enable_2fa_with_invalid_code()
    {
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update(['google2fa_secret' => $secretKey]);

        $response = $this->actingAs($this->user)
            ->post('/2fa/enable', [
                'code' => '123456', // Invalid code
            ]);

        $response->assertSessionHasErrors(['code']);
        
        // Verify 2FA is not enabled
        $this->user->refresh();
        $this->assertFalse($this->user->google2fa_enabled);
    }

    /** @test */
    public function test_user_can_verify_2fa_code()
    {
        // Setup 2FA
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
        ]);

        // Generate valid TOTP code
        $validCode = Google2FA::getCurrentOtp($secretKey);

        $response = $this->actingAs($this->user)
            ->post('/2fa/verify', [
                'code' => $validCode,
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');
        $this->assertTrue(session('2fa_verified'));
    }

    /** @test */
    public function test_user_can_use_backup_code()
    {
        // Setup 2FA with backup codes
        $secretKey = Google2FA::generateSecretKey();
        $backupCodes = ['BACKUP1', 'BACKUP2', 'BACKUP3'];
        
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
            'backup_codes' => $backupCodes,
        ]);

        $response = $this->actingAs($this->user)
            ->post('/2fa/verify', [
                'code' => 'BACKUP1',
            ]);

        $response->assertRedirect('/dashboard');
        $response->assertSessionHas('success');

        // Verify backup code was used (removed from user)
        $this->user->refresh();
        $this->assertNotContains('BACKUP1', $this->user->backup_codes);
    }

    /** @test */
    public function test_user_cannot_use_invalid_backup_code()
    {
        $secretKey = Google2FA::generateSecretKey();
        $backupCodes = ['BACKUP1', 'BACKUP2'];
        
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
            'backup_codes' => $backupCodes,
        ]);

        $response = $this->actingAs($this->user)
            ->post('/2fa/verify', [
                'code' => 'INVALID',
            ]);

        $response->assertSessionHasErrors(['code']);
    }

    /** @test */
    public function test_user_can_disable_2fa()
    {
        // Setup 2FA
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
        ]);

        $validCode = Google2FA::getCurrentOtp($secretKey);

        $response = $this->actingAs($this->user)
            ->post('/2fa/disable', [
                'password' => 'password123',
                'code' => $validCode,
            ]);

        $response->assertRedirect('/profile');
        $response->assertSessionHas('success');

        // Verify 2FA is disabled
        $this->user->refresh();
        $this->assertFalse($this->user->google2fa_enabled);
        $this->assertNull($this->user->google2fa_secret);
        $this->assertNull($this->user->backup_codes);
    }

    /** @test */
    public function test_user_cannot_disable_2fa_with_wrong_password()
    {
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
        ]);

        $validCode = Google2FA::getCurrentOtp($secretKey);

        $response = $this->actingAs($this->user)
            ->post('/2fa/disable', [
                'password' => 'wrongpassword',
                'code' => $validCode,
            ]);

        $response->assertSessionHasErrors(['password']);

        // Verify 2FA is still enabled
        $this->user->refresh();
        $this->assertTrue($this->user->google2fa_enabled);
    }

    /** @test */
    public function test_user_can_regenerate_backup_codes()
    {
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
        ]);

        $response = $this->actingAs($this->user)
            ->post('/2fa/regenerate-backup-codes', [
                'password' => 'password123',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $response->assertSessionHas('backup_codes');

        // Verify new backup codes are generated
        $this->user->refresh();
        $this->assertCount(10, $this->user->backup_codes);
    }

    /** @test */
    public function test_2fa_setup_creates_audit_log()
    {
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update(['google2fa_secret' => $secretKey]);

        $validCode = Google2FA::getCurrentOtp($secretKey);

        $this->actingAs($this->user)
            ->post('/2fa/enable', [
                'code' => $validCode,
            ]);

        // Verify audit log is created
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $this->user->id,
            'event_type' => '2fa_enabled',
        ]);
    }

    /** @test */
    public function test_2fa_verification_creates_audit_log()
    {
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
        ]);

        $validCode = Google2FA::getCurrentOtp($secretKey);

        $this->actingAs($this->user)
            ->post('/2fa/verify', [
                'code' => $validCode,
            ]);

        // Verify audit log is created
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $this->user->id,
            'event_type' => 'successful_login',
        ]);
    }

    /** @test */
    public function test_2fa_disabled_creates_audit_log()
    {
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
        ]);

        $validCode = Google2FA::getCurrentOtp($secretKey);

        $this->actingAs($this->user)
            ->post('/2fa/disable', [
                'password' => 'password123',
                'code' => $validCode,
            ]);

        // Verify audit log is created
        $this->assertDatabaseHas('security_audit_logs', [
            'user_id' => $this->user->id,
            'event_type' => '2fa_disabled',
        ]);
    }

    /** @test */
    public function test_api_2fa_setup_returns_secret_and_qr()
    {
        $response = $this->actingAs($this->user)
            ->postJson('/api/2fa/setup');

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'secret_key',
            'qr_code_url',
            'backup_codes',
        ]);

        // Verify user has secret key
        $this->user->refresh();
        $this->assertNotNull($this->user->google2fa_secret);
        $this->assertCount(10, $this->user->backup_codes);
    }

    /** @test */
    public function test_api_2fa_enable_with_valid_code()
    {
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update(['google2fa_secret' => $secretKey]);

        $validCode = Google2FA::getCurrentOtp($secretKey);

        $response = $this->actingAs($this->user)
            ->postJson('/api/2fa/enable', [
                'code' => $validCode,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => '2FA enabled successfully']);

        $this->user->refresh();
        $this->assertTrue($this->user->google2fa_enabled);
    }

    /** @test */
    public function test_api_2fa_verify_with_valid_code()
    {
        $secretKey = Google2FA::generateSecretKey();
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
        ]);

        $validCode = Google2FA::getCurrentOtp($secretKey);

        $response = $this->actingAs($this->user)
            ->postJson('/api/2fa/verify', [
                'code' => $validCode,
            ]);

        $response->assertStatus(200);
        $response->assertJson(['message' => '2FA verification successful']);
    }

    /** @test */
    public function test_api_2fa_status_returns_correct_info()
    {
        $secretKey = Google2FA::generateSecretKey();
        $backupCodes = ['CODE1', 'CODE2'];
        
        $this->user->update([
            'google2fa_secret' => $secretKey,
            'google2fa_enabled' => true,
            'backup_codes' => $backupCodes,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/2fa/status');

        $response->assertStatus(200);
        $response->assertJson([
            'enabled' => true,
            'backup_codes_count' => 2,
        ]);
    }

    /** @test */
    public function test_2fa_required_middleware_blocks_access()
    {
        $this->user->update(['google2fa_enabled' => true]);
        
        // Simulate 2FA required session
        session(['2fa_required' => true]);

        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertRedirect('/2fa/verify');
    }

    /** @test */
    public function test_2fa_verified_session_allows_access()
    {
        $this->user->update(['google2fa_enabled' => true]);
        
        // Simulate 2FA verified session
        session(['2fa_verified' => true]);

        $response = $this->actingAs($this->user)
            ->get('/dashboard');

        $response->assertStatus(200);
    }
}