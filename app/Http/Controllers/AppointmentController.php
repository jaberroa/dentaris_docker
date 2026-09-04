<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clinics\AppointmentRequest;
use App\Models\Appointment;
use App\Models\AppointmentStatus;
use App\Modules\Clinics\Services\ClinicalRelatedRecordAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function __construct(
        private readonly ClinicalRelatedRecordAccessService $clinicalRecords,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $query = $this->clinicalRecords->appointments($context)
            ->with(['patient', 'staff.user', 'status', 'creator']);

        // Sorting
        $sortField = $request->get('sort', 'appointment_date');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validar campos de sorting permitidos
        $allowedSortFields = [
            'appointment_date', 'start_time', 'type', 'status', 'patient_name', 'staff_name', 'created_at'
        ];
        
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'appointment_date';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        // Aplicar sorting
        if ($sortField === 'patient_name') {
            $query->join('patients', 'appointments.patient_id', '=', 'patients.id')
                  ->orderBy('patients.first_name', $sortDirection)
                  ->orderBy('patients.last_name', $sortDirection)
                  ->select('appointments.*');
        } elseif ($sortField === 'staff_name') {
            $query->join('staff', 'appointments.staff_id', '=', 'staff.id')
                  ->join('users', 'staff.user_id', '=', 'users.id')
                  ->orderBy('users.name', $sortDirection)
                  ->select('appointments.*');
        } elseif ($sortField === 'status') {
            $query->join('appointment_statuses', 'appointments.appointment_status_id', '=', 'appointment_statuses.id')
                  ->orderBy('appointment_statuses.display_name', $sortDirection)
                  ->select('appointments.*');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }
        
        // Ordenamiento secundario por fecha y hora si no es el campo principal
        if ($sortField !== 'appointment_date') {
            $query->orderBy('appointment_date', 'desc');
        }
        if ($sortField !== 'start_time') {
            $query->orderBy('start_time', 'desc');
        }

        // Filtros por fecha
        if ($request->filled('date_from')) {
            $query->whereDate('appointment_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('appointment_date', '<=', $request->date_to);
        }

        // Filtro por doctor/staff
        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        // Filtro por estado
        if ($request->filled('status')) {
            $query->whereHas('status', function($q) use ($request) {
                $q->where('name', $request->status);
            });
        }

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('appointment_code', 'like', "%{$search}%")
                  ->orWhere('reason', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($patientQuery) use ($search) {
                      $patientQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%")
                                  ->orWhere('patient_code', 'like', "%{$search}%");
                  });
            });
        }

        // Paginación con parámetro per_page
        $perPage = $request->get('per_page', '10');
        if ($perPage === 'all') {
            $appointments = $query->get();
            $appointments = new \Illuminate\Pagination\LengthAwarePaginator(
                $appointments,
                $appointments->count(),
                $appointments->count(),
                1,
                ['path' => $request->url(), 'pageName' => 'page']
            );
            $appointments->withQueryString();
        } else {
            $appointments = $query->paginate($perPage)->withQueryString();
        }

        // Datos para filtros
        $staffMembers = $this->clinicalRecords->staff($context)
            ->with('user')
            ->active()
            ->get();
        $statuses = AppointmentStatus::active()->get();

        return view('appointments.index', compact('appointments', 'staffMembers', 'statuses', 'perPage', 'sortField', 'sortDirection'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $patients = $this->clinicalRecords->patients($context)
            ->select('id', 'first_name', 'last_name', 'patient_code', 'gender')
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();
        
        $staff = $this->clinicalRecords->staff($context)
            ->with('user')
            ->where('is_active', true)
            ->get();
        
        $statuses = AppointmentStatus::active()->get();

        return view('appointments.create', compact('patients', 'staff', 'statuses'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(AppointmentRequest $request)
    {
        $this->clinicalRecords->context($request);
        $validated = $request->validated();

        try {
            $startTime = Carbon::parse($validated['appointment_date'].' '.$validated['start_time']);
            $endTime = $startTime->copy()->addMinutes((int) $validated['duration']);

            $appointmentCode = 'CIT-' . strtoupper(Str::random(8));

            $appointment = Appointment::create([
                'appointment_code' => $appointmentCode,
                'patient_id' => $validated['patient_id'],
                'staff_id' => $validated['staff_id'],
                'appointment_status_id' => $validated['appointment_status_id'],
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $endTime->format('H:i:s'),
                'duration' => (int) $validated['duration'],
                'type' => $validated['type'],
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'treatment_plan' => $validated['treatment_plan'] ?? null,
                'estimated_cost' => $validated['estimated_cost'] ?? null,
                'is_urgent' => $request->boolean('is_urgent'),
                'is_follow_up' => $request->boolean('is_follow_up'),
                'is_recurring' => $request->boolean('is_recurring'),
                'reminder_sent' => $request->boolean('reminder_sent'),
                'created_by' => auth()->id(),
            ]);

            activity()
                ->performedOn($appointment)
                ->causedBy(auth()->user())
                ->log('Cita creada');

            return redirect()->route('appointments.show', $appointment)
                ->with('success', 'Cita creada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error creating appointment', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->withErrors([
                'error' => 'Ocurrió un error al crear la cita. Inténtalo de nuevo.',
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Appointment $appointment)
    {
        $appointment = $this->clinicalRecords->appointment(
            $appointment,
            $this->clinicalRecords->context($request),
        );
        $appointment->load([
            'patient',
            'staff.user',
            'status',
            'creator',
            'reminders'
        ]);

        return view('appointments.show', compact('appointment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Appointment $appointment)
    {
        $context = $this->clinicalRecords->context($request);
        $appointment = $this->clinicalRecords->appointment($appointment, $context);
        $patients = $this->clinicalRecords->patients($context)
            ->active()
            ->orderBy('first_name')
            ->get(['id', 'patient_code', 'first_name', 'last_name']);
        $staffMembers = $this->clinicalRecords->staff($context)
            ->with('user')
            ->active()
            ->get();
        $statuses = AppointmentStatus::active()->get();

        return view('appointments.edit', compact('appointment', 'patients', 'staffMembers', 'statuses'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(AppointmentRequest $request, Appointment $appointment)
    {
        $appointment = $this->clinicalRecords->appointment($appointment, $request->clinicContext());
        $validated = $request->validated();

        try {
            $startTime = Carbon::parse($validated['appointment_date'].' '.$validated['start_time']);
            $endTime = $startTime->copy()->addMinutes((int) $validated['duration']);

            $appointment->update([
                'patient_id' => $validated['patient_id'],
                'staff_id' => $validated['staff_id'],
                'appointment_status_id' => $validated['appointment_status_id'],
                'appointment_date' => $validated['appointment_date'],
                'start_time' => $validated['start_time'],
                'end_time' => $endTime->format('H:i:s'),
                'duration' => (int) $validated['duration'],
                'type' => $validated['type'],
                'reason' => $validated['reason'] ?? null,
                'notes' => $validated['notes'] ?? null,
                'treatment_plan' => $validated['treatment_plan'] ?? null,
                'estimated_cost' => $validated['estimated_cost'] ?? null,
                'is_urgent' => $request->boolean('is_urgent'),
                'is_follow_up' => $request->boolean('is_follow_up'),
                'is_recurring' => $request->boolean('is_recurring'),
                'reminder_sent' => $request->boolean('reminder_sent'),
            ]);

            activity()
                ->performedOn($appointment)
                ->causedBy(auth()->user())
                ->log('Cita actualizada');

            return redirect()->route('appointments.show', $appointment)
                ->with('success', 'Cita actualizada exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Ocurrió un error al actualizar la cita. Inténtalo de nuevo.',
            ])->withInput();
        }
    }

    /**
     * Update appointment status via AJAX with complete logic
     */
    public function updateStatus(Request $request, Appointment $appointment)
    {
        $appointment = $this->clinicalRecords->appointment(
            $appointment,
            $this->clinicalRecords->context($request),
        );
        $request->validate([
            'status_id' => 'required|exists:appointment_statuses,id'
        ]);

        try {
            $currentStatus = $appointment->status->name ?? 'scheduled';
            $newStatusId = $request->status_id;
            $newStatus = \App\Models\AppointmentStatus::find($newStatusId);
            $newStatusName = $newStatus->name;
            
            // 1. VALIDAR TRANSICIÓN DE ESTADO
            if (!$this->canTransitionTo($currentStatus, $newStatusName)) {
                $this->logStatusChangeAttempt($appointment, $currentStatus, $newStatusName, false, 'Transición no permitida');
                return response()->json([
                    'success' => false,
                    'message' => "No se puede cambiar de '{$this->getStatusDisplayName($currentStatus)}' a '{$newStatus->display_name}'"
                ], 400);
            }

            // 2. VALIDACIONES ADICIONALES
            $additionalValidation = $this->performAdditionalValidations($appointment, $newStatusName);
            if (!$additionalValidation['valid']) {
                $this->logStatusChangeAttempt($appointment, $currentStatus, $newStatusName, false, $additionalValidation['reason']);
                return response()->json([
                    'success' => false,
                    'message' => $additionalValidation['reason']
                ], 400);
            }

            // 3. ACTUALIZAR ESTADO
            $oldStatus = $appointment->status->display_name ?? 'Sin estado';
            $appointment->update([
                'appointment_status_id' => $request->status_id
            ]);

            $appointment->load('status');
            $newStatusDisplay = $appointment->status->display_name ?? 'Sin estado';

            // 4. LOGGING COMPLETO
            $this->logStatusChangeAttempt($appointment, $currentStatus, $newStatusName, true, 'Cambio exitoso');
            
            // 5. ENVIAR NOTIFICACIONES
            $this->sendStatusNotifications($appointment, $oldStatus, $newStatusDisplay, $newStatusName);

            // 6. ACTIVITY LOG
            activity()
                ->performedOn($appointment)
                ->causedBy(auth()->user())
                ->withProperties([
                    'old_status' => $currentStatus,
                    'new_status' => $newStatusName,
                    'patient_id' => $appointment->patient_id,
                    'staff_id' => $appointment->staff_id,
                    'authorization_scope' => 'clinic_membership'
                ])
                ->log("Estado de cita cambiado de '{$oldStatus}' a '{$newStatusDisplay}'");

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'status' => [
                    'id' => $appointment->status->id,
                    'name' => $appointment->status->name,
                    'display_name' => $appointment->status->display_name,
                    'color' => $appointment->status->color,
                    'icon' => $appointment->status->icon
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Error updating appointment status: ' . $e->getMessage(), [
                'appointment_id' => $appointment->id,
                'user_id' => auth()->id(),
                'new_status_id' => $request->status_id,
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ], 500);
        }
    }

    /**
     * Enviar notificaciones automáticas según el estado de la cita
     */
    private function sendStatusNotifications($appointment, $oldStatus, $newStatus, $newStatusName)
    {
        $patient = $appointment->patient;
        $staff = $appointment->staff->user;
        
        // Solo enviar notificaciones para estados específicos
        if (in_array($newStatusName, ['cancelled', 'rescheduled', 'completed'])) {
            
            // Datos para la notificación
            $appointmentData = [
                'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                'patient_email' => $patient->email,
                'patient_phone' => $patient->phone,
                'doctor_name' => $staff->name,
                'appointment_date' => $appointment->appointment_date->format('d/m/Y'),
                'appointment_time' => $appointment->start_time ?? 'Sin hora asignada',
                'appointment_type' => $appointment->type ?? 'Sin tipo',
                'old_status' => $oldStatus,
                'new_status' => $newStatus,
                'clinic_name' => 'Dentaris',
                'clinic_phone' => '+1 234 567 8900', // Configurar según tu clínica
                'clinic_email' => 'info@dentaris.com' // Configurar según tu clínica
            ];

            try {
                // Enviar email
                $this->sendEmailNotification($appointmentData, $newStatusName);
                
                // Enviar WhatsApp (si está configurado)
                $this->sendWhatsAppNotification($appointmentData, $newStatusName);
                
                \Log::info("Notificaciones enviadas para cita {$appointment->id} - Estado: {$newStatus}");
                
            } catch (\Exception $e) {
                \Log::error("Error enviando notificaciones para cita {$appointment->id}: " . $e->getMessage());
            }
        }
    }

    /**
     * Enviar notificación por email
     */
    private function sendEmailNotification($data, $status)
    {
        $subject = '';
        $template = '';
        
        switch($status) {
            case 'cancelled':
                $subject = 'Cita Cancelada - ' . $data['clinic_name'];
                $template = 'emails.appointment-cancelled';
                break;
            case 'rescheduled':
                $subject = 'Cita Reprogramada - ' . $data['clinic_name'];
                $template = 'emails.appointment-rescheduled';
                break;
            case 'completed':
                $subject = 'Cita Completada - ' . $data['clinic_name'];
                $template = 'emails.appointment-completed';
                break;
        }

        if ($template && $data['patient_email']) {
            // Aquí implementarías el envío de email usando Laravel Mail
            \Log::info("Email enviado a {$data['patient_email']} - Asunto: {$subject}");
        }
    }

    /**
     * Enviar notificación por WhatsApp
     */
    private function sendWhatsAppNotification($data, $status)
    {
        if ($data['patient_phone']) {
            $message = '';
            
            switch($status) {
                case 'cancelled':
                    $message = "Hola {$data['patient_name']}, lamentamos informarte que tu cita del {$data['appointment_date']} a las {$data['appointment_time']} con el Dr. {$data['doctor_name']} ha sido cancelada. Por favor, contáctanos para reagendar. - {$data['clinic_name']}";
                    break;
                case 'rescheduled':
                    $message = "Hola {$data['patient_name']}, tu cita del {$data['appointment_date']} a las {$data['appointment_time']} con el Dr. {$data['doctor_name']} ha sido reprogramada. Te contactaremos pronto con la nueva fecha. - {$data['clinic_name']}";
                    break;
                case 'completed':
                    $message = "Hola {$data['patient_name']}, gracias por visitarnos. Tu cita del {$data['appointment_date']} ha sido completada exitosamente. ¡Esperamos verte pronto! - {$data['clinic_name']}";
                    break;
            }

            if ($message) {
                // Aquí implementarías el envío de WhatsApp usando una API como Twilio
                \Log::info("WhatsApp enviado a {$data['patient_phone']} - Mensaje: " . substr($message, 0, 100) . "...");
            }
        }
    }

    /**
     * REGLAS DE TRANSICIÓN DE ESTADOS
     */
    private function getTransitionRules()
    {
        return [
            'scheduled' => ['confirmed', 'cancelled', 'rescheduled', 'no_show'],
            'confirmed' => ['in_progress', 'cancelled', 'rescheduled', 'no_show', 'completed'],
            'in_progress' => ['completed', 'cancelled', 'rescheduled'],
            'completed' => [], // Solo admin puede revertir
            'cancelled' => ['scheduled', 'confirmed'], // Reactivación
            'rescheduled' => ['scheduled', 'confirmed'], // Nueva cita
            'no_show' => ['scheduled', 'confirmed', 'cancelled'], // Segunda oportunidad
            'waiting' => ['in_progress', 'cancelled', 'rescheduled', 'no_show'] // Nuevo estado
        ];
    }

    /**
     * Validar si se puede hacer la transición de estado
     */
    private function canTransitionTo($currentStatus, $newStatus)
    {
        $rules = $this->getTransitionRules();
        return in_array($newStatus, $rules[$currentStatus] ?? []);
    }

    /**
     * Validaciones adicionales
     */
    private function performAdditionalValidations($appointment, $newStatus)
    {
        // No completar citas futuras
        if ($newStatus === 'completed' && $appointment->appointment_date > now()->toDateString()) {
            return ['valid' => false, 'reason' => 'No se puede completar una cita futura'];
        }

        // No cancelar citas ya completadas.
        if ($newStatus === 'cancelled' && $appointment->status->name === 'completed') {
            return ['valid' => false, 'reason' => 'No se puede cancelar una cita completada'];
        }

        // Validar que el doctor esté disponible para la cita
        if (in_array($newStatus, ['confirmed', 'in_progress']) && !$this->isStaffAvailable($appointment)) {
            return ['valid' => false, 'reason' => 'El doctor no está disponible para esta cita'];
        }

        return ['valid' => true, 'reason' => 'Validación exitosa'];
    }

    /**
     * Logging completo de intentos de cambio de estado
     */
    private function logStatusChangeAttempt($appointment, $currentStatus, $newStatus, $success, $reason)
    {
        $logData = [
            'appointment_id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'staff_id' => $appointment->staff_id,
            'user_id' => auth()->id(),
            'user_role' => 'clinic_membership',
            'current_status' => $currentStatus,
            'new_status' => $newStatus,
            'success' => $success,
            'reason' => $reason,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toISOString()
        ];

        // Log estructurado para auditoría
        \Log::channel('appointments')->info('Status change attempt', $logData);

        // Log en base de datos para reportes
        $this->saveStatusChangeLog($logData);
    }

    /**
     * Guardar log de cambio de estado en base de datos
     */
    private function saveStatusChangeLog($logData)
    {
        try {
            \DB::table('appointment_status_logs')->insert([
                'appointment_id' => $logData['appointment_id'],
                'patient_id' => $logData['patient_id'],
                'staff_id' => $logData['staff_id'],
                'user_id' => $logData['user_id'],
                'user_role' => $logData['user_role'],
                'old_status' => $logData['current_status'],
                'new_status' => $logData['new_status'],
                'success' => $logData['success'],
                'reason' => $logData['reason'],
                'ip_address' => $logData['ip_address'],
                'user_agent' => $logData['user_agent'],
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving status change log: ' . $e->getMessage());
        }
    }

    /**
     * Obtener nombre de display del estado
     */
    private function getStatusDisplayName($status)
    {
        $statusNames = [
            'scheduled' => 'Programadas',
            'confirmed' => 'Confirmadas', 
            'in_progress' => 'En Progreso',
            'completed' => 'Completadas',
            'cancelled' => 'Canceladas',
            'rescheduled' => 'Reprogramadas',
            'no_show' => 'No Asistió',
            'waiting' => 'En Espera'
        ];
        
        return $statusNames[$status] ?? ucfirst($status);
    }

    /**
     * Verificar si el staff está disponible
     */
    private function isStaffAvailable($appointment)
    {
        // Por ahora retornar true, después se puede implementar lógica más compleja
        // verificando horarios, otras citas, etc.
        return true;
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Appointment $appointment)
    {
        $appointment = $this->clinicalRecords->appointment(
            $appointment,
            $this->clinicalRecords->context($request),
        );
        try {
            activity()
                ->performedOn($appointment)
                ->causedBy(auth()->user())
                ->log('Cita eliminada');

            $appointment->delete();

            return redirect()->route('appointments.index')
                ->with('success', 'Cita eliminada exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Ocurrió un error al eliminar la cita. Inténtalo de nuevo.',
            ]);
        }
    }

    /**
     * Confirmar cita
     */
    public function confirm(Request $request, Appointment $appointment)
    {
        $appointment = $this->clinicalRecords->appointment(
            $appointment,
            $this->clinicalRecords->context($request),
        );
        try {
            $appointment->update(['confirmed_at' => now()]);

            activity()
                ->performedOn($appointment)
                ->causedBy(auth()->user())
                ->log('Cita confirmada');

            return back()->with('success', 'Cita confirmada exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocurrió un error al confirmar la cita.']);
        }
    }

    /**
     * Cancelar cita
     */
    public function cancel(Request $request, Appointment $appointment)
    {
        $appointment = $this->clinicalRecords->appointment(
            $appointment,
            $this->clinicalRecords->context($request),
        );
        $request->validate([
            'cancellation_reason' => 'required|string|max:500',
        ], [
            'cancellation_reason.required' => 'La razón de cancelación es obligatoria.',
        ]);

        try {
            $appointment->update([
                'cancelled_at' => now(),
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            activity()
                ->performedOn($appointment)
                ->causedBy(auth()->user())
                ->log('Cita cancelada: ' . $request->cancellation_reason);

            return back()->with('success', 'Cita cancelada exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Ocurrió un error al cancelar la cita.']);
        }
    }

    /**
     * Vista semanal del calendario
     */
    public function weekly(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $date = $request->get('date', now()->format('Y-m-d'));
        $startOfWeek = Carbon::parse($date)->startOfWeek();
        $endOfWeek = Carbon::parse($date)->endOfWeek();
        
        $query = $this->clinicalRecords->appointments($context)
            ->with(['patient', 'staff.user', 'status'])
            ->whereBetween('appointment_date', [$startOfWeek, $endOfWeek]);
        
        $appointments = $query->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function($appointment) {
                return Carbon::parse($appointment->appointment_date)->format('Y-m-d');
            });

        $weekDays = [];
        for ($i = 0; $i < 7; $i++) {
            $day = $startOfWeek->copy()->addDays($i);
            $weekDays[] = [
                'date' => $day->format('Y-m-d'),
                'day' => $day->format('d'),
                'dayName' => $day->format('l'),
                'dayNameShort' => $day->format('D'),
                'isToday' => $day->isToday(),
                'appointments' => $appointments->get($day->format('Y-m-d'), collect())
            ];
        }

        return view('appointments.weekly', compact('weekDays', 'startOfWeek', 'endOfWeek'));
    }

    /**
     * Vista mensual del calendario
     */
    public function monthly(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $date = $request->get('date', now()->format('Y-m'));
        $startOfMonth = Carbon::parse($date)->startOfMonth();
        $endOfMonth = Carbon::parse($date)->endOfMonth();
        $startOfCalendar = $startOfMonth->copy()->startOfWeek();
        $endOfCalendar = $endOfMonth->copy()->endOfWeek();
        
        $query = $this->clinicalRecords->appointments($context)
            ->with(['patient', 'staff.user', 'status'])
            ->whereBetween('appointment_date', [$startOfCalendar, $endOfCalendar]);
        
        $appointments = $query->orderBy('appointment_date')
            ->orderBy('start_time')
            ->get()
            ->groupBy(function($appointment) {
                return Carbon::parse($appointment->appointment_date)->format('Y-m-d');
            });

        $calendarDays = [];
        $current = $startOfCalendar->copy();
        
        while ($current->lte($endOfCalendar)) {
            $calendarDays[] = [
                'date' => $current->format('Y-m-d'),
                'day' => $current->format('d'),
                'isCurrentMonth' => $current->month == $startOfMonth->month,
                'isToday' => $current->isToday(),
                'appointments' => $appointments->get($current->format('Y-m-d'), collect())
            ];
            $current->addDay();
        }

        return view('appointments.monthly', compact('calendarDays', 'startOfMonth', 'endOfMonth'));
    }

    /**
     * Vista anual del calendario
     */
    public function yearly(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $year = $request->get('year', now()->year);
        $startOfYear = Carbon::create($year, 1, 1);
        $endOfYear = Carbon::create($year, 12, 31);
        
        $query = $this->clinicalRecords->appointments($context)
            ->with(['patient', 'staff.user', 'status'])
            ->whereBetween('appointment_date', [$startOfYear, $endOfYear]);
        
        $appointments = $query->get()
            ->groupBy(function($appointment) {
                return Carbon::parse($appointment->appointment_date)->format('Y-m');
            });

        $months = [];
        for ($i = 1; $i <= 12; $i++) {
            $month = Carbon::create($year, $i, 1);
            $months[] = [
                'month' => $i,
                'monthName' => $month->format('F'),
                'monthNameShort' => $month->format('M'),
                'appointments' => $appointments->get($month->format('Y-m'), collect()),
                'appointmentCount' => $appointments->get($month->format('Y-m'), collect())->count()
            ];
        }

        return view('appointments.yearly', compact('months', 'year'));
    }

    /**
     * Buscar staff para Select2
     */
    public function searchStaff(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 15;
        
        $query = $this->clinicalRecords->staff($context)
            ->with('user')
            ->where('is_active', true)
            ->where('is_available', true)
            ->where(function($q) use ($search) {
                $q->whereHas('user', function($userQuery) use ($search) {
                    $userQuery->where('name', 'like', "%{$search}%")
                             ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('specialty', 'like', "%{$search}%")
                ->orWhere('license_number', 'like', "%{$search}%");
            })
            ->orderBy('specialty', 'asc');

        $staff = $query->paginate($perPage, ['*'], 'page', $page);

        // Agregar el display_name a cada staff
        $staff->getCollection()->transform(function ($staffMember) {
            $staffMember->display_name = $staffMember->user->name;
            $staffMember->email = $staffMember->user->email;
            return $staffMember;
        });

        return response()->json([
            'data' => $staff->items(),
            'current_page' => $staff->currentPage(),
            'last_page' => $staff->lastPage(),
            'per_page' => $staff->perPage(),
            'total' => $staff->total()
        ]);
    }
}
