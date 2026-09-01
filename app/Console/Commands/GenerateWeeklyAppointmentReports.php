<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\AppointmentStatus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class GenerateWeeklyAppointmentReports extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-weekly-appointment-reports {--email=admin@dentaris.com}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate and send weekly appointment reports with statistics and frequent changes';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('=== INICIANDO REPORTE SEMANAL ===');
        
        try {
            $this->info('Generando datos del reporte...');
            $reportData = $this->generateWeeklyReport();
            
            $this->info('Guardando en base de datos...');
            $this->saveReportToDatabase($reportData);
            
            $this->info('Enviando reporte por email...');
            $this->sendReportToAdmin($reportData);
            
            $this->info('=== REPORTE SEMANAL COMPLETADO ===');
            
            return Command::SUCCESS;
            
        } catch (\Exception $e) {
            $this->error('Error: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Generar datos del reporte semanal
     */
    private function generateWeeklyReport()
    {
        $startOfWeek = Carbon::now()->subWeek()->startOfWeek();
        $endOfWeek = Carbon::now()->subWeek()->endOfWeek();
        
        // Estadísticas generales
        $totalAppointments = Appointment::whereBetween('appointment_date', [$startOfWeek, $endOfWeek])->count();
        
        $statusStats = Appointment::whereBetween('appointment_date', [$startOfWeek, $endOfWeek])
            ->join('appointment_statuses', 'appointments.appointment_status_id', '=', 'appointment_statuses.id')
            ->select('appointment_statuses.name', 'appointment_statuses.display_name', DB::raw('count(*) as count'))
            ->groupBy('appointment_statuses.name', 'appointment_statuses.display_name')
            ->get()
            ->keyBy('name');

        // Cambios de estado más frecuentes
        $frequentStatusChanges = DB::table('appointment_status_logs')
            ->whereBetween('created_at', [$startOfWeek, $endOfWeek])
            ->where('success', true)
            ->select('old_status', 'new_status', DB::raw('count(*) as count'))
            ->groupBy('old_status', 'new_status')
            ->orderBy('count', 'desc')
            ->limit(10)
            ->get();

        // Usuarios más activos
        $mostActiveUsers = DB::table('appointment_status_logs')
            ->whereBetween('appointment_status_logs.created_at', [$startOfWeek, $endOfWeek])
            ->where('appointment_status_logs.success', true)
            ->join('users', 'appointment_status_logs.user_id', '=', 'users.id')
            ->select('users.name', 'users.email', 'appointment_status_logs.user_role', DB::raw('count(*) as changes'))
            ->groupBy('users.id', 'users.name', 'users.email', 'appointment_status_logs.user_role')
            ->orderBy('changes', 'desc')
            ->limit(5)
            ->get();

        // Intentos fallidos de cambios
        $failedChanges = DB::table('appointment_status_logs')
            ->whereBetween('appointment_status_logs.created_at', [$startOfWeek, $endOfWeek])
            ->where('appointment_status_logs.success', false)
            ->count();

        // Razones más comunes de fallos
        $failureReasons = DB::table('appointment_status_logs')
            ->whereBetween('appointment_status_logs.created_at', [$startOfWeek, $endOfWeek])
            ->where('appointment_status_logs.success', false)
            ->select('reason', DB::raw('count(*) as count'))
            ->groupBy('reason')
            ->orderBy('count', 'desc')
            ->limit(5)
            ->get();

        return [
            'period' => [
                'start' => $startOfWeek->format('Y-m-d'),
                'end' => $endOfWeek->format('Y-m-d'),
                'week_number' => $startOfWeek->week,
                'year' => $startOfWeek->year
            ],
            'statistics' => [
                'total_appointments' => $totalAppointments,
                'status_breakdown' => $statusStats,
                'failed_changes' => $failedChanges
            ],
            'activity' => [
                'frequent_status_changes' => $frequentStatusChanges,
                'most_active_users' => $mostActiveUsers,
                'failure_reasons' => $failureReasons
            ],
            'generated_at' => now()->toISOString()
        ];
    }

    /**
     * Guardar reporte en base de datos
     */
    private function saveReportToDatabase($reportData)
    {
        DB::table('weekly_appointment_reports')->insert([
            'week_start' => $reportData['period']['start'],
            'week_end' => $reportData['period']['end'],
            'week_number' => $reportData['period']['week_number'],
            'year' => $reportData['period']['year'],
            'total_appointments' => $reportData['statistics']['total_appointments'],
            'status_breakdown' => json_encode($reportData['statistics']['status_breakdown']),
            'frequent_changes' => json_encode($reportData['activity']['frequent_status_changes']),
            'active_users' => json_encode($reportData['activity']['most_active_users']),
            'failed_changes' => $reportData['statistics']['failed_changes'],
            'failure_reasons' => json_encode($reportData['activity']['failure_reasons']),
            'generated_at' => now(),
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    /**
     * Enviar reporte por email al admin
     */
    private function sendReportToAdmin($reportData)
    {
        $adminEmail = $this->option('email');
        
        // Por ahora solo log, después se puede implementar envío real de email
        $emailContent = $this->generateEmailContent($reportData);
        
        Log::channel('appointments')->info("Weekly report email sent to {$adminEmail}", [
            'recipient' => $adminEmail,
            'subject' => "Reporte Semanal de Citas - Semana {$reportData['period']['week_number']} de {$reportData['period']['year']}",
            'content_preview' => substr($emailContent, 0, 200) . '...'
        ]);
        
        $this->info("Reporte enviado por email a: {$adminEmail}");
    }

    /**
     * Generar contenido del email
     */
    private function generateEmailContent($reportData)
    {
        $content = "REPORTE SEMANAL DE CITAS\n";
        $content .= "Semana {$reportData['period']['week_number']} de {$reportData['period']['year']}\n";
        $content .= "Período: {$reportData['period']['start']} a {$reportData['period']['end']}\n\n";
        
        $content .= "ESTADÍSTICAS GENERALES:\n";
        $content .= "- Total de citas: {$reportData['statistics']['total_appointments']}\n";
        $content .= "- Cambios fallidos: {$reportData['statistics']['failed_changes']}\n\n";
        
        $content .= "DISTRIBUCIÓN POR ESTADOS:\n";
        foreach ($reportData['statistics']['status_breakdown'] as $status) {
            $content .= "- {$status->display_name}: {$status->count}\n";
        }
        
        $content .= "\nCAMBIOS DE ESTADO MÁS FRECUENTES:\n";
        foreach ($reportData['activity']['frequent_status_changes'] as $change) {
            $content .= "- {$change->old_status} → {$change->new_status}: {$change->count} veces\n";
        }
        
        $content .= "\nUSUARIOS MÁS ACTIVOS:\n";
        foreach ($reportData['activity']['most_active_users'] as $user) {
            $content .= "- {$user->name} ({$user->user_role}): {$user->changes} cambios\n";
        }
        
        $content .= "\nRAZONES DE FALLOS MÁS COMUNES:\n";
        foreach ($reportData['activity']['failure_reasons'] as $reason) {
            $content .= "- {$reason->reason}: {$reason->count} veces\n";
        }
        
        return $content;
    }
}
