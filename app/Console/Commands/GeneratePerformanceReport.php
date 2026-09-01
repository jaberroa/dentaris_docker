<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueryOptimizer;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class GeneratePerformanceReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:report {--output= : Output file path} {--format=html : Report format (html, json, txt)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a comprehensive performance report';

    protected $queryOptimizer;
    protected $cacheService;

    public function __construct(QueryOptimizer $queryOptimizer, CacheService $cacheService)
    {
        parent::__construct();
        $this->queryOptimizer = $queryOptimizer;
        $this->cacheService = $cacheService;
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generando reporte de rendimiento...');

        $report = $this->generateReport();
        $format = $this->option('format');
        $output = $this->option('output');

        if ($output) {
            $this->saveReport($report, $output, $format);
            $this->info("Reporte guardado en: {$output}");
        } else {
            $this->displayReport($report, $format);
        }
    }

    protected function generateReport(): array
    {
        $report = [
            'generated_at' => now()->toISOString(),
            'system' => $this->getSystemInfo(),
            'database' => $this->getDatabaseInfo(),
            'cache' => $this->getCacheInfo(),
            'performance' => $this->getPerformanceMetrics(),
            'recommendations' => $this->getRecommendations(),
        ];

        return $report;
    }

    protected function getSystemInfo(): array
    {
        return [
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'environment' => app()->environment(),
            'debug_mode' => config('app.debug'),
            'memory_limit' => ini_get('memory_limit'),
            'max_execution_time' => ini_get('max_execution_time'),
            'current_memory_usage' => $this->formatBytes(memory_get_usage(true)),
            'peak_memory_usage' => $this->formatBytes(memory_get_peak_usage(true)),
        ];
    }

    protected function getDatabaseInfo(): array
    {
        try {
            $connection = DB::connection();
            $dbStats = $this->queryOptimizer->getDatabaseStats();
            $slowQueries = $this->queryOptimizer->analyzeSlowQueries();

            return [
                'driver' => $connection->getDriverName(),
                'database' => $connection->getDatabaseName(),
                'tables' => $dbStats,
                'slow_queries' => $slowQueries,
                'total_size_mb' => array_sum(array_column($dbStats, 'size_mb')),
                'total_rows' => array_sum(array_column($dbStats, 'table_rows')),
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function getCacheInfo(): array
    {
        try {
            $cacheStats = $this->cacheService->getCacheStatistics();
            
            // Test cache performance
            $startTime = microtime(true);
            Cache::put('performance_test', 'test_value', 60);
            $value = Cache::get('performance_test');
            $endTime = microtime(true);
            
            Cache::forget('performance_test');

            return [
                'driver' => $cacheStats['cache_driver'],
                'prefix' => $cacheStats['cache_prefix'],
                'performance_ms' => round(($endTime - $startTime) * 1000, 2),
                'memory_usage' => $cacheStats['memory_usage'],
                'peak_memory' => $cacheStats['peak_memory'],
            ];
        } catch (\Exception $e) {
            return ['error' => $e->getMessage()];
        }
    }

    protected function getPerformanceMetrics(): array
    {
        return [
            'query_count' => count(DB::getQueryLog()),
            'cache_hits' => 0, // Would need to implement cache hit tracking
            'cache_misses' => 0, // Would need to implement cache miss tracking
            'average_response_time' => 0, // Would need to implement response time tracking
        ];
    }

    protected function getRecommendations(): array
    {
        $recommendations = [];

        // Memory recommendations
        $memoryUsage = memory_get_usage(true);
        if ($memoryUsage > 100 * 1024 * 1024) { // 100MB
            $recommendations[] = [
                'type' => 'memory',
                'priority' => 'high',
                'message' => 'High memory usage detected. Consider optimizing queries or increasing memory limit.',
            ];
        }

        // Database recommendations
        try {
            $dbStats = $this->queryOptimizer->getDatabaseStats();
            if (!empty($dbStats)) {
                $totalSize = array_sum(array_column($dbStats, 'size_mb'));
                if ($totalSize > 100) { // 100MB
                    $recommendations[] = [
                        'type' => 'database',
                        'priority' => 'medium',
                        'message' => 'Large database size. Consider archiving old data or optimizing tables.',
                    ];
                }
            }
        } catch (\Exception $e) {
            // Ignore errors
        }

        // Cache recommendations
        if (config('cache.default') === 'file') {
            $recommendations[] = [
                'type' => 'cache',
                'priority' => 'low',
                'message' => 'Using file cache. Consider upgrading to Redis for better performance.',
            ];
        }

        return $recommendations;
    }

    protected function saveReport(array $report, string $output, string $format): void
    {
        $content = match ($format) {
            'json' => json_encode($report, JSON_PRETTY_PRINT),
            'html' => $this->generateHtmlReport($report),
            'txt' => $this->generateTextReport($report),
            default => json_encode($report, JSON_PRETTY_PRINT),
        };

        File::put($output, $content);
    }

    protected function displayReport(array $report, string $format): void
    {
        match ($format) {
            'json' => $this->line(json_encode($report, JSON_PRETTY_PRINT)),
            'html' => $this->line($this->generateHtmlReport($report)),
            'txt' => $this->line($this->generateTextReport($report)),
            default => $this->line(json_encode($report, JSON_PRETTY_PRINT)),
        };
    }

    protected function generateHtmlReport(array $report): string
    {
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Performance Report - ' . $report['generated_at'] . '</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .section { margin-bottom: 30px; }
        .metric { margin: 10px 0; }
        .recommendation { padding: 10px; margin: 5px 0; border-left: 4px solid #007bff; background: #f8f9fa; }
        .high { border-left-color: #dc3545; }
        .medium { border-left-color: #ffc107; }
        .low { border-left-color: #28a745; }
    </style>
</head>
<body>
    <h1>Performance Report</h1>
    <p>Generated: ' . $report['generated_at'] . '</p>';

        // System info
        $html .= '<div class="section"><h2>System Information</h2>';
        foreach ($report['system'] as $key => $value) {
            $html .= "<div class='metric'><strong>{$key}:</strong> {$value}</div>";
        }
        $html .= '</div>';

        // Recommendations
        if (!empty($report['recommendations'])) {
            $html .= '<div class="section"><h2>Recommendations</h2>';
            foreach ($report['recommendations'] as $rec) {
                $html .= "<div class='recommendation {$rec['priority']}'>
                    <strong>{$rec['type']}:</strong> {$rec['message']}
                </div>";
            }
            $html .= '</div>';
        }

        $html .= '</body></html>';
        return $html;
    }

    protected function generateTextReport(array $report): string
    {
        $text = "PERFORMANCE REPORT\n";
        $text .= "Generated: " . $report['generated_at'] . "\n\n";

        $text .= "SYSTEM INFORMATION\n";
        $text .= "==================\n";
        foreach ($report['system'] as $key => $value) {
            $text .= "{$key}: {$value}\n";
        }

        $text .= "\nRECOMMENDATIONS\n";
        $text .= "===============\n";
        foreach ($report['recommendations'] as $rec) {
            $text .= "[{$rec['priority']}] {$rec['type']}: {$rec['message']}\n";
        }

        return $text;
    }

    protected function formatBytes(int $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}