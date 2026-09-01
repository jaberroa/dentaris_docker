<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueryOptimizer;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class MonitorPerformance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'performance:monitor {--detailed : Show detailed performance metrics}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Monitor application performance metrics';

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
        $this->info('Monitoreando rendimiento de la aplicación...');

        $this->showSystemMetrics();
        $this->showDatabaseMetrics();
        $this->showCacheMetrics();
        
        if ($this->option('detailed')) {
            $this->showDetailedMetrics();
        }

        $this->info('Monitoreo completado.');
    }

    protected function showSystemMetrics()
    {
        $this->info('📊 Métricas del Sistema:');
        
        $metrics = [
            'Memory Usage' => $this->formatBytes(memory_get_usage(true)),
            'Peak Memory' => $this->formatBytes(memory_get_peak_usage(true)),
            'PHP Version' => PHP_VERSION,
            'Laravel Version' => app()->version(),
            'Environment' => app()->environment(),
            'Debug Mode' => config('app.debug') ? 'Enabled' : 'Disabled',
        ];

        foreach ($metrics as $key => $value) {
            $this->line("  {$key}: {$value}");
        }
    }

    protected function showDatabaseMetrics()
    {
        $this->info('🗄️ Métricas de Base de Datos:');
        
        try {
            $connection = DB::connection();
            $this->line("  Driver: " . $connection->getDriverName());
            $this->line("  Database: " . $connection->getDatabaseName());
            
            // Estadísticas de tablas
            $dbStats = $this->queryOptimizer->getDatabaseStats();
            if (!empty($dbStats) && !isset($dbStats['error'])) {
                $totalSize = array_sum(array_column($dbStats, 'size_mb'));
                $totalRows = array_sum(array_column($dbStats, 'table_rows'));
                
                $this->line("  Total Tables: " . count($dbStats));
                $this->line("  Total Rows: " . number_format($totalRows));
                $this->line("  Total Size: " . round($totalSize, 2) . " MB");
            }

            // Consultas lentas
            $slowQueries = $this->queryOptimizer->analyzeSlowQueries();
            if (!empty($slowQueries) && !isset($slowQueries['error'])) {
                $this->warn("  ⚠️ Consultas lentas detectadas: " . count($slowQueries));
            } else {
                $this->line("  ✅ No hay consultas lentas");
            }

        } catch (\Exception $e) {
            $this->error("  ❌ Error obteniendo métricas de DB: " . $e->getMessage());
        }
    }

    protected function showCacheMetrics()
    {
        $this->info('💾 Métricas de Cache:');
        
        try {
            $cacheStats = $this->cacheService->getCacheStatistics();
            $this->line("  Driver: " . $cacheStats['cache_driver']);
            $this->line("  Prefix: " . $cacheStats['cache_prefix']);
            
            // Test cache performance
            $startTime = microtime(true);
            Cache::put('performance_test', 'test_value', 60);
            $value = Cache::get('performance_test');
            $endTime = microtime(true);
            
            $cacheTime = round(($endTime - $startTime) * 1000, 2);
            $this->line("  Performance: {$cacheTime}ms");
            
            Cache::forget('performance_test');
            
        } catch (\Exception $e) {
            $this->error("  ❌ Error obteniendo métricas de cache: " . $e->getMessage());
        }
    }

    protected function showDetailedMetrics()
    {
        $this->info('🔍 Métricas Detalladas:');
        
        // Métricas de consultas
        $this->line("  Consultas ejecutadas: " . DB::getQueryLog() ? count(DB::getQueryLog()) : 'N/A');
        
        // Métricas de memoria por función
        $this->line("  Memory Limit: " . ini_get('memory_limit'));
        $this->line("  Max Execution Time: " . ini_get('max_execution_time') . 's');
        
        // Métricas de cache detalladas
        if (config('cache.default') === 'redis') {
            try {
                $redis = Cache::getRedis();
                $info = $redis->info();
                $this->line("  Redis Memory: " . $this->formatBytes($info['used_memory']));
                $this->line("  Redis Keys: " . $info['db0'] ?? 'N/A');
            } catch (\Exception $e) {
                $this->warn("  Redis info no disponible");
            }
        }
    }

    protected function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, $precision) . ' ' . $units[$i];
    }
}