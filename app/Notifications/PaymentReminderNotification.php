<?php

namespace App\Notifications;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Messages\DatabaseMessage;
use Illuminate\Notifications\Notification;

class PaymentReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Invoice $invoice,
        public Patient $patient,
        public string $reminderType = 'overdue'
    ) {}

    public function via($notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail($notifiable): MailMessage
    {
        $subject = match($this->reminderType) {
            'overdue' => 'Recordatorio de Pago Vencido',
            'due_soon' => 'Recordatorio de Pago Próximo a Vencer',
            'payment_received' => 'Confirmación de Pago Recibido',
            default => 'Recordatorio de Pago'
        };

        $message = match($this->reminderType) {
            'overdue' => "Tienes un pago vencido que requiere atención",
            'due_soon' => "Tienes un pago próximo a vencer",
            'payment_received' => "Hemos recibido tu pago",
            default => "Recordatorio de pago"
        };

        return (new MailMessage)
            ->subject($subject)
            ->greeting('Hola ' . $this->patient->first_name . ',')
            ->line($message)
            ->line('**Detalles del pago:**')
            ->line('📄 Factura: ' . $this->invoice->invoice_number)
            ->line('💰 Monto Total: $' . number_format($this->invoice->total_amount, 2))
            ->line('💳 Saldo Pendiente: $' . number_format($this->invoice->balance_due, 2))
            ->line('📅 Fecha de Vencimiento: ' . $this->invoice->due_date->format('d/m/Y'))
            ->when($this->reminderType === 'overdue', function ($message) {
                return $message->line('⚠️ Días de retraso: ' . $this->invoice->getDaysOverdueAttribute());
            })
            ->action('Ver Factura', url('/billing/' . $this->invoice->id))
            ->line('Por favor, realiza el pago lo antes posible o contacta con nosotros para hacer arreglos de pago.');
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'payment_reminder',
            'invoice_id' => $this->invoice->id,
            'patient_id' => $this->patient->id,
            'reminder_type' => $this->reminderType,
            'total_amount' => $this->invoice->total_amount,
            'balance_due' => $this->invoice->balance_due,
            'due_date' => $this->invoice->due_date,
            'message' => 'Recordatorio de pago',
        ];
    }
}