<?php

namespace App\Http\Requests\Staff;

use App\Models\Staff;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Foundation\Http\FormRequest;

abstract class StaffRequest extends FormRequest
{
    public function authorize(): bool
    {
        $context = $this->attributes->get(ClinicContext::class);

        if (! $context instanceof ClinicContext) {
            return false;
        }

        $staff = $this->route('staff');

        if ($staff instanceof Staff && (int) $staff->clinic_id !== $context->clinicId) {
            abort(404);
        }

        return true;
    }

    public function clinicContext(): ClinicContext
    {
        $context = $this->attributes->get(ClinicContext::class);

        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');

        return $context;
    }

    /**
     * @return array<string, mixed>
     */
    protected function identityData(bool $includePassword): array
    {
        $data = $this->safe()->only(['name', 'email', 'phone', 'address']);

        if ($includePassword && $this->filled('password')) {
            $data['password'] = $this->string('password')->toString();
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    protected function clinicalData(): array
    {
        return $this->safe()->only([
            'specialty',
            'license_number',
            'license_expiry',
            'university',
            'graduation_year',
            'bio',
            'consultation_fee',
            'experience_years',
            'languages',
            'certifications',
            'is_available',
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function commonRules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string', 'max:1000'],
            'specialty' => ['nullable', 'string', 'max:255'],
            'license_number' => ['nullable', 'string', 'max:100'],
            'license_expiry' => ['nullable', 'date'],
            'university' => ['nullable', 'string', 'max:255'],
            'graduation_year' => ['nullable', 'integer', 'digits:4', 'min:1900', 'max:'.now()->year],
            'bio' => ['nullable', 'string', 'max:5000'],
            'consultation_fee' => ['nullable', 'numeric', 'min:0', 'max:999999.99'],
            'experience_years' => ['nullable', 'integer', 'min:0', 'max:80'],
            'languages' => ['nullable', 'array'],
            'languages.*' => ['string', 'max:100'],
            'certifications' => ['nullable', 'array'],
            'certifications.*' => ['string', 'max:255'],
            'is_available' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'clinic_id' => ['prohibited'],
            'user_id' => ['prohibited'],
            'employee_id' => ['prohibited'],
            // El esquema actual no contiene estas columnas; fallar explícitamente
            // evita aceptar datos que luego se descartarían silenciosamente.
            'hire_date' => ['prohibited'],
            'salary' => ['prohibited'],
            'notes' => ['prohibited'],
        ];
    }
}
