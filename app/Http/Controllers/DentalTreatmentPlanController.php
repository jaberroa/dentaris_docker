<?php

namespace App\Http\Controllers;

use App\Models\DentalTreatmentPlan;
use App\Models\DentalProcedure;
use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class DentalTreatmentPlanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $plans = DentalTreatmentPlan::with(['patient', 'staff'])
            ->latest()
            ->paginate(10);

        return view('dental-plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $patients = Patient::where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();

        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();

        return view('dental-plans.create', compact('patients', 'staff'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'nullable|exists:staff,id',
            'plan_name' => 'required|string|max:255',
            'patient_type' => 'required|in:adult,child,mixed',
            'work_schema' => 'required|in:odontogram,periodontogram,both',
        ]);

        $data['plan_code'] = 'DTP-' . Str::upper(Str::random(6));
        $data['status'] = 'draft';

        $plan = DB::transaction(function () use ($data) {
            return DentalTreatmentPlan::create($data);
        });

        return redirect()->route('dental-plans.show', $plan)
            ->with('success', 'Plan odontológico creado');
    }

    /**
     * Display the specified resource.
     */
    public function show(DentalTreatmentPlan $dental_plan)
    {
        $plan = $dental_plan->load(['patient', 'staff', 'procedures']);
        return view('dental-plans.show', compact('plan'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(DentalTreatmentPlan $dental_plan)
    {
        $plan = $dental_plan->load(['patient', 'staff']);

        $patients = Patient::where('is_active', true)
            ->select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();

        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();

        return view('dental-plans.edit', compact('plan', 'patients', 'staff'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, DentalTreatmentPlan $dental_plan)
    {
        $data = $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'nullable|exists:staff,id',
            'plan_name' => 'required|string|max:255',
            'patient_type' => 'required|in:adult,child,mixed',
            'work_schema' => 'required|in:odontogram,periodontogram,both',
            'status' => 'required|in:draft,active,on_hold,completed,cancelled',
        ]);

        $dental_plan->update($data);

        return redirect()->route('dental-plans.show', $dental_plan)
            ->with('success', 'Plan odontológico actualizado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(DentalTreatmentPlan $dental_plan)
    {
        $dental_plan->delete();
        return redirect()->route('dental-plans.index')
            ->with('success', 'Plan odontológico eliminado');
    }

    public function storeProcedure(Request $request, DentalTreatmentPlan $dental_plan)
    {
        $data = $request->validate([
            'procedure_name' => 'required|string|max:255',
            'procedure_code' => 'nullable|string|max:30',
            'procedure_type' => 'required|in:odontogram,periodontogram',
            'tooth_number' => 'nullable|string|max:3',
            'surface' => 'nullable|string|max:20',
            'periodontal_zone' => 'nullable|string|max:50',
            'estimated_time_minutes' => 'nullable|integer|min:5',
            'estimated_cost' => 'nullable|numeric|min:0',
            'responsible_staff_id' => 'nullable|exists:staff,id',
        ]);

        $data['dental_treatment_plan_id'] = $dental_plan->id;
        $data['status'] = 'pending';

        DentalProcedure::create($data);

        // refrescar progreso básico (placeholder)
        $dental_plan->refreshProgress();

        return redirect()->route('dental-plans.show', $dental_plan)
            ->with('success', 'Procedimiento agregado');
    }

    /**
     * Guardar evaluación de diente (odontograma)
     */
    public function saveToothEvaluation(Request $request, DentalTreatmentPlan $dental_plan)
    {
        $data = $request->validate([
            'tooth_number' => 'required|string|max:3',
            'general_condition' => 'nullable|string|max:50',
            'surface_conditions' => 'nullable|array',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'general_notes' => 'nullable|string',
            'procedure_name' => 'nullable|string|max:255',
            'procedure_code' => 'nullable|string|max:30',
            'estimated_time' => 'nullable|integer',
            'estimated_cost' => 'nullable|numeric',
            'scheduled_date' => 'nullable|date',
            'treatment_notes' => 'nullable|string',
            'mobility' => 'nullable|integer|min:0|max:3',
            'pocket_depth' => 'nullable|string',
            'bleeding' => 'nullable|boolean',
            'recession' => 'nullable|numeric',
            'radiolucency' => 'nullable|boolean',
            'bone_loss' => 'nullable|boolean',
            'periapical_lesion' => 'nullable|boolean',
            'advanced_notes' => 'nullable|string',
        ]);

        // Guardar odontograma (condición general O superficies O cualquier dato)
        $shouldSave = !empty($data['general_condition']) || 
                     !empty($data['surface_conditions']) || 
                     !empty($data['general_notes']);
        
        if ($shouldSave) {
            // Preparar el array de condiciones
            $conditions = [];
            if (!empty($data['general_condition'])) {
                $conditions['general'] = $data['general_condition'];
            }
            if (!empty($data['priority'])) {
                $conditions['priority'] = $data['priority'];
            }
            
            // Procesar superficies para el formato correcto
            $processedSurfaces = null;
            if (!empty($data['surface_conditions'])) {
                $processedSurfaces = [];
                foreach ($data['surface_conditions'] as $surface => $condition) {
                    $processedSurfaces[$surface] = [$condition => true];
                }
            }
            
            \App\Models\DentalOdontogram::updateOrCreate(
                [
                    'dental_treatment_plan_id' => $dental_plan->id,
                    'tooth_number' => $data['tooth_number']
                ],
                [
                    'conditions' => !empty($conditions) ? $conditions : null,
                    'surfaces' => $processedSurfaces,
                    'notes' => $data['general_notes'] ?? null,
                    'needs_attention' => !empty($data['priority']) && in_array($data['priority'], ['high', 'urgent'])
                ]
            );
            
            \Log::info('Odontogram saved', [
                'plan_id' => $dental_plan->id,
                'tooth' => $data['tooth_number'],
                'condition' => $data['general_condition'] ?? 'none',
                'surfaces' => $data['surface_conditions'] ?? 'none'
            ]);
        }

        // Guardar periodontograma (datos avanzados)
        if (!empty($data['mobility']) || !empty($data['pocket_depth'])) {
            \App\Models\DentalPeriodontogram::updateOrCreate(
                [
                    'dental_treatment_plan_id' => $dental_plan->id,
                    'tooth_number' => $data['tooth_number']
                ],
                [
                    'mobility' => $data['mobility'] ?? null,
                    'pocket_depth' => $data['pocket_depth'] ? json_decode($data['pocket_depth']) : null,
                    'bleeding' => $data['bleeding'] ? json_decode($data['bleeding']) : null,
                    'gingival_recession' => $data['recession'] ?? null,
                    'notes' => $data['advanced_notes']
                ]
            );
        }

        // Guardar procedimiento si existe
        if (!empty($data['procedure_name'])) {
            \App\Models\DentalProcedure::create([
                'dental_treatment_plan_id' => $dental_plan->id,
                'tooth_number' => $data['tooth_number'],
                'surface' => !empty($data['surface_conditions']) ? implode(',', array_keys($data['surface_conditions'])) : null,
                'procedure_name' => $data['procedure_name'],
                'procedure_code' => $data['procedure_code'],
                'procedure_type' => 'odontogram',
                'estimated_time_minutes' => $data['estimated_time'],
                'estimated_cost' => $data['estimated_cost'],
                'status' => 'pending',
                'notes' => $data['treatment_notes']
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Evaluación guardada correctamente'
        ]);
    }

    /**
     * Eliminar evaluación de diente (odontograma)
     */
    public function deleteToothEvaluation(Request $request, DentalTreatmentPlan $dental_plan, string $toothNumber)
    {
        try {
            $conditionsToDelete = $request->input('conditions_to_delete', []);
            
            \Log::info('Delete tooth evaluation request', [
                'tooth' => $toothNumber,
                'conditions_to_delete' => $conditionsToDelete,
                'request_data' => $request->all()
            ]);
            
            if (empty($conditionsToDelete)) {
                // Si no se especifican condiciones, eliminar todo (comportamiento anterior)
                \App\Models\DentalOdontogram::where('dental_treatment_plan_id', $dental_plan->id)
                    ->where('tooth_number', $toothNumber)
                    ->delete();
                
                \App\Models\DentalPeriodontogram::where('dental_treatment_plan_id', $dental_plan->id)
                    ->where('tooth_number', $toothNumber)
                    ->delete();
            } else {
                // Borrado selectivo
                $odontogram = \App\Models\DentalOdontogram::where('dental_treatment_plan_id', $dental_plan->id)
                    ->where('tooth_number', $toothNumber)
                    ->first();
                
                if ($odontogram) {
                    $conditions = $odontogram->conditions ?? [];
                    $surfaces = $odontogram->surfaces ?? [];
                    
                    \Log::info('Current conditions and surfaces', [
                        'conditions' => $conditions,
                        'surfaces' => $surfaces,
                        'surfaces_type' => gettype($surfaces),
                        'surfaces_is_array' => is_array($surfaces)
                    ]);
                    
                    // Procesar condiciones a eliminar
                    \Log::info('Processing conditions to delete', [
                        'conditions_to_delete' => $conditionsToDelete,
                        'count' => count($conditionsToDelete)
                    ]);
                    
                    foreach ($conditionsToDelete as $condition) {
                        \Log::info('Processing individual condition', [
                            'condition' => $condition,
                            'is_general' => ($condition === 'general'),
                            'has_dash' => (strpos($condition, '-') !== false)
                        ]);
                        
                        if ($condition === 'general') {
                            unset($conditions['general']);
                            \Log::info('Removed general condition');
                        } elseif (strpos($condition, '-') !== false) {
                            // Es una condición de superficie (ej: "mesial-caries")
                            $parts = explode('-', $condition, 2);
                            $surface = $parts[0];
                            $conditionType = $parts[1];
                            
                            \Log::info('Processing surface condition', [
                                'surface' => $surface,
                                'condition_type' => $conditionType,
                                'surfaces_array' => $surfaces,
                                'surface_exists' => isset($surfaces[$surface]),
                                'surface_is_array' => isset($surfaces[$surface]) && is_array($surfaces[$surface])
                            ]);
                            
                            if (isset($surfaces[$surface]) && is_array($surfaces[$surface])) {
                                unset($surfaces[$surface][$conditionType]);
                                if (empty($surfaces[$surface])) {
                                    unset($surfaces[$surface]);
                                }
                                \Log::info('Successfully removed surface condition');
                            } else {
                                \Log::warning('Surface condition not found or not array', [
                                    'surface' => $surface,
                                    'surfaces' => $surfaces
                                ]);
                            }
                        }
                    }
                    
                    // Actualizar o eliminar el registro
                    \Log::info('Final state after processing', [
                        'conditions' => $conditions,
                        'surfaces' => $surfaces,
                        'conditions_empty' => empty($conditions),
                        'surfaces_empty' => empty($surfaces),
                        'will_delete' => (empty($conditions) && empty($surfaces))
                    ]);
                    
                    if (empty($conditions) && empty($surfaces)) {
                        $odontogram->delete();
                        \Log::info('Odontogram deleted completely');
                    } else {
                        $odontogram->update([
                            'conditions' => !empty($conditions) ? $conditions : null,
                            'surfaces' => !empty($surfaces) ? $surfaces : null
                        ]);
                        \Log::info('Odontogram updated with remaining conditions');
                    }
                }
            }
            
            \Log::info('Tooth evaluation deleted', [
                'plan_id' => $dental_plan->id,
                'tooth' => $toothNumber,
                'conditions_deleted' => $conditionsToDelete
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Condiciones eliminadas correctamente'
            ]);
        } catch (\Exception $e) {
            \Log::error('Error deleting tooth evaluation', [
                'error' => $e->getMessage(),
                'plan_id' => $dental_plan->id,
                'tooth' => $toothNumber
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la evaluación'
            ], 500);
        }
    }

    /**
     * Exportar odontograma a PDF
     */
    public function exportPDF(DentalTreatmentPlan $dental_plan)
    {
        // Por ahora, retornar un PDF básico
        // Puedes usar DomPDF o similar para generar el PDF real
        
        $data = [
            'plan' => $dental_plan->load(['patient', 'odontograms', 'procedures']),
            'odontograms' => $dental_plan->odontograms,
            'procedures' => $dental_plan->procedures,
        ];
        
        // Placeholder - aquí implementarías la generación del PDF
        // Por ahora, redirigir a la vista show con mensaje
        return redirect()->route('dental-plans.show', $dental_plan)
            ->with('info', 'Función de exportación PDF en desarrollo. Usa "Descargar Imagen" o "Imprimir" por ahora.');
    }
}
