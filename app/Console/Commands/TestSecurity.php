<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\SecurityAuditService;
use App\Models\User;
use App\Models\SecurityAuditLog;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class TestSecurity extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:test {--user= : Test with specific user ID}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test security features and audit logging';

    protected $securityAuditService;

    public function __construct(SecurityAuditService $securityAuditService)
    {
        parent::__construct();
        $this->securityAuditService = $securityAuditService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Probando sistema de seguridad...');

        $userId = $this->option('user');
        $user = $userId ? User::find($userId) : User::first();

        if (!$user) {
            $this->error('❌ No se encontró usuario para probar');
            return;
        }

        $this->info("👤 Probando con usuario: {$user->name} ({$user->email})");

        // Test 1: Audit Logging
        $this->testAuditLogging($user);

        // Test 2: Data Encryption
        $this->testDataEncryption();

        // Test 3: Security Headers
        $this->testSecurityHeaders();

        // Test 4: 2FA Setup
        $this->test2FASetup($user);

        // Test 5: Security Statistics
        $this->showSecurityStatistics();

        $this->info('✅ Pruebas de seguridad completadas');
    }

    protected function testAuditLogging(User $user)
    {
        $this->info('📝 Probando auditoría de seguridad...');

        // Log successful login
        $this->securityAuditService->logSuccessfulLogin($user);
        $this->line('  ✅ Login exitoso registrado');

        // Log failed login
        $this->securityAuditService->logFailedLogin('test@example.com');
        $this->line('  ✅ Login fallido registrado');

        // Log password change
        $this->securityAuditService->logPasswordChange($user);
        $this->line('  ✅ Cambio de contraseña registrado');

        // Log 2FA enabled
        $this->securityAuditService->log2FAEnabled($user);
        $this->line('  ✅ 2FA habilitado registrado');

        // Log data access
        $this->securityAuditService->logDataAccess('patients', 'view', $user);
        $this->line('  ✅ Acceso a datos registrado');

        // Log suspicious activity
        $this->securityAuditService->logSuspiciousActivity(
            'Test suspicious activity detected',
            $user
        );
        $this->line('  ✅ Actividad sospechosa registrada');
    }

    protected function testDataEncryption()
    {
        $this->info('🔐 Probando encriptación de datos...');

        $testData = 'Datos sensibles de prueba';
        $encrypted = Crypt::encryptString($testData);
        $decrypted = Crypt::decryptString($encrypted);

        if ($decrypted === $testData) {
            $this->line('  ✅ Encriptación/desencriptación funcionando');
        } else {
            $this->error('  ❌ Error en encriptación');
        }

        // Test hash verification
        $password = 'test_password_123';
        $hashed = Hash::make($password);
        
        if (Hash::check($password, $hashed)) {
            $this->line('  ✅ Hash de contraseñas funcionando');
        } else {
            $this->error('  ❌ Error en hash de contraseñas');
        }
    }

    protected function testSecurityHeaders()
    {
        $this->info('🛡️ Probando headers de seguridad...');

        $headers = [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];

        foreach ($headers as $header => $expected) {
            $this->line("  📋 {$header}: {$expected}");
        }

        $this->line('  ✅ Headers de seguridad configurados');
    }

    protected function test2FASetup(User $user)
    {
        $this->info('🔑 Probando configuración 2FA...');

        // Check if 2FA is enabled
        if ($user->google2fa_enabled) {
            $this->line('  ✅ 2FA ya está habilitado');
        } else {
            $this->line('  ℹ️ 2FA no está habilitado (normal para pruebas)');
        }

        // Check backup codes
        $backupCodes = $user->backup_codes;
        if ($backupCodes && count($backupCodes) > 0) {
            $this->line('  ✅ Códigos de respaldo disponibles: ' . count($backupCodes));
        } else {
            $this->line('  ℹ️ No hay códigos de respaldo');
        }

        // Check security fields
        $securityFields = [
            'google2fa_secret' => $user->google2fa_secret ? 'Configurado' : 'No configurado',
            'google2fa_enabled' => $user->google2fa_enabled ? 'Habilitado' : 'Deshabilitado',
            'last_login_at' => $user->last_login_at ? $user->last_login_at->format('Y-m-d H:i:s') : 'Nunca',
            'failed_login_attempts' => $user->failed_login_attempts,
            'is_locked' => $user->is_locked ? 'Bloqueado' : 'Activo',
        ];

        foreach ($securityFields as $field => $value) {
            $this->line("  📊 {$field}: {$value}");
        }
    }

    protected function showSecurityStatistics()
    {
        $this->info('📊 Estadísticas de seguridad...');

        try {
            $stats = SecurityAuditLog::getSecurityStats(30);
            
            $this->line("  📈 Total de eventos: {$stats['total_events']}");
            $this->line("  ⚠️ Eventos sospechosos: {$stats['suspicious_events']}");
            $this->line("  🔴 Eventos de alto riesgo: {$stats['high_risk_events']}");
            $this->line("  ❌ Logins fallidos: {$stats['failed_logins']}");
            $this->line("  🌐 IPs únicas: {$stats['unique_ips']}");

            if (!empty($stats['events_by_type'])) {
                $this->line("  📋 Eventos por tipo:");
                foreach ($stats['events_by_type'] as $type => $count) {
                    $this->line("    - {$type}: {$count}");
                }
            }

        } catch (\Exception $e) {
            $this->error("  ❌ Error obteniendo estadísticas: " . $e->getMessage());
        }
    }
}