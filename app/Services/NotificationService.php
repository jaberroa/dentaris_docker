<?php

namespace App\Services;

use App\Models\NotificationTemplate;
use App\Models\NotificationLog;
use App\Jobs\SendWhatsAppNotificationJob;
use App\Jobs\SendSmsNotificationJob;
use Illuminate\Support\Facades\Mail;
use App\Mail\GeneralNotificationMail;

class NotificationService
{
    public function sendNotification($recipient, $type, $templateCode, $data = [], $channel = 'email')
    {
        $template = NotificationTemplate::where('template_code', $templateCode)->first();
        if (!$template) {
            // Log error or throw exception
            return false;
        }

        $message = $this->replacePlaceholders($template->message_template, $data);
        $subject = $this->replacePlaceholders($template->subject, $data);

        $log = NotificationLog::create([
            'notification_template_id' => $template->id,
            'recipient' => $recipient,
            'type' => $type,
            'channel' => $channel,
            'subject' => $subject,
            'message' => $message,
            'status' => 'pending',
            'sent_at' => null,
        ]);

        try {
            switch ($channel) {
                case 'email':
                    Mail::to($recipient)->send(new GeneralNotificationMail($subject, $message));
                    break;
                case 'whatsapp':
                    SendWhatsAppNotificationJob::dispatch($recipient, $message, $log->id);
                    break;
                case 'sms':
                    SendSmsNotificationJob::dispatch($recipient, $message, $log->id);
                    break;
                default:
                    // Log error
                    $log->update(['status' => 'failed', 'error_message' => 'Invalid channel']);
                    return false;
            }

            $log->update(['status' => 'sent', 'sent_at' => now()]);
            return true;
        } catch (\Exception $e) {
            $log->update(['status' => 'failed', 'error_message' => $e->getMessage()]);
            return false;
        }
    }

    protected function replacePlaceholders($content, $data)
    {
        foreach ($data as $key => $value) {
            $content = str_replace("{{$key}}", $value, $content);
        }
        return $content;
    }

    public function getNotificationStats()
    {
        return [
            'total_sent' => NotificationLog::where('status', 'sent')->count(),
            'total_failed' => NotificationLog::where('status', 'failed')->count(),
            'pending' => NotificationLog::where('status', 'pending')->count(),
            'by_channel' => NotificationLog::select('channel', \DB::raw('count(*) as total'))
                                ->groupBy('channel')
                                ->get()
                                ->keyBy('channel')
                                ->map->total,
        ];
    }

    public function getTemplate($templateCode)
    {
        return NotificationTemplate::where('template_code', $templateCode)->first();
    }

    public function createTemplate($code, $subject, $content, $description = null)
    {
        return NotificationTemplate::create([
            'template_code' => $code,
            'template_name' => $code,
            'subject' => $subject,
            'message_template' => $content,
            'description' => $description,
            'type' => 'general',
            'channel' => 'email',
            'is_active' => true,
            'is_system' => false,
            'created_by' => auth()->id() ?? 1,
        ]);
    }

    public function updateTemplate($templateId, $data)
    {
        $template = NotificationTemplate::find($templateId);
        if ($template) {
            $template->update($data);
        }
        return $template;
    }
}