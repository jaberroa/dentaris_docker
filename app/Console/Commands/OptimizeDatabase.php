<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\QueryOptimizer;
use App\Services\CacheService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;

class OptimizeDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'db:optimize {--analyze : Analyze database performance} {--indexes : Add database indexes} {--cache : Clear and rebuild cache}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Optimize database performance by adding indexes and analyzing queries';

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
        $this->info('Iniciando optimización de base de datos...');

        if ($this->option('indexes')) {
            $this->addDatabaseIndexes();
        }

        if ($this->option('analyze')) {
            $this->analyzeDatabasePerformance();
        }

        if ($this->option('cache')) {
            $this->optimizeCache();
        }

        if (!$this->option('indexes') && !$this->option('analyze') && !$this->option('cache')) {
            $this->addDatabaseIndexes();
            $this->analyzeDatabasePerformance();
            $this->optimizeCache();
        }

        $this->info('Optimización de base de datos completada.');
    }

    protected function addDatabaseIndexes()
    {
        $this->info('Agregando índices de base de datos...');
        
        try {
            $this->queryOptimizer->addCommonIndexes();
            $this->info('✅ Índices agregados exitosamente');
        } catch (\Exception $e) {
            $this->error('❌ Error agregando índices: ' . $e->getMessage());
        }
    }

    protected function analyzeDatabasePerformance()
    {
        $this->info('Analizando rendimiento de base de datos...');

        try {
            // Estadísticas de base de datos
            $dbStats = $this->queryOptimizer->getDatabaseStats();
            if (!empty($dbStats) && !isset($dbStats['error'])) {
                $tableData = array_map(function($stat) {
                    return [
                        $stat->table_name,
                        $stat->table_rows,
                        $stat->size_mb
                    ];
                }, $dbStats);
                $this->table(['Table', 'Rows', 'Size (MB)'], $tableData);
            } else {
                $this->warn('No se pudieron obtener estadísticas de base de datos');
            }

            // Consultas lentas
            $slowQueries = $this->queryOptimizer->analyzeSlowQueries();
            if (!empty($slowQueries) && !isset($slowQueries['error'])) {
                $this->warn('Consultas lentas detectadas:');
                $tableData = array_map(function($query) {
                    return [
                        substr($query->sql_text ?? 'N/A', 0, 50) . '...',
                        $query->exec_count ?? 'N/A',
                        $query->avg_time_seconds ?? 'N/A',
                        $query->total_time_seconds ?? 'N/A'
                    ];
                }, $slowQueries);
                $this->table(['Query', 'Exec Count', 'Avg Time (s)', 'Total Time (s)'], $tableData);
            } else {
                $this->info('✅ No se detectaron consultas lentas');
            }

            // Estadísticas de cache
            $cacheStats = $this->cacheService->getCacheStatistics();
            $this->info('Estadísticas de cache:');
            $this->line("Driver: {$cacheStats['cache_driver']}");
            $this->line("Memory Usage: " . $this->formatBytes($cacheStats['memory_usage']));
            $this->line("Peak Memory: " . $this->formatBytes($cacheStats['peak_memory']));

        } catch (\Exception $e) {
            $this->error('❌ Error analizando rendimiento: ' . $e->getMessage());
        }
    }

    protected function optimizeCache()
    {
        $this->info('Optimizando cache...');

        try {
            // Limpiar cache existente
            $this->cacheService->clearAllCache();
            $this->info('✅ Cache limpiado');

            // Los caches clínicos se reconstruyen bajo demanda porque cada
            // clave exige un contexto de membresía validado.
            $this->info('✅ Cache clínico listo para reconstrucción segura bajo demanda');

        } catch (\Exception $e) {
            $this->error('❌ Error optimizando cache: ' . $e->getMessage());
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
