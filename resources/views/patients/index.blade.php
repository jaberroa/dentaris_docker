@extends('layouts.master')

@section('title')
    Gestión de Pacientes
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
        border: 1px solid #e9ecef;
        border-radius: 0.5rem;
        padding: 0.75rem 1rem;
        margin-bottom: 1rem;
    }
    
    /* Estilos para el toast de éxito */
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
        background-color: #38c66c !important;
    }
    
    .toast-body {
        border-radius: 0 0 0.5rem 0.5rem;
        padding: 0.6rem 0.8rem;
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
    
    /* Estilos personalizados para el toast (igual que success-toast) */
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
    }
    
    .toast-body {
        border-radius: 0 0 0.5rem 0.5rem;
        padding: 0.6rem 0.8rem;
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
    
    .toast.show {
        animation: none;
    }
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.9);
        }
    }
</style>
@endsection


@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Gestión de Pacientes</h4>
                    <p class="text-muted mb-3">Administra la información de tus pacientes y sus historias clínicas.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('patients.create') }}" class="btn btn-primary">
                        <i class="fas fa-user-plus me-1"></i>
                        Nuevo Paciente
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="patientSuccessToast">
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
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#patientFiltersCollapse" aria-expanded="false" aria-controls="patientFiltersCollapse">
                            <i class="fas fa-filter me-1"></i>
                            <span class="filter-text">Mostrar Filtros</span>
                        </button>
                    </div>
                </div>
                <div class="collapse" id="patientFiltersCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('patients.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="patient-search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="patient-search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Nombre, código, email o teléfono">
                        </div>
                        <div class="col-md-2">
                            <label for="gender" class="form-label">Género</label>
                            <select class="form-select" id="gender" name="gender">
                                <option value="">Todos</option>
                                <option value="male" {{ request('gender') == 'male' ? 'selected' : '' }}>Masculino</option>
                                <option value="female" {{ request('gender') == 'female' ? 'selected' : '' }}>Femenino</option>
                                <option value="other" {{ request('gender') == 'other' ? 'selected' : '' }}>Otro</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="age_range" class="form-label">Edad</label>
                            <select class="form-select" id="age_range" name="age_range">
                                <option value="">Todas</option>
                                <option value="0-17" {{ request('age_range') == '0-17' ? 'selected' : '' }}>0-17 años</option>
                                <option value="18-30" {{ request('age_range') == '18-30' ? 'selected' : '' }}>18-30 años</option>
                                <option value="31-50" {{ request('age_range') == '31-50' ? 'selected' : '' }}>31-50 años</option>
                                <option value="51-65" {{ request('age_range') == '51-65' ? 'selected' : '' }}>51-65 años</option>
                                <option value="65+" {{ request('age_range') == '65+' ? 'selected' : '' }}>65+ años</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="patient-from" class="form-label">Desde</label>
                            <input type="date" class="form-control" id="patient-from" name="created_from" 
                                   value="{{ request('created_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="patient-to" class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="patient-to" name="created_to" 
                                   value="{{ request('created_to') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Lista de Pacientes -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-users fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Lista de Pacientes</h4>
                </div>
                
                <!-- Controles de tabla -->
                <div class="table-controls">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-flex align-items-center">
                                <label class="per-page-label me-2 mb-0">Mostrar:</label>
                                 <select class="form-select form-select-sm per-page-selector" style="width: 80px;" onchange="changePerPage(this.value)">
                                     <option value="10" {{ ($perPage ?? '10') == '10' ? 'selected' : '' }}>10</option>
                                     <option value="25" {{ ($perPage ?? '10') == '25' ? 'selected' : '' }}>25</option>
                                     <option value="50" {{ ($perPage ?? '10') == '50' ? 'selected' : '' }}>50</option>
                                     <option value="100" {{ ($perPage ?? '10') == '100' ? 'selected' : '' }}>100</option>
                                     <option value="all" {{ ($perPage ?? '10') == 'all' ? 'selected' : '' }}>Todos</option>
                                 </select>
                            </div>
                            <div class="text-muted small">
                                <i class="fas fa-info-circle me-1"></i>
                                Mostrando {{ $patients->count() }} de {{ $patients->total() }} registros
                            </div>
                        </div>
                        
                        <!-- Botón de exportar -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i> Exportarar
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="{{ route('patients.export.pdf', request()->query()) }}" class="dropdown-item">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i> PDF
                                </a>
                                <a href="{{ route('patients.export.excel', request()->query()) }}" class="dropdown-item">
                                    <i class="fas fa-file-excel me-2 text-success"></i> Excel
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="{{ route('patients.export.pdf') }}" class="dropdown-item">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i> PDF - Todos
                                </a>
                                <a href="{{ route('patients.export.excel') }}" class="dropdown-item">
                                    <i class="fas fa-file-excel me-2 text-success"></i> Excel - Todos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($patients->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th>
                                            <a href="{{ getPatientSortUrl('patient_code', $sortField, $sortDirection) }}">
                                                Código
                                                {!! getPatientSortIcon('patient_code', $sortField, $sortDirection) !!}
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ getPatientSortUrl('first_name', $sortField, $sortDirection) }}">
                                                Paciente
                                                {!! getPatientSortIcon('first_name', $sortField, $sortDirection) !!}
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ getPatientSortUrl('email', $sortField, $sortDirection) }}">
                                                Contacto
                                                {!! getPatientSortIcon('email', $sortField, $sortDirection) !!}
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ getPatientSortUrl('birth_date', $sortField, $sortDirection) }}">
                                                Edad
                                                {!! getPatientSortIcon('birth_date', $sortField, $sortDirection) !!}
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ getPatientSortUrl('gender', $sortField, $sortDirection) }}">
                                                Género
                                                {!! getPatientSortIcon('gender', $sortField, $sortDirection) !!}
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ getPatientSortUrl('created_at', $sortField, $sortDirection) }}">
                                                Registro
                                                {!! getPatientSortIcon('created_at', $sortField, $sortDirection) !!}
                                            </a>
                                        </th>
                                        <th>
                                            <a href="{{ getPatientSortUrl('is_active', $sortField, $sortDirection) }}" class="text-decoration-none text-dark">
                                                Estado
                                                {!! getPatientSortIcon('is_active', $sortField, $sortDirection) !!}
                                            </a>
                                        </th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patients as $patient)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ $patient->display_code }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm avatar-label-primary me-3">
                                                        @if($patient->gender === 'male')
                                                            <i class="fas fa-male text-primary"></i>
                                                        @elseif($patient->gender === 'female')
                                                            <i class="fas fa-female text-danger"></i>
                                                        @else
                                                            <i class="fas fa-user text-secondary"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $patient->first_name }} {{ $patient->last_name }}</h6>
                                                        <small class="text-muted">{{ $patient->email }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <small class="text-muted">
                                                        <i class="fas fa-phone me-1"></i>
                                                        {{ $patient->phone }}
                                                    </small>
                                                    <small class="text-muted">
                                                        <i class="fas fa-envelope me-1"></i>
                                                        {{ $patient->email }}
                                                    </small>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="fw-semibold">{{ $patient->age }} años</span>
                                            </td>
                                            <td>
                                                @php
                                                    $genderConfig = [
                                                        'male' => ['color' => 'primary', 'icon' => 'fa-male', 'text' => 'Masculino'],
                                                        'female' => ['color' => 'danger', 'icon' => 'fa-female', 'text' => 'Femenino'],
                                                        'other' => ['color' => 'secondary', 'icon' => 'fa-question', 'text' => 'Otro']
                                                    ];
                                                    $currentGender = $patient->gender ?? 'male';
                                                    $config = $genderConfig[$currentGender] ?? $genderConfig['male'];
                                                @endphp
                                                
                                                <div class="dropdown" onclick="event.stopPropagation(); closeOtherDropdowns(this);">
                                                    <button class="btn btn-link p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true">
                                                        <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }}" style="cursor: pointer;">
                                                            <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                                    </span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1050;">
                                                        @foreach(['male' => 'Masculino', 'female' => 'Femenino', 'other' => 'Otro'] as $genderValue => $genderText)
                                                            @php
                                                                $optionConfig = $genderConfig[$genderValue];
                                                            @endphp
                                                            <li>
                                                                <a class="dropdown-item d-flex align-items-center" 
                                                                   href="#" 
                                                                   onclick="updatePatientGender({{ $patient->id }}, '{{ $genderValue }}', '{{ $optionConfig['color'] }}', '{{ $optionConfig['icon'] }}', '{{ $optionConfig['text'] }}'); return false;">
                                                                    <span class="badge bg-{{ $optionConfig['color'] }}-subtle text-{{ $optionConfig['color'] }} me-2">
                                                                        <i class="fas {{ $optionConfig['icon'] }} me-1"></i>{{ $optionConfig['text'] }}
                                                    </span>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $patient->created_at->format('d/m/Y') }}</span>
                                                    <small class="text-muted">{{ $patient->created_at->diffForHumans() }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusConfig = [
                                                        'active' => ['color' => 'success', 'icon' => 'fa-check', 'text' => 'Activo'],
                                                        'inactive' => ['color' => 'secondary', 'icon' => 'fa-times', 'text' => 'Inactivo']
                                                    ];
                                                    $currentStatus = $patient->is_active ? 'active' : 'inactive';
                                                    $config = $statusConfig[$currentStatus];
                                                @endphp
                                                
                                                <div class="dropdown" onclick="event.stopPropagation(); closeOtherDropdowns(this);">
                                                    <button class="btn btn-link p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true">
                                                        <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }}" style="cursor: pointer;">
                                                            <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                                        </span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1050;">
                                                        @foreach(['active' => 'Activo', 'inactive' => 'Inactivo'] as $statusValue => $statusText)
                                                            @php
                                                                $optionConfig = $statusConfig[$statusValue];
                                                                $isActiveValue = $statusValue === 'active';
                                                            @endphp
                                                            <li>
                                                                <a class="dropdown-item d-flex align-items-center" 
                                                                   href="#" 
                                                                   onclick="updatePatientStatus({{ $patient->id }}, {{ $isActiveValue ? 'true' : 'false' }}, '{{ $optionConfig['color'] }}', '{{ $optionConfig['icon'] }}', '{{ $optionConfig['text'] }}'); return false;">
                                                                    <span class="badge bg-{{ $optionConfig['color'] }}-subtle text-{{ $optionConfig['color'] }} me-2">
                                                                        <i class="fas {{ $optionConfig['icon'] }} me-1"></i>{{ $optionConfig['text'] }}
                                                </span>
                                                                </a>
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('patients.show', $patient) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <a href="{{ route('patients.edit', $patient) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" 
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $patient->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Modal de confirmación de eliminación -->
                                        <div class="modal fade" id="deleteModal{{ $patient->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar Eliminación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Estás seguro de que deseas eliminar al paciente <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>?</p>
                                                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelarar</button>
                                                        <form method="POST" action="{{ route('patients.destroy', $patient) }}" class="d-inline">
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
                        <div class="d-flex justify-content-center mt-3">
                            {{ $patients->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-users fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No hay pacientes registrados</h5>
                            <p class="text-muted">Comienza agregando tu primer paciente.</p>
                            <a href="{{ route('patients.create') }}" class="btn btn-primary">
                                <i class="fas fa-user-plus me-1"></i>
                                Nuevo Paciente
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection

@section('scripts')
<script>
// Inicializar toast de éxito
document.addEventListener('DOMContentLoaded', function() {
    // Gestión de estado de filtros con localStorage
    const filtersCollapse = document.getElementById('patientFiltersCollapse');
    const filterButton = document.querySelector('[data-bs-target="#patientFiltersCollapse"]');
    const filterText = document.querySelector('.filter-text');
    
    // Recuperar estado del localStorage
    const savedState = localStorage.getItem('patients-filters-collapse');
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
        localStorage.setItem('patients-filters-collapse', 'true');
        filterText.textContent = 'Ocultar Filtros';
        filterButton.setAttribute('aria-expanded', 'true');
    });
    
    filtersCollapse.addEventListener('hidden.bs.collapse', function() {
        localStorage.setItem('patients-filters-collapse', 'false');
        filterText.textContent = 'Mostrar Filtros';
        filterButton.setAttribute('aria-expanded', 'false');
    });
    
    // Toast de éxito
    const successToast = document.getElementById('patientSuccessToast');
    if (successToast) {
        const toast = new bootstrap.Toast(successToast, {
            autohide: false, // Disable Bootstrap's autohide
            delay: 0
        });
        toast.show();
        
        setTimeout(function() {
            successToast.classList.add('fade-out'); // Agregar clase personalizada fade-out
            setTimeout(function() {
                successToast.remove(); // Remover elemento después de la animación
            }, 500); // Duración de la animación fadeOut
        }, 3000); // 3 segundos antes de iniciar fade-out
    }
});

