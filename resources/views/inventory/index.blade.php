@extends('layouts.master')

@section('title', 'Gestión de Inventario')

@section('css')
<style>
    .inventory-table-controls {
        background-color: #f8f9fa;
        border: 1px solid #e9ecef;
        border-radius: .5rem;
        padding: .75rem 1rem;
        margin-bottom: 1rem;
    }

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

    .inventory-table tbody tr {
        transition: background-color .15s ease-in-out;
    }

    .inventory-table tbody tr:hover {
        background-color: rgba(13, 110, 253, .035);
    }
</style>
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Gestión de Inventario</h4>
                    <p class="text-muted mb-3">Controla existencias, disponibilidad y movimientos de los insumos clínicos.</p>
                </div>
                <div class="page-title-right">
                    <div class="d-flex gap-2">
                        <a href="{{ route('inventory.report') }}" class="btn btn-outline-primary">
                            <i class="fas fa-chart-bar me-1"></i>Reporte
                        </a>
                        <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning">
                            <i class="fas fa-exclamation-triangle me-1"></i>Stock Bajo
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
                <div class="toast-header bg-success text-white border-0">
                    <i class="fas fa-check-circle me-2"></i>
                    <strong class="me-auto">Operación completada</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Cerrar"></button>
                </div>
                <div class="toast-body bg-light text-muted">{{ session('success') }}</div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>
            <strong>No se pudo registrar el ajuste.</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h6 class="card-title mb-0">Filtros de búsqueda</h6>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#inventoryFiltersCollapse" aria-expanded="{{ request()->hasAny(['search', 'category', 'stock_level']) ? 'true' : 'false' }}" aria-controls="inventoryFiltersCollapse">
                            <i class="fas fa-filter me-1"></i>Mostrar filtros
                        </button>
                    </div>
                </div>
                <div class="collapse {{ request()->hasAny(['search', 'category', 'stock_level']) ? 'show' : '' }}" id="inventoryFiltersCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('inventory.index') }}" class="row g-3">
                            <div class="col-md-5">
                                <label for="inventory-search" class="form-label">Buscar</label>
                                <input type="text" class="form-control" id="inventory-search" name="search" value="{{ request('search') }}" placeholder="Producto o código">
                            </div>
                            <div class="col-md-3">
                                <label for="inventory-category" class="form-label">Categoría</label>
                                <select class="form-select" id="inventory-category" name="category">
                                    <option value="">Todas</option>
                                    @foreach($categories as $category)
                                        <option value="{{ $category }}" @selected(request('category') === $category)>{{ $category }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="inventory-stock-level" class="form-label">Disponibilidad</label>
                                <select class="form-select" id="inventory-stock-level" name="stock_level">
                                    <option value="">Todos</option>
                                    <option value="low" @selected(request('stock_level') === 'low')>Stock bajo</option>
                                    <option value="out" @selected(request('stock_level') === 'out')>Agotado</option>
                                    <option value="normal" @selected(request('stock_level') === 'normal')>Disponible</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary" title="Aplicar filtros" aria-label="Aplicar filtros"><i class="fas fa-search"></i></button>
                                    <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary" title="Limpiar filtros" aria-label="Limpiar filtros"><i class="fas fa-times"></i></a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="inventory-summary-icon me-2"><i class="fas fa-boxes"></i></div>
                    <h4 class="card-title mb-0">Lista de productos</h4>
                </div>
                <div class="inventory-table-controls">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="text-muted small">
                            <i class="fas fa-info-circle me-1"></i>
                            Mostrando {{ $inventories->count() }} de {{ $inventories->total() }} productos
                        </div>
                        <span class="badge bg-light text-muted border">Página {{ $inventories->currentPage() }} de {{ $inventories->lastPage() }}</span>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 inventory-table">
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
                                        <span class="badge bg-{{ $inventory->available_stock <= ($inventory->product->minimum_stock ?? 0) ? 'danger' : 'success' }}">
                                            {{ $inventory->available_stock }}
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
                    @if($inventories->hasPages())
                        <div class="d-flex justify-content-end mt-3">
                            {{ $inventories->withQueryString()->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @endif
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





