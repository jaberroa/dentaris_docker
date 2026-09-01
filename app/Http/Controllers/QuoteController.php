<?php

namespace App\Http\Controllers;

use App\Models\Quote;
use App\Models\QuoteItem;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\TreatmentPlan;
use App\Models\CdtCatalog;
use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class QuoteController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $query = Quote::with(['patient', 'staff.user', 'treatmentPlan', 'creator']);

        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
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

        if ($request->filled('date_from')) {
            $query->where('quote_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('quote_date', '<=', $request->date_to);
        }

        if ($request->filled('expiry_status')) {
            switch ($request->expiry_status) {
                case 'valid':
                    $query->where('valid_until', '>=', now())->where('status', 'pending');
                    break;
                case 'expiring':
                    $query->where('valid_until', '<=', now()->addDays(3))
                          ->where('valid_until', '>', now())
                          ->where('status', 'pending');
                    break;
                case 'expired':
                    $query->where('valid_until', '<', now())->where('status', 'pending');
                    break;
            }
        }

        // Ordenamiento
        $sortBy = $request->get('sort_by', 'quote_date');
        $sortOrder = $request->get('sort_order', 'desc');

        switch ($sortBy) {
            case 'valid_until':
                $query->orderBy('valid_until', $sortOrder);
                break;
            case 'total_amount':
                $query->orderBy('total_amount', $sortOrder);
                break;
            case 'patient':
                $query->join('patients', 'quotes.patient_id', '=', 'patients.id')
                      ->orderBy('patients.last_name', $sortOrder);
                break;
            default:
                $query->orderBy('quote_date', $sortOrder);
        }

        $quotes = $query->paginate(20);

        // Datos para filtros
        $statuses = Quote::getStatusOptions();
        $patients = Patient::select('id', 'first_name', 'last_name')->orderBy('last_name')->get();
        $staff = Staff::with('user')->where('is_active', true)->get();

        return view('quotes.index', compact('quotes', 'statuses', 'patients', 'staff'));
    }

    public function create()
    {
        $patients = Patient::select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();
        
        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();
        
        $treatmentPlans = TreatmentPlan::where('status', 'approved')
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $cdtCatalog = CdtCatalog::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('quotes.create', compact('patients', 'staff', 'treatmentPlans', 'cdtCatalog'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'required|exists:staff,id',
            'treatment_plan_id' => 'nullable|exists:treatment_plans,id',
            'quote_date' => 'required|date',
            'valid_until' => 'required|date|after:quote_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.cdt_catalog_id' => 'required|exists:cdt_catalog,id',
            'items.*.sequence_order' => 'required|integer|min:1',
            'items.*.description' => 'required|string',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
            'items.*.notes' => 'nullable|string',
        ]);

        DB::transaction(function() use ($request) {
            $quote = Quote::create([
                'quote_number' => Quote::generateQuoteNumber(),
                'patient_id' => $request->patient_id,
                'staff_id' => $request->staff_id,
                'treatment_plan_id' => $request->treatment_plan_id,
                'quote_date' => $request->quote_date,
                'valid_until' => $request->valid_until,
                'status' => 'pending',
                'tax_rate' => $request->tax_rate ?: 0,
                'discount_percentage' => $request->discount_percentage ?: 0,
                'notes' => $request->notes,
                'terms_conditions' => $request->terms_conditions,
                'created_by' => auth()->id(),
            ]);

            // Crear items de la cotización
            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $subtotal += $itemTotal;

                QuoteItem::create([
                    'quote_id' => $quote->id,
                    'cdt_catalog_id' => $itemData['cdt_catalog_id'],
                    'sequence_order' => $itemData['sequence_order'],
                    'description' => $itemData['description'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                    'notes' => $itemData['notes'],
                ]);
            }

            // Calcular totales
            $quote->calculateTotals();
        });

        return redirect()->route('quotes.index')
            ->with('success', 'Cotización creada correctamente');
    }

    public function show(Quote $quote)
    {
        $quote->load([
            'patient',
            'staff.user',
            'treatmentPlan',
            'items.cdtCatalog',
            'creator'
        ]);

        return view('quotes.show', compact('quote'));
    }

    public function edit(Quote $quote)
    {
        if (!$quote->canBeModified()) {
            return redirect()->route('quotes.show', $quote)
                ->with('error', 'Esta cotización no puede ser modificada');
        }

        $quote->load(['items.cdtCatalog']);

        $patients = Patient::select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();
        
        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();
        
        $treatmentPlans = TreatmentPlan::where('status', 'approved')
            ->with('patient')
            ->orderBy('created_at', 'desc')
            ->get();
        
        $cdtCatalog = CdtCatalog::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('quotes.edit', compact('quote', 'patients', 'staff', 'treatmentPlans', 'cdtCatalog'));
    }

    public function update(Request $request, Quote $quote)
    {
        if (!$quote->canBeModified()) {
            return back()->withErrors(['error' => 'Esta cotización no puede ser modificada']);
        }

        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'required|exists:staff,id',
            'treatment_plan_id' => 'nullable|exists:treatment_plans,id',
            'quote_date' => 'required|date',
            'valid_until' => 'required|date|after:quote_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_percentage' => 'nullable|numeric|min:0|max:100',
            'notes' => 'nullable|string',
            'terms_conditions' => 'nullable|string',
        ]);

        $quote->update([
            'patient_id' => $request->patient_id,
            'staff_id' => $request->staff_id,
            'treatment_plan_id' => $request->treatment_plan_id,
            'quote_date' => $request->quote_date,
            'valid_until' => $request->valid_until,
            'tax_rate' => $request->tax_rate ?: 0,
            'discount_percentage' => $request->discount_percentage ?: 0,
            'notes' => $request->notes,
            'terms_conditions' => $request->terms_conditions,
        ]);

        $quote->calculateTotals();

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Cotización actualizada correctamente');
    }

    public function destroy(Quote $quote)
    {
        if (!$quote->canBeModified()) {
            return back()->withErrors(['error' => 'Esta cotización no puede ser eliminada']);
        }

        $quote->delete();

        return redirect()->route('quotes.index')
            ->with('success', 'Cotización eliminada correctamente');
    }

    public function approve(Quote $quote)
    {
        if (!$quote->canBeApproved()) {
            return back()->withErrors(['error' => 'Esta cotización no puede ser aprobada']);
        }

        $quote->approve(auth()->id());

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Cotización aprobada correctamente');
    }

    public function reject(Quote $quote, Request $request)
    {
        if ($quote->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo se pueden rechazar cotizaciones pendientes']);
        }

        $request->validate([
            'reason' => 'required|string|max:255',
        ]);

        $quote->reject($request->reason);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Cotización rechazada correctamente');
    }

    public function markAsApprovedByPatient(Quote $quote)
    {
        if ($quote->status !== 'pending') {
            return back()->withErrors(['error' => 'Solo se pueden aprobar cotizaciones pendientes']);
        }

        $quote->markAsApprovedByPatient();

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Cotización aprobada por el paciente');
    }

    public function addItem(Request $request, Quote $quote)
    {
        if (!$quote->canBeModified()) {
            return back()->withErrors(['error' => 'No se pueden agregar items a esta cotización']);
        }

        $request->validate([
            'cdt_catalog_id' => 'required|exists:cdt_catalog,id',
            'sequence_order' => 'required|integer|min:1',
            'description' => 'required|string',
            'quantity' => 'required|integer|min:1',
            'unit_price' => 'required|numeric|min:0',
            'notes' => 'nullable|string',
        ]);

        $quote->addItem(
            $request->cdt_catalog_id,
            $request->quantity,
            $request->unit_price,
            $request->description
        );

        $quote->calculateTotals();

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Item agregado correctamente');
    }

    public function removeItem(QuoteItem $quoteItem)
    {
        $quote = $quoteItem->quote;
        
        if (!$quote->canBeModified()) {
            return back()->withErrors(['error' => 'No se pueden modificar items de esta cotización']);
        }

        $quoteItem->delete();
        $quote->calculateTotals();

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Item eliminado correctamente');
    }

    public function updateDiscount(Request $request, Quote $quote)
    {
        if (!$quote->canBeModified()) {
            return back()->withErrors(['error' => 'No se puede modificar esta cotización']);
        }

        $request->validate([
            'discount_percentage' => 'required|numeric|min:0|max:100',
        ]);

        $quote->updateDiscount($request->discount_percentage);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Descuento actualizado correctamente');
    }

    public function updateTaxRate(Request $request, Quote $quote)
    {
        if (!$quote->canBeModified()) {
            return back()->withErrors(['error' => 'No se puede modificar esta cotización']);
        }

        $request->validate([
            'tax_rate' => 'required|numeric|min:0|max:100',
        ]);

        $quote->updateTaxRate($request->tax_rate);

        return redirect()->route('quotes.show', $quote)
            ->with('success', 'Tasa de impuesto actualizada correctamente');
    }

    public function createInvoice(Quote $quote)
    {
        if ($quote->status !== 'approved') {
            return back()->withErrors(['error' => 'Solo se pueden crear facturas de cotizaciones aprobadas']);
        }

        $invoice = Invoice::create([
            'invoice_number' => Invoice::generateInvoiceNumber(),
            'patient_id' => $quote->patient_id,
            'staff_id' => $quote->staff_id,
            'treatment_plan_id' => $quote->treatment_plan_id,
            'invoice_date' => now(),
            'due_date' => now()->addDays(30),
            'status' => 'draft',
            'subtotal' => $quote->subtotal,
            'tax_rate' => $quote->tax_rate,
            'tax_amount' => $quote->tax_amount,
            'discount_amount' => $quote->discount_amount,
            'total_amount' => $quote->total_amount,
            'notes' => "Factura generada desde cotización: {$quote->quote_number}",
            'created_by' => auth()->id(),
        ]);

        // Crear items de la factura basados en la cotización
        foreach ($quote->items as $item) {
            $invoice->addItem(
                $item->cdt_catalog_id,
                $item->quantity,
                $item->unit_price,
                $item->description
            );
        }

        $invoice->calculateTotals();

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Factura creada correctamente');
    }

    public function pending()
    {
        $quotes = Quote::with(['patient', 'staff.user'])
            ->where('status', 'pending')
            ->where('valid_until', '>=', now())
            ->orderBy('valid_until')
            ->paginate(20);

        return view('quotes.pending', compact('quotes'));
    }

    public function approved()
    {
        $quotes = Quote::with(['patient', 'staff.user'])
            ->where('status', 'approved')
            ->orderBy('approved_date', 'desc')
            ->paginate(20);

        return view('quotes.approved', compact('quotes'));
    }

    public function rejected()
    {
        $quotes = Quote::with(['patient', 'staff.user'])
            ->where('status', 'rejected')
            ->orderBy('updated_at', 'desc')
            ->paginate(20);

        return view('quotes.rejected', compact('quotes'));
    }

    public function expired()
    {
        $quotes = Quote::with(['patient', 'staff.user'])
            ->where('valid_until', '<', now())
            ->where('status', 'pending')
            ->orderBy('valid_until', 'desc')
            ->paginate(20);

        return view('quotes.expired', compact('quotes'));
    }

    public function expiringSoon()
    {
        $quotes = Quote::with(['patient', 'staff.user'])
            ->where('valid_until', '<=', now()->addDays(3))
            ->where('valid_until', '>', now())
            ->where('status', 'pending')
            ->orderBy('valid_until')
            ->paginate(20);

        return view('quotes.expiring-soon', compact('quotes'));
    }

    public function report()
    {
        $stats = [
            'total_quotes' => Quote::count(),
            'pending_quotes' => Quote::where('status', 'pending')->count(),
            'approved_quotes' => Quote::where('status', 'approved')->count(),
            'rejected_quotes' => Quote::where('status', 'rejected')->count(),
            'expired_quotes' => Quote::where('valid_until', '<', now())->where('status', 'pending')->count(),
            'total_value' => Quote::sum('total_amount'),
            'approval_rate' => Quote::getApprovalRate(),
        ];

        $quotesByStatus = Quote::select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get();

        $monthlyQuotes = Quote::selectRaw('YEAR(quote_date) as year, MONTH(quote_date) as month, COUNT(*) as count')
            ->whereYear('quote_date', now()->year)
            ->groupBy('year', 'month')
            ->orderBy('month')
            ->get();

        return view('quotes.report', compact('stats', 'quotesByStatus', 'monthlyQuotes'));
    }

    public function print(Quote $quote)
    {
        $quote->load([
            'patient',
            'staff.user',
            'items.cdtCatalog'
        ]);

        return view('quotes.print', compact('quote'));
    }

    public function export(Request $request)
    {
        // TODO: Implementar exportación a PDF/Excel
        return response()->json(['message' => 'Función de exportación pendiente de implementar']);
    }
}
