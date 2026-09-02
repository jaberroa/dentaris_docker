@extends('layouts.master')

@section('title', 'Detalle de inventario')

@section('css')
<style>
    .inventory-summary-icon {
        width: 2rem;
        height: 2rem;
        border-radius: .5rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: rgba(13, 110, 253, .1);
        color: #0d6efd;
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="page-title-box d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-2">Detalle de inventario</h4>
            <p class="text-muted mb-0">Consulta el estado y los movimientos del producto.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Volver a inventario
        </a>
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">{{ $inventory->product->name ?? 'Producto' }}</h5>
                    <span class="badge bg-{{ $inventory->current_stock > 0 ? 'success' : 'danger' }}">
                        {{ $inventory->current_stock > 0 ? 'Disponible' : 'Agotado' }}
                    </span>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4"><span class="text-muted d-block">Código</span><strong>{{ $inventory->product->product_code ?? 'N/A' }}</strong></div>
                        <div class="col-md-4"><span class="text-muted d-block">Categoría</span><strong>{{ $inventory->product->category ?? 'N/A' }}</strong></div>
                        <div class="col-md-4"><span class="text-muted d-block">Ubicación</span><strong>{{ $inventory->location ?? 'No definida' }}</strong></div>
                        <div class="col-md-4"><span class="text-muted d-block">Stock actual</span><strong>{{ $inventory->current_stock }}</strong></div>
                        <div class="col-md-4"><span class="text-muted d-block">Stock reservado</span><strong>{{ $inventory->reserved_stock }}</strong></div>
                        <div class="col-md-4"><span class="text-muted d-block">Stock disponible</span><strong>{{ $inventory->available_stock }}</strong></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="card">
                <div class="card-header"><h5 class="card-title mb-0">Resumen</h5></div>
                <div class="card-body">
                    <p class="mb-2">Stock mínimo: <strong>{{ $inventory->product->minimum_stock ?? 0 }}</strong></p>
                    <p class="mb-2">Costo promedio: <strong>${{ number_format($inventory->average_cost ?? 0, 2) }}</strong></p>
                    <p class="mb-0">Última reposición: <strong>{{ optional($inventory->last_restocked)->format('d/m/Y') ?? 'Nunca' }}</strong></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex align-items-center">
                    <div class="inventory-summary-icon me-2"><i class="fas fa-history"></i></div>
                    <h4 class="card-title mb-0">Historial de movimientos</h4>
                </div>
                <div class="card-body">
                    @if($movements->isEmpty())
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                            <p class="mb-0">Todavía no hay movimientos registrados para este producto.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Tipo</th>
                                        <th>Cantidad</th>
                                        <th>Stock</th>
                                        <th>Motivo</th>
                                        <th>Registrado por</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($movements as $movement)
                                        @php
                                            $movementType = match($movement->type) {
                                                'restock' => ['label' => 'Entrada', 'color' => 'success', 'icon' => 'fa-arrow-down'],
                                                'consumption' => ['label' => 'Salida', 'color' => 'danger', 'icon' => 'fa-arrow-up'],
                                                default => ['label' => 'Ajuste', 'color' => 'warning', 'icon' => 'fa-sliders-h'],
                                            };
                                        @endphp
                                        <tr>
                                            <td class="text-muted">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                            <td><span class="badge bg-{{ $movementType['color'] }}"><i class="fas {{ $movementType['icon'] }} me-1"></i>{{ $movementType['label'] }}</span></td>
                                            <td class="fw-semibold">{{ $movement->quantity }}</td>
                                            <td>{{ $movement->stock_before }} <i class="fas fa-arrow-right text-muted mx-1"></i> {{ $movement->stock_after }}</td>
                                            <td>{{ $movement->reason ?: 'Sin motivo indicado' }}</td>
                                            <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if($movements->hasPages())
                            <div class="d-flex justify-content-end mt-3">
                                {{ $movements->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
