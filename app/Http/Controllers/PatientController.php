<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\PatientDocument;
use App\Http\Requests\PatientRequest;
use Illuminate\Http\Request;
use App\Exports\PatientsExport;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;
use Barryvdh\DomPDF\Facade\Pdf;

class PatientController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Patient::with(['creator', 'contacts']);

        // Sorting
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');
        
        // Validar campos de sorting permitidos
        $allowedSortFields = [
            'patient_code', 'first_name', 'last_name', 'email', 'phone', 
            'birth_date', 'gender', 'created_at', 'city', 'state', 'age', 'is_active'
        ];
        
        if (!in_array($sortField, $allowedSortFields)) {
            $sortField = 'created_at';
        }
        
        if (!in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = 'desc';
        }
        
        // Aplicar sorting especial para campos calculados
        if ($sortField === 'age') {
            $query->orderBy('birth_date', $sortDirection === 'asc' ? 'desc' : 'asc');
        } else {
            $query->orderBy($sortField, $sortDirection);
        }

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        // Filtros
        if ($request->filled('gender')) {
            $query->where('gender', $request->gender);
        }

        // Filtro por rango de edad
        if ($request->filled('age_range')) {
            $ageRange = $request->age_range;
            
            switch ($ageRange) {
                case '0-17':
                    $query->byAgeRange(0, 17);
                    break;
                case '18-30':
                    $query->byAgeRange(18, 30);
                    break;
                case '31-50':
                    $query->byAgeRange(31, 50);
                    break;
                case '51-65':
                    $query->byAgeRange(51, 65);
                    break;
                case '65+':
                    // Para 65+ años, usamos una lógica especial
                    $query->where('birth_date', '<=', now()->subYears(65)->format('Y-m-d'));
                    break;
            }
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        // Filtro por consentimiento de marketing
        if ($request->filled('consent_marketing')) {
            $query->where('consent_marketing', $request->consent_marketing === '1');
        }

        // Paginación
        $perPage = $request->get('per_page', '10');
        
        // Validar valores permitidos para per_page
        $allowedPerPage = ['10', '25', '50', '100', 'all'];
        if (!in_array($perPage, $allowedPerPage)) {
            $perPage = '10';
        }
        
        // Manejar paginación
        if ($perPage === 'all') {
            // Para "todos", obtener todos los registros
            $allResults = $query->get();
            $patients = new \Illuminate\Pagination\LengthAwarePaginator(
                $allResults,
                $allResults->count(),
                $allResults->count(),
                1,
                [
                    'path' => request()->url(),
                    'pageName' => 'page'
                ]
            );
            $patients->appends(request()->query());
        } else {
            // Paginación normal
            $perPageInt = (int) $perPage;
            $patients = $query->paginate($perPageInt);
            $patients->appends(request()->query());
        }

        return view('patients.index', compact('patients', 'sortField', 'sortDirection', 'perPage'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('patients.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(PatientRequest $request)
    {
        try {
            // Crear el paciente primero sin código para obtener el ID
            $patient = Patient::create([
                'patient_code' => 'TEMP', // Código temporal
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'phone_secondary' => $request->phone_secondary,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country ?? 'México',
                'medical_history' => $request->medical_history,
                'dental_history' => $request->dental_history,
                'allergies' => $request->allergies,
                'medications' => $request->medications,
                'family_history' => $request->family_history,
                'social_history' => $request->social_history,
                'blood_type' => $request->blood_type,
                'occupation' => $request->occupation,
                'marital_status' => $request->marital_status,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'emergency_contact_address' => $request->emergency_contact_address,
                'notes' => $request->notes,
                'preferences' => $request->preferences,
                'consent_marketing' => $request->consent_marketing ?? false,
                'consent_data_processing' => $request->consent_data_processing ?? true,
                'is_active' => $request->is_active ?? true,
                'created_by' => auth()->id(),
            ]);

            // Generar código único basado en el ID del paciente creado
            $uniqueCode = Patient::generateUniquePatientCode(
                $request->first_name, 
                $request->last_name, 
                $patient->id
            );
            
            // Actualizar el paciente con el código único
            $patient->update(['patient_code' => $uniqueCode]);

            // Log de creación
            activity()
                ->performedOn($patient)
                ->causedBy(auth()->user())
                ->log('Paciente creado');

            return redirect()->route('patients.show', $patient)
                ->with('success', 'Paciente creado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Ocurrió un error al crear el paciente. Inténtalo de nuevo.',
            ])->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient)
    {
        $patient->load([
            'creator',
            'contacts',
            'documents' => function($query) {
                $query->orderBy('created_at', 'desc');
            },
            'medicalRecords' => function($query) {
                $query->with(['staff.user'])->orderBy('created_at', 'desc');
            }
        ]);

        return view('patients.show', compact('patient'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Patient $patient)
    {
        $patient->load('contacts');
        return view('patients.edit', compact('patient'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientRequest $request, Patient $patient)
    {
        try {
            $patient->update([
                'first_name' => $request->first_name,
                'last_name' => $request->last_name,
                'email' => $request->email,
                'phone' => $request->phone,
                'phone_secondary' => $request->phone_secondary,
                'birth_date' => $request->birth_date,
                'gender' => $request->gender,
                'address' => $request->address,
                'city' => $request->city,
                'state' => $request->state,
                'postal_code' => $request->postal_code,
                'country' => $request->country ?? 'México',
                'medical_history' => $request->medical_history,
                'dental_history' => $request->dental_history,
                'allergies' => $request->allergies,
                'medications' => $request->medications,
                'family_history' => $request->family_history,
                'social_history' => $request->social_history,
                'blood_type' => $request->blood_type,
                'occupation' => $request->occupation,
                'marital_status' => $request->marital_status,
                'emergency_contact_name' => $request->emergency_contact_name,
                'emergency_contact_phone' => $request->emergency_contact_phone,
                'emergency_contact_relationship' => $request->emergency_contact_relationship,
                'emergency_contact_address' => $request->emergency_contact_address,
                'notes' => $request->notes,
                'preferences' => $request->preferences,
                'consent_marketing' => $request->consent_marketing ?? false,
                'consent_data_processing' => $request->consent_data_processing ?? true,
                'is_active' => $request->is_active ?? true,
            ]);

            // Log de actualización
            activity()
                ->performedOn($patient)
                ->causedBy(auth()->user())
                ->log('Paciente actualizado');

            return redirect()->route('patients.index')
                ->with('success', 'Paciente actualizado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Ocurrió un error al actualizar el paciente. Inténtalo de nuevo.',
            ])->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient)
    {
        try {
            // Verificar si tiene registros relacionados
            if ($patient->appointments()->exists()) {
                return back()->withErrors([
                    'error' => 'No se puede eliminar el paciente porque tiene citas registradas.',
                ]);
            }

            // Log de eliminación
            activity()
                ->performedOn($patient)
                ->causedBy(auth()->user())
                ->log('Paciente eliminado');

            $patient->delete();

            return redirect()->route('patients.index')
                ->with('success', 'Paciente eliminado exitosamente.');

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'Ocurrió un error al eliminar el paciente. Inténtalo de nuevo.',
            ]);
        }
    }

    /**
     * API: Obtener pacientes para autocompletado
     */
    public function search(Request $request)
    {
        $search = $request->get('search', '');
        $page = $request->get('page', 1);
        $perPage = 15;
        
        $query = Patient::active()
            ->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            })
            ->orderBy('first_name', 'asc')
            ->orderBy('last_name', 'asc');

        $patients = $query->paginate($perPage, ['*'], 'page', $page);

        // Agregar el display_code a cada paciente
        $patients->getCollection()->transform(function ($patient) {
            $patient->display_code = $patient->display_code;
            return $patient;
        });

        return response()->json([
            'data' => $patients->items(),
            'current_page' => $patients->currentPage(),
            'last_page' => $patients->lastPage(),
            'per_page' => $patients->perPage(),
            'total' => $patients->total()
        ]);
    }

    /**
     * Exportar pacientes a Excel
     */
    public function exportExcel(Request $request)
    {
        $filters = $request->only(['search', 'gender', 'is_active', 'date_from', 'date_to']);
        
        $filename = 'pacientes_' . now()->format('Y-m-d_H-i-s') . '.xlsx';
        
        return Excel::download(new PatientsExport($filters), $filename);
    }

    /**
     * Exportar pacientes a PDF
     */
    public function exportPdf(Request $request)
    {
        $filters = $request->only(['search', 'gender', 'is_active', 'date_from', 'date_to']);
        
        // Obtener pacientes con filtros aplicados
        $query = Patient::with(['creator']);

        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['gender'])) {
            $query->where('gender', $filters['gender']);
        }

        if (!empty($filters['is_active'])) {
            $query->where('is_active', $filters['is_active']);
        }

        if (!empty($filters['date_from'])) {
            $query->whereDate('created_at', '>=', $filters['date_from']);
        }

        if (!empty($filters['date_to'])) {
            $query->whereDate('created_at', '<=', $filters['date_to']);
        }

        $patients = $query->orderBy('created_at', 'desc')->get();
        
        $filename = 'pacientes_' . now()->format('Y-m-d_H-i-s') . '.pdf';
        
        $pdf = Pdf::loadView('patients.export-pdf', compact('patients', 'filters'))
                  ->setPaper('A4', 'landscape')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);

        return $pdf->download($filename);
    }

    /**
     * Exportar historial médico de un paciente específico a PDF
     */
    public function exportPatientHistory(Patient $patient)
    {
        $patient->load(['medicalRecords.staff.user', 'appointments.staff.user', 'treatmentPlans']);
        
        $filename = 'historial_' . $patient->patient_code . '_' . now()->format('Y-m-d') . '.pdf';
        
        $pdf = Pdf::loadView('patients.patient-history-pdf', compact('patient'))
                  ->setPaper('A4', 'portrait')
                  ->setOptions([
                      'isHtml5ParserEnabled' => true,
                      'isRemoteEnabled' => true,
                      'defaultFont' => 'DejaVu Sans'
                  ]);

        return $pdf->download($filename);
    }

    /**
     * Update patient gender via AJAX
     */
    public function updateGender(Request $request, Patient $patient)
    {
        $request->validate([
            'gender' => 'required|in:male,female,other'
        ]);

        try {
            $oldGender = $patient->gender;
            
            // Actualizar directamente sin usar update() para evitar observers
            $patient->gender = $request->gender;
            $patient->save();
            
            // Recargar el modelo para asegurar que se actualizó
            $patient->refresh();

            // Log de cambio de género
            activity()
                ->performedOn($patient)
                ->causedBy(auth()->user())
                ->log("Género del paciente cambiado de '{$oldGender}' a '{$request->gender}'");

            return response()->json([
                'success' => true,
                'message' => 'Género actualizado correctamente',
                'gender' => $patient->gender
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el género del paciente'
            ], 500);
        }
    }

    /**
     * Update patient status via AJAX
     */
    public function updateStatus(Request $request, Patient $patient)
    {
        try {
            // Validar que is_active sea boolean o string que represente boolean
            $isActive = $request->is_active;
            if (is_string($isActive)) {
                $isActive = filter_var($isActive, FILTER_VALIDATE_BOOLEAN);
            }
            
            if (!is_bool($isActive)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Valor de estado inválido'
                ], 400);
            }

            $oldStatus = $patient->is_active ? 'Activo' : 'Inactivo';
            
            // Actualizar directamente
            $patient->is_active = $isActive;
            $patient->save();
            
            // Recargar el modelo para asegurar que se actualizó
            $patient->refresh();
            
            $newStatus = $patient->is_active ? 'Activo' : 'Inactivo';

            // Log de cambio de estado
            activity()
                ->performedOn($patient)
                ->causedBy(auth()->user())
                ->log("Estado del paciente cambiado de '{$oldStatus}' a '{$newStatus}'");

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente',
                'is_active' => $patient->is_active,
                'debug' => [
                    'old_status' => $oldStatus,
                    'new_status' => $newStatus,
                    'request_value' => $request->is_active,
                    'processed_value' => $isActive,
                    'patient_id' => $patient->id
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado del paciente'
            ], 500);
        }
    }
}
