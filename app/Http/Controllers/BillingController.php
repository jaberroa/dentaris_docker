<?php

namespace App\Http\Controllers;

use App\Http\Requests\Billing\CancelInvoiceRequest;
use App\Http\Requests\Billing\StoreInvoiceRequest;
use App\Http\Requests\Billing\UpdateInvoiceRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\DailyCash;
use App\Models\AccountsReceivable;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\CdtCatalog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Services\InvoiceLifecycleService;
use Barryvdh\DomPDF\Facade\Pdf;
use InvalidArgumentException;
use App\Modules\Clinics\Data\ClinicContext;

class BillingController extends Controller
{
    public function __construct()
    {
        // Middleware se aplica en las rutas, no en el constructor
    }

    public function index(Request $request)
    {
        $context = $this->clinicContext($request);
        $query = Invoice::forClinic($context)->with(['patient', 'staff.user', 'creator']);

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

        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        $patients = Patient::forClinic($context)->select('id', 'first_name', 'last_name')->orderBy('last_name')->get();
        $staff = Staff::forClinic($context)->with('user')->where('is_active', true)->get();

        return view('billing.index', compact('invoices', 'statuses', 'patients', 'staff'));
    }

    public function create(Request $request)
    {
        $context = $this->clinicContext($request);
        $patients = Patient::forClinic($context)->select('id', 'first_name', 'last_name', 'patient_code')
            ->orderBy('last_name')
            ->get();
        
        $staff = Staff::forClinic($context)->with('user')
            ->where('is_active', true)
            ->get();
        
        $cdtCatalog = CdtCatalog::where('is_active', true)
            ->orderBy('procedure_name')
            ->get();

        return view('billing.create', compact('patients', 'staff', 'cdtCatalog'));
    }

    public function store(StoreInvoiceRequest $request)
    {
        $context = $request->clinicContext();
        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');
        $data = $request->validated();

        DB::transaction(function() use ($request, $data, $context) {
            $invoice = new Invoice([
                'invoice_number' => 'INV-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'patient_id' => $data['patient_id'],
                'staff_id' => $data['staff_id'],
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'status' => 'draft',
                'tax_rate' => $data['tax_rate'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'notes' => $data['notes'] ?? null,
                'created_by' => $request->user()->id,
            ]);
            $invoice->forceFill(['clinic_id' => $context->clinicId])->save();

            $subtotal = 0;
            foreach ($data['items'] as $index => $itemData) {
                $itemTotal = $itemData['quantity'] * $itemData['unit_price'];
                $subtotal += $itemTotal;
                $catalogItem = CdtCatalog::query()->findOrFail($itemData['cdt_catalog_id']);

                $invoice->items()->create([
                    'cdt_catalog_id' => $catalogItem->id,
                    'sequence_order' => $index + 1,
                    'item_name' => $catalogItem->procedure_name,
                    'description' => $catalogItem->description,
                    'quantity' => $itemData['quantity'],
                    'unit_price' => $itemData['unit_price'],
                    'total_price' => $itemTotal,
                ]);
            }

            $invoice->calculateTotals();

            activity('billing')
                ->causedBy($request->user())
                ->performedOn($invoice)
                ->withProperties(['clinic_id' => $context->clinicId, 'items_count' => count($data['items'])])
                ->log('invoice.created');
        });

        return redirect()->route('billing.index')
            ->with('success', 'Factura creada correctamente');
    }

    public function show(Request $request, Invoice $invoice)
    {
        $context = $this->clinicContext($request);
        $invoice = $this->invoiceForContext($invoice, $context);
        $invoice->load([
            'patient',
            'staff.user',
            'items.cdtCatalog',
            'payments' => fn ($query) => $query->forClinic($context),
            'creator'
        ]);

        return view('billing.show', compact('invoice'));
    }

    public function edit(Request $request, Invoice $invoice)
    {
        $invoice = $this->invoiceForContext($invoice, $this->clinicContext($request));
        $invoice->load('items.cdtCatalog');
        $cdtCatalog = CdtCatalog::query()->where('is_active', true)->orderBy('procedure_name')->get();

        return view('billing.edit', compact('invoice', 'cdtCatalog'));
    }

    public function update(UpdateInvoiceRequest $request, Invoice $invoice, InvoiceLifecycleService $service)
    {
        $context = $this->clinicContext($request);
        $invoice = $this->invoiceForContext($invoice, $context);
        try {
            $invoice = $service->updateDraft($invoice, $request->validated(), $request->user(), $context);
        } catch (InvalidArgumentException $exception) {
            return back()->withInput()->with('error', $exception->getMessage());
        }

        return redirect()->route('billing.show', $invoice)->with('success', 'Factura actualizada correctamente.');
    }

    public function downloadPdf(Request $request, Invoice $invoice)
    {
        $context = $this->clinicContext($request);
        $invoice = $this->invoiceForContext($invoice, $context);
        $invoice->load(['patient','staff.user','items.cdtCatalog']);
        activity('billing')->causedBy($request->user())->performedOn($invoice)->withProperties(['clinic_id' => $context->clinicId])->log('invoice.pdf.downloaded');
        return Pdf::loadView('billing.pdf', compact('invoice'))->download('factura_'.$invoice->invoice_number.'.pdf');
    }

    public function sendInvoice(Request $request, Invoice $invoice, InvoiceLifecycleService $service)
    {
        $context = $this->clinicContext($request);
        $invoice = $this->invoiceForContext($invoice, $context);
        try { $service->send($invoice, $request->user(), $context); } catch (InvalidArgumentException $e) { return back()->with('error',$e->getMessage()); }
        return back()->with('success','Factura marcada como enviada.');
    }

    public function destroy(CancelInvoiceRequest $request, Invoice $invoice, InvoiceLifecycleService $service)
    {
        $context = $this->clinicContext($request);
        $invoice = $this->invoiceForContext($invoice, $context);
        try {
            $service->cancel($invoice, $request->user(), $request->validated('reason'), $context);
        } catch (InvalidArgumentException $exception) {
            return back()->with('error', $exception->getMessage());
        }

        return redirect()->route('billing.index')->with('success', 'Factura anulada correctamente.');
    }

    public function addPayment(Request $request, Invoice $invoice)
    {
        $context = $this->clinicContext($request);
        $invoice = $this->invoiceForContext($invoice, $context);
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:' . $invoice->balance_due,
            'payment_method' => 'required|string',
            'payment_date' => 'required|date',
            'reference_number' => 'nullable|string|max:255',
        ]);

