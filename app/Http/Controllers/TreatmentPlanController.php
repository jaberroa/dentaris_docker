<?php

namespace App\Http\Controllers;

use App\Models\TreatmentPlan;
use App\Models\TreatmentPlanItem;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\CdtCatalog;
use App\Models\Quote;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TreatmentPlanController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $query = TreatmentPlan::with(['patient', 'staff.user', 'items.cdtCatalog']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('plan_name', 'like', "%{$search}%")
                  ->orWhere('plan_code', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('patient_id')) {
            $query->where('patient_id', $request->patient_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('priority')) {
            $query->where('priority', $request->priority);
        }

        // Ordenamiento
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        switch ($sortField) {
            case 'id':
                $query->orderBy('id', $sortDirection);
                break;
            case 'patient_id':
                $query->join('patients', 'treatment_plans.patient_id', '=', 'patients.id')
                      ->orderBy('patients.last_name', $sortDirection);
                break;
            case 'treatment_name':
                $query->orderBy('plan_name', $sortDirection);
                break;
            case 'start_date':
                $query->orderBy('start_date', $sortDirection);
                break;
            case 'status':
                $query->orderBy('status', $sortDirection);
                break;
            case 'progress':
                // Para progreso, ordenamos por el número de items completados
                $query->withCount(['items as completed_items_count' => function($q) {
                    $q->where('status', 'completed');
                }])->orderBy('completed_items_count', $sortDirection);
                break;
            case 'total_cost':
                $query->orderBy('total_cost', $sortDirection);
                break;
            default:
                $query->orderBy('created_at', $sortDirection);
        }

        $treatmentPlans = $query->paginate(20);

        // Datos para filtros
        $statuses = TreatmentPlan::getStatusOptions();
        $priorities = TreatmentPlan::getPriorityOptions();
        $patients = Patient::select('id', 'first_name', 'last_name')->orderBy('last_name')->get();
        $staff = Staff::with('user')->where('is_active', true)->get();

        return view('treatment-plans.index', compact('treatmentPlans', 'statuses', 'priorities', 'patients', 'staff', 'sortField', 'sortDirection'));
    }

    public function create()
    {
        $patients = Patient::select('id', 'first_name', 'last_name', 'patient_code', 'gender')
            ->where('is_active', true)
            ->orderBy('last_name')
            ->get();
        
        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();
        
        $cdtCatalog = CdtCatalog::where('is_active', true)
            ->orderBy('procedure_name')
            ->get();

        return view('treatment-plans.create', compact('patients', 'staff', 'cdtCatalog'))->withErrors([]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'required|exists:staff,id',
            'plan_name' => 'required|string|max:255',
            'description' => 'required|string',
            'priority' => 'required|string',
            'estimated_duration' => 'required|integer|min:1',
            'estimated_cost' => 'nullable|numeric|min:0',
            'payment_plan' => 'required|string',
            'warranty_period' => 'nullable|integer|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.cdt_catalog_id' => 'required|exists:cdt_catalog,id',
            'items.*.sequence_order' => 'required|integer|min:1',
            'items.*.tooth_number' => 'nullable|string',
            'items.*.surface' => 'nullable|string',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            $treatmentPlan = TreatmentPlan::create([
                'plan_code' => $this->generatePlanCode(),
                'patient_id' => $request->patient_id,
                'staff_id' => $request->staff_id,
                'plan_name' => $request->plan_name,
                'description' => $request->description,
                'priority' => $request->priority,
                'status' => 'draft',
                'estimated_duration' => $request->estimated_duration,
                'estimated_cost' => $request->estimated_cost,
                'payment_plan' => $request->payment_plan,
                'warranty_period' => $request->warranty_period,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            // Crear items del plan de tratamiento
            $totalCost = 0;
            foreach ($request->items as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $totalCost += $itemTotal;

                TreatmentPlanItem::create([
                    'treatment_plan_id' => $treatmentPlan->id,
                    'cdt_catalog_id' => $itemData['cdt_catalog_id'],
                    'sequence_order' => $itemData['sequence_order'],
                    'tooth_number' => $itemData['tooth_number'],
                    'surface' => $itemData['surface'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                    'notes' => $itemData['notes'],
                    'status' => 'pending',
                ]);
            }

            // Actualizar costo total
            $treatmentPlan->update(['total_cost' => $totalCost]);
        });

        return redirect()->route('treatment-plans.index')
            ->with('success', 'Plan de tratamiento creado correctamente');
    }

    public function show(TreatmentPlan $treatmentPlan)
    {
        $treatmentPlan->load([
            'patient',
            'staff.user',
            'items.cdtCatalog',
            'creator'
        ]);

        return view('treatment-plans.show', compact('treatmentPlan'));
    }

    public function edit(TreatmentPlan $treatmentPlan)
    {
        if (!$treatmentPlan->canBeModified()) {
            return redirect()->route('treatment-plans.show', $treatmentPlan)
                ->with('error', 'Este plan no puede ser modificado');
        }

        $treatmentPlan->load(['items.cdtCatalog']);

        $patients = Patient::select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();
        
        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();
        
        $cdtCatalog = CdtCatalog::where('is_active', true)
            ->orderBy('procedure_name')
            ->get();

        $priorities = TreatmentPlan::getPriorityOptions();
        $paymentPlans = TreatmentPlan::getPaymentPlanOptions();

        return view('treatment-plans.edit', compact('treatmentPlan', 'patients', 'staff', 'cdtCatalog', 'priorities', 'paymentPlans'));
    }

    public function update(Request $request, TreatmentPlan $treatmentPlan)
    {
        \Log::info('TreatmentPlan Update - Start', ['plan_id' => $treatmentPlan->id]);
        
        if (!$treatmentPlan->canBeModified()) {
            \Log::warning('TreatmentPlan Update - Cannot be modified', ['plan_id' => $treatmentPlan->id, 'status' => $treatmentPlan->status]);
            return back()->withErrors(['error' => 'Este plan no puede ser modificado']);
        }

        try {
            \Log::info('TreatmentPlan Update - Validating', ['plan_id' => $treatmentPlan->id]);
            
            $request->validate([
                'patient_id' => 'required|exists:patients,id',
                'staff_id' => 'required|exists:staff,id',
                'plan_name' => 'required|string|max:255',
                'description' => 'required|string',
                'start_date' => 'nullable|date',
                'end_date' => 'nullable|date|after_or_equal:start_date',
                'total_sessions' => 'nullable|integer|min:1',
                'total_cost' => 'nullable|numeric|min:0',
                'notes' => 'nullable|string',
                'is_urgent' => 'nullable|boolean',
            ]);

            \Log::info('TreatmentPlan Update - Validation passed', ['plan_id' => $treatmentPlan->id]);
            
            $treatmentPlan->update([
                'patient_id' => $request->patient_id,
                'staff_id' => $request->staff_id,
                'plan_name' => $request->plan_name,
                'description' => $request->description,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'total_sessions' => $request->total_sessions,
                'total_cost' => $request->total_cost,
                'notes' => $request->notes,
                'is_urgent' => $request->has('is_urgent'),
            ]);

            \Log::info('TreatmentPlan Update - Updated successfully', ['plan_id' => $treatmentPlan->id]);

            return redirect()->route('treatment-plans.show', $treatmentPlan)
                ->with('success', 'Plan de tratamiento actualizado correctamente');
                
        } catch (\Exception $e) {
            \Log::error('TreatmentPlan Update - Error', [
                'plan_id' => $treatmentPlan->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()->withErrors(['error' => 'Error al actualizar el plan: ' . $e->getMessage()]);
        }
    }

    public function destroy(TreatmentPlan $treatmentPlan)
    {
        if (!$treatmentPlan->canBeModified()) {
            return back()->withErrors(['error' => 'Este plan no puede ser eliminado']);
        }

        $treatmentPlan->delete();

        return redirect()->route('treatment-plans.index')
            ->with('success', 'Plan de tratamiento eliminado correctamente');
    }

    public function approve(TreatmentPlan $treatmentPlan)
    {
        if ($treatmentPlan->status !== 'draft') {
            return back()->withErrors(['error' => 'Solo se pueden aprobar planes en borrador']);
        }

        $treatmentPlan->approve();

        return redirect()->route('treatment-plans.show', $treatmentPlan)
            ->with('success', 'Plan de tratamiento aprobado correctamente');
    }

    public function start(TreatmentPlan $treatmentPlan)
    {
        if ($treatmentPlan->status !== 'approved') {
            return back()->withErrors(['error' => 'Solo se pueden iniciar planes aprobados']);
        }

        $treatmentPlan->start();

        return redirect()->route('treatment-plans.show', $treatmentPlan)
            ->with('success', 'Plan de tratamiento iniciado correctamente');
    }

    public function pause(TreatmentPlan $treatmentPlan)
    {
        if ($treatmentPlan->status !== 'active') {
            return back()->withErrors(['error' => 'Solo se pueden pausar planes activos']);
        }

        $treatmentPlan->pause();

        return redirect()->route('treatment-plans.show', $treatmentPlan)
            ->with('success', 'Plan de tratamiento pausado correctamente');
    }

    public function resume(TreatmentPlan $treatmentPlan)
    {
        if ($treatmentPlan->status !== 'paused') {
            return back()->withErrors(['error' => 'Solo se pueden reanudar planes pausados']);
        }

        $treatmentPlan->resume();

        return redirect()->route('treatment-plans.show', $treatmentPlan)
            ->with('success', 'Plan de tratamiento reanudado correctamente');
    }

    public function complete(TreatmentPlan $treatmentPlan)
    {
        if (!in_array($treatmentPlan->status, ['active', 'paused'])) {
            return back()->withErrors(['error' => 'Estado inválido para completar']);
        }

        $treatmentPlan->complete();

        return redirect()->route('treatment-plans.show', $treatmentPlan)
            ->with('success', 'Plan de tratamiento completado correctamente');
    }

    public function cancel(TreatmentPlan $treatmentPlan, Request $request)
    {
        if (in_array($treatmentPlan->status, ['completed', 'cancelled'])) {
            return back()->withErrors(['error' => 'No se puede cancelar este plan']);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $treatmentPlan->cancel($request->reason);

        return redirect()->route('treatment-plans.show', $treatmentPlan)
            ->with('success', 'Plan de tratamiento cancelado correctamente');
    }

    public function updateStatus(Request $request, TreatmentPlan $treatmentPlan)
    {
        try {
            $request->validate([
                'status' => 'required|in:draft,active,completed,cancelled,on_hold'
            ]);

            $user = auth()->user();
            $userRole = $this->getUserRole($user);
            $currentStatus = $treatmentPlan->status;
            $newStatus = $request->status;

            // Validar permisos según el rol
            if (!$this->canUserChangeStatus($user, $treatmentPlan, $newStatus, $userRole, $currentStatus)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permisos para realizar este cambio'
                ], 403);
            }

            $oldStatus = $treatmentPlan->status;
            $treatmentPlan->status = $request->status;
            $treatmentPlan->save();

            \Log::info("Estado del plan de tratamiento actualizado", [
                'plan_id' => $treatmentPlan->id,
                'old_status' => $oldStatus,
                'new_status' => $request->status,
                'user_id' => auth()->id(),
                'user_role' => $userRole
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Estado actualizado correctamente'
            ]);

        } catch (\Exception $e) {
            \Log::error("Error al actualizar estado del plan de tratamiento", [
                'plan_id' => $treatmentPlan->id,
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el estado'
            ], 500);
        }
    }

    /**
     * Validar si el usuario puede cambiar el estado del plan
     */
    private function canUserChangeStatus($user, $treatmentPlan, $newStatus, $userRole, $currentStatus)
    {
        // Admin puede cambiar cualquier estado
        if ($userRole === 'admin') {
            return true;
        }

        // Lógica por rol
        switch($userRole) {
            case 'doctor':
                // Doctor solo puede cambiar sus propios planes
                if ($treatmentPlan->staff_id !== ($user->staff->id ?? null)) {
                    return false;
                }
                
                // Validar transiciones permitidas para doctor
                switch($currentStatus) {
                    case 'draft':
                        return in_array($newStatus, ['active', 'cancelled']);
                    case 'active':
                        return in_array($newStatus, ['on_hold', 'completed', 'cancelled']);
                    case 'on_hold':
                        return in_array($newStatus, ['active', 'cancelled']);
                    case 'completed':
                        return false; // Solo admin puede reabrir
                    case 'cancelled':
                        return in_array($newStatus, ['draft', 'active']);
                    default:
                        return in_array($newStatus, ['active', 'cancelled']);
                }
                
            case 'receptionist':
                // Recepcionista solo puede activar planes en borrador
                switch($currentStatus) {
                    case 'draft':
                        return in_array($newStatus, ['active', 'cancelled']);
                    case 'active':
                        return in_array($newStatus, ['on_hold', 'cancelled']);
                    case 'on_hold':
                        return in_array($newStatus, ['active', 'cancelled']);
                    default:
                        return false;
                }
                
            case 'assistant':
                // Asistente solo puede cambiar estados específicos
                switch($currentStatus) {
                    case 'active':
                        return in_array($newStatus, ['on_hold', 'completed']);
                    case 'on_hold':
                        return in_array($newStatus, ['active']);
                    default:
                        return false;
                }
                
            default:
                return false;
        }
    }

    public function addItem(Request $request, TreatmentPlan $treatmentPlan)
    {
        if (!$treatmentPlan->canBeModified()) {
            return back()->withErrors(['error' => 'No se pueden agregar items a este plan']);
        }

        $request->validate([
            'cdt_catalog_id' => 'required|exists:cdt_catalog,id',
            'sequence_order' => 'required|integer|min:1',
            'tooth_number' => 'nullable|string',
            'surface' => 'nullable|string',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $item = TreatmentPlanItem::create([
            'treatment_plan_id' => $treatmentPlan->id,
            'cdt_catalog_id' => $request->cdt_catalog_id,
            'sequence_order' => $request->sequence_order,
            'tooth_number' => $request->tooth_number,
            'surface' => $request->surface,
            'description' => $request->description,
            'quantity' => $request->quantity,
            'unit_price' => $request->unit_price,
            'total_price' => $request->quantity * $request->unit_price,
            'notes' => $request->notes,
            'status' => 'pending',
        ]);

        $treatmentPlan->update(['total_cost' => $treatmentPlan->items()->sum('total_price')]);

        return redirect()->route('treatment-plans.show', $treatmentPlan)
            ->with('success', 'Item agregado correctamente');
    }

    public function updateItemStatus(Request $request, TreatmentPlanItem $treatmentPlanItem)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $treatmentPlanItem->updateStatus($request->status);

        // Verificar si todos los items están completados
        $treatmentPlan = $treatmentPlanItem->treatmentPlan;
        if ($treatmentPlan->isFullyCompleted()) {
            $treatmentPlan->complete();
        }

        return redirect()->route('treatment-plans.show', $treatmentPlan)
            ->with('success', 'Estado del item actualizado correctamente');
    }

    public function createQuote(TreatmentPlan $treatmentPlan)
    {
        if ($treatmentPlan->status !== 'approved') {
            return back()->withErrors(['error' => 'Solo se pueden crear cotizaciones para planes aprobados']);
        }

        $quote = Quote::create([
            'quote_number' => Quote::generateQuoteNumber(),
            'patient_id' => $treatmentPlan->patient_id,
            'staff_id' => $treatmentPlan->staff_id,
            'treatment_plan_id' => $treatmentPlan->id,
            'quote_date' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'pending',
            'notes' => "Cotización generada desde el plan de tratamiento: {$treatmentPlan->plan_name}",
            'created_by' => auth()->id(),
        ]);

        // Crear items de la cotización basados en el plan de tratamiento
        foreach ($treatmentPlan->items as $item) {
            $quote->addItem(
                $item->cdt_catalog_id,
                $item->quantity,
                $item->unit_price,
                $item->description
            );
        }

        $quote->calculateTotals();

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Cotización creada correctamente');
    }

    public function active()
    {
        $treatmentPlans = TreatmentPlan::with(['patient', 'staff.user'])
            ->where('status', 'active')
            ->orderBy('start_date')
            ->paginate(20);

        return view('treatment-plans.active', compact('treatmentPlans'));
    }

    public function completed()
    {
        $treatmentPlans = TreatmentPlan::with(['patient', 'staff.user'])
            ->where('status', 'completed')
            ->orderBy('completed_date', 'desc')
            ->paginate(20);

        return view('treatment-plans.completed', compact('treatmentPlans'));
    }

    public function cancelled()
    {
        $treatmentPlans = TreatmentPlan::with(['patient', 'staff.user'])
            ->where('status', 'cancelled')
            ->orderBy('cancelled_date', 'desc')
            ->paginate(20);

        return view('treatment-plans.cancelled', compact('treatmentPlans'));
    }

    public function report()
    {
        $stats = [
            'total_plans' => TreatmentPlan::count(),
            'draft_plans' => TreatmentPlan::where('status', 'draft')->count(),
            'approved_plans' => TreatmentPlan::where('status', 'approved')->count(),
            'active_plans' => TreatmentPlan::where('status', 'active')->count(),
            'completed_plans' => TreatmentPlan::where('status', 'completed')->count(),
            'total_value' => TreatmentPlan::sum('total_cost'),
        ];

        $plansByStatus = TreatmentPlan::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $plansByPriority = TreatmentPlan::select('priority', DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get();

        $monthlyPlans = TreatmentPlan::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
            ->whereYear('created_at', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('month')
            ->get();

        return view('treatment-plans.report', compact('stats', 'plansByStatus', 'plansByPriority', 'monthlyPlans'));
    }

    private function generatePlanCode()
    {
        $lastPlan = TreatmentPlan::latest()->first();
        $number = $lastPlan ? (int) substr($lastPlan->plan_code, -6) + 1 : 1;
        
        return 'PLAN-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Determinar el rol del usuario
     */
    private function getUserRole($user)
    {
        if (!$user) {
            return 'assistant'; // Por defecto
        }
        
        // Admin siempre es admin, independientemente de su especialidad
        if ($user->email === 'admin@dentaris.com') {
            return 'admin';
        }
        
        if ($user->staff && $user->staff->specialty) {
            $specialty = strtolower($user->staff->specialty);
            
            // Detectar doctores/odontólogos
            if (str_contains($specialty, 'doctor') || 
                str_contains($specialty, 'dentista') ||
                str_contains($specialty, 'odontolog') ||
                str_contains($specialty, 'ortodoncia') ||
                str_contains($specialty, 'endodoncia') ||
                str_contains($specialty, 'cirugia') ||
                str_contains($specialty, 'periodoncia') ||
                str_contains($specialty, 'prostodoncia') ||
                str_contains($specialty, 'protesis') ||
                str_contains($specialty, 'pediatria') ||
                str_contains($specialty, 'odontopediatria') ||
                str_contains($specialty, 'odontopediatría') ||
                str_contains($specialty, 'oral') ||
                str_contains($specialty, 'dental')) {
                return 'doctor';
            }
            
            // Detectar asistentes
            if (str_contains($specialty, 'enfermer') ||
                str_contains($specialty, 'nurse') ||
                str_contains($specialty, 'asistente')) {
                return 'assistant';
            }
            
            // Detectar recepcionistas
            if (str_contains($specialty, 'recepcion') ||
                str_contains($specialty, 'reception')) {
                return 'receptionist';
            }
        }
        
        return 'assistant'; // Default
    }
}
