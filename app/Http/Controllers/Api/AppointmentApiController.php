<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Patient;
use App\Models\Staff;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class AppointmentApiController extends Controller
{
    use ApiResponse;

    /**
     * Display a listing of appointments.
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Appointment::with(['patient', 'staff.user', 'appointmentStatus']);

            // Filtros
            if ($request->has('date')) {
                $date = Carbon::parse($request->get('date'));
                $query->whereDate('appointment_date', $date);
            }

            if ($request->has('staff_id')) {
                $query->where('staff_id', $request->get('staff_id'));
            }

            if ($request->has('patient_id')) {
                $query->where('patient_id', $request->get('patient_id'));
            }

            if ($request->has('status')) {
                $query->whereHas('appointmentStatus', function ($q) use ($request) {
                    $q->where('name', $request->get('status'));
                });
            }

            // Ordenamiento
            $sortBy = $request->get('sort_by', 'appointment_date');
            $sortOrder = $request->get('sort_order', 'asc');
            $query->orderBy($sortBy, $sortOrder);

            // Paginación
            $perPage = $request->get('per_page', 15);
            $appointments = $query->paginate($perPage);

            return $this->paginatedResponse($appointments, 'Appointments retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving appointments: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created appointment.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'staff_id' => 'required|exists:staff,id',
                'appointment_date' => 'required|date|after:today',
                'start_time' => 'required|date_format:H:i',
                'duration' => 'required|integer|min:15|max:480',
                'type' => 'required|string|max:255',
                'reason' => 'nullable|string|max:1000',
                'notes' => 'nullable|string|max:1000',
            ]);

            // Calcular end_time
            $startTime = Carbon::parse($validated['appointment_date'] . ' ' . $validated['start_time']);
            $validated['end_time'] = $startTime->addMinutes($validated['duration']);

            $appointment = Appointment::create($validated);

            return $this->createdResponse($appointment->load(['patient', 'staff.user', 'appointmentStatus']), 'Appointment created successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error creating appointment: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified appointment.
     */
    public function show(Appointment $appointment): JsonResponse
    {
        try {
            $appointment->load(['patient', 'staff.user', 'appointmentStatus']);
            
            return $this->successResponse($appointment, 'Appointment retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving appointment: ' . $e->getMessage());
        }
    }

    /**
     * Update the specified appointment.
     */
    public function update(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'patient_id' => 'sometimes|required|exists:patients,id',
                'staff_id' => 'sometimes|required|exists:staff,id',
                'appointment_date' => 'sometimes|required|date',
                'start_time' => 'sometimes|required|date_format:H:i',
                'duration' => 'sometimes|required|integer|min:15|max:480',
                'type' => 'sometimes|required|string|max:255',
                'reason' => 'nullable|string|max:1000',
                'notes' => 'nullable|string|max:1000',
            ]);

            $appointment->update($validated);

            return $this->updatedResponse($appointment->load(['patient', 'staff.user', 'appointmentStatus']), 'Appointment updated successfully');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return $this->validationErrorResponse($e->errors());
        } catch (\Exception $e) {
            return $this->errorResponse('Error updating appointment: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified appointment.
     */
    public function destroy(Appointment $appointment): JsonResponse
    {
        try {
            $appointment->delete();

            return $this->deletedResponse('Appointment deleted successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error deleting appointment: ' . $e->getMessage());
        }
    }

    /**
     * Confirm an appointment.
     */
    public function confirm(Appointment $appointment): JsonResponse
    {
        try {
            $appointment->update(['confirmed_at' => now()]);

            return $this->updatedResponse($appointment->load(['patient', 'staff.user', 'appointmentStatus']), 'Appointment confirmed successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error confirming appointment: ' . $e->getMessage());
        }
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(Request $request, Appointment $appointment): JsonResponse
    {
        try {
            $validated = $request->validate([
                'cancellation_reason' => 'required|string|max:1000',
            ]);

            $appointment->update([
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'],
            ]);

            return $this->updatedResponse($appointment->load(['patient', 'staff.user', 'appointmentStatus']), 'Appointment cancelled successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error cancelling appointment: ' . $e->getMessage());
        }
    }

    /**
     * Get calendar data for a specific date.
     */
    public function calendar(Request $request): JsonResponse
    {
        try {
            $date = $request->get('date', now()->format('Y-m-d'));
            $staffId = $request->get('staff_id');

            $query = Appointment::with(['patient', 'staff.user', 'appointmentStatus'])
                ->whereDate('appointment_date', $date);

            if ($staffId) {
                $query->where('staff_id', $staffId);
            }

            $appointments = $query->orderBy('start_time')->get();

            return $this->successResponse($appointments, 'Calendar data retrieved successfully');
        } catch (\Exception $e) {
            return $this->errorResponse('Error retrieving calendar data: ' . $e->getMessage());
        }
    }
}