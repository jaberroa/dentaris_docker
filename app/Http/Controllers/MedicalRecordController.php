<?php

namespace App\Http\Controllers;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class MedicalRecordController extends Controller
{
    public function __construct()
    {
        // Middleware de roles removido temporalmente
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = MedicalRecord::with(['patient', 'staff.user', 'appointment'])
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
        $patient = null;
        if ($request->filled('patient_id')) {
            $patient = Patient::findOrFail($request->patient_id);
        }

        $staff = Staff::with('user')->get();
        $patients = Patient::active()->get();

        return view('medical-records.create', compact('patient', 'staff', 'patients'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        \Log::info('=== MEDICAL RECORD STORE METHOD CALLED ===', ['request_data' => $request->all()]);
        
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'required|exists:staff,id',
            'record_type' => 'required|in:consulta,tratamiento,seguimiento,urgencia',
            'chief_complaint' => 'required|string|max:1000',
            'present_illness' => 'nullable|string|max:2000',
            'medical_history' => 'nullable|string|max:2000',
            'dental_history' => 'nullable|string|max:2000',
            'family_history' => 'nullable|string|max:2000',
            'social_history' => 'nullable|string|max:2000',
            'clinical_examination' => 'nullable|string|max:2000',
            'vital_signs' => 'nullable|string|max:500',
            'oral_examination' => 'nullable|string|max:2000',
            'diagnostic_impression' => 'nullable|string|max:2000',
            'treatment_plan' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:1000',
            'is_confidential' => 'boolean',
            'appointment_id' => 'nullable|exists:appointments,id'
        ]);

        \Log::info('Medical record validation passed');

        try {
            DB::beginTransaction();

            $record = MedicalRecord::create([
                'patient_id' => $request->patient_id,
                'appointment_id' => $request->appointment_id,
                'staff_id' => $request->staff_id,
                'record_type' => $request->record_type,
                'chief_complaint' => $request->chief_complaint,
                'present_illness' => $request->present_illness ?? '',
                'medical_history' => $request->medical_history ?? '',
                'dental_history' => $request->dental_history ?? '',
                'family_history' => $request->family_history ?? '',
                'social_history' => $request->social_history ?? '',
                'clinical_examination' => $request->clinical_examination,
                'vital_signs' => $request->vital_signs,
                'oral_examination' => $request->oral_examination ?? '',
                'diagnostic_impression' => $request->diagnostic_impression,
                'treatment_plan' => $request->treatment_plan ?? '',
                'recommendations' => $request->recommendations ?? '',
                'notes' => $request->notes,
                'is_confidential' => $request->boolean('is_confidential'),
                'created_by' => Auth::id()
            ]);

            DB::commit();

            \Log::info('Medical record created successfully', ['record_id' => $record->id]);

            return redirect()->route('patients.show', $record->patient_id)
                ->with('success', 'Historia clínica creada exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error creating medical record', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error al crear la historia clínica: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(MedicalRecord $medicalRecord)
    {
        $medicalRecord->load(['patient', 'staff.user', 'appointment', 'diagnoses', 'images']);
        
        return view('medical-records.show', compact('medicalRecord'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MedicalRecord $medicalRecord)
    {
        $staff = Staff::with('user')->get();
        $patients = Patient::active()->get();
        
        return view('medical-records.edit', compact('medicalRecord', 'staff', 'patients'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MedicalRecord $medicalRecord)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'required|exists:staff,id',
            'record_type' => 'required|in:consulta,tratamiento,seguimiento,urgencia',
            'chief_complaint' => 'required|string|max:1000',
            'present_illness' => 'nullable|string|max:2000',
            'medical_history' => 'nullable|string|max:2000',
            'dental_history' => 'nullable|string|max:2000',
            'family_history' => 'nullable|string|max:2000',
            'social_history' => 'nullable|string|max:2000',
            'clinical_examination' => 'nullable|string|max:2000',
            'vital_signs' => 'nullable|string|max:500',
            'oral_examination' => 'nullable|string|max:2000',
            'diagnostic_impression' => 'nullable|string|max:2000',
            'treatment_plan' => 'nullable|string|max:2000',
            'recommendations' => 'nullable|string|max:2000',
            'notes' => 'nullable|string|max:1000',
            'is_confidential' => 'boolean',
            'appointment_id' => 'nullable|exists:appointments,id'
        ]);

        try {
            $medicalRecord->update([
                'patient_id' => $request->patient_id,
                'appointment_id' => $request->appointment_id,
                'staff_id' => $request->staff_id,
                'record_type' => $request->record_type,
                'chief_complaint' => $request->chief_complaint,
                'present_illness' => $request->present_illness,
                'medical_history' => $request->medical_history,
                'dental_history' => $request->dental_history,
                'family_history' => $request->family_history,
                'social_history' => $request->social_history,
                'clinical_examination' => $request->clinical_examination,
                'vital_signs' => $request->vital_signs,
                'oral_examination' => $request->oral_examination,
                'diagnostic_impression' => $request->diagnostic_impression,
                'treatment_plan' => $request->treatment_plan,
                'recommendations' => $request->recommendations,
                'notes' => $request->notes,
                'is_confidential' => $request->boolean('is_confidential')
            ]);

            return redirect()->route('patients.show', $medicalRecord->patient_id)
                ->with('success', 'Historia clínica actualizada exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al actualizar la historia clínica: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MedicalRecord $medicalRecord)
    {
        try {
            $patientId = $medicalRecord->patient_id;
            $medicalRecord->delete();

            return redirect()->route('patients.show', $patientId)
                ->with('success', 'Historia clínica eliminada exitosamente.');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al eliminar la historia clínica: ' . $e->getMessage());
        }
    }

    /**
     * Obtener historias clínicas de un paciente específico
     */
    public function getPatientRecords(Patient $patient)
    {
        $records = $patient->medicalRecords()
            ->with(['staff.user', 'appointment'])
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($records);
    }

    /**
     * Exportar historia clínica a PDF
     */
    public function exportPdf(MedicalRecord $medicalRecord)
    {
        // Implementar exportación a PDF
        // return PDF::loadView('medical-records.pdf', compact('medicalRecord'))->download();
    }
}
