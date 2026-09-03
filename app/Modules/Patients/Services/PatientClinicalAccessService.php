<?php

namespace App\Modules\Patients\Services;

use App\Models\Patient;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Http\Request;

/**
 * Punto único de acceso a pacientes dentro del contexto clínico ya validado.
 */
class PatientClinicalAccessService
{
    public function context(Request $request): ClinicContext
    {
        $context = $request->attributes->get(ClinicContext::class);
        $userId = $request->user()?->getAuthIdentifier();

        abort_unless(
            $context instanceof ClinicContext
                && $userId !== null
                && (int) $userId === $context->userId,
            403,
            'El contexto clínico no está disponible.'
        );

        return $context;
    }

    public function patient(Patient $patient, ClinicContext $context): Patient
    {
        return Patient::query()
            ->forClinic($context)
            ->findOrFail($patient->getKey());
    }
}
