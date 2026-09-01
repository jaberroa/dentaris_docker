<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class TestPatientsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:patients 
                            {--coverage : Generate coverage report}
                            {--html : Generate HTML coverage report}
                            {--xml : Generate XML coverage report}
                            {--text : Generate text coverage report}
                            {--min=80 : Minimum coverage percentage}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run all tests for the Patients module with optional coverage reporting';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('🧪 Running Patients module tests...');
        
        $coverage = $this->option('coverage');
        $html = $this->option('html');
        $xml = $this->option('xml');
        $text = $this->option('text');
        $minCoverage = $this->option('min');

        // Build the test command
        $command = 'test --filter=Patient';
        
        if ($coverage || $html || $xml || $text) {
            $command .= ' --coverage';
            
            if ($html) {
                $command .= ' --coverage-html=coverage/patients';
            }
            
            if ($xml) {
                $command .= ' --coverage-clover=coverage/patients/coverage.xml';
            }
            
            if ($text) {
                $command .= ' --coverage-text';
            }
        }

        $this->info("Running command: php artisan {$command}");
        
        // Execute the test command
        $exitCode = Artisan::call($command);
        
        if ($exitCode === 0) {
            $this->info('✅ All tests passed!');
            
            if ($coverage || $html || $xml || $text) {
                $this->info('📊 Coverage reports generated:');
                
                if ($html) {
                    $this->line('   - HTML: coverage/patients/index.html');
                }
                
                if ($xml) {
                    $this->line('   - XML: coverage/patients/coverage.xml');
                }
                
                if ($text) {
                    $this->line('   - Text: coverage.txt');
                }
                
                // Check minimum coverage if specified
                if ($minCoverage > 0) {
                    $this->checkCoverage($minCoverage);
                }
            }
        } else {
            $this->error('❌ Some tests failed!');
            return 1;
        }

        return 0;
    }

    /**
     * Check if coverage meets minimum requirements
     */
    private function checkCoverage($minCoverage)
    {
        $this->info("🔍 Checking minimum coverage of {$minCoverage}%...");
        
        // This would need to be implemented based on the actual coverage output
        // For now, we'll just show a message
        $this->warn('Coverage validation not yet implemented. Please check the generated reports manually.');
    }
}
