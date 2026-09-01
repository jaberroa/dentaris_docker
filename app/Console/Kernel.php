<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Recordatorios de citas - cada hora
        $schedule->command('notifications:send-appointment-reminders')
            ->hourly()
            ->withoutOverlapping()
            ->runInBackground();

        // Recordatorios de pagos - diario a las 9:00 AM
        $schedule->command('notifications:send-payment-reminders')
            ->dailyAt('09:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Verificación de alertas de inventario - cada 6 horas
        $schedule->command('notifications:check-inventory-alerts')
            ->everySixHours()
            ->withoutOverlapping()
            ->runInBackground();

        // Limpieza de logs antiguos - semanal
        $schedule->command('activitylog:clean')
            ->weekly()
            ->withoutOverlapping();

        // Backup de base de datos - diario a las 2:00 AM
        $schedule->command('backup:run')
            ->dailyAt('02:00')
            ->withoutOverlapping()
            ->runInBackground();

        // Reportes semanales de citas - todos los lunes a las 8:00 AM
        $schedule->command('app:generate-weekly-appointment-reports')
            ->weeklyOn(1, '08:00')
            ->withoutOverlapping()
            ->runInBackground();
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}





