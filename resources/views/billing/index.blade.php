@extends('layouts.master')

@section('title', 'Facturación')

@section('css')
<style>
    .per-page-selector { background-color: #fff; border: 1px solid #e9ecef; border-radius: .375rem; box-shadow: 0 1px 3px rgba(0,0,0,.1); }
    .per-page-label { font-size: .875rem; font-weight: 500; color: #6c757d; }
    .table-controls { background-color: #f8f9fa; border: 1px solid #e9ecef; border-radius: .5rem; padding: .75rem 1rem; margin-bottom: 1rem; }
</style>
@endsection

@section('content')
    @php
        $statusClasses = [
            'draft' => 'bg-secondary',
            'sent' => 'bg-primary',
            'paid' => 'bg-success',
            'overdue' => 'bg-warning text-dark',
            'cancelled' => 'bg-danger',
        ];
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Gestión de Facturación</h4>
                    <p class="text-muted mb-3">Administra facturas, sus estados y el saldo pendiente de cada paciente.</p>
                </div>
                <div class="page-title-right">
                    @can('create', App\Models\Invoice::class)
                        <a href="{{ route('billing.create') }}" class="btn btn-primary"><i class="fas fa-file-invoice-dollar me-1"></i> Nueva factura</a>
                    @endcan
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show"><i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h6 class="card-title mb-0">Filtros de búsqueda</h6>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#billingFilters" aria-expanded="{{ request()->hasAny(['search', 'status']) ? 'true' : 'false' }}">
                            <i class="fas fa-filter me-1"></i> Filtros
                        </button>
                    </div>
                </div>
                <div class="collapse {{ request()->hasAny(['search', 'status']) ? 'show' : '' }}" id="billingFilters">
                    <div class="card-body">
                        <form method="GET" action="{{ route('billing.index') }}" class="row g-3">
                            <div class="col-md-5"><label class="form-label" for="billing-search">Buscar</label><input id="billing-search" type="text" class="form-control" name="search" value="{{ request('search') }}" placeholder="Número o nombre de paciente"></div>
                            <div class="col-md-3"><label class="form-label" for="billing-status">Estado</label><select id="billing-status" class="form-select" name="status"><option value="">Todos</option>@foreach($statuses as $status)<option value="{{ $status }}" {{ request('status') === $status ? 'selected' : '' }}>{{ ucfirst($status) }}</option>@endforeach</select></div>
                            <div class="col-auto"><label class="form-label d-block">&nbsp;</label><button class="btn btn-primary"><i class="fas fa-search"></i></button> <a href="{{ route('billing.index') }}" class="btn btn-outline-secondary"><i class="fas fa-times"></i></a></div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header"><div class="card-icon"><i class="fas fa-file-invoice-dollar fs-14 text-muted"></i></div><h4 class="card-title mb-0">Lista de Facturas</h4></div>
                <div class="table-controls">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center"><span class="per-page-label me-2">Mostrar:</span><span class="form-select form-select-sm per-page-selector" style="width:80px">20</span></div>
                            <span class="text-muted small"><i class="fas fa-info-circle me-1"></i>Mostrando {{ $invoices->count() }} de {{ $invoices->total() }} registros</span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($invoices->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead><tr><th>Factura <i class="fas fa-sort sort-icon text-muted ms-1"></i></th><th>Paciente <i class="fas fa-sort sort-icon text-muted ms-1"></i></th><th>Fecha <i class="fas fa-sort sort-icon text-muted ms-1"></i></th><th class="text-end">Total</th><th class="text-end">Saldo</th><th>Estado</th><th>Acciones</th></tr></thead>
                                <tbody>
                                    @foreach($invoices as $invoice)
                                        <tr>
                                            <td><span class="fw-semibold">{{ $invoice->invoice_number }}</span><small class="d-block text-muted">#{{ $invoice->id }}</small></td>
                                            <td><div class="d-flex align-items-center"><div class="avatar avatar-sm avatar-label-primary me-2"><i class="fas fa-user"></i></div><div><h6 class="mb-0">{{ $invoice->patient?->first_name }} {{ $invoice->patient?->last_name }}</h6><small class="text-muted">{{ $invoice->patient?->patient_code }}</small></div></div></td>
                                            <td>{{ $invoice->invoice_date?->format('d/m/Y') }}</td>
                                            <td class="text-end fw-semibold">${{ number_format($invoice->total_amount, 2) }}</td>
                                            <td class="text-end">${{ number_format($invoice->balance_due, 2) }}</td>
                                            <td><span class="badge {{ $statusClasses[$invoice->status] ?? 'bg-secondary' }}">{{ ucfirst($invoice->status) }}</span></td>
                                            <td><div class="d-flex gap-1"><a href="{{ route('billing.show', $invoice) }}" class="btn btn-sm btn-outline-success" title="Ver detalle"><i class="fas fa-eye"></i></a><a href="{{ route('billing.pdf', $invoice) }}" class="btn btn-sm btn-outline-danger" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>@can('update', $invoice)<a href="{{ route('billing.edit', $invoice) }}" class="btn btn-sm btn-outline-primary" title="Editar"><i class="fas fa-pen"></i></a>@endcan</div></td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-end">{{ $invoices->links() }}</div>
                    @else
                        <div class="text-center py-5"><div class="avatar avatar-lg avatar-label-light mx-auto mb-3"><i class="fas fa-file-invoice-dollar fs-24 text-muted"></i></div><h5 class="mb-1">No hay facturas registradas</h5><p class="text-muted mb-3">Crea la primera factura para comenzar a controlar la facturación.</p>@can('create', App\Models\Invoice::class)<a href="{{ route('billing.create') }}" class="btn btn-primary"><i class="fas fa-plus me-1"></i> Nueva factura</a>@endcan</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
