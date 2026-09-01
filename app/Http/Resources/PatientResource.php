<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PatientResource extends JsonResource
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
            'patient_code' => $this->patient_code,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'full_name' => $this->full_name,
            'email' => $this->email,
            'phone' => $this->phone,
            'phone_secondary' => $this->phone_secondary,
            'birth_date' => $this->birth_date?->format('Y-m-d'),
            'age' => $this->age,
            'gender' => $this->gender,
            'address' => $this->address,
            'city' => $this->city,
            'state' => $this->state,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'full_address' => $this->full_address,
            
            // Información médica
            'medical_history' => $this->medical_history,
            'dental_history' => $this->dental_history,
            'allergies' => $this->allergies,
            'medications' => $this->medications,
            'family_history' => $this->family_history,
            'social_history' => $this->social_history,
            'blood_type' => $this->blood_type,
            'occupation' => $this->occupation,
            'marital_status' => $this->marital_status,
            'has_allergies' => $this->hasAllergies(),
            
            // Contactos de emergencia
            'emergency_contact_name' => $this->emergency_contact_name,
            'emergency_contact_phone' => $this->emergency_contact_phone,
            'emergency_contact_relationship' => $this->emergency_contact_relationship,
            'emergency_contact_address' => $this->emergency_contact_address,
            'emergency_contact_info' => $this->emergency_contact_info,
            
            // Información adicional
            'notes' => $this->notes,
            'preferences' => $this->preferences,
            'consent_marketing' => $this->consent_marketing,
            'consent_data_processing' => $this->consent_data_processing,
            'is_active' => $this->is_active,
            
            // Relaciones básicas
            'creator' => new UserResource($this->whenLoaded('creator')),
            
            // Estadísticas
            'stats' => $this->when($request->has('include_stats'), $this->stats),
            
            // Metadatos
            'created_at' => $this->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $this->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}