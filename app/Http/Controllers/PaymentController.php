<?php

namespace App\Http\Controllers;

use App\Http\Requests\Billing\StorePaymentRequest;
use App\Http\Requests\Billing\UpdatePaymentRequest;
use App\Models\Invoice;
use App\Models\Payment;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $payments = Payment::forClinic($this->clinicContext($request))
            ->with(['invoice.patient', 'invoice'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $invoices = Invoice::forClinic($this->clinicContext($request))
            ->with('patient')
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->get();

        return view('payments.create', compact('invoices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePaymentRequest $request)
    {
        $context = $this->clinicContext($request);
        $data = $request->validated();

        $payment = DB::transaction(function () use ($request, $data, $context): Payment {
            $invoice = Invoice::forClinic($context)->lockForUpdate()->findOrFail($data['invoice_id']);
            $this->assertPaymentFitsInvoice($invoice, $context, (float) $data['amount']);
            $payment = new Payment([
                ...$data,
                'payment_number' => 'PAY-'.now()->format('Ymd').'-'.Str::upper(Str::random(8)),
                'patient_id' => $invoice->patient_id,
                'processed_by' => $request->user()->id,
                'status' => 'completed',
            ]);
            $payment->forceFill(['clinic_id' => $context->clinicId])->save();
            $this->synchronizeInvoicePaymentState($invoice, $context);

            activity('billing')
                ->causedBy($request->user())
                ->performedOn($payment)
                ->withProperties(['clinic_id' => $context->clinicId, 'invoice_id' => $invoice->id])
                ->log('payment.created');

            return $payment;
        });

        return redirect()->route('payments.index')
            ->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Payment $payment)
    {
        $payment = $this->paymentForContext($payment, $this->clinicContext($request));
        $payment->load(['invoice.patient', 'invoice']);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, Payment $payment)
    {
        $context = $this->clinicContext($request);
        $payment = $this->paymentForContext($payment, $context);
        $invoices = Invoice::forClinic($context)
            ->with('patient')
            ->where('status', '!=', 'paid')
            ->where('status', '!=', 'cancelled')
            ->get();

        return view('payments.edit', compact('payment', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePaymentRequest $request, Payment $payment)
    {
        $context = $this->clinicContext($request);
        $payment = $this->paymentForContext($payment, $context);
        $data = $request->validated();

        DB::transaction(function () use ($request, $payment, $data, $context): void {
            $payment = Payment::forClinic($context)->lockForUpdate()->findOrFail($payment->id);
            $originalInvoice = Invoice::forClinic($context)->lockForUpdate()->findOrFail($payment->invoice_id);
            $targetInvoice = (int) $data['invoice_id'] === (int) $originalInvoice->id
                ? $originalInvoice
                : Invoice::forClinic($context)->lockForUpdate()->findOrFail($data['invoice_id']);

            $this->assertPaymentFitsInvoice(
                $targetInvoice,
                $context,
                (float) $data['amount'],
                $payment->id,
            );
            $payment->fill($data);
            $payment->patient_id = $targetInvoice->patient_id;
            $payment->save();

            $this->synchronizeInvoicePaymentState($originalInvoice, $context);
            if (! $targetInvoice->is($originalInvoice)) {
                $this->synchronizeInvoicePaymentState($targetInvoice, $context);
            }

            activity('billing')
                ->causedBy($request->user())
                ->performedOn($payment)
                ->withProperties(['clinic_id' => $context->clinicId, 'invoice_id' => $targetInvoice->id])
                ->log('payment.updated');
        });

        return redirect()->route('payments.index')
            ->with('success', 'Pago actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Payment $payment)
    {
        $context = $this->clinicContext($request);
        $payment = $this->paymentForContext($payment, $context);

        DB::transaction(function () use ($request, $payment, $context): void {
            $payment = Payment::forClinic($context)->lockForUpdate()->findOrFail($payment->id);
            $invoice = Invoice::forClinic($context)->lockForUpdate()->findOrFail($payment->invoice_id);

            activity('billing')
                ->causedBy($request->user())
                ->performedOn($payment)
                ->withProperties(['clinic_id' => $context->clinicId, 'invoice_id' => $invoice->id])
                ->log('payment.deleted');

            $payment->delete();
            $this->synchronizeInvoicePaymentState($invoice, $context);
        });

        return redirect()->route('payments.index')
            ->with('success', 'Pago eliminado exitosamente.');
    }

    private function clinicContext(Request $request): ClinicContext
    {
        $context = $request->attributes->get(ClinicContext::class)
            ?? $request->attributes->get('clinic.context');

        abort_unless($context instanceof ClinicContext, 403, 'El contexto clínico no está disponible.');

        return $context;
    }

    private function paymentForContext(Payment $payment, ClinicContext $context): Payment
    {
        abort_unless($payment->clinic_id !== null && (int) $payment->clinic_id === $context->clinicId, 404);

        return $payment;
    }

    private function synchronizeInvoicePaymentState(Invoice $invoice, ClinicContext $context): void
    {
        $paidAmount = (float) $invoice->payments()
            ->forClinic($context)
            ->where('status', 'completed')
            ->sum('amount');
        $balanceDue = max(0, (float) $invoice->total_amount - $paidAmount);

        $invoice->update([
            'paid_amount' => $paidAmount,
            'balance_due' => $balanceDue,
            'status' => $balanceDue <= 0 ? 'paid' : 'sent',
        ]);
    }

    private function assertPaymentFitsInvoice(
        Invoice $invoice,
        ClinicContext $context,
        float $amount,
        ?int $exceptPaymentId = null,
    ): void {
        $alreadyPaid = (float) $invoice->payments()
            ->forClinic($context)
            ->where('status', 'completed')
            ->when($exceptPaymentId !== null, fn ($query) => $query->whereKeyNot($exceptPaymentId))
            ->sum('amount');
        $remaining = max(0, (float) $invoice->total_amount - $alreadyPaid);

        if ($amount > $remaining + 0.00001) {
            throw ValidationException::withMessages([
                'amount' => 'El monto supera el saldo pendiente de la factura.',
            ]);
        }
    }
}



