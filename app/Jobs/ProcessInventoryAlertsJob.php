<?php

namespace App\Jobs;

use App\Services\InventoryAlertService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessInventoryAlertsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(InventoryAlertService $alertService): void
    {
        try {
            Log::info("Starting inventory alerts processing");

            // Verificar todas las alertas de inventario
            $alerts = $alertService->checkInventoryAlerts();

            $alertCount = count($alerts);
            
            Log::info("Inventory alerts processing completed", [
                'total_alerts' => $alertCount,
                'alert_types' => array_count_values(array_column($alerts, 'type'))
            ]);

            // Si hay alertas críticas, enviar notificación adicional
            $criticalAlerts = array_filter($alerts, function($alert) {
                return $alert['severity'] === 'critical';
            });

            if (count($criticalAlerts) > 0) {
                Log::warning("Critical inventory alerts detected", [
                    'critical_count' => count($criticalAlerts)
                ]);
                
                // TODO: Enviar notificación a administradores sobre alertas críticas
                // $this->notifyAdminsOfCriticalAlerts($criticalAlerts);
            }

        } catch (\Exception $e) {
            Log::error("Error processing inventory alerts", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Process inventory alerts job failed", [
            'error' => $exception->getMessage(),
            'trace' => $exception->getTraceAsString()
        ]);
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'inventory-alerts',
            'scheduled-job'
        ];
    }

    /**
     * Notificar a administradores sobre alertas críticas
     */
    private function notifyAdminsOfCriticalAlerts($criticalAlerts)
    {
        // TODO: Implementar notificación a administradores
        // Esto podría incluir:
        // - Envío de email a administradores
        // - Notificación en el sistema
        // - Integración con Slack/Discord
    }
}





