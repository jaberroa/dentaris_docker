<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class RunSecurityTests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'security:test-suite {--coverage : Generate test coverage report} {--detailed : Show detailed output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run comprehensive security test suite';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🔒 Ejecutando suite completa de tests de seguridad...');

        $coverage = $this->option('coverage');
        $verbose = $this->option('detailed');

        $tests = [
            'SecurityMiddlewareTest' => 'Tests de middleware de seguridad',
            'TwoFactorAuthTest' => 'Tests de autenticación 2FA',
            'ApiSecurityTest' => 'Tests de seguridad de APIs',
            'PenetrationTest' => 'Tests de penetración',
        ];

        $results = [];
        $totalTests = 0;
        $passedTests = 0;
        $failedTests = 0;

        foreach ($tests as $testClass => $description) {
            $this->info("🧪 Ejecutando: {$description}");
            
            $command = "php artisan test tests/Feature/{$testClass}.php";
            
            if ($coverage) {
                $command .= " --coverage";
            }
            
            if ($verbose) {
                $command .= " --verbose";
            }

            $output = [];
            $returnCode = 0;
            
            exec($command . ' 2>&1', $output, $returnCode);
            
            $testResult = [
                'class' => $testClass,
                'description' => $description,
                'passed' => $returnCode === 0,
                'output' => implode("\n", $output),
            ];

            $results[] = $testResult;
            
            if ($testResult['passed']) {
                $this->info("  ✅ {$description} - PASÓ");
                $passedTests++;
            } else {
                $this->error("  ❌ {$description} - FALLÓ");
                $failedTests++;
                
                if ($verbose) {
                    $this->line("    Output: " . $testResult['output']);
                }
            }
            
            $totalTests++;
        }

        // Run additional security checks
        $this->runAdditionalSecurityChecks();

        // Generate report
        $this->generateSecurityTestReport($results, $totalTests, $passedTests, $failedTests);

        $this->info("\n📊 Resumen de Tests de Seguridad:");
        $this->line("  Total: {$totalTests}");
        $this->line("  Pasaron: {$passedTests}");
        $this->line("  Fallaron: {$failedTests}");
        
        if ($failedTests > 0) {
            $this->error("  ⚠️ Se encontraron fallos en los tests de seguridad");
            return 1;
        } else {
            $this->info("  ✅ Todos los tests de seguridad pasaron");
            return 0;
        }
    }

    protected function runAdditionalSecurityChecks()
    {
        $this->info("\n🔍 Ejecutando verificaciones adicionales de seguridad...");

        // Check for security vulnerabilities in dependencies
        $this->checkDependencyVulnerabilities();

        // Check for security misconfigurations
        $this->checkSecurityConfigurations();

        // Check for exposed sensitive information
        $this->checkExposedSecrets();

        // Check for weak passwords
        $this->checkWeakPasswords();
    }

    protected function checkDependencyVulnerabilities()
    {
        $this->info("  🔍 Verificando vulnerabilidades en dependencias...");
        
        $command = 'composer audit --format=json';
        $output = [];
        $returnCode = 0;
        
        exec($command . ' 2>&1', $output, $returnCode);
        
        if ($returnCode === 0) {
            $this->line("    ✅ No se encontraron vulnerabilidades conocidas");
        } else {
            $this->warn("    ⚠️ Se encontraron vulnerabilidades en dependencias");
            if ($this->option('verbose')) {
                $this->line("    Output: " . implode("\n", $output));
            }
        }
    }

    protected function checkSecurityConfigurations()
    {
        $this->info("  🔍 Verificando configuraciones de seguridad...");
        
        $configurations = [
            'APP_DEBUG' => env('APP_DEBUG', false),
            'APP_ENV' => env('APP_ENV', 'production'),
            'DB_ENCRYPTION' => config('database.encryption', false),
            'SESSION_SECURE' => config('session.secure', false),
            'SESSION_HTTP_ONLY' => config('session.http_only', true),
        ];

        $issues = [];
        
        foreach ($configurations as $config => $value) {
            if ($config === 'APP_DEBUG' && $value === true) {
                $issues[] = "APP_DEBUG está habilitado en producción";
            }
            if ($config === 'APP_ENV' && $value !== 'production') {
                $issues[] = "APP_ENV no está configurado para producción";
            }
        }

        if (empty($issues)) {
            $this->line("    ✅ Configuraciones de seguridad correctas");
        } else {
            $this->warn("    ⚠️ Problemas de configuración encontrados:");
            foreach ($issues as $issue) {
                $this->line("      - {$issue}");
            }
        }
    }

    protected function checkExposedSecrets()
    {
        $this->info("  🔍 Verificando información sensible expuesta...");
        
        $sensitiveFiles = [
            '.env',
            'config/database.php',
            'storage/logs/',
            'bootstrap/cache/',
        ];

        $exposedSecrets = [];
        
        foreach ($sensitiveFiles as $file) {
            if (file_exists(base_path($file))) {
                if (is_file(base_path($file))) {
                    $content = file_get_contents(base_path($file));
                    if (strpos($content, 'password') !== false || 
                        strpos($content, 'secret') !== false ||
                        strpos($content, 'key') !== false) {
                        $exposedSecrets[] = $file;
                    }
                }
            }
        }

        if (empty($exposedSecrets)) {
            $this->line("    ✅ No se encontraron secretos expuestos");
        } else {
            $this->warn("    ⚠️ Posibles secretos expuestos en:");
            foreach ($exposedSecrets as $file) {
                $this->line("      - {$file}");
            }
        }
    }

    protected function checkWeakPasswords()
    {
        $this->info("  🔍 Verificando contraseñas débiles...");
        
        // Check for default passwords
        $defaultPasswords = [
            'password',
            '123456',
            'admin',
            'root',
            'test',
        ];

        $weakPasswords = [];
        
        foreach ($defaultPasswords as $password) {
            if (Hash::check($password, '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi')) {
                $weakPasswords[] = $password;
            }
        }

        if (empty($weakPasswords)) {
            $this->line("    ✅ No se encontraron contraseñas débiles");
        } else {
            $this->warn("    ⚠️ Contraseñas débiles detectadas:");
            foreach ($weakPasswords as $password) {
                $this->line("      - {$password}");
            }
        }
    }

    protected function generateSecurityTestReport($results, $totalTests, $passedTests, $failedTests)
    {
        $this->info("\n📄 Generando reporte de tests de seguridad...");

        $report = [
            'timestamp' => now()->toISOString(),
            'summary' => [
                'total_tests' => $totalTests,
                'passed_tests' => $passedTests,
                'failed_tests' => $failedTests,
                'success_rate' => $totalTests > 0 ? round(($passedTests / $totalTests) * 100, 2) : 0,
            ],
            'test_results' => $results,
            'security_score' => $this->calculateSecurityScore($results),
        ];

        $reportPath = storage_path('logs/security-test-report.json');
        file_put_contents($reportPath, json_encode($report, JSON_PRETTY_PRINT));

        $this->info("  📄 Reporte guardado en: {$reportPath}");
    }

    protected function calculateSecurityScore($results)
    {
        $totalTests = count($results);
        $passedTests = collect($results)->where('passed', true)->count();
        
        if ($totalTests === 0) {
            return 0;
        }

        $baseScore = ($passedTests / $totalTests) * 100;
        
        // Bonus points for comprehensive testing
        $bonusPoints = 0;
        
        if ($totalTests >= 4) {
            $bonusPoints += 10; // Comprehensive test suite
        }
        
        if (collect($results)->where('class', 'PenetrationTest')->where('passed', true)->count() > 0) {
            $bonusPoints += 15; // Penetration testing passed
        }
        
        if (collect($results)->where('class', 'TwoFactorAuthTest')->where('passed', true)->count() > 0) {
            $bonusPoints += 10; // 2FA testing passed
        }

        return min(100, $baseScore + $bonusPoints);
    }
}