@extends('layouts.master')

@section('title', 'Detalle de factura')

@section('content')
    @php
        $statusClass = match ($invoice->status) {
            'sent' => 'bg-primary',
            'paid' => 'bg-success',
            'overdue' => 'bg-warning text-dark',
            'cancelled' => 'bg-danger',
            default => 'bg-secondary',
        };
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Factura {{ $invoice->invoice_number }}</h4>
                    <p class="text-muted mb-3">Detalle, pagos y trazabilidad de la factura.</p>
                </div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('billing.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Volver</a>
                    <a href="{{ route('billing.pdf', $invoice) }}" class="btn btn-outline-danger"><i class="fas fa-file-pdf me-1"></i> PDF</a>
                    @can('update', $invoice)
                        <a href="{{ route('billing.edit', $invoice) }}" class="btn btn-outline-primary"><i class="fas fa-pen me-1"></i> Editar</a>
                    @endcan
                    @can('send', $invoice)
                        @if($invoice->isDraft())
                            <form method="POST" action="{{ route('billing.send', $invoice) }}">@csrf
                                <button class="btn btn-primary"><i class="fas fa-paper-plane me-1"></i> Marcar enviada</button>
                            </form>
                        @endif
                    @endcan
                    @can('delete', $invoice)
                        <button class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#cancelInvoiceModal"><i class="fas fa-ban me-1"></i> Anular</button>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))<div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif
    @if(session('error'))<div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>@endif

    <div class="row">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header"><div class="card-icon"><i class="fas fa-file-invoice-dollar fs-14 text-muted"></i></div><h4 class="card-title mb-0">Conceptos facturados</h4></div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead><tr><th>Concepto</th><th class="text-center">Cantidad</th><th class="text-end">Precio unitario</th><th class="text-end">Total</th></tr></thead>
                            <tbody>
                                @forelse($invoice->items as $item)
                                    <tr>
                                        <td><div class="fw-semibold">{{ $item->item_name }}</div><small class="text-muted">{{ $item->cdtCatalog?->cdt_code }}</small></td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end">${{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end fw-semibold">${{ number_format($item->total_price, 2) }}</td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" class="text-center text-muted py-4">Esta factura no contiene conceptos.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h4 class="card-title mb-0">Resumen</h4></div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Estado</span><span class="badge {{ $statusClass }}">{{ ucfirst($invoice->status) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Fecha</span><span>{{ $invoice->invoice_date?->format('d/m/Y') }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Vencimiento</span><span>{{ $invoice->due_date?->format('d/m/Y') ?? 'Sin fecha' }}</span></div>
                    <hr>
                    <div class="d-flex justify-content-between mb-2"><span>Subtotal</span><span>${{ number_format($invoice->subtotal, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Impuestos</span><span>${{ number_format($invoice->tax_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span>Descuento</span><span>-${{ number_format($invoice->discount_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between fs-5 fw-bold"><span>Total</span><span>${{ number_format($invoice->total_amount, 2) }}</span></div>
                    <div class="d-flex justify-content-between mt-2"><span class="text-muted">Saldo pendiente</span><span class="fw-semibold">${{ number_format($invoice->balance_due, 2) }}</span></div>
                </div>
            </div>
            <div class="card"><div class="card-header"><h4 class="card-title mb-0">Paciente</h4></div><div class="card-body"><div class="fw-semibold">{{ $invoice->patient?->first_name }} {{ $invoice->patient?->last_name }}</div><small class="text-muted">{{ $invoice->patient?->patient_code }}</small><hr><div class="small text-muted">Profesional: {{ $invoice->staff?->user?->name ?? 'No asignado' }}</div></div></div>
        </div>
    </div>

    @can('delete', $invoice)
        <div class="modal fade" id="cancelInvoiceModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><form method="POST" action="{{ route('billing.destroy', $invoice) }}" class="modal-content">@csrf @method('DELETE')
            <div class="modal-header"><h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Anular factura</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body"><p class="mb-3">La factura se conservará para auditoría; no se borrarán datos ni pagos.</p><label for="cancel-reason" class="form-label">Motivo de anulación</label><textarea class="form-control @error('reason') is-invalid @enderror" id="cancel-reason" name="reason" rows="3" required maxlength="1000">{{ old('reason') }}</textarea>@error('reason')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Volver</button><button class="btn btn-danger"><i class="fas fa-ban me-1"></i>Anular factura</button></div>
        </form></div></div>
    @endcan
@endsection