// Función para cambiar registros por página (debe estar disponible globalmente)
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

// Función para generar URL de sorting
function getSortUrlJS(field) {
    const currentParams = new URLSearchParams(window.location.search);
    const currentSort = currentParams.get('sort');
    const currentDirection = currentParams.get('direction');
    
    let newDirection = 'asc';
    if (currentSort === field && currentDirection === 'asc') {
        newDirection = 'desc';
    }
    
    currentParams.set('sort', field);
    currentParams.set('direction', newDirection);
    
    return window.location.pathname + '?' + currentParams.toString();
}

// Función para obtener icono de sorting
function getPatientSortIconJS(field) {
    const currentSort = '{{ $sortField ?? '' }}';
    const currentDirection = '{{ $sortDirection ?? '' }}';
    
    if (currentSort !== field) {
        return '<i class="fas fa-sort text-muted ms-1"></i>';
    }
    
    if (currentDirection === 'asc') {
        return '<i class="fas fa-sort-up text-primary ms-1"></i>';
    } else {
        return '<i class="fas fa-sort-down text-primary ms-1"></i>';
    }
}

// Inicializar filtros collapse
initializeFiltersCollapse('patients');

// Función para cerrar otros dropdowns cuando se abre uno
function closeOtherDropdowns(currentDropdown) {
    // Cerrar todos los dropdowns excepto el actual
    const allDropdowns = document.querySelectorAll('.dropdown');
    allDropdowns.forEach(dropdown => {
        if (dropdown !== currentDropdown) {
            const button = dropdown.querySelector('[data-bs-toggle="dropdown"]');
            const bootstrapDropdown = bootstrap.Dropdown.getInstance(button);
            if (bootstrapDropdown) {
                bootstrapDropdown.hide();
            }
        }
    });
}

