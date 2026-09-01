<?php

namespace App\Notifications;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class AppointmentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Appointment $appointment,
        public Patient $patient,
        public Staff $staff,
        public string $reminderType = '24_hour'
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = match($this->reminderType) {
            '24_hour' => 'Recordatorio de Cita - Mañana',
            '1_hour' => 'Recordatorio de Cita - En 1 Hora',
            'same_day' => 'Recordatorio de Cita - Hoy',
            default => 'Recordatorio de Cita'
        };

        $message = match($this->reminderType) {
            '24_hour' => "Tienes una cita programada para mañana",
            '1_hour' => "Tienes una cita en 1 hora",
            'same_day' => "Tienes una cita programada para hoy",
            default => "Tienes una cita programada"
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola ' . $this->patient->first_name . ',')
            ->line($message)
            ->line('**Detalles de la cita:**')
            ->line('📅 Fecha: ' . $this->appointment->appointment_date->format('d/m/Y'))
            ->line('🕐 Hora: ' . $this->appointment->start_time->format('H:i'))
            ->line('👨‍⚕️ Doctor: ' . $this->staff->user->name)
            ->line('📋 Tipo: ' . $this->appointment->type)
            ->when($this->appointment->reason, function ($message) {
                return $message->line('📝 Motivo: ' . $this->appointment->reason);
            })
            ->line('📍 Ubicación: ' . config('app.address', 'Calle Principal 123'))
            ->line('📞 Teléfono: ' . config('app.phone', '+1234567890'))
            ->action('Ver Cita', url('/appointments/' . $this->appointment->id))
            ->line('Por favor, confirma tu asistencia o contacta con nosotros si necesitas reprogramar.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'appointment_reminder',
            'appointment_id' => $this->appointment->id,
            'patient_id' => $this->patient->id,
            'staff_id' => $this->staff->id,
            'reminder_type' => $this->reminderType,
            'appointment_date' => $this->appointment->appointment_date,
            'appointment_time' => $this->appointment->start_time,
            'message' => 'Recordatorio de cita programada',
        ];
    }
}