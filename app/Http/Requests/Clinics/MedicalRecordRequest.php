<?php

namespace App\Http\Requests\Clinics;

use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicalRelatedRecordAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class MedicalRecordRequest extends FormRequest
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
            'record_type' => ['required', Rule::in(['consulta', 'tratamiento', 'seguimiento', 'urgencia'])],
            'chief_complaint' => ['required', 'string', 'max:1000'],
            'present_illness' => ['nullable', 'string', 'max:2000'],
            'medical_history' => ['nullable', 'string', 'max:2000'],
            'dental_history' => ['nullable', 'string', 'max:2000'],
            'family_history' => ['nullable', 'string', 'max:2000'],
            'social_history' => ['nullable', 'string', 'max:2000'],
            'clinical_examination' => ['nullable', 'string', 'max:2000'],
            'vital_signs' => ['nullable', 'string', 'max:500'],
            'oral_examination' => ['nullable', 'string', 'max:2000'],
            'diagnostic_impression' => ['nullable', 'string', 'max:2000'],
            'treatment_plan' => ['nullable', 'string', 'max:2000'],
            'recommendations' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'is_confidential' => ['boolean'],
            'appointment_id' => ['nullable', 'integer'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator): void {
            if ($validator->errors()->hasAny(['patient_id', 'staff_id', 'appointment_id'])) {
                return;
            }

            $appointmentId = $this->input('appointment_id');

            if ($appointmentId === null || $appointmentId === '') {
                return;
            }

            $appointment = app(ClinicalRelatedRecordAccessService::class)
                ->appointments($this->clinicContext())
                ->find($appointmentId);

            if ($appointment === null
                || (int) $appointment->patient_id !== (int) $this->input('patient_id')
                || (int) $appointment->staff_id !== (int) $this->input('staff_id')) {
                $validator->errors()->add(
                    'appointment_id',
                    'La cita debe pertenecer a la clínica, al paciente y al personal seleccionados.'
                );
            }
        }];
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
