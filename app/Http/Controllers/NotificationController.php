<?php

namespace App\Http\Controllers;

use App\Models\NotificationTemplate;
use App\Models\NotificationLog;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Display a listing of notification templates.
     */
    public function index()
    {
        $templates = NotificationTemplate::with('logs')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20);

        return view('notifications.index', compact('templates'));
    }

    /**
     * Show the form for creating a new notification template.
     */
    public function create()
    {
        return view('notifications.create');
    }

    /**
     * Store a newly created notification template.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:notification_templates',
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'channel' => 'required|in:email,whatsapp,sms',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $template = NotificationTemplate::create($request->all());

        return redirect()->route('notifications.index')
            ->with('success', 'Plantilla de notificación creada exitosamente.');
    }

    /**
     * Display the specified notification template.
     */
    public function show(NotificationTemplate $notification)
    {
        $logs = $notification->logs()
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('notifications.show', compact('notification', 'logs'));
    }

    /**
     * Show the form for editing the specified notification template.
     */
    public function edit(NotificationTemplate $notification)
    {
        return view('notifications.edit', compact('notification'));
    }

    /**
     * Update the specified notification template.
     */
    public function update(Request $request, NotificationTemplate $notification)
    {
        $validator = Validator::make($request->all(), [
            'code' => 'required|string|max:255|unique:notification_templates,code,' . $notification->id,
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'description' => 'nullable|string',
            'type' => 'required|string|max:255',
            'channel' => 'required|in:email,whatsapp,sms',
            'is_active' => 'boolean',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        $notification->update($request->all());

        return redirect()->route('notifications.index')
            ->with('success', 'Plantilla de notificación actualizada exitosamente.');
    }

    /**
     * Remove the specified notification template.
     */
    public function destroy(NotificationTemplate $notification)
    {
        $notification->delete();

        return redirect()->route('notifications.index')
            ->with('success', 'Plantilla de notificación eliminada exitosamente.');
    }

    /**
     * Send a test notification.
     */
    public function sendTest(Request $request, NotificationTemplate $notification)
    {
        $validator = Validator::make($request->all(), [
            'recipient' => 'required|email',
            'data' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return back()->withErrors($validator);
        }

        $data = $request->get('data', []);
        $data = array_merge($data, [
            'patient_name' => 'Paciente de Prueba',
            'appointment_date' => now()->format('d/m/Y'),
            'appointment_time' => now()->format('H:i'),
            'staff_name' => 'Dr. Prueba',
            'clinic_name' => config('app.name'),
            'clinic_phone' => config('app.phone', '+1234567890'),
            'clinic_address' => config('app.address', 'Calle Principal 123'),
        ]);

        $success = $this->notificationService->sendNotification(
            $request->recipient,
            $notification->type,
            $notification->code,
            $data,
            $notification->channel
        );

        if ($success) {
            return back()->with('success', 'Notificación de prueba enviada exitosamente.');
        } else {
            return back()->with('error', 'Error al enviar la notificación de prueba.');
        }
    }

    /**
     * Get notification statistics.
     */
    public function statistics()
    {
        $stats = $this->notificationService->getNotificationStats();
        
        $templates = NotificationTemplate::withCount('logs')
            ->orderBy('logs_count', 'desc')
            ->get();

        return view('notifications.statistics', compact('stats', 'templates'));
    }

    /**
     * Get notification logs.
     */
    public function logs(Request $request)
    {
        $query = NotificationLog::with('template')
            ->orderBy('created_at', 'desc');

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        if ($request->has('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        $logs = $query->paginate(50);

        return view('notifications.logs', compact('logs'));
    }

    /**
     * Toggle template active status.
     */
    public function toggleActive(NotificationTemplate $notification)
    {
        $notification->update(['is_active' => !$notification->is_active]);

        return back()->with('success', 'Estado de la plantilla actualizado exitosamente.');
    }
}