        DB::transaction(function() use ($invoice, $request) {
            $payment = new Payment([
                'payment_number' => 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'invoice_id' => $invoice->id,
                'patient_id' => $invoice->patient_id,
                'payment_date' => $request->payment_date,
                'amount' => $request->amount,
                'payment_method' => $request->payment_method,
                'reference_number' => $request->reference_number,
                'status' => 'completed',
                'processed_by' => auth()->id(),
            ]);
            $payment->forceFill(['clinic_id' => $invoice->clinic_id])->save();

            $invoice->addPayment($request->amount, $request->payment_method);
        });

        return redirect()->route('billing.show', $invoice)
            ->with('success', 'Pago registrado correctamente');
    }

    public function markAsPaid(Request $request, Invoice $invoice)
    {
        $invoice = $this->invoiceForContext($invoice, $this->clinicContext($request));
        $invoice->markAsPaid();
        return redirect()->route('billing.show', $invoice)
            ->with('success', 'Factura marcada como pagada');
    }

    public function payments(Request $request)
    {
        $context = $this->clinicContext($request);
        $payments = Payment::forClinic($context)->with(['invoice.patient', 'patient'])
            ->orderBy('payment_date', 'desc')
            ->paginate(20);

        return view('billing.payments', compact('payments'));
    }

    public function report(Request $request)
    {
        $context = $this->clinicContext($request);
        $stats = [
            'total_invoices' => Invoice::forClinic($context)->count(),
            'paid_invoices' => Invoice::forClinic($context)->where('status', 'paid')->count(),
            'total_revenue' => Payment::forClinic($context)->where('status', 'completed')->sum('amount'),
            'monthly_revenue' => Payment::forClinic($context)->whereMonth('payment_date', now()->month)
                ->whereYear('payment_date', now()->year)
                ->where('status', 'completed')
                ->sum('amount'),
        ];

        return view('billing.report', compact('stats'));
    }

    private function clinicContext(Request $request): ClinicContext
    {
        $context = $request->attributes->get(ClinicContext::class)
            ?? $request->attributes->get('clinic.context');

        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');

        return $context;
    }

    private function invoiceForContext(Invoice $invoice, ClinicContext $context): Invoice
    {
        abort_unless($invoice->clinic_id !== null && (int) $invoice->clinic_id === $context->clinicId, 404);

        return $invoice;
    }
}
