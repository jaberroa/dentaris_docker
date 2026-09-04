@extends('layouts.master')

@section('title', 'Gestión de Inventario')

@section('css')
<style>
    .table-controls {
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

    .table th a { display: flex; align-items: center; justify-content: space-between; color: inherit !important; text-decoration: none !important; }
    .table th a:hover { color: #0d6efd !important; }
    .table th a .sort-icon { font-size: 12px; opacity: .5; margin-left: .5rem; }
    .table th a .sort-icon.active { opacity: 1; color: #0d6efd; }
    .per-page-selector { background-color: #fff; border: 1px solid #e9ecef; border-radius: .375rem; box-shadow: 0 1px 3px rgba(0, 0, 0, .1); }
    .per-page-label { font-size: .875rem; font-weight: 500; color: #6c757d; }
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
                        @can('viewAny', App\Models\Inventory::class)
                            <a href="{{ route('inventory.locations.index') }}" class="btn btn-outline-info">
                                <i class="fas fa-map-marker-alt me-1"></i>Ubicaciones
                            </a>
                        @endcan
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
            <strong>No se pudo completar la operación de inventario.</strong>
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
                    <div class="card-icon"><i class="fas fa-boxes fs-14 text-muted"></i></div>
                    <h4 class="card-title mb-0">Lista de productos</h4>
                </div>
                @php
                    $inventorySortUrl = fn ($field) => request()->fullUrlWithQuery(['sort_by' => $field, 'sort_order' => $sortBy === $field && $sortOrder === 'asc' ? 'desc' : 'asc']);
                    $inventorySortIcon = fn ($field) => 'fas '.($sortBy === $field ? ($sortOrder === 'asc' ? 'fa-sort-up active' : 'fa-sort-down active') : 'fa-sort').' sort-icon';
                @endphp
                <div class="table-controls">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center"><label class="per-page-label me-2 mb-0">Mostrar:</label><select class="form-select form-select-sm per-page-selector" style="width: 80px;" onchange="changeInventoryPerPage(this.value)">@foreach([10,25,50,100] as $size)<option value="{{ $size }}" @selected($perPage === $size)>{{ $size }}</option>@endforeach</select></div>
                            <div class="text-muted small"><i class="fas fa-info-circle me-1"></i>Mostrando {{ $inventories->count() }} de {{ $inventories->total() }} productos</div>
                        </div>
                        @can('export', App\Models\Inventory::class)
                            <div class="dropdown"><button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown"><i class="fas fa-download me-1"></i>Exportar</button><div class="dropdown-menu dropdown-menu-end">@foreach(['pdf' => ['fa-file-pdf', 'text-danger', 'PDF'], 'xlsx' => ['fa-file-excel', 'text-success', 'Excel'], 'csv' => ['fa-file-csv', 'text-primary', 'CSV']] as $format => [$icon, $color, $label])<form method="POST" action="{{ route('inventory.export') }}">@csrf<input type="hidden" name="format" value="{{ $format }}"><input type="hidden" name="category" value="{{ request('category') }}"><input type="hidden" name="stock_level" value="{{ request('stock_level') }}"><button class="dropdown-item" type="submit"><i class="fas {{ $icon }} me-2 {{ $color }}"></i>{{ $label }} - filtros actuales</button></form>@endforeach<div class="dropdown-divider"></div>@foreach(['pdf' => ['fa-file-pdf', 'text-danger', 'PDF'], 'xlsx' => ['fa-file-excel', 'text-success', 'Excel'], 'csv' => ['fa-file-csv', 'text-primary', 'CSV']] as $format => [$icon, $color, $label])<form method="POST" action="{{ route('inventory.export') }}">@csrf<input type="hidden" name="format" value="{{ $format }}"><button class="dropdown-item" type="submit"><i class="fas {{ $icon }} me-2 {{ $color }}"></i>{{ $label }} - todos</button></form>@endforeach</div></div>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th><a href="{{ $inventorySortUrl('product') }}">ID <i class="{{ $inventorySortIcon('product') }}"></i></a></th>
                                    <th><a href="{{ $inventorySortUrl('product') }}">Producto <i class="{{ $inventorySortIcon('product') }}"></i></a></th>
                                    <th><a href="{{ $inventorySortUrl('category') }}">Categoría <i class="{{ $inventorySortIcon('category') }}"></i></a></th>
                                    <th><a href="{{ $inventorySortUrl('location') }}">Ubicación <i class="{{ $inventorySortIcon('location') }}"></i></a></th>
                                    <th><a href="{{ $inventorySortUrl('stock') }}">Stock Actual <i class="{{ $inventorySortIcon('stock') }}"></i></a></th>
                                    <th>Stock Mínimo</th>
                                    <th><a href="{{ $inventorySortUrl('value') }}">Precio <i class="{{ $inventorySortIcon('value') }}"></i></a></th>
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
                                    <td>{{ $inventory->inventoryLocation->name ?? $inventory->location ?? 'Sin asignar' }}</td>
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
                                            @can('adjust', $inventory)
                                                <button type="button" class="btn btn-sm btn-outline-warning" title="Ajustar stock" aria-label="Ajustar stock" data-bs-toggle="modal" data-bs-target="#adjustStockModal" data-inventory-id="{{ $inventory->id }}" data-product-id="{{ $inventory->product_id }}" data-product-name="{{ $inventory->product->name ?? 'Producto' }}">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                            @endcan
                                            @can('update', $inventory)
                                                <button type="button" class="btn btn-sm btn-outline-secondary" title="Agregar ubicación de stock" aria-label="Agregar ubicación de stock" data-bs-toggle="modal" data-bs-target="#addStockLocationModal" data-inventory-id="{{ $inventory->id }}" data-product-name="{{ $inventory->product->name ?? 'Producto' }}" data-source-location-id="{{ $inventory->inventory_location_id }}">
                                                    <i class="fas fa-map-marker-alt"></i>
                                                </button>
                                                @php $hasTransferDestination = $transferDestinationsByProduct->get($inventory->product_id, collect())->count() > 1; @endphp
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-info"
                                                    title="{{ $hasTransferDestination ? 'Transferir stock' : 'No hay otra ubicación disponible para este producto' }}"
                                                    aria-label="Transferir stock"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#transferStockModal"
                                                    data-source-inventory-id="{{ $inventory->id }}"
                                                    data-product-id="{{ $inventory->product_id }}"
                                                    data-product-name="{{ $inventory->product->name ?? 'Producto' }}"
                                                    data-source-location="{{ $inventory->inventoryLocation->name ?? $inventory->location ?? 'Ubicación no definida' }}"
                                                    data-available-stock="{{ $inventory->available_stock }}"
                                                    @disabled(! $hasTransferDestination)
                                                >
                                                    <i class="fas fa-exchange-alt"></i>
                                                </button>
                                            @endcan
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

@foreach($transferDestinationsByProduct as $productId => $destinations)
    <template id="transfer-destinations-product-{{ $productId }}">
        @foreach($destinations as $destination)
            <option value="{{ $destination->id }}" data-inventory-id="{{ $destination->id }}">
                {{ $destination->inventoryLocation->name ?? $destination->location ?? 'Ubicación no definida' }} · Disponible: {{ $destination->available_stock }}
            </option>
        @endforeach
    </template>
@endforeach

<div class="modal fade" id="adjustStockModal" tabindex="-1" aria-labelledby="adjustStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustStockModalLabel"><i class="fas fa-boxes me-2 text-warning"></i>Ajustar stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('inventory.adjust', ['inventory' => '__inventory__']) }}" data-action-template="{{ route('inventory.adjust', ['inventory' => '__inventory__']) }}" id="adjustStockForm">
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

<div class="modal fade" id="transferStockModal" tabindex="-1" aria-labelledby="transferStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title text-info" id="transferStockModalLabel"><i class="fas fa-exchange-alt me-2"></i>Transferir stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('inventory.transfer') }}" id="transferStockForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-info d-flex gap-2" role="alert">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div>Solo puedes transferir stock disponible entre ubicaciones del mismo producto. El stock reservado se conserva en el origen.</div>
                    </div>
                    <input type="hidden" name="inventory_id" id="transferSourceInventoryId">
                    <div class="mb-3">
                        <span class="text-muted d-block small">Producto</span>
                        <strong id="transferProductName">—</strong>
                    </div>
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <span class="text-muted d-block small">Origen</span>
                            <strong id="transferSourceLocation">—</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="text-muted d-block small">Disponible</span>
                            <strong id="transferAvailableStock">0</strong>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="transferDestinationInventoryId" class="form-label">Destino</label>
                        <select name="destination_inventory_id" id="transferDestinationInventoryId" class="form-select" required></select>
                        <div id="transferDestinationHelp" class="form-text"></div>
                    </div>
                    <div class="mb-3">
                        <label for="transferQuantity" class="form-label">Cantidad a transferir</label>
                        <input type="number" name="quantity" id="transferQuantity" class="form-control" min="1" required>
                    </div>
                    <div>
                        <label for="transferReason" class="form-label">Motivo</label>
                        <textarea name="reason" id="transferReason" class="form-control" rows="3" maxlength="255" required placeholder="Ej.: Reposición del consultorio 1"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-info text-white" id="transferSubmitButton"><i class="fas fa-exchange-alt me-1"></i>Confirmar transferencia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="addStockLocationModal" tabindex="-1" aria-labelledby="addStockLocationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title" id="addStockLocationModalLabel"><i class="fas fa-map-marker-alt text-secondary me-2"></i>Agregar ubicación de stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <form method="POST" action="{{ route('inventory.locations.stock.store', ['inventory' => '__inventory__']) }}" data-action-template="{{ route('inventory.locations.stock.store', ['inventory' => '__inventory__']) }}" id="addStockLocationForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-secondary d-flex gap-2" role="alert">
                        <i class="fas fa-info-circle mt-1"></i>
                        <div id="addStockLocationDescription">Se creará una existencia inicial de <strong>0</strong> unidades para poder recibir transferencias. No se mueve stock con esta acción.</div>
                    </div>
                    <p class="mb-3">Producto: <strong id="addStockLocationProductName">—</strong></p>
                    <div class="mb-0">
                        <label for="addStockLocationId" class="form-label">Nueva ubicación</label>
                        <select name="inventory_location_id" id="addStockLocationId" class="form-select" required></select>
                        <div id="addStockLocationHelp" class="form-text"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-secondary" id="addStockLocationSubmit"><i class="fas fa-plus me-1"></i>Crear existencia</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="exportInventoryModal" tabindex="-1" aria-labelledby="exportInventoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
        <div class="modal-header bg-light"><h5 class="modal-title text-success" id="exportInventoryModalLabel"><i class="fas fa-file-csv me-2"></i>Exportar inventario</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <form method="POST" action="{{ route('inventory.export') }}">@csrf<input type="hidden" name="format" value="csv">
            <div class="modal-body p-4"><p class="text-muted">Descarga un CSV con existencias, stock reservado, disponibilidad y ubicación.</p><div class="row g-3"><div class="col-md-6"><label class="form-label" for="exportLocation">Ubicación</label><select class="form-select" id="exportLocation" name="inventory_location_id"><option value="">Todas</option>@foreach($exportLocations as $location)<option value="{{ $location->id }}">{{ $location->name }} ({{ $location->code }})</option>@endforeach</select></div><div class="col-md-6"><label class="form-label" for="exportCategory">Categoría</label><select class="form-select" id="exportCategory" name="category"><option value="">Todas</option>@foreach($categories as $category)<option value="{{ $category }}">{{ $category }}</option>@endforeach</select></div><div class="col-md-6"><label class="form-label" for="exportStockLevel">Estado</label><select class="form-select" id="exportStockLevel" name="stock_level"><option value="">Todos</option><option value="normal">Disponible</option><option value="low">Stock bajo</option><option value="out">Agotado</option></select></div></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-success"><i class="fas fa-download me-1"></i>Descargar CSV</button></div>
        </form>
    </div></div>
</div>

<script>
document.getElementById('adjustStockModal')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const form = document.getElementById('adjustStockForm');
    const inventoryId = button.dataset.inventoryId;
    document.getElementById('adjustInventoryId').value = inventoryId;
    document.getElementById('adjustProductId').value = button.dataset.productId;
    document.getElementById('adjustProductName').textContent = button.dataset.productName;
    form.action = form.dataset.actionTemplate.replace('__inventory__', inventoryId);
});

