@if (isset($clinicSelectionOptions))
    <div class="dropdown d-inline-block">
        <button type="button"
                class="btn btn-sm {{ $activeClinicSelection ? 'btn-primary' : 'btn-outline-warning' }} fs-14"
                id="clinic-context-dropdown"
                data-bs-toggle="dropdown"
                aria-haspopup="true"
                aria-expanded="false">
            <i class="fas fa-clinic-medical me-1"></i>
            <span class="d-none d-md-inline">
                {{ $activeClinicSelection?->name ?? 'Seleccionar clínica' }}
            </span>
            <i class="mdi mdi-chevron-down align-middle ms-1"></i>
        </button>

        <div class="dropdown-menu dropdown-menu-start dropdown-menu-animated" aria-labelledby="clinic-context-dropdown">
            <h6 class="dropdown-header">Contexto clínico</h6>

            @forelse ($clinicSelectionOptions as $clinic)
                @if ($activeClinicSelection && (int) $activeClinicSelection->id === (int) $clinic->id)
                    <div class="dropdown-item d-flex align-items-center justify-content-between active">
                        <span><i class="fas fa-tooth me-2"></i>{{ $clinic->name }}</span>
                        <span class="badge bg-light text-primary ms-3">Activa</span>
                    </div>
                @else
                    <form method="POST" action="{{ route('clinics.context.store') }}">
                        @csrf
                        <input type="hidden" name="clinic_id" value="{{ $clinic->id }}">
                        <button type="submit" class="dropdown-item">
                            <i class="fas fa-tooth me-2"></i>{{ $clinic->name }}
                        </button>
                    </form>
                @endif
            @empty
                <span class="dropdown-item-text text-muted">Sin clínicas autorizadas</span>
            @endforelse

            <div class="dropdown-divider"></div>
            <a href="{{ route('clinics.select') }}" class="dropdown-item">
                <i class="mdi mdi-swap-horizontal me-2"></i>Ver selector
            </a>

            @if ($activeClinicSelection)
                <form method="POST" action="{{ route('clinics.context.destroy') }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="dropdown-item text-danger">
                        <i class="mdi mdi-close-circle-outline me-2"></i>Cerrar contexto
                    </button>
                </form>
            @endif
        </div>
    </div>
@endif
