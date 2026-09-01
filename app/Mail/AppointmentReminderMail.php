<?php

namespace App\Mail;

use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AppointmentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Appointment $appointment,
        public Patient $patient,
        public Staff $staff,
        public string $reminderType = '24_hour'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->reminderType) {
            '24_hour' => 'Recordatorio de Cita - Mañana',
            '1_hour' => 'Recordatorio de Cita - En 1 Hora',
            'same_day' => 'Recordatorio de Cita - Hoy',
            default => 'Recordatorio de Cita'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.appointment-reminder',
            with: [
                'appointment' => $this->appointment,
                'patient' => $this->patient,
                'staff' => $this->staff,
                'reminderType' => $this->reminderType,
                'appointmentDate' => $this->appointment->appointment_date->format('d/m/Y'),
                'appointmentTime' => $this->appointment->start_time->format('H:i'),
                'clinicName' => config('app.name'),
                'clinicPhone' => config('app.phone', '+1234567890'),
                'clinicAddress' => config('app.address', 'Calle Principal 123'),
            ]
        );
    }

    public function attachments(): array
    {
        return [];
    }
}