document.getElementById('transferStockModal')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const sourceInventoryId = button.dataset.sourceInventoryId;
    const productId = button.dataset.productId;
    const destinationSelect = document.getElementById('transferDestinationInventoryId');
    const destinationHelp = document.getElementById('transferDestinationHelp');
    const submitButton = document.getElementById('transferSubmitButton');
    const destinationTemplate = document.getElementById(`transfer-destinations-product-${productId}`);

    document.getElementById('transferStockForm').reset();
    document.getElementById('transferSourceInventoryId').value = sourceInventoryId;
    document.getElementById('transferProductName').textContent = button.dataset.productName;
    document.getElementById('transferSourceLocation').textContent = button.dataset.sourceLocation;
    document.getElementById('transferAvailableStock').textContent = button.dataset.availableStock;
    document.getElementById('transferQuantity').max = button.dataset.availableStock;
    destinationSelect.innerHTML = '<option value="">Selecciona una ubicación</option>';

    if (destinationTemplate) {
        const destinations = [...destinationTemplate.content.children]
            .filter((option) => option.dataset.inventoryId !== sourceInventoryId);

        destinations.forEach((option) => destinationSelect.append(option.cloneNode(true)));
    }

    const hasDestinations = destinationSelect.options.length > 1;
    destinationSelect.disabled = !hasDestinations;
    submitButton.disabled = !hasDestinations;
    destinationHelp.textContent = hasDestinations
        ? 'Solo se muestran ubicaciones que contienen este mismo producto.'
        : 'Este producto aún no tiene otra ubicación de inventario disponible para transferir.';
});

