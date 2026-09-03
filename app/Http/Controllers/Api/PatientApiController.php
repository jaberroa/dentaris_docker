<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PatientApiRequest;
use App\Http\Resources\PatientResource;
use App\Models\Patient;
use App\Modules\Patients\Services\PatientClinicalAccessService;
use App\Modules\Patients\Services\PatientPersistenceService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class PatientApiController extends Controller
{
    public function __construct(
        private readonly PatientClinicalAccessService $clinicalAccess,
        private readonly PatientPersistenceService $persistence,
    ) {
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $context = $this->clinicalAccess->context($request);
        $query = Patient::query()
            ->forClinic($context)
            ->with(['creator', 'contacts', 'documents'])
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
    public function store(PatientApiRequest $request): JsonResponse
    {
        try {
            $context = $this->clinicalAccess->context($request);
            $patient = $this->persistence->create(
                $request->validated(),
                $context,
                (int) $request->user()->getAuthIdentifier(),
            );

            return response()->json([
                'message' => 'Paciente creado exitosamente',
                'data' => new PatientResource($patient->load(['creator', 'contacts', 'documents']))
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al crear el paciente',
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Patient $patient): JsonResponse
    {
        $context = $this->clinicalAccess->context($request);
        $patient = $this->clinicalAccess->patient($patient, $context);
        $patient->load(['creator', 'contacts', 'documents', 'appointments', 'medicalRecords', 'treatmentPlans', 'invoices', 'payments', 'insurances', 'labWorks', 'quotes']);

        return response()->json([
            'data' => new PatientResource($patient)
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(PatientApiRequest $request, Patient $patient): JsonResponse
    {
        try {
            $context = $this->clinicalAccess->context($request);
            $patient = $this->clinicalAccess->patient($patient, $context);
            $patient = $this->persistence->update($patient, $request->validated());

            return response()->json([
                'message' => 'Paciente actualizado exitosamente',
                'data' => new PatientResource($patient->load(['creator', 'contacts', 'documents']))
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al actualizar el paciente',
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Patient $patient): JsonResponse
    {
        $context = $this->clinicalAccess->context($request);
        $patient = $this->clinicalAccess->patient($patient, $context);

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
            ], 500);
        }
    }

    /**
     * Search patients for autocomplete
     */
    public function search(Request $request): JsonResponse
    {
        $context = $this->clinicalAccess->context($request);
        $query = $request->get('q', '');
        
        $patients = Patient::query()
            ->forClinic($context)
            ->active()
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
    public function appointments(Request $request, Patient $patient): JsonResponse
    {
        $context = $this->clinicalAccess->context($request);
        $patient = $this->clinicalAccess->patient($patient, $context);
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
    public function treatmentPlans(Request $request, Patient $patient): JsonResponse
    {
        $context = $this->clinicalAccess->context($request);
        $patient = $this->clinicalAccess->patient($patient, $context);
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
    public function invoices(Request $request, Patient $patient): JsonResponse
    {
        $context = $this->clinicalAccess->context($request);
        $patient = $this->clinicalAccess->patient($patient, $context);
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
    public function statistics(Request $request, Patient $patient): JsonResponse
    {
        $context = $this->clinicalAccess->context($request);
        $patient = $this->clinicalAccess->patient($patient, $context);

        return response()->json([
            'data' => $patient->stats
        ]);
    }
}
