@extends('layouts.master')

@section('title', 'Movimientos de inventario')

@section('content')
<div class="container-fluid">
    <div class="page-title-box d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-2">Movimientos de inventario</h4>
            <p class="text-muted mb-3">Consulta y revierte movimientos conservando la trazabilidad.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary"><i class="fas fa-boxes me-1"></i>Productos</a>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-3">
                    <label for="movement-type" class="form-label">Tipo</label>
                    <select id="movement-type" name="type" class="form-select">
                        <option value="">Todos</option>
                        <option value="restock" @selected(request('type') === 'restock')>Entrada</option>
                        <option value="consumption" @selected(request('type') === 'consumption')>Salida</option>
                        <option value="adjustment" @selected(request('type') === 'adjustment')>Ajuste</option>
                        <option value="transfer_out" @selected(request('type') === 'transfer_out')>Transferida</option>
                        <option value="transfer_in" @selected(request('type') === 'transfer_in')>Recibida</option>
                    </select>
                </div>
                <div class="col-md-3 d-flex align-items-end gap-2">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-filter me-1"></i>Filtrar</button>
                    <a href="{{ route('inventory.movements') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h4 class="card-title mb-0">Historial de movimientos</h4></div>
        <div class="card-body">
            @if($movements->isEmpty())
                <p class="text-center text-muted mb-0 py-4">No hay movimientos registrados.</p>
            @else
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead><tr><th>Fecha</th><th>Producto</th><th>Tipo</th><th>Cantidad</th><th>Stock</th><th>Trayecto</th><th>Motivo</th><th>Usuario</th><th class="text-end">Acciones</th></tr></thead>
                        <tbody>
                        @foreach($movements as $movement)
                            @php $type = match($movement->type) { 'restock' => ['Entrada', 'success'], 'consumption' => ['Salida', 'danger'], 'transfer_out' => ['Transferida', 'primary'], 'transfer_in' => ['Recibida', 'info'], default => ['Ajuste', 'warning'] }; @endphp
                            <tr>
                                <td class="text-muted">{{ $movement->created_at->format('d/m/Y H:i') }}</td>
                                <td><a href="{{ route('inventory.show', $movement->inventory) }}" class="text-decoration-none">{{ $movement->product->name ?? 'Producto' }}</a></td>
                                <td><span class="badge bg-{{ $type[1] }}">{{ $type[0] }}</span></td>
                                <td class="fw-semibold">{{ $movement->quantity }}</td>
                                <td>{{ $movement->stock_before }} <i class="fas fa-arrow-right text-muted mx-1"></i> {{ $movement->stock_after }}</td>
                                <td class="small text-muted">
                                    @if(in_array($movement->type, ['transfer_out', 'transfer_in'], true))
                                        {{ $movement->source_location }} <i class="fas fa-arrow-right mx-1"></i> {{ $movement->destination_location }}
                                    @else
                                        —
                                    @endif
                                </td>
                                <td>{{ $movement->reason ?: 'Sin motivo indicado' }}</td>
                                <td>{{ $movement->user->name ?? 'Sistema' }}</td>
                                <td class="text-end">
                                    @if($movement->reference_type !== \App\Models\InventoryMovement::class && empty($movement->metadata['transfer_id']) && !in_array($movement->id, $reversedMovementIds, true))
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-danger"
                                            data-bs-toggle="modal"
                                            data-bs-target="#reversalMovementModal"
                                            data-reversal-url="{{ route('inventory.movements.reverse', $movement) }}"
                                            data-reversal-label="{{ $movement->product->name ?? 'Producto' }} · {{ $movement->reason ?: 'Movimiento #'.$movement->id }}"
                                            title="Revertir movimiento"
                                            aria-label="Revertir movimiento"
                                        >
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    @else
                                        <span class="text-muted small">{{ $movement->reference_type === \App\Models\InventoryMovement::class ? 'Reversión' : 'Revertido' }}</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
                <div class="d-flex justify-content-end mt-3">{{ $movements->links('vendor.pagination.bootstrap-5') }}</div>
            @endif
        </div>
    </div>
</div>
@include('inventory.partials.reversal-modal')
@endsection
