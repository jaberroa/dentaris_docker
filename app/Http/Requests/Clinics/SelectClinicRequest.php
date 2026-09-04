<?php

namespace App\Http\Requests\Clinics;

use Illuminate\Foundation\Http\FormRequest;

class SelectClinicRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->is_active;
    }

    public function rules(): array
    {
        return [
            'clinic_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function messages(): array
    {
        return [
            'clinic_id.required' => 'Selecciona una clínica disponible.',
            'clinic_id.integer' => 'La clínica seleccionada no es válida.',
            'clinic_id.min' => 'La clínica seleccionada no es válida.',
        ];
    }
}
