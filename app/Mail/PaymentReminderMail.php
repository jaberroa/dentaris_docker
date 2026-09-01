<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public Patient $patient,
        public string $reminderType = 'overdue'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match($this->reminderType) {
            'overdue' => 'Recordatorio de Pago Vencido',
            'due_soon' => 'Recordatorio de Pago Próximo a Vencer',
            'payment_received' => 'Confirmación de Pago Recibido',
            default => 'Recordatorio de Pago'
        };

        return new Envelope(
            subject: $subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.payment-reminder',
            with: [
                'invoice' => $this->invoice,
                'patient' => $this->patient,
                'reminderType' => $this->reminderType,
                'invoiceNumber' => $this->invoice->invoice_number,
                'totalAmount' => $this->invoice->total_amount,
                'balanceDue' => $this->invoice->balance_due,
                'dueDate' => $this->invoice->due_date->format('d/m/Y'),
                'daysOverdue' => $this->invoice->getDaysOverdueAttribute(),
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