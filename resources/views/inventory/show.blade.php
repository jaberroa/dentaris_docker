@extends('layouts.master')

@section('title', 'Detalle de inventario')

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
</div>
@endsection
