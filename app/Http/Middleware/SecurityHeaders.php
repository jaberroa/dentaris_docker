<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $this->addSecurityHeaders($response);

        return $response;
    }

    /**
     * Add security headers to response
     */
    protected function addSecurityHeaders(Response $response): void
    {
        // Prevent clickjacking
        $response->headers->set('X-Frame-Options', 'DENY');
        
        // Prevent MIME type sniffing
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        
        // XSS Protection
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        
        // Referrer Policy
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        
        // Content Security Policy
        $this->addContentSecurityPolicy($response);
        
        // Permissions Policy
        $this->addPermissionsPolicy($response);
        
        // Strict Transport Security (HTTPS only)
        if ($this->isHttps()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }
        
        // Cross-Origin Policies
        $this->addCrossOriginPolicies($response);
        
        // Additional security headers
        $this->addAdditionalSecurityHeaders($response);
    }

    /**
     * Add Content Security Policy
     */
    protected function addContentSecurityPolicy(Response $response): void
    {
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com",
            "img-src 'self' data: https: blob:",
            "font-src 'self' https://fonts.gstatic.com data:",
            "connect-src 'self' https: wss:",
            "media-src 'self' data: blob:",
            "object-src 'none'",
            "child-src 'self' blob:",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
            "manifest-src 'self'",
            "worker-src 'self' blob:",
            "upgrade-insecure-requests",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));
    }

    /**
     * Add Permissions Policy
     */
    protected function addPermissionsPolicy(Response $response): void
    {
        $permissions = [
            'geolocation=()',
            'microphone=()',
            'camera=()',
            'payment=()',
            'usb=()',
            'magnetometer=()',
            'gyroscope=()',
            'accelerometer=()',
            'ambient-light-sensor=()',
            'autoplay=()',
            'encrypted-media=()',
            'fullscreen=(self)',
            'picture-in-picture=()',
        ];

        $response->headers->set('Permissions-Policy', implode(', ', $permissions));
    }

    /**
     * Add Cross-Origin Policies
     */
    protected function addCrossOriginPolicies(Response $response): void
    {
        // Cross-Origin Embedder Policy
        $response->headers->set('Cross-Origin-Embedder-Policy', 'require-corp');
        
        // Cross-Origin Opener Policy
        $response->headers->set('Cross-Origin-Opener-Policy', 'same-origin');
        
        // Cross-Origin Resource Policy
        $response->headers->set('Cross-Origin-Resource-Policy', 'same-origin');
    }

    /**
     * Add additional security headers
     */
    protected function addAdditionalSecurityHeaders(Response $response): void
    {
        // Server information hiding
        $response->headers->remove('Server');
        $response->headers->remove('X-Powered-By');
        
        // Cache control for sensitive pages
        if ($this->isSensitivePage()) {
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
            $response->headers->set('Pragma', 'no-cache');
            $response->headers->set('Expires', '0');
        }
        
        // Feature Policy (legacy, for older browsers)
        $response->headers->set('Feature-Policy', 'geolocation \'none\'; microphone \'none\'; camera \'none\'');
        
        // Expect-CT (Certificate Transparency)
        if ($this->isHttps()) {
            $response->headers->set('Expect-CT', 'max-age=86400, enforce');
        }
    }

    /**
     * Check if request is HTTPS
     */
    protected function isHttps(): bool
    {
        return request()->isSecure() || request()->header('X-Forwarded-Proto') === 'https';
    }

    /**
     * Check if current page is sensitive
     */
    protected function isSensitivePage(): bool
    {
        $sensitivePaths = [
            '/login',
            '/register',
            '/password',
            '/profile',
            '/admin',
            '/dashboard',
            '/api/',
        ];

        $currentPath = request()->path();

        foreach ($sensitivePaths as $path) {
            if (str_starts_with($currentPath, ltrim($path, '/'))) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get environment-specific CSP
     */
    protected function getEnvironmentCsp(): array
    {
        $csp = [
            "default-src 'self'",
            "script-src 'self'",
            "style-src 'self' 'unsafe-inline'",
            "img-src 'self' data:",
            "font-src 'self'",
            "connect-src 'self'",
            "object-src 'none'",
            "frame-ancestors 'none'",
            "base-uri 'self'",
            "form-action 'self'",
        ];

        // Add development-specific policies
        if (app()->environment('local', 'development')) {
            $csp[1] = "script-src 'self' 'unsafe-inline' 'unsafe-eval'";
            $csp[2] = "style-src 'self' 'unsafe-inline'";
        }

        return $csp;
    }

    /**
     * Add API-specific headers
     */
    protected function addApiHeaders(Response $response): void
    {
        if (request()->is('api/*')) {
            $response->headers->set('X-Content-Type-Options', 'nosniff');
            $response->headers->set('X-Frame-Options', 'DENY');
            $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate, private');
        }
    }
}