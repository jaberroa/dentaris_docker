<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\NotificationTemplate;
use App\Services\NotificationService;
use App\Jobs\AppointmentReminderJob;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-appointment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de citas programadas';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando envío de recordatorios de citas...');

        // Recordatorios de 24 horas
        $this->sendReminders('24_hour', 24);
        
        // Recordatorios de 1 hora
        $this->sendReminders('1_hour', 1);
        
        // Recordatorios del mismo día
        $this->sendReminders('same_day', 0);

        $this->info('Recordatorios enviados exitosamente.');
    }

    protected function sendReminders($type, $hours)
    {
        $targetTime = Carbon::now()->addHours($hours);
        $startTime = $targetTime->copy()->startOfHour();
        $endTime = $targetTime->copy()->endOfHour();

        $appointments = Appointment::with(['patient', 'staff.user', 'appointmentStatus'])
            ->whereHas('appointmentStatus', function ($query) {
                $query->where('name', 'scheduled');
            })
            ->whereBetween('start_time', [$startTime, $endTime])
            ->get();

        $this->info("Enviando recordatorios {$type} para {$appointments->count()} citas...");

        foreach ($appointments as $appointment) {
            // Verificar si ya se envió este tipo de recordatorio
            $alreadySent = \App\Models\AppointmentReminder::where('appointment_id', $appointment->id)
                ->where('reminder_type', $type)
                ->where('sent_at', '>=', Carbon::now()->subHours(2))
                ->exists();

            if (!$alreadySent) {
                // Enviar recordatorio
                AppointmentReminderJob::dispatch(
                    $appointment->id,
                    $type
                );

                // Registrar el recordatorio enviado
                \App\Models\AppointmentReminder::create([
                    'appointment_id' => $appointment->id,
                    'reminder_type' => $type,
                    'sent_at' => Carbon::now(),
                    'sent_to' => $appointment->patient->email,
                ]);

                $this->line("✓ Recordatorio {$type} enviado para cita #{$appointment->id}");
            }
        }
    }
}