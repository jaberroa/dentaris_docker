<?php

namespace App\Http\Requests\Clinics;

use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $context = $this->resolvedClinicContext();

        return $context instanceof ClinicContext
            && $this->user() !== null
            && (int) $this->user()->getAuthIdentifier() === $context->userId;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $context = $this->clinicContext();
        $appointmentDateRules = ['required', 'date'];

        if ($this->isMethod('post')) {
            $appointmentDateRules[] = 'after_or_equal:today';
        }

        return [
            'clinic_id' => ['prohibited'],
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->where(fn ($query) => $query
                    ->where('clinic_id', $context->clinicId)
                    ->where('is_active', true)),
            ],
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(fn ($query) => $query
                    ->where('clinic_id', $context->clinicId)
                    ->where('is_active', true)),
            ],
            'appointment_status_id' => ['required', 'exists:appointment_statuses,id'],
            'appointment_date' => $appointmentDateRules,
            'start_time' => ['required', 'date_format:H:i'],
            'duration' => ['required', 'integer', 'min:15', 'max:480'],
            'type' => ['required', 'string', 'max:255'],
            'reason' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
            'treatment_plan' => ['nullable', 'string'],
            'estimated_cost' => ['nullable', 'numeric', 'min:0'],
            'is_urgent' => ['boolean'],
            'is_follow_up' => ['boolean'],
            'is_recurring' => ['boolean'],
            'reminder_sent' => ['boolean'],
        ];
    }

    public function clinicContext(): ClinicContext
    {
        $context = $this->resolvedClinicContext();

        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');

        return $context;
    }

    private function resolvedClinicContext(): ?ClinicContext
    {
        $context = $this->attributes->get(ClinicContext::class)
            ?? $this->attributes->get('clinic.context');

        return $context instanceof ClinicContext ? $context : null;
    }
}
