<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AppointmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'appointment_code' => $this->appointment_code,
            'appointment_date' => $this->appointment_date?->format('Y-m-d'),
            'start_time' => $this->start_time?->format('H:i'),
            'end_time' => $this->end_time?->format('H:i'),
            'duration' => $this->duration,
            'type' => $this->type,
            'reason' => $this->reason,
            'notes' => $this->notes,
            'treatment_plan' => $this->treatment_plan,
            'estimated_cost' => $this->estimated_cost,
            'is_urgent' => $this->is_urgent,
            'is_follow_up' => $this->is_follow_up,
            'confirmed_at' => $this->confirmed_at?->format('Y-m-d H:i:s'),
            'cancelled_at' => $this->cancelled_at?->format('Y-m-d H:i:s'),
            'cancellation_reason' => $this->cancellation_reason,
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
            
            // Relaciones
            'patient' => new PatientResource($this->whenLoaded('patient')),
            'staff' => new StaffResource($this->whenLoaded('staff')),
            'appointment_status' => new AppointmentStatusResource($this->whenLoaded('appointmentStatus')),
            'reminders' => AppointmentReminderResource::collection($this->whenLoaded('reminders')),
        ];
    }
}