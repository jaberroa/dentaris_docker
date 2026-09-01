<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);

        $this->logPerformanceMetrics($request, $response, $startTime, $endTime, $startMemory, $endMemory);

        return $response;
    }

    /**
     * Log performance metrics
     */
    protected function logPerformanceMetrics(
        Request $request,
        Response $response,
        float $startTime,
        float $endTime,
        int $startMemory,
        int $endMemory
    ): void {
        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = $endMemory - $startMemory;
        $peakMemory = memory_get_peak_usage(true);

        $metrics = [
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'execution_time_ms' => round($executionTime, 2),
            'memory_used_bytes' => $memoryUsed,
            'memory_used_mb' => round($memoryUsed / 1024 / 1024, 2),
            'peak_memory_mb' => round($peakMemory / 1024 / 1024, 2),
            'status_code' => $response->getStatusCode(),
            'user_agent' => $request->userAgent(),
            'ip' => $request->ip(),
            'timestamp' => now()->toISOString(),
        ];

        // Log slow requests
        if ($executionTime > config('performance.database.slow_query_threshold', 100)) {
            Log::warning('Slow request detected', $metrics);
        }

        // Log high memory usage
        if ($memoryUsed > 50 * 1024 * 1024) { // 50MB
            Log::warning('High memory usage detected', $metrics);
        }

        // Log performance metrics in development
        if (app()->environment('local')) {
            Log::info('Performance metrics', $metrics);
        }

        // Add performance headers
        $response->headers->set('X-Execution-Time', $executionTime . 'ms');
        $response->headers->set('X-Memory-Usage', $this->formatBytes($memoryUsed));
        $response->headers->set('X-Peak-Memory', $this->formatBytes($peakMemory));
    }

    /**
     * Format bytes to human readable format
     */
    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}