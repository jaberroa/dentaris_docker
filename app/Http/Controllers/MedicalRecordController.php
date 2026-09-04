<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clinics\MedicalRecordRequest;
use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Modules\Clinics\Services\ClinicalRelatedRecordAccessService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicalRecordController extends Controller
{
    public function __construct(
        private readonly ClinicalRelatedRecordAccessService $clinicalRecords,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $query = $this->clinicalRecords->medicalRecords($context)
            ->with(['patient', 'staff.user', 'appointment'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('record_type')) {
            $query->where('record_type', $request->record_type);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('chief_complaint', 'like', "%{$search}%")
                  ->orWhere('diagnostic_impression', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($patientQuery) use ($search) {
                      $patientQuery->where('first_name', 'like', "%{$search}%")
                                  ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        $records = $query->paginate(15)->withQueryString();

        return view('medical-records.index', compact('records'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $context = $this->clinicalRecords->context($request);
        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = $this->clinicalRecords->patients($context)->findOrFail($request->patient_id);
        }

        $staff = $this->clinicalRecords->staff($context)->with('user')->active()->get();
        $patients = $this->clinicalRecords->patients($context)->active()->get();

        return view('medical-records.create', compact('patient', 'staff', 'patients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(MedicalRecordRequest $request)
    {
        $this->clinicalRecords->context($request);
        $validated = $request->validated();

        try {
            DB::beginTransaction();

            $record = MedicalRecord::create([
                'patient_id' => $validated['patient_id'],
                'appointment_id' => $validated['appointment_id'] ?? null,
                'staff_id' => $validated['staff_id'],
                'record_type' => $validated['record_type'],
                'chief_complaint' => $validated['chief_complaint'],
                'present_illness' => $validated['present_illness'] ?? '',
                'medical_history' => $validated['medical_history'] ?? '',
                'dental_history' => $validated['dental_history'] ?? '',
                'family_history' => $validated['family_history'] ?? '',
                'social_history' => $validated['social_history'] ?? '',
                'clinical_examination' => $validated['clinical_examination'] ?? '',
                'vital_signs' => $validated['vital_signs'] ?? null,
                'oral_examination' => $validated['oral_examination'] ?? '',
                'diagnostic_impression' => $validated['diagnostic_impression'] ?? '',
                'treatment_plan' => $validated['treatment_plan'] ?? '',
                'recommendations' => $validated['recommendations'] ?? '',
                'notes' => $validated['notes'] ?? null,
                'is_confidential' => $request->boolean('is_confidential'),
                'created_by' => Auth::id()
            ]);

            activity()
                ->performedOn($record)
                ->causedBy($request->user())
                ->log('Historia clínica creada');

            DB::commit();

            return redirect()->route('patients.show', $record->patient_id)
                ->with('success', 'Historia clínica creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating medical record', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error al crear la historia clínica. Inténtalo de nuevo.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, MedicalRecord $medicalRecord)
    {
        $medicalRecord = $this->clinicalRecords->medicalRecord(
            $medicalRecord,
            $this->clinicalRecords->context($request),
        );
        $medicalRecord->load(['patient', 'staff.user', 'appointment', 'diagnoses', 'images']);
        
        return view('medical-records.show', compact('medicalRecord'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, MedicalRecord $medicalRecord)
    {
        $context = $this->clinicalRecords->context($request);
        $medicalRecord = $this->clinicalRecords->medicalRecord($medicalRecord, $context);
        $staff = $this->clinicalRecords->staff($context)->with('user')->active()->get();
        $patients = $this->clinicalRecords->patients($context)->active()->get();
        
        return view('medical-records.edit', compact('medicalRecord', 'staff', 'patients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(MedicalRecordRequest $request, MedicalRecord $medicalRecord)
    {
        $medicalRecord = $this->clinicalRecords->medicalRecord($medicalRecord, $request->clinicContext());
        $validated = $request->validated();

        try {
            $medicalRecord->update([
                'patient_id' => $validated['patient_id'],
                'appointment_id' => $validated['appointment_id'] ?? null,
                'staff_id' => $validated['staff_id'],
                'record_type' => $validated['record_type'],
                'chief_complaint' => $validated['chief_complaint'],
                'present_illness' => $validated['present_illness'] ?? '',
                'medical_history' => $validated['medical_history'] ?? '',
                'dental_history' => $validated['dental_history'] ?? '',
                'family_history' => $validated['family_history'] ?? '',
                'social_history' => $validated['social_history'] ?? '',
                'clinical_examination' => $validated['clinical_examination'] ?? '',
                'vital_signs' => $validated['vital_signs'] ?? null,
                'oral_examination' => $validated['oral_examination'] ?? '',
                'diagnostic_impression' => $validated['diagnostic_impression'] ?? '',
                'treatment_plan' => $validated['treatment_plan'] ?? '',
                'recommendations' => $validated['recommendations'] ?? '',
                'notes' => $validated['notes'] ?? null,
                'is_confidential' => $request->boolean('is_confidential')
            ]);

            activity()
                ->performedOn($medicalRecord)
                ->causedBy($request->user())
                ->log('Historia clínica actualizada');

            return redirect()->route('patients.show', $medicalRecord->patient_id)
                ->with('success', 'Historia clínica actualizada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error updating medical record', [
                'record_id' => $medicalRecord->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al actualizar la historia clínica. Inténtalo de nuevo.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, MedicalRecord $medicalRecord)
    {
        $medicalRecord = $this->clinicalRecords->medicalRecord(
            $medicalRecord,
            $this->clinicalRecords->context($request),
        );
        try {
            $patientId = $medicalRecord->patient_id;

            activity()
                ->performedOn($medicalRecord)
                ->causedBy($request->user())
                ->log('Historia clínica eliminada');

            $medicalRecord->delete();

            return redirect()->route('patients.show', $patientId)
                ->with('success', 'Historia clínica eliminada exitosamente.');

        } catch (\Exception $e) {
            \Log::error('Error deleting medical record', [
                'record_id' => $medicalRecord->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', 'Error al eliminar la historia clínica. Inténtalo de nuevo.');
        }
    }

    /**
     * Obtener historias clínicas de un paciente específico
     */
    public function getPatientRecords(Request $request, Patient $patient)
    {
        $context = $this->clinicalRecords->context($request);
        $patient = $this->clinicalRecords->patients($context)->findOrFail($patient->getKey());
        $records = $this->clinicalRecords->medicalRecords($context)
            ->where('patient_id', $patient->id)
            ->with(['staff.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($records);
    }

    /**
     * Exportar historia clínica a PDF
     */
    public function exportPdf(Request $request, MedicalRecord $medicalRecord)
    {
        $this->clinicalRecords->medicalRecord(
            $medicalRecord,
            $this->clinicalRecords->context($request),
        );

        // Implementar exportación a PDF
        // return PDF::loadView('medical-records.pdf', compact('medicalRecord'))->download();

        abort(501, 'La exportación todavía no está disponible.');
    }
}
