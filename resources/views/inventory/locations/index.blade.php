@extends('layouts.master')

@section('title', 'Ubicaciones de inventario')

@section('content')
<div class="container-fluid">
    <div class="page-title-box d-flex align-items-center justify-content-between">
        <div>
            <h4 class="mb-2">Ubicaciones de inventario</h4>
            <p class="text-muted mb-0">Administra los depósitos y consultorios donde se distribuye el stock.</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary"><i class="fas fa-arrow-left me-1"></i>Inventario</a>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#locationModal"><i class="fas fa-plus me-1"></i>Nueva ubicación</button>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert"><i class="fas fa-check-circle me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Cerrar"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger"><strong>No se pudo guardar la ubicación.</strong><ul class="mb-0 mt-2">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
    @endif

    <div class="card">
        <div class="card-header"><h4 class="card-title mb-0">Catálogo de ubicaciones</h4></div>
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead><tr><th>Código</th><th>Ubicación</th><th>Tipo</th><th>Productos</th><th>Stock actual</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                    <tbody>
                    @forelse($locations as $location)
                        <tr>
                            <td><span class="badge bg-light text-dark border">{{ $location->code }}</span></td>
                            <td><strong>{{ $location->name }}</strong>@if($location->notes)<small class="d-block text-muted">{{ $location->notes }}</small>@endif</td>
                            <td>{{ match($location->type) { 'clinic' => 'Consultorio', 'warehouse' => 'Almacén', default => 'Depósito' } }}</td>
                            <td>{{ $location->inventories_count }}</td>
                            <td>{{ $location->inventories_sum_current_stock ?? 0 }}</td>
                            <td><span class="badge bg-{{ $location->is_active ? 'success' : 'secondary' }}">{{ $location->is_active ? 'Activa' : 'Inactiva' }}</span></td>
                            <td class="text-end"><button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#locationModal" data-location-id="{{ $location->id }}" data-location-code="{{ $location->code }}" data-location-name="{{ $location->name }}" data-location-type="{{ $location->type }}" data-location-notes="{{ $location->notes }}" data-location-active="{{ $location->is_active ? '1' : '0' }}" title="Editar ubicación"><i class="fas fa-edit"></i></button></td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">Todavía no hay ubicaciones. Crea la primera para distribuir el inventario.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
            @if($locations->hasPages())<div class="d-flex justify-content-end mt-3">{{ $locations->links('vendor.pagination.bootstrap-5') }}</div>@endif
        </div>
    </div>
</div>

<div class="modal fade" id="locationModal" tabindex="-1" aria-labelledby="locationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow">
        <div class="modal-header bg-light"><h5 class="modal-title" id="locationModalLabel"><i class="fas fa-map-marker-alt me-2 text-primary"></i>Nueva ubicación</h5><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button></div>
        <form method="POST" action="{{ route('inventory.locations.store') }}" data-store-action="{{ route('inventory.locations.store') }}" data-update-template="{{ route('inventory.locations.update', ['inventoryLocation' => '__location__']) }}" id="locationForm">@csrf <input type="hidden" name="_method" id="locationMethod" value="POST">
            <div class="modal-body"><div class="row g-3"><div class="col-md-4"><label class="form-label" for="locationCode">Código</label><input class="form-control" id="locationCode" name="code" maxlength="50" required></div><div class="col-md-8"><label class="form-label" for="locationName">Nombre</label><input class="form-control" id="locationName" name="name" maxlength="255" required></div><div class="col-md-6"><label class="form-label" for="locationType">Tipo</label><select class="form-select" id="locationType" name="type" required><option value="storage">Depósito</option><option value="clinic">Consultorio</option><option value="warehouse">Almacén</option></select></div><div class="col-md-6"><label class="form-label" for="locationActive">Estado</label><select class="form-select" id="locationActive" name="is_active"><option value="1">Activa</option><option value="0">Inactiva</option></select></div><div class="col-12"><label class="form-label" for="locationNotes">Notas</label><textarea class="form-control" id="locationNotes" name="notes" rows="3" maxlength="1000"></textarea></div></div></div>
            <div class="modal-footer"><button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button><button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i>Guardar ubicación</button></div>
        </form>
    </div></div>
</div>

<script>
document.getElementById('locationModal')?.addEventListener('show.bs.modal', function (event) {
    const button = event.relatedTarget;
    const form = document.getElementById('locationForm');
    form.reset();
    if (!button.dataset.locationId) { form.action = form.dataset.storeAction; document.getElementById('locationMethod').value = 'POST'; document.getElementById('locationModalLabel').innerHTML = '<i class="fas fa-map-marker-alt me-2 text-primary"></i>Nueva ubicación'; return; }
    form.action = form.dataset.updateTemplate.replace('__location__', button.dataset.locationId);
    document.getElementById('locationMethod').value = 'PUT';
    document.getElementById('locationModalLabel').innerHTML = '<i class="fas fa-edit me-2 text-primary"></i>Editar ubicación';
    document.getElementById('locationCode').value = button.dataset.locationCode;
    document.getElementById('locationName').value = button.dataset.locationName;
    document.getElementById('locationType').value = button.dataset.locationType;
    document.getElementById('locationActive').value = button.dataset.locationActive;
    document.getElementById('locationNotes').value = button.dataset.locationNotes;
});
</script>
@endsection
