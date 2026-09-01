<?php

namespace App\Jobs;

use App\Models\Appointment;
use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class AppointmentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $appointment;
    protected $reminderType;
    protected $channels;

    /**
     * Create a new job instance.
     */
    public function __construct(Appointment $appointment, $reminderType = '24h', $channels = ['whatsapp', 'sms'])
    {
        $this->appointment = $appointment;
        $this->reminderType = $reminderType;
        $this->channels = $channels;
    }

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            // Verificar que la cita aún existe y está en estado válido
            $appointment = Appointment::find($this->appointment->id);
            
            if (!$appointment || !in_array($appointment->status, ['confirmed', 'scheduled'])) {
                Log::info("Appointment reminder skipped - appointment not found or invalid status", [
                    'appointment_id' => $this->appointment->id,
                    'status' => $appointment?->status
                ]);
                return;
            }

            // Enviar recordatorio
            $results = $notificationService->sendAppointmentReminder($appointment, $this->channels);

            Log::info("Appointment reminder sent", [
                'appointment_id' => $appointment->id,
                'reminder_type' => $this->reminderType,
                'channels' => $this->channels,
                'results' => $results
            ]);

        } catch (\Exception $e) {
            Log::error("Error sending appointment reminder", [
                'appointment_id' => $this->appointment->id,
                'reminder_type' => $this->reminderType,
                'error' => $e->getMessage()
            ]);

            // Re-lanzar la excepción para que el job falle y pueda ser reintentado
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Appointment reminder job failed", [
            'appointment_id' => $this->appointment->id,
            'reminder_type' => $this->reminderType,
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
            'appointment-reminder',
            'appointment:' . $this->appointment->id,
            'type:' . $this->reminderType
        ];
    }
}





