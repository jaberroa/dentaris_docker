<?php

namespace App\Modules\Clinics\Services;

use App\Models\Appointment;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Staff;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

/**
 * Limita consumidores clínicos que heredan la clínica de paciente y personal.
 */
class ClinicalRelatedRecordAccessService
{
    public function context(Request $request): ClinicContext
    {
        $context = $request->attributes->get(ClinicContext::class)
            ?? $request->attributes->get('clinic.context');
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

    public function patients(ClinicContext $context): Builder
    {
        return Patient::query()->forClinic($context);
    }

    public function staff(ClinicContext $context): Builder
    {
        return Staff::query()->forClinic($context);
    }

    public function appointments(ClinicContext $context): Builder
    {
        return $this->constrainAppointments(Appointment::query(), $context);
    }

    public function appointment(Appointment $appointment, ClinicContext $context): Appointment
    {
        return $this->appointments($context)->findOrFail($appointment->getKey());
    }

    public function medicalRecords(ClinicContext $context): Builder
    {
        return MedicalRecord::query()
            ->whereHas('patient', fn (Builder $query) => $query->forClinic($context))
            ->whereHas('staff', fn (Builder $query) => $query->forClinic($context))
            ->where(function (Builder $query) use ($context): void {
                $query->whereNull('appointment_id')
                    ->orWhereHas('appointment', function (Builder $appointmentQuery) use ($context): void {
                        $this->constrainAppointments($appointmentQuery, $context)
                            ->whereColumn('appointments.patient_id', 'medical_records.patient_id')
                            ->whereColumn('appointments.staff_id', 'medical_records.staff_id');
                    });
            });
    }

    public function medicalRecord(MedicalRecord $medicalRecord, ClinicContext $context): MedicalRecord
    {
        return $this->medicalRecords($context)->findOrFail($medicalRecord->getKey());
    }

    private function constrainAppointments(Builder $query, ClinicContext $context): Builder
    {
        return $query
            ->whereHas('patient', fn (Builder $patientQuery) => $patientQuery->forClinic($context))
            ->whereHas('staff', fn (Builder $staffQuery) => $staffQuery->forClinic($context));
    }
}
