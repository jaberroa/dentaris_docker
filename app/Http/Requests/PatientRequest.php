<?php

namespace App\Http\Requests;

use App\Models\Patient;
use App\Modules\Patients\Services\PatientClinicalAccessService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PatientRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        $access = app(PatientClinicalAccessService::class);
        $context = $access->context($this);
        $patient = $this->route('patient');

        if ($patient instanceof Patient) {
            $access->patient($patient, $context);
        }

        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $patientId = $this->route('patient')?->id;

        return [
            // Información personal
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('patients')->ignore($patientId)],
            'phone' => ['nullable', 'string', 'max:20'],
            'phone_secondary' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['required', 'date', 'before:today'],
            'gender' => ['required', 'in:male,female,other'],
            
            // Dirección
            'address' => ['nullable', 'string', 'max:500'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'country' => ['nullable', 'string', 'max:100'],
            
            // Información médica
            'medical_history' => ['nullable', 'string'],
            'dental_history' => ['nullable', 'string'],
            'allergies' => ['nullable', 'string'],
            'medications' => ['nullable', 'string'],
            'family_history' => ['nullable', 'string'],
            'social_history' => ['nullable', 'string'],
            'blood_type' => ['nullable', 'string', 'in:A+,A-,B+,B-,AB+,AB-,O+,O-'],
            'occupation' => ['nullable', 'string', 'max:255'],
            'marital_status' => ['nullable', 'string', 'in:single,married,divorced,widowed'],
            
            // Contactos de emergencia
            'emergency_contact_name' => ['nullable', 'string', 'max:255'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relationship' => ['nullable', 'string', 'max:100'],
            'emergency_contact_address' => ['nullable', 'string', 'max:500'],
            
            // Información adicional
            'notes' => ['nullable', 'string'],
            'preferences' => ['nullable', 'array'],
            'consent_marketing' => ['boolean'],
            'consent_data_processing' => ['required', 'boolean'],
            'is_active' => ['boolean'],
            'clinic_id' => ['prohibited'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'first_name.required' => 'El nombre es obligatorio.',
            'first_name.max' => 'El nombre no puede tener más de 255 caracteres.',
            'last_name.required' => 'El apellido es obligatorio.',
            'last_name.max' => 'El apellido no puede tener más de 255 caracteres.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
            'phone.max' => 'El teléfono no puede tener más de 20 caracteres.',
            'phone_secondary.max' => 'El teléfono secundario no puede tener más de 20 caracteres.',
            'birth_date.required' => 'La fecha de nacimiento es obligatoria.',
            'birth_date.date' => 'La fecha de nacimiento debe ser una fecha válida.',
            'birth_date.before' => 'La fecha de nacimiento debe ser anterior a hoy.',
            'gender.required' => 'El género es obligatorio.',
            'gender.in' => 'El género debe ser masculino, femenino u otro.',
            'address.max' => 'La dirección no puede tener más de 500 caracteres.',
            'city.max' => 'La ciudad no puede tener más de 100 caracteres.',
            'state.max' => 'El estado no puede tener más de 100 caracteres.',
            'postal_code.max' => 'El código postal no puede tener más de 20 caracteres.',
            'country.max' => 'El país no puede tener más de 100 caracteres.',
            'blood_type.in' => 'El tipo de sangre debe ser uno de los valores válidos.',
            'occupation.max' => 'La ocupación no puede tener más de 255 caracteres.',
            'marital_status.in' => 'El estado civil debe ser uno de los valores válidos.',
            'emergency_contact_name.max' => 'El nombre del contacto de emergencia no puede tener más de 255 caracteres.',
            'emergency_contact_phone.max' => 'El teléfono del contacto de emergencia no puede tener más de 20 caracteres.',
            'emergency_contact_relationship.max' => 'La relación del contacto de emergencia no puede tener más de 100 caracteres.',
            'emergency_contact_address.max' => 'La dirección del contacto de emergencia no puede tener más de 500 caracteres.',
            'consent_data_processing.required' => 'Debe aceptar el procesamiento de datos.',
            'consent_data_processing.accepted' => 'Debe aceptar el procesamiento de datos.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'email' => 'email',
            'phone' => 'teléfono',
            'phone_secondary' => 'teléfono secundario',
            'birth_date' => 'fecha de nacimiento',
            'gender' => 'género',
            'address' => 'dirección',
            'city' => 'ciudad',
            'state' => 'estado',
            'postal_code' => 'código postal',
            'country' => 'país',
            'medical_history' => 'historial médico',
            'dental_history' => 'historial dental',
            'allergies' => 'alergias',
            'medications' => 'medicamentos',
            'family_history' => 'historial familiar',
            'social_history' => 'historial social',
            'blood_type' => 'tipo de sangre',
            'occupation' => 'ocupación',
            'marital_status' => 'estado civil',
            'emergency_contact_name' => 'nombre del contacto de emergencia',
            'emergency_contact_phone' => 'teléfono del contacto de emergencia',
            'emergency_contact_relationship' => 'relación del contacto de emergencia',
            'emergency_contact_address' => 'dirección del contacto de emergencia',
            'notes' => 'notas',
            'preferences' => 'preferencias',
            'consent_marketing' => 'consentimiento de marketing',
            'consent_data_processing' => 'consentimiento de procesamiento de datos',
            'is_active' => 'estado activo',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Asegurar que los valores booleanos sean correctos
        $mergeData = [
            'consent_marketing' => $this->boolean('consent_marketing'),
        ];

        if ($this->has('consent_data_processing')) {
            $mergeData['consent_data_processing'] = $this->boolean('consent_data_processing');
        }
        
        // Solo incluir is_active si está presente en el request
        if ($this->has('is_active')) {
            $mergeData['is_active'] = $this->boolean('is_active');
        }
        
        $this->merge($mergeData);
    }
}