document.getElementById('addStockLocationModal')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const form = document.getElementById('addStockLocationForm');
    const select = document.getElementById('addStockLocationId');
    const sourceLocationId = button.dataset.sourceLocationId;
    const submitButton = document.getElementById('addStockLocationSubmit');

    form.reset();
    form.action = form.dataset.actionTemplate.replace('__inventory__', button.dataset.inventoryId);
    document.getElementById('addStockLocationProductName').textContent = button.dataset.productName;
    select.innerHTML = '<option value="">Selecciona una ubicación</option>';

    @foreach($activeInventoryLocations as $location)
        if ('{{ $location->id }}' !== sourceLocationId) {
            select.append(new Option(@js($location->name.' ('.$location->code.')'), '{{ $location->id }}'));
        }
    @endforeach

    const hasLocations = select.options.length > 1;
    select.disabled = !hasLocations;
    submitButton.disabled = !hasLocations;
    document.getElementById('addStockLocationHelp').textContent = hasLocations
        ? 'Solo se muestran ubicaciones activas distintas al origen.'
        : 'Crea primero una ubicación activa en el catálogo de ubicaciones.';
    const isUnassigned = !sourceLocationId;
    document.getElementById('addStockLocationDescription').innerHTML = isUnassigned
        ? 'El inventario existente quedará asignado a la ubicación seleccionada. No se moverá stock con esta acción.'
        : 'Se creará una existencia inicial de <strong>0</strong> unidades para poder recibir transferencias. No se mueve stock con esta acción.';
    submitButton.innerHTML = isUnassigned
        ? '<i class="fas fa-check me-1"></i>Asignar ubicación'
        : '<i class="fas fa-plus me-1"></i>Crear existencia';
});

function changeInventoryPerPage(value) {
    const url = new URL(window.location.href);
    url.searchParams.set('per_page', value);
    url.searchParams.delete('page');
    window.location.href = url.toString();
}
</script>
@endsection





