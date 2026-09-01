<?php

namespace App\Http\Controllers;

use App\Models\LabWork;
use App\Models\LabWorkItem;
use App\Models\DentalLab;
use App\Models\Prosthesis;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class LabWorkController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $query = LabWork::with(['patient', 'staff.user', 'dentalLab', 'items.prosthesis']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('work_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('dental_lab_id')) {
            $query->where('dental_lab_id', $request->dental_lab_id);
        }

        if ($request->filled('staff_id')) {
            $query->where('staff_id', $request->staff_id);
        }

        if ($request->filled('date_from')) {
            $query->where('work_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('work_date', '<=', $request->date_to);
        }

        if ($request->filled('is_urgent')) {
            $query->where('is_urgent', $request->is_urgent);
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'work_date');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'expected_delivery':
                $query->orderBy('expected_delivery', $sortOrder);
                break;
            case 'status':
                $query->orderBy('status', $sortOrder);
                break;
            case 'patient':
                $query->join('patients', 'lab_works.patient_id', '=', 'patients.id')
                      ->orderBy('patients.last_name', $sortOrder);
                break;
            default:
                $query->orderBy('work_date', $sortOrder);
        }

        $labWorks = $query->paginate(20);

        // Datos para filtros
        $statuses = LabWork::getStatusOptions();
        $dentalLabs = DentalLab::where('is_active', true)->get();
        $staff = Staff::with('user')->where('is_active', true)->get();

        return view('lab-works.index', compact('labWorks', 'statuses', 'dentalLabs', 'staff'));
    }

    public function create()
    {
        $patients = Patient::select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();
        
        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();
        
        $dentalLabs = DentalLab::where('is_active', true)
            ->orderBy('lab_name')
            ->get();
        
        $appointments = Appointment::with(['patient', 'staff.user'])
            ->whereDate('appointment_date', '>=', now()->subDays(7))
            ->orderBy('appointment_date', 'desc')
            ->get();

        return view('lab-works.create', compact('patients', 'staff', 'dentalLabs', 'appointments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'required|exists:staff,id',
            'dental_lab_id' => 'required|exists:dental_labs,id',
            'appointment_id' => 'nullable|exists:appointments,id',
            'work_date' => 'required|date',
            'expected_delivery' => 'required|date|after:work_date',
            'work_description' => 'required|string',
            'specifications' => 'nullable|string',
            'is_urgent' => 'boolean',
            'requires_pickup' => 'boolean',
            'items' => 'required|array|min:1',
            'items.*.prosthesis_id' => 'required|exists:prostheses,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_cost' => 'required|numeric|min:0',
            'items.*.specifications' => 'nullable|string',
        ]);

        $labWork = LabWork::create([
            'work_number' => LabWork::generateWorkNumber(),
            'patient_id' => $request->patient_id,
            'staff_id' => $request->staff_id,
            'dental_lab_id' => $request->dental_lab_id,
            'appointment_id' => $request->appointment_id,
            'work_date' => $request->work_date,
            'expected_delivery' => $request->expected_delivery,
            'status' => 'pending',
            'work_description' => $request->work_description,
            'specifications' => $request->specifications,
            'is_urgent' => $request->has('is_urgent'),
            'requires_pickup' => $request->has('requires_pickup'),
            'created_by' => auth()->id(),
        ]);

        // Crear items del trabajo
        $totalCost = 0;
        foreach ($request->items as $itemData) {
            $itemCost = $itemData['quantity'] * $itemData['unit_cost'];
            $totalCost += $itemCost;

            LabWorkItem::create([
                'lab_work_id' => $labWork->id,
                'prosthesis_id' => $itemData['prosthesis_id'],
                'quantity' => $itemData['quantity'],
                'unit_cost' => $itemData['unit_cost'],
                'total_cost' => $itemCost,
                'specifications' => $itemData['specifications'],
                'status' => 'pending',
            ]);
        }

        $labWork->update(['cost' => $totalCost]);

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Trabajo de laboratorio creado correctamente');
    }

    public function show(LabWork $labWork)
    {
        $labWork->load([
            'patient',
            'staff.user',
            'dentalLab',
            'appointment',
            'items.prosthesis',
            'creator'
        ]);

        return view('lab-works.show', compact('labWork'));
    }

    public function edit(LabWork $labWork)
    {
        if (!$labWork->canBeModified()) {
            return redirect()->route('lab-works.show', $labWork)
                ->with('error', 'Este trabajo no puede ser modificado');
        }

        $labWork->load(['items.prosthesis']);

        $patients = Patient::select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();
        
        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();
        
        $dentalLabs = DentalLab::where('is_active', true)
            ->orderBy('lab_name')
            ->get();
        
        $prostheses = Prosthesis::where('is_active', true)
            ->orderBy('prosthesis_name')
            ->get();

        return view('lab-works.edit', compact('labWork', 'patients', 'staff', 'dentalLabs', 'prostheses'));
    }

    public function update(Request $request, LabWork $labWork)
    {
        if (!$labWork->canBeModified()) {
            return back()->withErrors(['error' => 'Este trabajo no puede ser modificado']);
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'required|exists:staff,id',
            'dental_lab_id' => 'required|exists:dental_labs,id',
            'work_date' => 'required|date',
            'expected_delivery' => 'required|date|after:work_date',
            'work_description' => 'required|string',
            'specifications' => 'nullable|string',
            'is_urgent' => 'boolean',
            'requires_pickup' => 'boolean',
            'notes' => 'nullable|string',
        ]);

        $labWork->update([
            'patient_id' => $request->patient_id,
            'staff_id' => $request->staff_id,
            'dental_lab_id' => $request->dental_lab_id,
            'work_date' => $request->work_date,
            'expected_delivery' => $request->expected_delivery,
            'work_description' => $request->work_description,
            'specifications' => $request->specifications,
            'is_urgent' => $request->has('is_urgent'),
            'requires_pickup' => $request->has('requires_pickup'),
            'notes' => $request->notes,
        ]);

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Trabajo de laboratorio actualizado correctamente');
    }

    public function destroy(LabWork $labWork)
    {
        if (!$labWork->canBeModified()) {
            return back()->withErrors(['error' => 'Este trabajo no puede ser eliminado']);
        }

        $labWork->delete();

        return redirect()->route('lab-works.index')
            ->with('success', 'Trabajo de laboratorio eliminado correctamente');
    }

    public function send(LabWork $labWork, Request $request)
    {
        if ($labWork->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo se pueden enviar trabajos pendientes']);
        }

        $request->validate([
            'tracking_number' => 'nullable|string|max:255',
        ]);

        $labWork->markAsSent($request->tracking_number);

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Trabajo enviado al laboratorio correctamente');
    }

    public function markInProgress(LabWork $labWork)
    {
        if ($labWork->status !== 'sent') {
            return back()->withErrors(['error' => 'Solo se pueden marcar como en progreso trabajos enviados']);
        }

        $labWork->markAsInProgress();

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Trabajo marcado como en progreso');
    }

    public function complete(LabWork $labWork, Request $request)
    {
        if (!in_array($labWork->status, ['sent', 'in_progress'])) {
            return back()->withErrors(['error' => 'Estado inválido para completar']);
        }

        $request->validate([
            'actual_delivery' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $labWork->markAsCompleted();
        $labWork->update([
            'actual_delivery' => $request->actual_delivery,
            'notes' => $labWork->notes . "\n" . $request->notes,
        ]);

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Trabajo completado correctamente');
    }

    public function deliver(LabWork $labWork, Request $request)
    {
        if ($labWork->status !== 'completed') {
            return back()->withErrors(['error' => 'Solo se pueden entregar trabajos completados']);
        }

        $request->validate([
            'actual_delivery' => 'required|date',
            'notes' => 'nullable|string',
        ]);

        $labWork->markAsDelivered($request->actual_delivery);
        $labWork->update([
            'notes' => $labWork->notes . "\nEntregado: " . $request->notes,
        ]);

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Trabajo entregado correctamente');
    }

    public function cancel(LabWork $labWork, Request $request)
    {
        if (in_array($labWork->status, ['delivered', 'cancelled'])) {
            return back()->withErrors(['error' => 'No se puede cancelar este trabajo']);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $labWork->markAsCancelled($request->reason);

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Trabajo cancelado correctamente');
    }

    public function addItem(Request $request, LabWork $labWork)
    {
        if (!$labWork->canBeModified()) {
            return back()->withErrors(['error' => 'No se pueden agregar items a este trabajo']);
        }

        $request->validate([
            'prosthesis_id' => 'required|exists:prostheses,id',
            'quantity' => 'required|integer|min:1',
            'unit_cost' => 'required|numeric|min:0',
            'specifications' => 'nullable|string',
        ]);

        $labWork->addItem(
            $request->prosthesis_id,
            $request->quantity,
            $request->unit_cost,
            $request->specifications
        );

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Item agregado correctamente');
    }

    public function updateItemStatus(Request $request, LabWorkItem $labWorkItem)
    {
        $request->validate([
            'status' => 'required|in:pending,in_progress,completed,cancelled',
        ]);

        $labWorkItem->updateStatus($request->status);

        // Verificar si todos los items están completados
        $labWork = $labWorkItem->labWork;
        if ($labWork->isFullyCompleted()) {
            $labWork->markAsCompleted();
        }

        return redirect()->route('lab-works.show', $labWork)
            ->with('success', 'Estado del item actualizado correctamente');
    }

    public function pending()
    {
        $labWorks = LabWork::with(['patient', 'dentalLab', 'staff.user'])
            ->where('status', 'pending')
            ->orderBy('work_date')
            ->paginate(20);

        return view('lab-works.pending', compact('labWorks'));
    }

    public function overdue()
    {
        $labWorks = LabWork::with(['patient', 'dentalLab', 'staff.user'])
            ->overdue()
            ->orderBy('expected_delivery')
            ->paginate(20);

        return view('lab-works.overdue', compact('labWorks'));
    }

    public function urgent()
    {
        $labWorks = LabWork::with(['patient', 'dentalLab', 'staff.user'])
            ->urgent()
            ->orderBy('expected_delivery')
            ->paginate(20);

        return view('lab-works.urgent', compact('labWorks'));
    }

    public function report()
    {
        $stats = [
            'total_works' => LabWork::count(),
            'pending_works' => LabWork::where('status', 'pending')->count(),
            'in_progress_works' => LabWork::where('status', 'in_progress')->count(),
            'completed_works' => LabWork::where('status', 'completed')->count(),
            'overdue_works' => LabWork::overdue()->count(),
            'urgent_works' => LabWork::urgent()->count(),
            'total_value' => LabWork::sum('cost'),
        ];

        $dentalLabs = DentalLab::withCount('labWorks')
            ->where('is_active', true)
            ->get();

        $monthlyWorks = LabWork::selectRaw('YEAR(work_date) as year, MONTH(work_date) as month, COUNT(*) as count')
            ->whereYear('work_date', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('month')
            ->get();

        return view('lab-works.report', compact('stats', 'dentalLabs', 'monthlyWorks'));
    }
}
