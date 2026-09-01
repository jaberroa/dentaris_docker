<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\DailyCash;
use App\Models\AccountsReceivable;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\CdtCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BillingController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $query = Invoice::with(['patient', 'staff.user', 'creator']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('patient', function($subQ) use ($search) {
                      $subQ->where('first_name', 'like', "%{$search}%")
                           ->orWhere('last_name', 'like', "%{$search}%");
                  });
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $invoices = $query->orderBy('invoice_date', 'desc')->paginate(20);

        $statuses = ['draft', 'sent', 'paid', 'overdue'];
        $patients = Patient::select('id', 'first_name', 'last_name')->orderBy('last_name')->get();
        $staff = Staff::with('user')->where('is_active', true)->get();

        return view('billing.index', compact('invoices', 'statuses', 'patients', 'staff'));
    }

    public function create()
    {
        $patients = Patient::select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();
        
        $staff = Staff::with('user')
            ->where('is_active', true)
            ->get();
        
        $cdtCatalog = CdtCatalog::where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('billing.create', compact('patients', 'staff', 'cdtCatalog'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'required|exists:patients,id',
            'staff_id' => 'required|exists:staff,id',
            'invoice_date' => 'required|date',
            'due_date' => 'required|date|after:invoice_date',
            'tax_rate' => 'nullable|numeric|min:0|max:100',
            'discount_amount' => 'nullable|numeric|min:0',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.cdt_catalog_id' => 'required|exists:cdt_catalog,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::transaction(function() use ($request) {
            $invoice = Invoice::create([
                'invoice_number' => 'INV-' . str_pad(Invoice::count() + 1, 6, '0', STR_PAD_LEFT),
                'patient_id' => $request->patient_id,
                'staff_id' => $request->staff_id,
                'invoice_date' => $request->invoice_date,
                'due_date' => $request->due_date,
                'status' => 'draft',
                'tax_rate' => $request->tax_rate ?: 0,
                'discount_amount' => $request->discount_amount ?: 0,
                'notes' => $request->notes,
                'created_by' => auth()->id(),
            ]);

            $subtotal = 0;
            foreach ($request->items as $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $subtotal += $itemTotal;

                $invoice->items()->create([
                    'cdt_catalog_id' => $itemData['cdt_catalog_id'],
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                ]);
            }

            $invoice->calculateTotals();
        });

        return redirect()->route('billing.index')
            ->with('success', 'Factura creada correctamente');
    }

    public function show(Invoice $invoice)
    {
        $invoice->load([
            'patient',
            'staff.user',
            'items.cdtCatalog',
            'payments',
            'creator'
        ]);

        return view('billing.show', compact('invoice'));
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
        ]);

        DB::transaction(function() use ($invoice, $request) {
            Payment::create([
                'payment_number' => 'PAY-' . str_pad(Payment::count() + 1, 6, '0', STR_PAD_LEFT),
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'status' => 'completed',
                'processed_by' => auth()->id(),
            ]);

            $invoice->addPayment($request->amount, $request->payment_method);
        });

        return redirect()->route('billing.show', $invoice)
            ->with('success', 'Pago registrado correctamente');
    }

    public function markAsPaid(Invoice $invoice)
    {
        $invoice->markAsPaid();
        return redirect()->route('billing.show', $invoice)
            ->with('success', 'Factura marcada como pagada');
    }

    public function payments()
    {
        $payments = Payment::with(['invoice.patient', 'patient'])
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        return view('billing.payments', compact('payments'));
    }

    public function report()
    {
        $stats = [
            'total_invoices' => Invoice::count(),
            'paid_invoices' => Invoice::where('status', 'paid')->count(),
            'total_revenue' => Payment::where('status', 'completed')->sum('amount'),
            'monthly_revenue' => Payment::whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        return view('billing.report', compact('stats'));
    }
}
