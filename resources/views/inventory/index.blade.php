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
                                <i class="fas fa-exclamation-triangle align-middle me-1"></i> Stock Bajo
                            </a>
                            <a href="{{ route('inventory.report') }}" class="btn btn-info">
                                <i class="fas fa-chart-bar align-middle me-1"></i> Reporte
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
                                    <th>Producto</th>
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
                                    <td>{{ $inventory->product->minimum_stock ?? 0 }}</td>
                                    <td>${{ number_format($inventory->product->cost_price ?? 0, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $inventory->current_stock > 0 ? 'success' : 'danger' }}">
                                            {{ $inventory->current_stock > 0 ? 'Disponible' : 'Agotado' }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="{{ route('inventory.show', $inventory) }}" class="btn btn-sm btn-outline-primary" title="Ver inventario" aria-label="Ver inventario">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-warning" title="Ajustar stock" aria-label="Ajustar stock" data-bs-toggle="modal" data-bs-target="#adjustStockModal" data-inventory-id="{{ $inventory->id }}" data-product-id="{{ $inventory->product_id }}" data-product-name="{{ $inventory->product->name ?? 'Producto' }}">
                                                <i class="fas fa-edit"></i>
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

<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustStockModalLabel"><i class="fas fa-boxes me-2 text-warning"></i>Ajustar stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('inventory.adjust', ['inventory' => '__inventory__']) }}" id="adjustStockForm">
                @csrf
                <div class="modal-body">
                    <p class="text-muted mb-3">Producto: <strong id="adjustProductName">—</strong></p>
                    <input type="hidden" name="inventory_id" id="adjustInventoryId">
                    <input type="hidden" name="product_id" id="adjustProductId">
                    <div class="mb-3">
                        <label for="adjustType" class="form-label">Tipo de movimiento</label>
                        <select name="type" id="adjustType" class="form-select" required>
                            <option value="restock">Entrada / reposición</option>
                            <option value="consumption">Salida / consumo</option>
                            <option value="adjustment">Ajuste a cantidad exacta</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="adjustQuantity" class="form-label">Cantidad</label>
                        <input type="number" name="quantity" id="adjustQuantity" class="form-control" min="1" required>
                    </div>
                    <div>
                        <label for="adjustReason" class="form-label">Motivo</label>
                        <textarea name="reason" id="adjustReason" class="form-control" rows="3" maxlength="255" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning"><i class="fas fa-check me-1"></i>Guardar ajuste</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('adjustStockModal')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const form = document.getElementById('adjustStockForm');
    const inventoryId = button.dataset.inventoryId;
    document.getElementById('adjustInventoryId').value = inventoryId;
    document.getElementById('adjustProductId').value = button.dataset.productId;
    document.getElementById('adjustProductName').textContent = button.dataset.productName;
    form.action = form.action.replace('__inventory__', inventoryId);
});
</script>
@endsection





