<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use App\Models\Invoice;
use App\Models\Patient;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $payments = Payment::with(['invoice.patient', 'invoice'])
            ->orderBy('payment_date', 'desc')
            ->paginate(15);

        return view('payments.index', compact('payments'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $invoices = Invoice::with('patient')
            ->where('status', '!=', 'paid')
            ->get();

        return view('payments.create', compact('invoices'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment = Payment::create($request->all());

        // Actualizar el estado de la factura si está completamente pagada
        $invoice = $payment->invoice;
        $totalPaid = $invoice->payments()->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Pago registrado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Payment $payment)
    {
        $payment->load(['invoice.patient', 'invoice']);

        return view('payments.show', compact('payment'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Payment $payment)
    {
        $invoices = Invoice::with('patient')
            ->where('status', '!=', 'paid')
            ->get();

        return view('payments.edit', compact('payment', 'invoices'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Payment $payment)
    {
        $request->validate([
            'invoice_id' => 'required|exists:invoices,id',
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string|max:255',
            'payment_date' => 'required|date',
            'notes' => 'nullable|string|max:1000',
        ]);

        $payment->update($request->all());

        // Recalcular el estado de la factura
        $invoice = $payment->invoice;
        $totalPaid = $invoice->payments()->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'sent']);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Pago actualizado exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Payment $payment)
    {
        $invoice = $payment->invoice;
        $payment->delete();

        // Recalcular el estado de la factura
        $totalPaid = $invoice->payments()->sum('amount');
        
        if ($totalPaid >= $invoice->total_amount) {
            $invoice->update(['status' => 'paid']);
        } else {
            $invoice->update(['status' => 'sent']);
        }

        return redirect()->route('payments.index')
            ->with('success', 'Pago eliminado exitosamente.');
    }
}



