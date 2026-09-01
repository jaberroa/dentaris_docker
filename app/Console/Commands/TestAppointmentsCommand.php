<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;

class TestAppointmentsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:appointments 
                            {--unit : Run only unit tests}
                            {--feature : Run only feature tests}
                            {--api : Run only API tests}
                            {--coverage : Run with code coverage}
                            {--html : Generate HTML coverage report}
                            {--xml : Generate XML coverage report}
                            {--fast : Run tests quickly without coverage}
                            {--verbose : Verbose output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run appointment module tests with various options';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Ejecutando pruebas del módulo de appointments...');
        $this->newLine();

        // Verificar que estamos en el directorio correcto
        if (!File::exists(base_path('artisan'))) {
            $this->error('❌ No se encontró el archivo artisan. Asegúrate de ejecutar este comando desde la raíz del proyecto Laravel.');
            return 1;
        }

        // Crear directorios de cobertura si es necesario
        if ($this->option('coverage') || $this->option('html') || $this->option('xml')) {
            $coverageDir = base_path('coverage/appointments');
            if (!File::exists($coverageDir)) {
                File::makeDirectory($coverageDir, 0755, true);
                $this->info("📁 Directorio de cobertura creado: $coverageDir");
            }
        }

        // Construir comando de prueba
        $command = $this->buildTestCommand();
        
        $this->info("🔧 Comando a ejecutar: $command");
        $this->newLine();

        // Ejecutar pruebas
        $result = Process::run($command);

        // Mostrar resultado
        $this->displayResult($result);

        return $result->successful() ? 0 : 1;
    }

    /**
     * Construir el comando de prueba basado en las opciones
     */
    private function buildTestCommand(): string
    {
        $command = 'php artisan test';

        // Agregar archivos de prueba específicos
        $testFiles = [];
        
        if ($this->option('unit')) {
            $testFiles[] = 'tests/Unit/AppointmentTest.php';
        }
        
        if ($this->option('feature')) {
            $testFiles[] = 'tests/Feature/AppointmentTest.php';
        }
        
        if ($this->option('api')) {
            $testFiles[] = 'tests/Feature/AppointmentApiTest.php';
        }

        // Si no se especificó ningún tipo, ejecutar todos
        if (empty($testFiles)) {
            $testFiles = [
                'tests/Unit/AppointmentTest.php',
                'tests/Feature/AppointmentTest.php',
                'tests/Feature/AppointmentApiTest.php'
            ];
        }

        $command .= ' ' . implode(' ', $testFiles);

        // Agregar opciones de cobertura
        if ($this->option('coverage') || $this->option('html') || $this->option('xml')) {
            $coverageOptions = [];
            
            if ($this->option('html')) {
                $coverageOptions[] = '--coverage-html=coverage/appointments/html';
            }
            
            if ($this->option('xml')) {
                $coverageOptions[] = '--coverage-xml=coverage/appointments/coverage.xml';
            }
            
            if (empty($coverageOptions)) {
                $coverageOptions[] = '--coverage-text';
            }
            
            $command .= ' ' . implode(' ', $coverageOptions);
        }

        // Agregar opciones adicionales
        if ($this->option('verbose')) {
            $command .= ' --verbose';
        }

        // Usar configuración específica de appointments
        $command .= ' --configuration=phpunit-appointments.xml';

        return $command;
    }

    /**
     * Mostrar el resultado de las pruebas
     */
    private function displayResult($result): void
    {
        $this->newLine();
        
        if ($result->successful()) {
            $this->info('✅ ¡Todas las pruebas pasaron exitosamente!');
            
            // Mostrar información de cobertura si se generó
            if ($this->option('coverage') || $this->option('html') || $this->option('xml')) {
                $this->newLine();
                $this->info('📊 Información de cobertura:');
                
                if ($this->option('html')) {
                    $this->line('   📄 Reporte HTML: coverage/appointments/html/index.html');
                }
                
                if ($this->option('xml')) {
                    $this->line('   📄 Reporte XML: coverage/appointments/coverage.xml');
                }
                
                $coverageTextFile = base_path('coverage/appointments/coverage.txt');
                if (File::exists($coverageTextFile)) {
                    $this->line('   📄 Reporte de texto: coverage/appointments/coverage.txt');
                }
            }
            
            $this->newLine();
            $this->info('🎉 Módulo de appointments APTO para integración');
            
        } else {
            $this->error('❌ Algunas pruebas fallaron');
            $this->newLine();
            
            if ($result->output()) {
                $this->error('Output de error:');
                $this->line($result->output());
            }
            
            if ($result->errorOutput()) {
                $this->error('Error output:');
                $this->line($result->errorOutput());
            }
            
            $this->newLine();
            $this->error('🔧 Revisar los errores antes de continuar');
        }
    }
}

