<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SecurityAuditService;
use Symfony\Component\HttpFoundation\Response;

class EnhancedCsrfProtection
{
    protected $securityAuditService;

    public function __construct(SecurityAuditService $securityAuditService)
    {
        $this->securityAuditService = $securityAuditService;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Skip CSRF for API routes
        if ($request->is('api/*')) {
            return $next($request);
        }

        // Skip CSRF for GET, HEAD, OPTIONS requests
        if (in_array($request->method(), ['GET', 'HEAD', 'OPTIONS'])) {
            return $next($request);
        }

        // Enhanced CSRF validation
        if (!$this->validateCsrfToken($request)) {
            $this->logCsrfViolation($request);
            return $this->handleCsrfViolation($request);
        }

        // Additional security checks
        $this->performAdditionalSecurityChecks($request);

        return $next($request);
    }

    /**
     * Validate CSRF token
     */
    protected function validateCsrfToken(Request $request): bool
    {
        $token = $request->input('_token') ?: $request->header('X-CSRF-TOKEN');
        
        if (!$token) {
            return false;
        }

        // Check if token matches session token
        if (!hash_equals($request->session()->token(), $token)) {
            return false;
        }

        // Check token age (optional - tokens expire after 2 hours by default)
        $tokenAge = $request->session()->get('_token_age');
        if ($tokenAge && (time() - $tokenAge) > 7200) { // 2 hours
            return false;
        }

        return true;
    }

    /**
     * Perform additional security checks
     */
    protected function performAdditionalSecurityChecks(Request $request): void
    {
        // Check for suspicious patterns
        $this->checkForSuspiciousPatterns($request);
        
        // Check request frequency
        $this->checkRequestFrequency($request);
        
        // Check for bot patterns
        $this->checkForBotPatterns($request);
    }

    /**
     * Check for suspicious patterns
     */
    protected function checkForSuspiciousPatterns(Request $request): void
    {
        $suspiciousPatterns = [
            '/<script[^>]*>.*?<\/script>/i',
            '/javascript:/i',
            '/on\w+\s*=/i',
            '/<iframe[^>]*>.*?<\/iframe>/i',
            '/<object[^>]*>.*?<\/object>/i',
            '/<embed[^>]*>/i',
        ];

        $allInput = $request->all();
        $inputString = json_encode($allInput);

        foreach ($suspiciousPatterns as $pattern) {
            if (preg_match($pattern, $inputString)) {
                $this->securityAuditService->logSuspiciousActivity(
                    'Suspicious input pattern detected in CSRF validation',
                    auth()->user(),
                    $request,
                    ['pattern' => $pattern, 'input' => $inputString]
                );
                break;
            }
        }
    }

    /**
     * Check request frequency
     */
    protected function checkRequestFrequency(Request $request): void
    {
        $key = 'csrf_requests_' . $request->ip();
        $requests = cache()->get($key, []);
        
        // Clean old requests (older than 1 minute)
        $requests = array_filter($requests, function ($timestamp) {
            return $timestamp > now()->subMinute()->timestamp;
        });

        $requests[] = now()->timestamp;
        cache()->put($key, $requests, 60);

        // If more than 10 requests per minute, log as suspicious
        if (count($requests) > 10) {
            $this->securityAuditService->logSuspiciousActivity(
                'High frequency CSRF requests detected',
                auth()->user(),
                $request,
                ['request_count' => count($requests)]
            );
        }
    }

    /**
     * Check for bot patterns
     */
    protected function checkForBotPatterns(Request $request): void
    {
        $userAgent = $request->userAgent();
        
        // Check for common bot patterns
        $botPatterns = [
            '/bot/i',
            '/crawler/i',
            '/spider/i',
            '/scraper/i',
            '/curl/i',
            '/wget/i',
        ];

        foreach ($botPatterns as $pattern) {
            if (preg_match($pattern, $userAgent)) {
                $this->securityAuditService->logSuspiciousActivity(
                    'Bot-like user agent detected',
                    auth()->user(),
                    $request,
                    ['user_agent' => $userAgent]
                );
                break;
            }
        }
    }

    /**
     * Log CSRF violation
     */
    protected function logCsrfViolation(Request $request): void
    {
        $this->securityAuditService->logSuspiciousActivity(
            'CSRF token validation failed',
            auth()->user(),
            $request,
            [
                'provided_token' => $request->input('_token') ?: $request->header('X-CSRF-TOKEN'),
                'session_token' => $request->session()->token(),
                'user_agent' => $request->userAgent(),
                'referer' => $request->header('referer'),
            ]
        );
    }

    /**
     * Handle CSRF violation
     */
    protected function handleCsrfViolation(Request $request): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'CSRF token mismatch.',
                'error' => 'csrf_token_mismatch'
            ], 419);
        }

        return redirect()->back()
            ->withErrors(['csrf' => 'Token de seguridad inválido. Por favor, recarga la página e intenta nuevamente.'])
            ->withInput();
    }
}