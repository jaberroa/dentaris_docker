<?php

namespace App\Services;

use App\Models\SecurityAuditLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class SecurityAuditService
{
    /**
     * Log a security event
     */
    public function logEvent(
        string $eventType,
        string $description,
        ?User $user = null,
        Request $request = null,
        array $metadata = [],
        string $riskLevel = 'low'
    ): SecurityAuditLog {
        $request = $request ?: request();
        
        $logData = [
            'user_id' => $user?->id,
            'event_type' => $eventType,
            'event_description' => $description,
            'ip_address' => $this->getClientIp($request),
            'user_agent' => $request->userAgent(),
            'session_id' => $request->hasSession() ? $request->session()->getId() : null,
            'metadata' => $metadata,
            'risk_level' => $riskLevel,
            'is_suspicious' => $this->isSuspiciousEvent($eventType, $request, $user),
            'location' => $this->getLocation($request),
            'device_fingerprint' => $this->generateDeviceFingerprint($request),
            'event_time' => now(),
        ];

        $auditLog = SecurityAuditLog::create($logData);

        // Log to Laravel log if high risk
        if (in_array($riskLevel, ['high', 'critical'])) {
            Log::warning("High risk security event: {$eventType}", $logData);
        }

        return $auditLog;
    }

    /**
     * Log successful login
     */
    public function logSuccessfulLogin(User $user, Request $request = null): SecurityAuditLog
    {
        $this->updateUserLoginInfo($user, $request);
        
        return $this->logEvent(
            'successful_login',
            "Usuario {$user->name} inició sesión exitosamente",
            $user,
            $request,
            ['login_method' => 'password'],
            'low'
        );
    }

    /**
     * Log failed login attempt
     */
    public function logFailedLogin(string $email, Request $request = null, string $reason = 'Invalid credentials'): SecurityAuditLog
    {
        $user = User::where('email', $email)->first();
        
        if ($user) {
            $this->incrementFailedLoginAttempts($user);
        }

        return $this->logEvent(
            'failed_login',
            "Intento de login fallido para {$email}: {$reason}",
            $user,
            $request,
            ['email' => $email, 'reason' => $reason],
            'medium'
        );
    }

    /**
     * Log password change
     */
    public function logPasswordChange(User $user, Request $request = null): SecurityAuditLog
    {
        return $this->logEvent(
            'password_change',
            "Usuario {$user->name} cambió su contraseña",
            $user,
            $request,
            [],
            'medium'
        );
    }

    /**
     * Log 2FA enabled
     */
    public function log2FAEnabled(User $user, Request $request = null): SecurityAuditLog
    {
        return $this->logEvent(
            '2fa_enabled',
            "Usuario {$user->name} habilitó autenticación de dos factores",
            $user,
            $request,
            [],
            'low'
        );
    }

    /**
     * Log 2FA disabled
     */
    public function log2FADisabled(User $user, Request $request = null): SecurityAuditLog
    {
        return $this->logEvent(
            '2fa_disabled',
            "Usuario {$user->name} deshabilitó autenticación de dos factores",
            $user,
            $request,
            [],
            'high'
        );
    }

    /**
     * Log suspicious activity
     */
    public function logSuspiciousActivity(
        string $description,
        ?User $user = null,
        Request $request = null,
        array $metadata = []
    ): SecurityAuditLog {
        $auditLog = $this->logEvent(
            'suspicious_activity',
            $description,
            $user,
            $request,
            $metadata,
            'high'
        );

        $auditLog->markAsSuspicious('Detected by security system');

        return $auditLog;
    }

    /**
     * Log data access
     */
    public function logDataAccess(
        string $dataType,
        string $action,
        ?User $user = null,
        Request $request = null,
        array $metadata = []
    ): SecurityAuditLog {
        return $this->logEvent(
            'data_access',
            "Acceso a {$dataType}: {$action}",
            $user,
            $request,
            array_merge($metadata, ['data_type' => $dataType, 'action' => $action]),
            'low'
        );
    }

    /**
     * Log system access
     */
    public function logSystemAccess(
        string $module,
        string $action,
        ?User $user = null,
        Request $request = null
    ): SecurityAuditLog {
        return $this->logEvent(
            'system_access',
            "Acceso al módulo {$module}: {$action}",
            $user,
            $request,
            ['module' => $module, 'action' => $action],
            'low'
        );
    }

    /**
     * Get client IP address
     */
    protected function getClientIp(Request $request): string
    {
        $ipKeys = ['HTTP_X_FORWARDED_FOR', 'HTTP_X_REAL_IP', 'HTTP_CLIENT_IP', 'REMOTE_ADDR'];
        
        foreach ($ipKeys as $key) {
            $ip = $request->server($key);
            if ($ip && filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                return $ip;
            }
        }

        return $request->ip();
    }

    /**
     * Check if event is suspicious
     */
    protected function isSuspiciousEvent(string $eventType, Request $request, ?User $user): bool
    {
        // Check for multiple failed logins from same IP
        if ($eventType === 'failed_login') {
            $recentFailedLogins = SecurityAuditLog::where('ip_address', $this->getClientIp($request))
                ->where('event_type', 'failed_login')
                ->where('event_time', '>=', now()->subMinutes(15))
                ->count();

            if ($recentFailedLogins >= 5) {
                return true;
            }
        }

        // Check for login from new location
        if ($eventType === 'successful_login' && $user) {
            $recentLogins = SecurityAuditLog::where('user_id', $user->id)
                ->where('event_type', 'successful_login')
                ->where('event_time', '>=', now()->subDays(30))
                ->get();

            $currentIp = $this->getClientIp($request);
            $hasLoggedFromThisIp = $recentLogins->contains('ip_address', $currentIp);

            if (!$hasLoggedFromThisIp && $recentLogins->count() > 0) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get location from IP (simplified)
     */
    protected function getLocation(Request $request): ?string
    {
        // In a real implementation, you would use a geolocation service
        // For now, we'll return null
        return null;
    }

    /**
     * Generate device fingerprint
     */
    protected function generateDeviceFingerprint(Request $request): string
    {
        $components = [
            $request->userAgent(),
            $request->header('Accept-Language'),
            $request->header('Accept-Encoding'),
        ];

        return hash('sha256', implode('|', array_filter($components)));
    }

    /**
     * Update user login information
     */
    protected function updateUserLoginInfo(User $user, Request $request = null): void
    {
        $request = $request ?: request();
        
        $user->update([
            'last_login_at' => now(),
            'last_login_ip' => $this->getClientIp($request),
            'failed_login_attempts' => 0,
            'is_locked' => false,
            'locked_until' => null,
        ]);
    }

    /**
     * Increment failed login attempts
     */
    protected function incrementFailedLoginAttempts(User $user): void
    {
        $attempts = $user->failed_login_attempts + 1;
        $maxAttempts = config('security.max_login_attempts', 5);
        
        $updateData = ['failed_login_attempts' => $attempts];
        
        if ($attempts >= $maxAttempts) {
            $lockDuration = config('security.lockout_duration', 15); // minutes
            $updateData = array_merge($updateData, [
                'is_locked' => true,
                'locked_until' => now()->addMinutes($lockDuration),
            ]);
        }

        $user->update($updateData);
    }

    /**
     * Get security dashboard data
     */
    public function getSecurityDashboard(int $days = 30): array
    {
        $startDate = now()->subDays($days);
        
        return [
            'stats' => SecurityAuditLog::getSecurityStats($days),
            'recent_events' => SecurityAuditLog::where('event_time', '>=', $startDate)
                ->orderBy('event_time', 'desc')
                ->limit(20)
                ->get(),
            'suspicious_events' => SecurityAuditLog::where('event_time', '>=', $startDate)
                ->where('is_suspicious', true)
                ->orderBy('event_time', 'desc')
                ->get(),
            'high_risk_events' => SecurityAuditLog::where('event_time', '>=', $startDate)
                ->whereIn('risk_level', ['high', 'critical'])
                ->orderBy('event_time', 'desc')
                ->get(),
        ];
    }
}
