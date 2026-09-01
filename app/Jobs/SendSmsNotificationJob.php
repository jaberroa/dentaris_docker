<?php

namespace App\Jobs;

use App\Models\Patient;
use App\Models\Notification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendSmsNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $patient;
    protected $message;
    protected $notificationId;

    /**
     * Create a new job instance.
     */
    public function __construct(Patient $patient, string $message, $notificationId = null)
    {
        $this->patient = $patient;
        $this->message = $message;
        $this->notificationId = $notificationId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if (!$this->patient->phone) {
                Log::warning("SMS notification skipped - patient has no phone number", [
                    'patient_id' => $this->patient->id
                ]);
                return;
            }

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.sms.token'),
                'Content-Type' => 'application/json',
            ])->post(config('services.sms.api_url') . '/send', [
                'to' => $this->patient->phone,
                'message' => $this->message
            ]);

            $success = $response->successful();

            if ($success) {
                Log::info("SMS notification sent successfully", [
                    'patient_id' => $this->patient->id,
                    'phone' => $this->patient->phone,
                    'notification_id' => $this->notificationId
                ]);

                // Actualizar notificación si existe
                if ($this->notificationId) {
                    $this->updateNotificationStatus('sent');
                }
            } else {
                Log::error("SMS notification failed", [
                    'patient_id' => $this->patient->id,
                    'phone' => $this->patient->phone,
                    'response' => $response->body(),
                    'status' => $response->status()
                ]);

                // Actualizar notificación si existe
                if ($this->notificationId) {
                    $this->updateNotificationStatus('failed', $response->body());
                }
            }

        } catch (\Exception $e) {
            Log::error("Error sending SMS notification", [
                'patient_id' => $this->patient->id,
                'error' => $e->getMessage()
            ]);

            // Actualizar notificación si existe
            if ($this->notificationId) {
                $this->updateNotificationStatus('failed', $e->getMessage());
            }

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("SMS notification job failed", [
            'patient_id' => $this->patient->id,
            'error' => $exception->getMessage()
        ]);

        // Actualizar notificación si existe
        if ($this->notificationId) {
            $this->updateNotificationStatus('failed', $exception->getMessage());
        }
    }

    /**
     * Actualizar estado de la notificación
     */
    private function updateNotificationStatus($status, $error = null)
    {
        try {
            $notification = Notification::find($this->notificationId);
            if ($notification) {
                $notification->update([
                    'status' => $status,
                    'sent_at' => $status === 'sent' ? now() : null,
                    'error_message' => $error
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating notification status", [
                'notification_id' => $this->notificationId,
                'error' => $e->getMessage()
            ]);
        }
    }

    /**
     * Get the tags that should be assigned to the job.
     */
    public function tags(): array
    {
        return [
            'sms-notification',
            'patient:' . $this->patient->id,
            'notification:' . $this->notificationId
        ];
    }
}





