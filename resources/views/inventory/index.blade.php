@extends('layouts.master')

@section('title', 'Gestión de Inventario')

@section('content')
<div class="container-fluid">
    <!-- start page title -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Inventario</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Panel de Control</a></li>
                        <li class="breadcrumb-item active">Inventario</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!-- end page title -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Lista de Productos en Inventario</h5>
                        <div class="d-flex gap-2">
                            <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning">
                                <i class="ri-alert-line align-middle me-1"></i> Stock Bajo
                            </a>
                            <a href="{{ route('inventory.report') }}" class="btn btn-info">
                                <i class="ri-file-chart-line align-middle me-1"></i> Reporte
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Productoo</th>
                                    <th>Categoría</th>
                                    <th>Stock Actual</th>
                                    <th>Stock Mínimo</th>
                                    <th>Precio</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($inventories as $inventory)
                                <tr>
                                    <td>{{ $inventory->id }}</td>
                                    <td>{{ $inventory->product->name ?? 'N/A' }}</td>
                                    <td>{{ $inventory->product->category ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $inventory->current_stock <= $inventory->min_stock ? 'danger' : 'success' }}">
                                            {{ $inventory->current_stock }}
                                        </span>
                                    </td>
                                    <td>{{ $inventory->min_stock }}</td>
                                    <td>${{ number_format($inventory->unit_price, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $inventory->current_stock > 0 ? 'success' : 'danger' }}">
                                            {{ $inventory->current_stock > 0 ? 'Disponible' : 'Agotado' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('inventory.show', $inventory) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <button class="btn btn-sm btn-outline-warning" onclick="adjustStock({{ $inventory->id }})">
                                                <i class="ri-edit-line"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="8" class="text-center">No hay productos en inventario</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function adjustStock(id) {
    // Función para ajustar stock
    alert('Función de ajuste de stock para el producto ID: ' + id);
}
</script>
@endsection





