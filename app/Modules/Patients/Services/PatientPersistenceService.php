<?php

namespace App\Modules\Patients\Services;

use App\Models\Patient;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicalOwnershipService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Persiste pacientes sin aceptar propiedad clínica ni autoría del cliente.
 */
class PatientPersistenceService
{
    public function __construct(
        private readonly ClinicalOwnershipService $ownership,
    ) {
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function create(array $attributes, ClinicContext $context, int $creatorId): Patient
    {
        return DB::transaction(function () use ($attributes, $context, $creatorId): Patient {
            $patient = new Patient($this->editableAttributes($attributes));
            $patient->patient_code = 'TMP-'.Str::uuid();
            $patient->created_by = $creatorId;

            $this->ownership->assignPatient($patient, $context);
            $patient->save();

            $patient->patient_code = Patient::generateUniquePatientCode(
                $patient->first_name,
                $patient->last_name,
                $patient->id,
            );
            $patient->save();

            return $patient->refresh();
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public function update(Patient $patient, array $attributes): Patient
    {
        $patient->fill($this->editableAttributes($attributes));
        $patient->save();

        return $patient->refresh();
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @return array<string, mixed>
     */
    private function editableAttributes(array $attributes): array
    {
        unset($attributes['clinic_id'], $attributes['patient_code'], $attributes['created_by']);

        return $attributes;
    }
}