// Función para actualizar género del paciente
function updatePatientGender(patientId, genderValue, genderColor, genderIcon, genderText) {
    // Obtener el dropdown para cerrarlo después
    const dropdown = event.target.closest('.dropdown');
    const dropdownButton = dropdown.querySelector('[data-bs-toggle="dropdown"]');
    
    // Mostrar indicador de carga
    const badge = dropdown.querySelector('.badge');
    const originalContent = badge.innerHTML;
    const originalClass = badge.className;
    
    badge.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...';
    badge.className = 'badge bg-secondary-subtle text-secondary';
    
    // Cerrar el dropdown inmediatamente
    const bootstrapDropdown = bootstrap.Dropdown.getInstance(dropdownButton);
    if (bootstrapDropdown) {
        bootstrapDropdown.hide();
    }
    
    fetch(`/patients/${patientId}/gender`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            gender: genderValue
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Actualizar el badge con el nuevo género
            badge.innerHTML = `<i class="fas ${genderIcon} me-1"></i>${genderText}`;
            badge.className = `badge bg-${genderColor}-subtle text-${genderColor}`;
            
            // Mostrar mensaje de éxito
            showToast('Género actualizado correctamente', 'success');
        } else {
            throw new Error(data.message || 'Error al actualizar el género');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Restaurar contenido original
        badge.innerHTML = originalContent;
        badge.className = originalClass;
        
        // Mostrar mensaje de error
        showToast('Error al actualizar el género del paciente', 'error');
    });
}

