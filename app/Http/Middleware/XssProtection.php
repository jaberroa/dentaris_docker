<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SecurityAuditService;
use Symfony\Component\HttpFoundation\Response;

class XssProtection
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
        // Clean input data
        $this->sanitizeInput($request);

        $response = $next($request);

        // Add security headers
        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Sanitize input data
     */
    protected function sanitizeInput(Request $request): void
    {
        $allInput = $request->all();
        $sanitizedInput = $this->recursiveSanitize($allInput);
        
        // Replace request data with sanitized data
        $request->replace($sanitizedInput);
    }

    /**
     * Recursively sanitize data
     */
    protected function recursiveSanitize($data)
    {
        if (is_array($data)) {
            return array_map([$this, 'recursiveSanitize'], $data);
        }

        if (is_string($data)) {
            return $this->sanitizeString($data);
        }

        return $data;
    }

    /**
     * Sanitize string data
     */
    protected function sanitizeString(string $input): string
    {
        // Check for XSS patterns
        if ($this->containsXssPatterns($input)) {
            $this->logXssAttempt($input);
        }

        // Basic HTML sanitization
        $input = $this->stripDangerousTags($input);
        $input = $this->encodeSpecialChars($input);
        $input = $this->removeNullBytes($input);

        return $input;
    }

    /**
     * Check for XSS patterns
     */
    protected function containsXssPatterns(string $input): bool
    {
        $xssPatterns = [
            // Script tags
            '/<script[^>]*>.*?<\/script>/i',
            '/<script[^>]*>/i',
            
            // Event handlers
            '/on\w+\s*=\s*["\'][^"\']*["\']/i',
            '/on\w+\s*=\s*[^>\s]+/i',
            
            // JavaScript URLs
            '/javascript\s*:/i',
            '/vbscript\s*:/i',
            '/data\s*:\s*text\/html/i',
            
            // Iframe and object tags
            '/<iframe[^>]*>.*?<\/iframe>/i',
            '/<object[^>]*>.*?<\/object>/i',
            '/<embed[^>]*>.*?<\/embed>/i',
            '/<applet[^>]*>.*?<\/applet>/i',
            
            // Form manipulation
            '/<form[^>]*>.*?<\/form>/i',
            '/<input[^>]*>/i',
            
            // CSS expressions
            '/expression\s*\(/i',
            '/url\s*\(\s*javascript\s*:/i',
            
            // Base64 encoded scripts
            '/data:text\/html;base64,/i',
            
            // SVG with scripts
            '/<svg[^>]*>.*?<script[^>]*>.*?<\/script>.*?<\/svg>/i',
        ];

        foreach ($xssPatterns as $pattern) {
            if (preg_match($pattern, $input)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Strip dangerous HTML tags
     */
    protected function stripDangerousTags(string $input): string
    {
        $dangerousTags = [
            'script', 'iframe', 'object', 'embed', 'applet', 'form', 'input',
            'textarea', 'select', 'button', 'link', 'meta', 'style'
        ];

        foreach ($dangerousTags as $tag) {
            $input = preg_replace("/<\/?{$tag}[^>]*>/i", '', $input);
        }

        return $input;
    }

    /**
     * Encode special characters
     */
    protected function encodeSpecialChars(string $input): string
    {
        // Encode HTML entities
        $input = htmlspecialchars($input, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        // Additional encoding for dangerous characters
        $input = str_replace(['<', '>', '"', "'", '&'], ['&lt;', '&gt;', '&quot;', '&#x27;', '&amp;'], $input);

        return $input;
    }

    /**
     * Remove null bytes
     */
    protected function removeNullBytes(string $input): string
    {
        return str_replace("\0", '', $input);
    }

    /**
     * Log XSS attempt
     */
    protected function logXssAttempt(string $input): void
    {
        $this->securityAuditService->logSuspiciousActivity(
            'XSS attack attempt detected',
            auth()->user(),
            request(),
            [
                'malicious_input' => substr($input, 0, 500), // Limit log size
                'input_length' => strlen($input),
                'user_agent' => request()->userAgent(),
            ]
        );

        Log::warning('XSS attack attempt detected', [
            'user_id' => auth()->id(),
            'ip' => request()->ip(),
            'input' => substr($input, 0, 200),
        ]);
    }

    /**
     * Add security headers
     */
    protected function addSecurityHeaders(Response $response): void
    {
        // X-XSS-Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // X-Content-Type-Options
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // X-Frame-Options
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Referrer-Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content-Security-Policy
        $csp = "default-src 'self'; " .
               "script-src 'self' 'unsafe-inline' 'unsafe-eval'; " .
               "style-src 'self' 'unsafe-inline'; " .
               "img-src 'self' data: https:; " .
               "font-src 'self' data:; " .
               "connect-src 'self'; " .
               "frame-ancestors 'none'; " .
               "base-uri 'self'; " .
               "form-action 'self';";
        
        $response->headers->set('Content-Security-Policy', $csp);
        
        // Permissions-Policy
        $permissionsPolicy = "geolocation=(), microphone=(), camera=(), " .
                            "payment=(), usb=(), magnetometer=(), gyroscope=(), " .
                            "accelerometer=(), ambient-light-sensor=()";
        
        $response->headers->set('Permissions-Policy', $permissionsPolicy);
    }

    /**
     * Get allowed HTML tags for specific contexts
     */
    protected function getAllowedTagsForContext(string $context = 'default'): array
    {
        $allowedTags = [
            'default' => ['p', 'br', 'strong', 'em', 'u', 'b', 'i'],
            'description' => ['p', 'br', 'strong', 'em', 'u', 'b', 'i', 'ul', 'ol', 'li'],
            'notes' => ['p', 'br', 'strong', 'em', 'u', 'b', 'i', 'ul', 'ol', 'li'],
            'address' => ['p', 'br', 'strong', 'em'],
        ];

        return $allowedTags[$context] ?? $allowedTags['default'];
    }

    /**
     * Sanitize HTML with allowed tags
     */
    protected function sanitizeHtmlWithAllowedTags(string $input, string $context = 'default'): string
    {
        $allowedTags = $this->getAllowedTagsForContext($context);
        $allowedTagsString = '<' . implode('><', $allowedTags) . '>';
        
        // Strip all tags except allowed ones
        $input = strip_tags($input, $allowedTagsString);
        
        // Remove dangerous attributes
        $input = preg_replace('/\s*on\w+\s*=\s*["\'][^"\']*["\']/i', '', $input);
        $input = preg_replace('/\s*javascript\s*:/i', '', $input);
        
        return $input;
    }
}