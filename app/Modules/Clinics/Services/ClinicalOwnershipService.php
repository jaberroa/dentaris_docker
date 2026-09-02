<?php

namespace App\Modules\Clinics\Services;

use App\Models\Patient;
use App\Models\Staff;
use App\Modules\Clinics\Data\ClinicContext;
use DomainException;

/**
 * Asigna entidades raíz al único contexto clínico validado del servidor.
 *
 * Ningún atributo clinic_id enviado por un formulario o API se conserva. Este
 * servicio no persiste: la operación funcional conserva su propia transacción.
 */
class ClinicalOwnershipService
{
    public function assignPatient(Patient $patient, ClinicContext $context): Patient
    {
        $this->guardOwnershipChange($patient, $context);
        $patient->clinic()->associate($context->clinicId);

        return $patient;
    }

    public function assignStaff(Staff $staff, ClinicContext $context): Staff
    {
        $this->guardOwnershipChange($staff, $context);
        $staff->clinic()->associate($context->clinicId);

        return $staff;
    }

    private function guardOwnershipChange(Patient|Staff $record, ClinicContext $context): void
    {
        if (! $record->exists) {
            return;
        }

        $currentClinicId = $record->getOriginal('clinic_id');

        if ($currentClinicId === null || (int) $currentClinicId !== $context->clinicId) {
            throw new DomainException('Clinical ownership cannot be changed through an ordinary operation.');
        }
    }
}
