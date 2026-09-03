<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PatientResource;
use App\Http\Resources\PatientContactResource;
use App\Http\Resources\PatientDocumentResource;
use App\Models\Patient;
use App\Models\PatientContact;
use App\Models\PatientDocument;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PatientApiController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Patient::with(['creator', 'contacts', 'documents'])
            ->active()
            ->orderBy('created_at', 'desc');

        // Búsqueda
        if ($request->filled('search')) {
            $search = $request->search;
            $query->search($search);
        }

        // Filtros
        if ($request->filled('gender')) {
            $query->byGender($request->gender);
        }

        if ($request->filled('age_min') && $request->filled('age_max')) {
            $query->byAgeRange($request->age_min, $request->age_max);
        }

        if ($request->filled('has_allergies')) {
            if ($request->boolean('has_allergies')) {
                $query->withAllergies();
            }
        }

        if ($request->filled('consent_marketing')) {
            if ($request->boolean('consent_marketing')) {
                $query->withMarketingConsent();
            }
        }

        if ($request->filled('created_from')) {
            $query->whereDate('created_at', '>=', $request->created_from);
        }

        if ($request->filled('created_to')) {
            $query->whereDate('created_at', '<=', $request->created_to);
        }

        $perPage = $request->integer('per_page', 15);

        if (! in_array($perPage, [10, 15, 25, 50, 100], true)) {
            $perPage = 15;
        }
        $patients = $query->paginate($perPage);

        return response()->json([
            'data' => PatientResource::collection($patients->items()),
            'current_page' => $patients->currentPage(),
            'last_page' => $patients->lastPage(),
            'per_page' => $patients->perPage(),
            'total' => $patients->total(),
            'from' => $patients->firstItem(),
            'to' => $patients->lastItem(),
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:patients,email',
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'medical_history' => 'nullable|string',
            'dental_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medications' => 'nullable|string',
            'family_history' => 'nullable|string',
            'social_history' => 'nullable|string',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'occupation' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'preferences' => 'nullable|array',
            'consent_marketing' => 'boolean',
            'consent_data_processing' => 'required|boolean|accepted',
            'is_active' => 'boolean',
        ]);

        try {
            $patientCode = 'PAT-' . strtoupper(Str::random(8));

            $patient = Patient::create([
                'patient_code' => $patientCode,
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
                'consent_marketing' => $request->boolean('consent_marketing'),
                'consent_data_processing' => $request->boolean('consent_data_processing'),
                'is_active' => $request->boolean('is_active', true),
                'created_by' => auth()->id(),
            ]);

            return response()->json([
                'message' => 'Paciente creado exitosamente',
                'data' => new PatientResource($patient->load(['creator', 'contacts', 'documents']))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el paciente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Patient $patient): JsonResponse
    {
        $patient->load(['creator', 'contacts', 'documents', 'appointments', 'medicalRecords', 'treatmentPlans', 'invoices', 'payments', 'insurances', 'labWorks', 'quotes']);

        return response()->json([
            'data' => new PatientResource($patient)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Patient $patient): JsonResponse
    {
        $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'nullable|email|unique:patients,email,' . $patient->id,
            'phone' => 'nullable|string|max:20',
            'phone_secondary' => 'nullable|string|max:20',
            'birth_date' => 'required|date|before:today',
            'gender' => 'required|in:male,female,other',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'country' => 'nullable|string|max:100',
            'medical_history' => 'nullable|string',
            'dental_history' => 'nullable|string',
            'allergies' => 'nullable|string',
            'medications' => 'nullable|string',
            'family_history' => 'nullable|string',
            'social_history' => 'nullable|string',
            'blood_type' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'occupation' => 'nullable|string|max:255',
            'marital_status' => 'nullable|string|in:single,married,divorced,widowed',
            'emergency_contact_name' => 'nullable|string|max:255',
            'emergency_contact_phone' => 'nullable|string|max:20',
            'emergency_contact_relationship' => 'nullable|string|max:100',
            'emergency_contact_address' => 'nullable|string|max:500',
            'notes' => 'nullable|string',
            'preferences' => 'nullable|array',
            'consent_marketing' => 'boolean',
            'consent_data_processing' => 'required|boolean|accepted',
            'is_active' => 'boolean',
        ]);

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
                'country' => $request->country,
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
                'consent_marketing' => $request->boolean('consent_marketing'),
                'consent_data_processing' => $request->boolean('consent_data_processing'),
                'is_active' => $request->boolean('is_active', true),
            ]);

            return response()->json([
                'message' => 'Paciente actualizado exitosamente',
                'data' => new PatientResource($patient->load(['creator', 'contacts', 'documents']))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el paciente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Patient $patient): JsonResponse
    {
        try {
            // Verificar si tiene registros relacionados
            if ($patient->appointments()->exists()) {
                return response()->json([
                    'message' => 'No se puede eliminar el paciente porque tiene citas registradas'
                ], 422);
            }

            $patient->delete();

            return response()->json([
                'message' => 'Paciente eliminado exitosamente.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al eliminar el paciente',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Search patients for autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $query = $request->get('q', '');
        
        $patients = Patient::active()
            ->search($query)
            ->limit(10)
            ->get(['id', 'patient_code', 'first_name', 'last_name', 'phone']);

        return response()->json([
            'data' => PatientResource::collection($patients)
        ]);
    }

    /**
     * Get patient appointments
     */
    public function appointments(Patient $patient): JsonResponse
    {
        $appointments = $patient->appointments()
            ->with(['staff.user', 'status'])
            ->orderBy('appointment_date', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $appointments->items(),
            'pagination' => [
                'current_page' => $appointments->currentPage(),
                'last_page' => $appointments->lastPage(),
                'per_page' => $appointments->perPage(),
                'total' => $appointments->total(),
            ]
        ]);
    }

    /**
     * Get patient treatment plans
     */
    public function treatmentPlans(Patient $patient): JsonResponse
    {
        $treatmentPlans = $patient->treatmentPlans()
            ->with(['staff.user'])
            ->orderBy('created_at', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $treatmentPlans->items(),
            'pagination' => [
                'current_page' => $treatmentPlans->currentPage(),
                'last_page' => $treatmentPlans->lastPage(),
                'per_page' => $treatmentPlans->perPage(),
                'total' => $treatmentPlans->total(),
            ]
        ]);
    }

    /**
     * Get patient invoices
     */
    public function invoices(Patient $patient): JsonResponse
    {
        $invoices = $patient->invoices()
            ->with(['staff.user', 'creator'])
            ->orderBy('invoice_date', 'desc')
            ->paginate(15);

        return response()->json([
            'data' => $invoices->items(),
            'pagination' => [
                'current_page' => $invoices->currentPage(),
                'last_page' => $invoices->lastPage(),
                'per_page' => $invoices->perPage(),
                'total' => $invoices->total(),
            ]
        ]);
    }

    /**
     * Get patient statistics
     */
    public function statistics(Patient $patient): JsonResponse
    {
        return response()->json([
            'data' => $patient->stats
        ]);
    }
}
