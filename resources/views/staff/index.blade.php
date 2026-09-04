@extends('layouts.master')

@section('title')
    Gestión de Personal
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('css')
<style>
    /* Estilos de paginación siguiendo exactamente Clivax */
    .pagination-rounded .page-link {
        border-radius: 50% !important;
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 2px;
        border: 1px solid #dee2e6;
        color: #6c757d;
        transition: all 0.15s ease-in-out;
    }
    
    .pagination-rounded .page-link:hover {
        background-color: #e9ecef;
        border-color: #adb5bd;
        color: #495057;
    }
    
    .pagination-rounded .page-item.active .page-link {
        background-color: #0d6efd;
        border-color: #0d6efd;
        color: #fff;
    }
    
    .pagination-rounded .page-item.disabled .page-link {
        color: #6c757d;
        background-color: #fff;
        border-color: #dee2e6;
        opacity: 0.5;
    }
    
    /* Iconos Material Design como en Clivax */
    .pagination-rounded .page-link i {
        font-size: 18px;
        font-weight: 400;
    }
    
    /* Estilos limpios para sorting de tabla */
    .table th a {
        display: flex;
        align-items: center;
        justify-content: space-between;
        color: inherit !important;
        text-decoration: none !important;
        transition: color 0.15s ease-in-out;
    }
    
    .table th a:hover {
        color: #0d6efd !important;
    }
    
    .table th a .sort-icon {
        font-size: 12px;
        opacity: 0.5;
        transition: opacity 0.15s ease-in-out;
        margin-left: 0.5rem;
    }
    
    .table th a:hover .sort-icon {
        opacity: 0.8;
    }
    
    .table th a .sort-icon.active {
        opacity: 1;
        color: #0d6efd;
    }
    
    /* Estilos para el select de registros por página */
    .per-page-selector {
        background-color: #fff;
        border: 1px solid #e9ecef;
        border-radius: 0.375rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        transition: all 0.15s ease-in-out;
    }
    
    .per-page-selector:hover {
        border-color: #86b7fe;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.15);
    }
    
    .per-page-selector:focus {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
    }
    
    .per-page-label {
        font-size: 0.875rem;
        font-weight: 500;
        color: #6c757d;
    }
    
    .table-controls {
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        padding: 1rem;
        border: 1px solid #e9ecef;
    }
    
    /* Toast de éxito */
    .toast {
        border: none;
        border-radius: 0.5rem;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        min-width: 240px;
        max-width: 280px;
    }
    
    .toast-header {
        border-radius: 0.5rem 0.5rem 0 0;
        padding: 0.6rem 0.8rem;
        background: #38c66c !important;
        background-color: #38c66c !important;
        background-image: none !important;
        color: white;
        border-bottom: none;
    }
    
    .toast-body {
        border-radius: 0 0 0.5rem 0.5rem;
        padding: 0.6rem 0.8rem;
        background-color: #f8f9fa;
    }
    
    .toast-container {
        animation: bounceIn 0.8s ease-out;
    }
    
    @keyframes bounceIn {
        0% {
            transform: scale(0.3) translateY(-50px);
            opacity: 0;
        }
        50% {
            transform: scale(1.05) translateY(0);
            opacity: 0.8;
        }
        70% {
            transform: scale(0.95) translateY(0);
            opacity: 0.9;
        }
        100% {
            transform: scale(1) translateY(0);
            opacity: 1;
        }
    }
    
    .toast.fade-out {
        animation: fadeOut 0.5s ease-out forwards;
    }
    
    @keyframes fadeOut {
        from { opacity: 1; transform: scale(1); }
        to { opacity: 0; transform: scale(0.9); }
    }
    
    @media (max-width: 576px) {
        .toast { 
            min-width: 224px; 
            max-width: 256px; 
        }
        .toast-container { 
            top: 1rem !important; 
            right: 1rem !important; 
        }
    }
</style>
@endsection

@php
$getStaffSortUrl = static function ($field, $currentSort, $currentDirection) {
    $params = request()->query();
    
    if ($currentSort === $field && $currentDirection === 'asc') {
        $params['direction'] = 'desc';
    } else {
        $params['direction'] = 'asc';
    }
    
    $params['sort'] = $field;
    
    return request()->url() . '?' . http_build_query($params);
};

