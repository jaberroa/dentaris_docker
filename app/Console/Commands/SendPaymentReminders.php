<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Invoice;
use App\Models\AccountsReceivable;
use App\Services\NotificationService;
use App\Jobs\SendPaymentReminderJob;
use Carbon\Carbon;

class SendPaymentReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-payment-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Envía recordatorios de pagos vencidos y próximos a vencer';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Iniciando envío de recordatorios de pagos...');

        // Recordatorios de pagos vencidos
        $this->sendOverdueReminders();
        
        // Recordatorios de pagos próximos a vencer
        $this->sendDueSoonReminders();

        $this->info('Recordatorios de pagos enviados exitosamente.');
    }

    protected function sendOverdueReminders()
    {
        $overdueInvoices = Invoice::with(['patient', 'items'])
            ->where('status', 'sent')
            ->where('due_date', '<', Carbon::now())
            ->where('balance_due', '>', 0)
            ->get();

        $this->info("Enviando recordatorios de pagos vencidos para {$overdueInvoices->count()} facturas...");

        foreach ($overdueInvoices as $invoice) {
            // Verificar si ya se envió recordatorio en las últimas 24 horas
            $lastReminder = $invoice->paymentReminders()
                ->where('reminder_type', 'overdue')
                ->where('sent_at', '>=', Carbon::now()->subDay())
                ->first();

            if (!$lastReminder) {
                // Enviar recordatorio
                SendPaymentReminderJob::dispatch(
                    $invoice->id,
                    'overdue'
                );

                // Registrar el recordatorio enviado
                $invoice->paymentReminders()->create([
                    'reminder_type' => 'overdue',
                    'sent_at' => Carbon::now(),
                    'sent_to' => $invoice->patient->email,
                ]);

                $this->line("✓ Recordatorio de pago vencido enviado para factura #{$invoice->invoice_number}");
            }
        }
    }

    protected function sendDueSoonReminders()
    {
        $dueSoonInvoices = Invoice::with(['patient', 'items'])
            ->where('status', 'sent')
            ->where('due_date', '>=', Carbon::now())
            ->where('due_date', '<=', Carbon::now()->addDays(3))
            ->where('balance_due', '>', 0)
            ->get();

        $this->info("Enviando recordatorios de pagos próximos a vencer para {$dueSoonInvoices->count()} facturas...");

        foreach ($dueSoonInvoices as $invoice) {
            // Verificar si ya se envió recordatorio en las últimas 48 horas
            $lastReminder = $invoice->paymentReminders()
                ->where('reminder_type', 'due_soon')
                ->where('sent_at', '>=', Carbon::now()->subDays(2))
                ->first();

            if (!$lastReminder) {
                // Enviar recordatorio
                SendPaymentReminderJob::dispatch(
                    $invoice->id,
                    'due_soon'
                );

                // Registrar el recordatorio enviado
                $invoice->paymentReminders()->create([
                    'reminder_type' => 'due_soon',
                    'sent_at' => Carbon::now(),
                    'sent_to' => $invoice->patient->email,
                ]);

                $this->line("✓ Recordatorio de pago próximo a vencer enviado para factura #{$invoice->invoice_number}");
            }
        }
    }
}