// Función para actualizar estado del paciente
function updatePatientStatus(patientId, isActive, statusColor, statusIcon, statusText) {
    // Obtener el dropdown para cerrarlo después
    const dropdown = event.target.closest('.dropdown');
    const dropdownButton = dropdown.querySelector('[data-bs-toggle="dropdown"]');
    
    // Mostrar indicador de carga
    const badge = dropdown.querySelector('.badge');
    const originalContent = badge.innerHTML;
    const originalClass = badge.className;
    
    badge.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i>Actualizando...';
    badge.className = 'badge bg-secondary-subtle text-secondary';
    
    // Cerrar el dropdown inmediatamente
    const bootstrapDropdown = bootstrap.Dropdown.getInstance(dropdownButton);
    if (bootstrapDropdown) {
        bootstrapDropdown.hide();
    }
    
    fetch(`/patients/${patientId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            is_active: isActive === 'true' || isActive === true
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Error en la respuesta del servidor');
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Actualizar el badge con el nuevo estado
            badge.innerHTML = `<i class="fas ${statusIcon} me-1"></i>${statusText}`;
            badge.className = `badge bg-${statusColor}-subtle text-${statusColor}`;
            
            // Mostrar mensaje de éxito
            showToast('Estado actualizado correctamente', 'success');
        } else {
            throw new Error(data.message || 'Error al actualizar el estado');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        // Restaurar contenido original
        badge.innerHTML = originalContent;
        badge.className = originalClass;
        
        // Mostrar mensaje de error
        showToast('Error al actualizar el estado del paciente', 'error');
    });
}

// Función para mostrar toast con el mismo estilo que el componente success-toast
function showToast(message, type = 'success') {
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    
    const toastId = 'toast-' + Date.now();
    const isSuccess = type === 'success';
    const bgColor = isSuccess ? '#38c66c' : '#dc3545';
    const icon = isSuccess ? 'fa-check' : 'fa-exclamation-triangle';
    const title = isSuccess ? '¡Éxito!' : 'Error';
    const bodyIcon = isSuccess ? 'fa-user-edit' : 'fa-exclamation-circle';
    
    const toastHTML = `
        <div id="${toastId}" class="toast show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header text-white border-0" style="background: ${bgColor} !important; background-color: ${bgColor} !important; background-image: none !important;">
                <div class="avatar avatar-xs avatar-label-light me-2">
                    <i class="fas ${icon} fs-12"></i>
                </div>
                <strong class="me-auto">${title}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-light">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xs avatar-label-${isSuccess ? 'success' : 'danger'} me-2">
                        <i class="fas ${bodyIcon} fs-12"></i>
                    </div>
                    <span class="text-muted">${message}</span>
                </div>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: false,
        delay: 0
    });
    toast.show();
    
    // Auto-ocultar después de 3 segundos con transición suave
    setTimeout(function() {
        // Remover la animación inicial y aplicar fade-out
        toastElement.style.animation = 'fadeOut 0.5s ease-out forwards';
        toastElement.classList.add('fade-out');
        
        setTimeout(function() {
            toastElement.remove();
        }, 500);
    }, 3000);
    
    // Remover el toast del DOM después de que se oculte manualmente
    toastElement.addEventListener('hidden.bs.toast', function() {
        toastElement.remove();
    });
}

// Función para crear el contenedor de toasts si no existe
function createToastContainer() {
    const container = document.createElement('div');
    container.className = 'toast-container position-fixed top-0 end-0 p-3';
    container.style.zIndex = '1055';
    document.body.appendChild(container);
    return container;
}
</script>
@endsection