$getStaffSortIcon = static function ($field, $currentSort, $currentDirection) {
    if ($currentSort !== $field) {
        return '<i class="fas fa-sort sort-icon"></i>';
    }
    
    if ($currentDirection === 'asc') {
        return '<i class="fas fa-sort-up sort-icon active"></i>';
    } else {
        return '<i class="fas fa-sort-down sort-icon active"></i>';
    }
};

$sortField = request('sort', 'id');
$sortDirection = request('direction', 'desc');
@endphp

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Gestión de Personal</h4>
                    <p class="text-muted mb-3">Administra el personal médico y administrativo de la clínica.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('staff.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>
                        Nuevo Empleado
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="staffSuccessToast">
                <div class="toast-header text-white border-0">
                    <div class="avatar avatar-xs avatar-label-light me-2">
                        <i class="fas fa-check fs-12"></i>
                    </div>
                    <strong class="me-auto">¡Éxito!</strong>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
                <div class="toast-body bg-light">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-xs avatar-label-success me-2">
                            <i class="fas fa-users fs-12"></i>
                        </div>
                        <span class="text-muted">{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="row">
            <div class="col-12">
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Error:</strong> Por favor corrige los siguientes errores:
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            </div>
        </div>
    @endif

    <!-- Filtros y Búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h6 class="card-title mb-0">Filtros de Búsqueda</h6>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#staffFiltersCollapse" aria-expanded="false" aria-controls="filtersCollapse">
                            <i class="fas fa-filter me-1"></i>
                            <span class="filter-text">Mostrar Filtros</span>
                        </button>
                    </div>
                </div>
                <div class="collapse" id="staffFiltersCollapse">
                    <div class="card-body">
                        <form action="{{ route('staff.index') }}" method="GET" class="mb-4">
                        <div class="row g-3">
                        <div class="col-md-4">
                            <label for="staff-search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="staff-search" name="search"
                                       value="{{ request('search') }}" 
                                       placeholder="Nombre, email o especialidad">
                            </div>
                            <div class="col-md-2">
                                <label for="specialty" class="form-label">Especialidad</label>
                                <select class="form-select" id="specialty" name="specialty">
                                    <option value="">Todas</option>
                                    <option value="Ortodoncista" {{ request('specialty') == 'Ortodoncista' ? 'selected' : '' }}>Ortodoncista</option>
                                    <option value="Cirujano Oral" {{ request('specialty') == 'Cirujano Oral' ? 'selected' : '' }}>Cirujano Oral</option>
                                    <option value="Endodoncista" {{ request('specialty') == 'Endodoncista' ? 'selected' : '' }}>Endodoncista</option>
                                    <option value="Higienista Dental" {{ request('specialty') == 'Higienista Dental' ? 'selected' : '' }}>Higienista Dental</option>
                                    <option value="Recepcionista" {{ request('specialty') == 'Recepcionista' ? 'selected' : '' }}>Recepcionista</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label for="is_active" class="form-label">Estado</label>
                                <select class="form-select" id="is_active" name="is_active">
                                    <option value="">Todos</option>
                                    <option value="1" {{ request('is_active') == '1' ? 'selected' : '' }}>Activo</option>
                                    <option value="0" {{ request('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <label class="form-label">&nbsp;</label>
                                <div class="d-flex gap-2">
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-times"></i>
                                    </a>
                                </div>
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
                    <div class="card-icon">
                        <i class="fas fa-users fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Lista de Personal</h4>
                </div>
                
                <!-- Controles de tabla -->
                <div class="table-controls">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center">
                                <label class="per-page-label me-2 mb-0">Mostrar:</label>
                                 <select class="form-select form-select-sm per-page-selector" style="width: 80px;" onchange="changePerPage(this.value)">
                                     <option value="10" {{ ($perPageValue ?? '10') == '10' ? 'selected' : '' }}>10</option>
                                     <option value="25" {{ ($perPageValue ?? '10') == '25' ? 'selected' : '' }}>25</option>
                                     <option value="50" {{ ($perPageValue ?? '10') == '50' ? 'selected' : '' }}>50</option>
                                     <option value="100" {{ ($perPageValue ?? '10') == '100' ? 'selected' : '' }}>100</option>
                                     <option value="all" {{ ($perPageValue ?? '10') == 'all' ? 'selected' : '' }}>Todos</option>
                                 </select>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Mostrando {{ $staff->count() }} de {{ $staff->total() }} registros
                            </div>
                        </div>
                        
                        <!-- Botón de exportar -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i> Exportar
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="{{ route('staff.export.pdf', request()->query()) }}" class="dropdown-item">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i> PDF
                                </a>
                                <a href="{{ route('staff.export.excel', request()->query()) }}" class="dropdown-item">
                                    <i class="fas fa-file-excel me-2 text-success"></i> Excel
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('staff.export.pdf') }}" class="dropdown-item">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i> PDF - Todos
                                </a>
                                <a href="{{ route('staff.export.excel') }}" class="dropdown-item">
                                    <i class="fas fa-file-excel me-2 text-success"></i> Excel - Todos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($staff->count() > 0)
                    <div class="table-responsive">
                            <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ $getStaffSortUrl('id', $sortField, $sortDirection) }}">
                                            Código
                                            {!! $getStaffSortIcon('id', $sortField, $sortDirection) !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getStaffSortUrl('name', $sortField, $sortDirection) }}">
                                            Personal
                                            {!! $getStaffSortIcon('name', $sortField, $sortDirection) !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getStaffSortUrl('email', $sortField, $sortDirection) }}">
                                            Contacto
                                            {!! $getStaffSortIcon('email', $sortField, $sortDirection) !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getStaffSortUrl('specialty', $sortField, $sortDirection) }}">
                                            Especialidad
                                            {!! $getStaffSortIcon('specialty', $sortField, $sortDirection) !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getStaffSortUrl('role', $sortField, $sortDirection) }}">
                                            Rol
                                            {!! $getStaffSortIcon('role', $sortField, $sortDirection) !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getStaffSortUrl('is_active', $sortField, $sortDirection) }}">
                                            Estado
                                            {!! $getStaffSortIcon('is_active', $sortField, $sortDirection) !!}
                                        </a>
                                    </th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach($staff as $employee)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ strtoupper(substr($employee->user->name ?? 'N', 0, 1)) }}{{ strtoupper(substr($employee->user->name ?? 'A', 1, 1)) }}-{{ str_pad($employee->id, 4, '0', STR_PAD_LEFT) }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm avatar-label-primary me-3">
                                                    @if($employee->user->gender === 'male')
                                                        <i class="fas fa-male text-primary"></i>
                                                    @elseif($employee->user->gender === 'female')
                                                        <i class="fas fa-female text-danger"></i>
                                                    @else
                                                        <i class="fas fa-user text-secondary"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $employee->user->name ?? 'N/A' }}</h6>
                                                    <small class="text-muted">{{ $employee->user->email ?? 'Sin email' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <i class="fas fa-phone me-1"></i> {{ $employee->user->phone ?? 'Sin teléfono' }}<br>
                                                <i class="fas fa-envelope me-1"></i> {{ $employee->user->email ?? 'N/A' }}
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info">
                                                {{ $employee->specialty ?? 'General' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary">
                                                {{ $employee->user->roles->first()->display_name ?? 'Sin rol' }}
                                        </span>
                                    </td>
                                    <td>
                                            @if($employee->is_active)
                                                <span class="badge bg-success-subtle text-success">
                                                    <i class="fas fa-check-circle me-1"></i>Activo
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger">
                                                    <i class="fas fa-times-circle me-1"></i>Inactivo
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex gap-1">
                                                <a href="{{ route('staff.show', $employee) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('staff.edit', $employee) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" 
                                                        data-bs-toggle="modal" data-bs-target="#deleteModal{{ $employee->id }}">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                </tr>

                                <!-- Modal de confirmación de eliminación -->
                                <div class="modal fade" id="deleteModal{{ $employee->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmar Eliminación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Estás seguro de que deseas eliminar al empleado <strong>{{ $employee->user->name ?? 'N/A' }}</strong>?</p>
                                                <p class="text-muted">Esta acción no se puede deshacer.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                <form method="POST" action="{{ route('staff.destroy', $employee) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                    @endforeach
                            </tbody>
                        </table>
                    </div>

                        <!-- Paginación -->
                        <div class="row mt-3">
                            <div class="col-sm-12 col-md-5">
                                <div class="dataTables_info">
                                    Mostrando {{ $staff->firstItem() }} a {{ $staff->lastItem() }} 
                                    de {{ $staff->total() }} registros
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    {{ $staff->links('vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar avatar-xl avatar-label-primary mx-auto mb-3">
                                <i class="fas fa-user-plus fs-24"></i>
                            </div>
                            <h5 class="text-muted">No hay personal registrado</h5>
                            <p class="text-muted mb-4">Comienza agregando el primer miembro del personal.</p>
                            <a href="{{ route('staff.create') }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>
                                Agregar Primer Empleado
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Modal de Confirmación de Eliminación -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center">
                        <div class="avatar avatar-sm avatar-label-danger me-3">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <div>
                            <h6 class="mb-1">¿Estás seguro?</h6>
                            <p class="mb-0 text-muted">Esta acción no se puede deshacer. Se eliminará permanentemente el miembro del personal y todos sus datos asociados.</p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <form id="staffDeleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">Eliminar</button>
                    </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Inicializar filtros collapse
initializeFiltersCollapse('staff');

// Función para cambiar registros por página
function changePerPage(value) {
    console.log('changePerPage llamada con valor:', value);
    
    const currentParams = new URLSearchParams(window.location.search);
    console.log('Parámetros actuales:', currentParams.toString());
    
    currentParams.set('per_page', value);
    currentParams.delete('page'); // Resetear a la primera página
    
    const newUrl = window.location.pathname + '?' + currentParams.toString();
    console.log('Nueva URL:', newUrl);
    
    window.location.href = newUrl;
}

// Inicialización cuando el DOM esté listo
document.addEventListener('DOMContentLoaded', function() {
    // Gestión de estado de filtros con localStorage
    const filtersCollapse = document.getElementById('staffFiltersCollapse');
    const filterButton = document.querySelector('[data-bs-target="#staffFiltersCollapse"]');
    const filterText = document.querySelector('.filter-text');
    
    // Recuperar estado del localStorage
    const savedState = localStorage.getItem('staff-filters-collapse');
    const shouldBeOpen = savedState === 'true';
    
    // Aplicar estado inicial
    if (shouldBeOpen) {
        filtersCollapse.classList.add('show');
        filterButton.setAttribute('aria-expanded', 'true');
        filterText.textContent = 'Ocultar Filtros';
    } else {
        filtersCollapse.classList.remove('show');
        filterButton.setAttribute('aria-expanded', 'false');
        filterText.textContent = 'Mostrar Filtros';
    }
    
    // Escuchar eventos de collapse de Bootstrap
    filtersCollapse.addEventListener('shown.bs.collapse', function() {
        localStorage.setItem('staff-filters-collapse', 'true');
        filterText.textContent = 'Ocultar Filtros';
        filterButton.setAttribute('aria-expanded', 'true');
    });
    
    filtersCollapse.addEventListener('hidden.bs.collapse', function() {
        localStorage.setItem('staff-filters-collapse', 'false');
        filterText.textContent = 'Mostrar Filtros';
        filterButton.setAttribute('aria-expanded', 'false');
    });
    
    // Inicializar toast de éxito
    const successToast = document.getElementById('staffSuccessToast');
    if (successToast) {
        const toast = new bootstrap.Toast(successToast, {
            autohide: false,
            delay: 0
        });
        toast.show();
        
        // Después de 3 segundos, aplicar fade-out
        setTimeout(() => {
            successToast.classList.add('fade-out');
            setTimeout(() => {
                successToast.remove();
            }, 500);
        }, 3000);
    }
});

function confirmEliminar(staffId) {
    const form = document.getElementById('deleteForm');
    form.action = `/staff/${staffId}`;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteModal'));
    deleteModal.show();
}
</script>
@endsection
