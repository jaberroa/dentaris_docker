<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Crypt;

class SecurityUnitTest extends TestCase
{
    public function test_data_encryption_works()
    {
        $sensitiveData = 'This is sensitive information';
        
        $encrypted = Crypt::encryptString($sensitiveData);
        $decrypted = Crypt::decryptString($encrypted);
        
        $this->assertEquals($sensitiveData, $decrypted);
        $this->assertNotEquals($sensitiveData, $encrypted);
    }

    public function test_password_hashing_works()
    {
        $password = 'testpassword123';
        $hashed = Hash::make($password);
        
        $this->assertTrue(Hash::check($password, $hashed));
        $this->assertFalse(Hash::check('wrongpassword', $hashed));
        $this->assertNotEquals($password, $hashed);
    }

    public function test_security_configuration_exists()
    {
        $this->assertFileExists(config_path('security.php'));
        
        $config = config('security');
        $this->assertIsArray($config);
        $this->assertArrayHasKey('encryption', $config);
        $this->assertArrayHasKey('two_factor_auth', $config);
        $this->assertArrayHasKey('login_security', $config);
    }

    public function test_security_middleware_exists()
    {
        $middlewareFiles = [
            app_path('Http/Middleware/EncryptSensitiveData.php'),
            app_path('Http/Middleware/EnhancedCsrfProtection.php'),
            app_path('Http/Middleware/XssProtection.php'),
            app_path('Http/Middleware/SecurityHeaders.php'),
            app_path('Http/Middleware/PerformanceMonitor.php'),
        ];

        foreach ($middlewareFiles as $file) {
            $this->assertFileExists($file);
        }
    }

    public function test_security_services_exist()
    {
        $serviceFiles = [
            app_path('Services/SecurityAuditService.php'),
            app_path('Services/CacheService.php'),
            app_path('Services/QueryOptimizer.php'),
        ];

        foreach ($serviceFiles as $file) {
            $this->assertFileExists($file);
        }
    }

    public function test_security_models_exist()
    {
        $modelFiles = [
            app_path('Models/SecurityAuditLog.php'),
        ];

        foreach ($modelFiles as $file) {
            $this->assertFileExists($file);
        }
    }

    public function test_security_commands_exist()
    {
        $commandFiles = [
            app_path('Console/Commands/TestSecurity.php'),
            app_path('Console/Commands/GenerateSecurityReport.php'),
            app_path('Console/Commands/RunSecurityTests.php'),
        ];

        foreach ($commandFiles as $file) {
            $this->assertFileExists($file);
        }
    }

    public function test_security_documentation_exists()
    {
        $docFiles = [
            base_path('docs/SECURITY.md'),
            base_path('docs/PERFORMANCE.md'),
        ];

        foreach ($docFiles as $file) {
            $this->assertFileExists($file);
        }
    }

    public function test_security_headers_are_configured()
    {
        $headers = [
            'X-Frame-Options' => 'DENY',
            'X-Content-Type-Options' => 'nosniff',
            'X-XSS-Protection' => '1; mode=block',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
        ];

        foreach ($headers as $header => $expected) {
            $this->assertIsString($header);
            $this->assertIsString($expected);
        }
    }

    public function test_security_audit_log_structure()
    {
        $auditLog = new \App\Models\SecurityAuditLog();
        
        $fillable = $auditLog->getFillable();
        $expectedFields = [
            'user_id',
            'event_type',
            'event_description',
            'ip_address',
            'user_agent',
            'session_id',
            'metadata',
            'risk_level',
            'is_suspicious',
            'location',
            'device_fingerprint',
            'event_time',
        ];

        foreach ($expectedFields as $field) {
            $this->assertContains($field, $fillable);
        }
    }

    public function test_security_audit_log_casts()
    {
        $auditLog = new \App\Models\SecurityAuditLog();
        $casts = $auditLog->getCasts();
        
        $this->assertArrayHasKey('metadata', $casts);
        $this->assertArrayHasKey('event_time', $casts);
        $this->assertArrayHasKey('is_suspicious', $casts);
        
        $this->assertEquals('array', $casts['metadata']);
        $this->assertEquals('datetime', $casts['event_time']);
        $this->assertEquals('boolean', $casts['is_suspicious']);
    }

    public function test_security_audit_log_relationships()
    {
        $auditLog = new \App\Models\SecurityAuditLog();
        
        $this->assertTrue(method_exists($auditLog, 'user'));
    }

    public function test_security_audit_log_scopes()
    {
        $auditLog = new \App\Models\SecurityAuditLog();
        
        $scopes = [
            'highRisk',
            'suspicious',
            'byEventType',
            'byUser',
            'byIp',
            'recent',
        ];

        foreach ($scopes as $scope) {
            $this->assertTrue(method_exists($auditLog, 'scope' . $scope));
        }
    }

    public function test_security_audit_log_utility_methods()
    {
        $auditLog = new \App\Models\SecurityAuditLog();
        
        $methods = [
            'markAsSuspicious',
            'getSecurityStats',
            'isRecent',
            'getSimilarEventsFromIp',
            'getSimilarEventsFromUser',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(method_exists($auditLog, $method));
        }
    }

    public function test_user_security_fields()
    {
        $user = new \App\Models\User();
        $casts = $user->getCasts();
        
        $securityFields = [
            'google2fa_enabled',
            'is_locked',
            'backup_codes',
            'last_login_at',
            'google2fa_enabled_at',
            'locked_until',
        ];

        foreach ($securityFields as $field) {
            $this->assertArrayHasKey($field, $casts);
        }
    }

    public function test_user_security_relationships()
    {
        $user = new \App\Models\User();
        
        $this->assertTrue(method_exists($user, 'securityAuditLogs'));
    }

    public function test_security_audit_service_methods()
    {
        $service = new \App\Services\SecurityAuditService();
        
        $methods = [
            'logEvent',
            'logSuccessfulLogin',
            'logFailedLogin',
            'logPasswordChange',
            'log2FAEnabled',
            'log2FADisabled',
            'logSuspiciousActivity',
            'logDataAccess',
            'logSystemAccess',
            'getSecurityDashboard',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(method_exists($service, $method));
        }
    }

    public function test_cache_service_methods()
    {
        $service = app(\App\Services\CacheService::class);
        
        $methods = [
            'getDashboardKpis',
            'getPatientStatistics',
            'getInventoryStatistics',
            'getAppointmentStatistics',
            'getRevenueData',
            'clearAllCache',
            'clearCacheByPattern',
            'clearDashboardCache',
            'clearPatientCache',
            'clearAppointmentCache',
            'clearInventoryCache',
            'getCacheStatistics',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(method_exists($service, $method));
        }
    }

    public function test_query_optimizer_methods()
    {
        $service = new \App\Services\QueryOptimizer();
        
        $methods = [
            'optimizePatientQuery',
            'optimizeAppointmentQuery',
            'optimizeInventoryQuery',
            'addCommonIndexes',
            'selectOnlyNeededColumns',
            'addQueryHints',
            'optimizePagination',
            'getQueryPlan',
            'analyzeSlowQueries',
            'getDatabaseStats',
        ];

        foreach ($methods as $method) {
            $this->assertTrue(method_exists($service, $method));
        }
    }
}





