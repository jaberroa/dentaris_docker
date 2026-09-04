@extends('layouts.master')

@section('title')
    Planes de Tratamiento
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

    /* Estilos para sorting */
    .sortable {
        cursor: pointer;
        user-select: none;
        position: relative;
    }
    
    .sortable:hover {
        background-color: #f8f9fa;
    }
    
    .sort-icon {
        opacity: 0.5;
        transition: opacity 0.2s;
    }
    
    .sortable:hover .sort-icon {
        opacity: 1;
    }
    
    .sort-active .sort-icon {
        opacity: 1;
        color: #0d6efd;
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
        min-width: 80px;
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
    
    /* Estilos para controles de tabla */
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
    
    @keyframes fadeOut {
        from {
            opacity: 1;
            transform: translateX(0);
        }
        to {
            opacity: 0;
            transform: translateX(100%);
        }
    }
    
    .fade-out {
        animation: fadeOut 0.5s ease-out forwards;
    }
    }
</style>
@endsection

@php
$getTreatmentPlanSortUrl = static function ($field, $currentSort, $currentDirection) {
    $params = request()->query();
    
    if ($currentSort === $field && $currentDirection === 'asc') {
        $params['direction'] = 'desc';
    } else {
        $params['direction'] = 'asc';
    }
    
    $params['sort'] = $field;
    
    return request()->url() . '?' . http_build_query($params);
};

$getTreatmentPlanSortIcon = static function ($field, $currentSort, $currentDirection) {
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
                    <h4 class="mb-2">Gestión de Planes de Tratamiento</h4>
                    <p class="text-muted mb-3">Administra los planes de tratamiento de tus pacientes y su seguimiento.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('treatment-plans.create') }}" class="btn btn-primary">
                        <i class="fas fa-plus me-1"></i>
                        Nuevo Plan
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Mensaje de éxito flotante -->
    @include('components.success-toast')

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

    <!-- Filtros de Búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h6 class="card-title mb-0">Filtros de Búsqueda</h6>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#treatmentFiltersCollapse" aria-expanded="false" aria-controls="filtersCollapse">
                            <i class="fas fa-filter me-1"></i>
                            <span class="filter-text">Mostrar Filtros</span>
                        </button>
                    </div>
                </div>
                <div class="collapse" id="treatmentFiltersCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('treatment-plans.index') }}" class="row g-3">
                        <div class="col-md-3">
                            <label for="treatment-search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="treatment-search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Paciente, tratamiento o notas">
                        </div>
                        <div class="col-md-2">
                            <label for="treatment-status" class="form-label">Estado</label>
                            <select class="form-select" id="treatment-status" name="status">
                                <option value="">Todos</option>
                                @foreach(\App\Models\TreatmentPlan::getStatusOptions() as $value => $label)
                                    <option value="{{ $value }}" {{ request('status') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach   
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="priority" class="form-label">Prioridad</label>
                            <select class="form-select" id="priority" name="priority">
                                <option value="">Todas</option>
                                <option value="low" {{ request('priority') == 'low' ? 'selected' : '' }}>Baja</option>
                                <option value="medium" {{ request('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                                <option value="high" {{ request('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="treatment-from" class="form-label">Desde</label>
                            <input type="date" class="form-control" id="treatment-from" name="created_from" 
                                   value="{{ request('created_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="treatment-to" class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="treatment-to" name="created_to" 
                                   value="{{ request('created_to') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('treatment-plans.index') }}" class="btn btn-outline-secondary">
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

    <!-- Lista de Planes de Tratamiento -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Lista de Planes de Tratamiento</h4>
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
                                Mostrando {{ $treatmentPlans->count() }} de {{ $treatmentPlans->total() }} registros
                            </div>
                        </div>
                        
                        <!-- Botón de exportar -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i> Exportar
                            </button>
                            <div class="dropdown-menu dropdown-menu-end">
                                <a href="#" class="dropdown-item">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i> PDF
                                </a>
                                <a href="#" class="dropdown-item">
                                    <i class="fas fa-file-excel me-2 text-success"></i> Excel
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item">
                                    <i class="fas fa-file-pdf me-2 text-danger"></i> PDF - Todos
                                </a>
                                <a href="#" class="dropdown-item">
                                    <i class="fas fa-file-excel me-2 text-success"></i> Excel - Todos
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    @if($treatmentPlans->count() > 0)
                    
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ $getTreatmentPlanSortUrl('id', $sortField ?? '', $sortDirection ?? '') }}">
                                            Código
                                            {!! $getTreatmentPlanSortIcon('id', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getTreatmentPlanSortUrl('patient_id', $sortField ?? '', $sortDirection ?? '') }}">
                                            Paciente
                                            {!! $getTreatmentPlanSortIcon('patient_id', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getTreatmentPlanSortUrl('treatment_name', $sortField ?? '', $sortDirection ?? '') }}">
                                            Tratamiento
                                            {!! $getTreatmentPlanSortIcon('treatment_name', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getTreatmentPlanSortUrl('start_date', $sortField ?? '', $sortDirection ?? '') }}">
                                            Fecha Inicio
                                            {!! $getTreatmentPlanSortIcon('start_date', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getTreatmentPlanSortUrl('status', $sortField ?? '', $sortDirection ?? '') }}">
                                            Estado
                                            {!! $getTreatmentPlanSortIcon('status', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getTreatmentPlanSortUrl('progress', $sortField ?? '', $sortDirection ?? '') }}">
                                            Progreso
                                            {!! $getTreatmentPlanSortIcon('progress', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach($treatmentPlans as $plan)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold">{{ $plan->patient->patient_code ?? 'N/A' }}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm avatar-label-primary me-3">
                                                        @if($plan->patient->gender === 'male')
                                                            <i class="fas fa-male text-primary"></i>
                                                        @elseif($plan->patient->gender === 'female')
                                                            <i class="fas fa-female text-danger"></i>
                                                        @else
                                                            <i class="fas fa-user text-secondary"></i>
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <h6 class="mb-0">{{ $plan->patient->first_name ?? 'N/A' }} {{ $plan->patient->last_name ?? '' }}</h6>
                                                        <small class="text-muted">{{ $plan->patient->email ?? 'Sin email' }}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $plan->treatment_name ?? 'Sin nombre' }}</span>
                                                    <small class="text-muted">{{ Str::limit($plan->description ?? 'Sin descripción', 50) }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column">
                                                    <span class="fw-semibold">{{ $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->format('d/m/Y') : 'N/A' }}</span>
                                                    <small class="text-muted">{{ $plan->start_date ? \Carbon\Carbon::parse($plan->start_date)->diffForHumans() : '' }}</small>
                                                </div>
                                            </td>
                                            <td>
                                                @php
                                                    $statusConfig = [
                                                        'draft' => ['color' => 'secondary', 'icon' => 'fa-edit', 'text' => 'Borrador'],
                                                        'active' => ['color' => 'success', 'icon' => 'fa-check', 'text' => 'Activo'],
                                                        'completed' => ['color' => 'primary', 'icon' => 'fa-check-circle', 'text' => 'Completado'],
                                                        'cancelled' => ['color' => 'danger', 'icon' => 'fa-times', 'text' => 'Cancelado'],
                                                        'on_hold' => ['color' => 'warning', 'icon' => 'fa-pause', 'text' => 'En Espera']
                                                    ];
                                                    $currentStatus = $plan->status ?? 'draft';
                                                    $config = $statusConfig[$currentStatus] ?? ['color' => 'secondary', 'icon' => 'fa-question', 'text' => 'Sin estado'];
                                                @endphp
                                                <div class="dropdown" onclick="event.stopPropagation(); closeOtherDropdowns(this);">
                                                    <button class="btn btn-link p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true">
                                                        <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }}" style="cursor: pointer;">
                                                            <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                                        </span>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1050;">
                                                        @php
                                                            // Estados disponibles
                                                            $validStatuses = ['draft', 'active', 'completed', 'cancelled', 'on_hold'];
                                                            
                                                            // Definir estados permitidos según el estado actual y rol
                                                            $currentStatus = $plan->status ?? 'draft';
                                                            $allowedStatuses = [];
                                                            
                                                            // Verificar rol del usuario
                                                            $userRole = 'admin'; // Temporal para testing
                                                            if (auth()->user()->email === 'admin@dentaris.com') {
                                                                $userRole = 'admin';
                                                            } elseif (auth()->user()->staff && auth()->user()->staff->specialty) {
                                                                $specialty = strtolower(auth()->user()->staff->specialty);
                                                                if (str_contains($specialty, 'doctor') || str_contains($specialty, 'dentista') || 
                                                                    str_contains($specialty, 'odontolog') || str_contains($specialty, 'ortodoncia') ||
                                                                    str_contains($specialty, 'endodoncia') || str_contains($specialty, 'cirugia') ||
                                                                    str_contains($specialty, 'periodoncia') || str_contains($specialty, 'prostodoncia') ||
                                                                    str_contains($specialty, 'protesis') || str_contains($specialty, 'pediatria') ||
                                                                    str_contains($specialty, 'odontopediatria') || str_contains($specialty, 'odontopediatría') ||
                                                                    str_contains($specialty, 'oral') || str_contains($specialty, 'dental')) {
                                                                    $userRole = 'doctor';
                                                                } elseif (str_contains($specialty, 'enfermer') || str_contains($specialty, 'nurse') || str_contains($specialty, 'asistente')) {
                                                                    $userRole = 'assistant';
                                                                } elseif (str_contains($specialty, 'recepcion') || str_contains($specialty, 'reception')) {
                                                                    $userRole = 'receptionist';
                                                                } else {
                                                                    $userRole = 'assistant';
                                                                }
                                                            } else {
                                                                $userRole = 'assistant';
                                                            }
                                                            
                                                            // Lógica por rol
                                                            switch($userRole) {
                                                                case 'admin':
                                                                    // Admin puede cambiar a cualquier estado
                                                                    $allowedStatuses = $validStatuses;
                                                                    break;
                                                                    
                                                                case 'doctor':
                                                                    // Doctor puede cambiar estados de sus propios planes
                                                                    if ($plan->staff_id === (auth()->user()->staff->id ?? null)) {
                                                                        switch($currentStatus) {
                                                                            case 'draft':
                                                                                $allowedStatuses = ['active', 'cancelled'];
                                                                                break;
                                                                            case 'active':
                                                                                $allowedStatuses = ['on_hold', 'completed', 'cancelled'];
                                                                                break;
                                                                            case 'on_hold':
                                                                                $allowedStatuses = ['active', 'cancelled'];
                                                                                break;
                                                                            case 'completed':
                                                                                // Solo admin puede reabrir
                                                                                $allowedStatuses = [];
                                                                                break;
                                                                            case 'cancelled':
                                                                                $allowedStatuses = ['draft', 'active']; // Reactivar
                                                                                break;
                                                                            default:
                                                                                $allowedStatuses = ['active', 'cancelled'];
                                                                        }
                                                                    } else {
                                                                        $allowedStatuses = []; // No puede cambiar planes de otros doctores
                                                                    }
                                                                    break;
                                                                    
                                                                case 'receptionist':
                                                                    // Recepcionista solo puede activar planes en borrador
                                                                    switch($currentStatus) {
                                                                        case 'draft':
                                                                            $allowedStatuses = ['active', 'cancelled'];
                                                                            break;
                                                                        case 'active':
                                                                            $allowedStatuses = ['on_hold', 'cancelled'];
                                                                            break;
                                                                        case 'on_hold':
                                                                            $allowedStatuses = ['active', 'cancelled'];
                                                                            break;
                                                                        default:
                                                                            $allowedStatuses = [];
                                                                    }
                                                                    break;
                                                                    
                                                                case 'assistant':
                                                                    // Asistente puede cambiar a estados específicos
                                                                    switch($currentStatus) {
                                                                        case 'active':
                                                                            $allowedStatuses = ['on_hold', 'completed'];
                                                                            break;
                                                                        case 'on_hold':
                                                                            $allowedStatuses = ['active'];
                                                                            break;
                                                                        default:
                                                                            $allowedStatuses = [];
                                                                    }
                                                                    break;
                                                                    
                                                                default:
                                                                    $allowedStatuses = [];
                                                            }
                                                            
                                                            // Filtrar estados según los permitidos
                                                            $allowedStatusesCollection = collect($validStatuses)->filter(function($status) use ($allowedStatuses) {
                                                                return in_array($status, $allowedStatuses);
                                                            });
                                                        @endphp
                                                        
                                                        @if($allowedStatusesCollection->count() > 0)
                                                            @foreach($allowedStatusesCollection as $statusValue)
                                                                @php
                                                                    $statusText = \App\Models\TreatmentPlan::getStatusOptions()[$statusValue] ?? $statusValue;
                                                                    $optionConfig = $statusConfig[$statusValue];
                                                                @endphp
                                                                <li>
                                                                    <a class="dropdown-item d-flex align-items-center" 
                                                                       href="#" 
                                                                       onclick="updateTreatmentPlanStatus({{ $plan->id }}, '{{ $statusValue }}', '{{ $optionConfig['color'] }}', '{{ $optionConfig['icon'] }}', '{{ $optionConfig['text'] }}'); return false;">
                                                                        <span class="badge bg-{{ $optionConfig['color'] }}-subtle text-{{ $optionConfig['color'] }} me-2">
                                                                            <i class="fas {{ $optionConfig['icon'] }} me-1"></i>{{ $optionConfig['text'] }}
                                                                        </span>
                                                                    </a>
                                                                </li>
                                                            @endforeach
                                                        @else
                                                            <li>
                                                                <span class="dropdown-item-text text-muted">
                                                                    <i class="fas fa-lock me-2"></i>No se pueden realizar cambios
                                                                </span>
                                                            </li>
                                                        @endif
                                                    </ul>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <div class="progress flex-grow-1 me-2" style="height: 6px;">
                                                        <div class="progress-bar bg-success" style="width: {{ $plan->progress ?? 0 }}%"></div>
                                                    </div>
                                                    <small class="text-muted">{{ $plan->progress ?? 0 }}%</small>
                                                </div>
                                    </td>
                                    <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('treatment-plans.show', $plan) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    @if($plan->canBeModified())
                                                        <a href="{{ route('treatment-plans.edit', $plan) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                            <i class="fas fa-edit"></i>
                                                        </a>
                                                    @else
                                                        <button class="btn btn-sm btn-outline-secondary" title="No se puede editar ({{ ucfirst($plan->status) }})" disabled>
                                                            <i class="fas fa-lock"></i>
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" 
                                                            data-bs-toggle="modal" data-bs-target="#deleteModal{{ $plan->id }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                        </div>
                                    </td>
                                </tr>

                                        <!-- Modal de confirmación de eliminación -->
                                        <div class="modal fade" id="deleteModal{{ $plan->id }}" tabindex="-1">
                                            <div class="modal-dialog">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <h5 class="modal-title">Confirmar Eliminación</h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                    </div>
                                                    <div class="modal-body">
                                                        <p>¿Estás seguro de que deseas eliminar el plan de tratamiento <strong>{{ $plan->treatment_name ?? 'Sin nombre' }}</strong>?</p>
                                                        <p class="text-muted">Esta acción no se puede deshacer.</p>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                                                        <form method="POST" action="{{ route('treatment-plans.destroy', $plan) }}" class="d-inline">
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
                            {{ $treatmentPlans->links('vendor.pagination.bootstrap-5') }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="mb-3">
                                <i class="fas fa-clipboard-list fa-3x text-muted"></i>
                            </div>
                            <h5 class="text-muted">No hay planes de tratamiento registrados</h5>
                            <p class="text-muted">Comienza creando tu primer plan de tratamiento.</p>
                            <a href="{{ route('treatment-plans.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Nuevo Plan
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
    const filtersCollapse = document.getElementById('treatmentFiltersCollapse');
    const filterButton = document.querySelector('[data-bs-target="#treatmentFiltersCollapse"]');
    const filterText = document.querySelector('.filter-text');
    
    // Recuperar estado del localStorage
    const savedState = localStorage.getItem('treatment-plans-filters-collapse');
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
        localStorage.setItem('treatment-plans-filters-collapse', 'true');
        filterText.textContent = 'Ocultar Filtros';
        filterButton.setAttribute('aria-expanded', 'true');
    });
    
    filtersCollapse.addEventListener('hidden.bs.collapse', function() {
        localStorage.setItem('treatment-plans-filters-collapse', 'false');
        filterText.textContent = 'Mostrar Filtros';
        filterButton.setAttribute('aria-expanded', 'false');
    });
    
    // Toast de éxito
    const successToast = document.getElementById('successToast');
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


// Función para cerrar otros dropdowns
function closeOtherDropdowns(currentDropdown) {
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

// Función para actualizar el estado del plan de tratamiento
function updateTreatmentPlanStatus(planId, statusValue, statusColor, statusIcon, statusText) {
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
    
    fetch(`/treatment-plans/${planId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: statusValue })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Actualizar el badge visualmente
            badge.className = `badge bg-${statusColor}-subtle text-${statusColor}`;
            badge.innerHTML = `<i class="fas ${statusIcon} me-1"></i>${statusText}`;
            
            // Mostrar toast de éxito
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
        showToast('Error al actualizar el estado del plan de tratamiento', 'error');
    });
}

// Función para mostrar toast
function showToast(message, type = 'success') {
    const toastContainer = createToastContainer();
    const toastId = 'toast-' + Date.now();
    const isSuccess = type === 'success';
    const headerIcon = isSuccess ? 'fa-check-circle' : 'fa-exclamation-triangle';
    const bodyIcon = isSuccess ? 'fa-check' : 'fa-times';
    
    const toastHTML = `
        <div id="${toastId}" class="toast" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="toast-header">
                <div class="avatar avatar-xs avatar-label-${isSuccess ? 'success' : 'danger'} me-2">
                    <i class="fas ${headerIcon} fs-12"></i>
                </div>
                <strong class="me-auto text-white">${isSuccess ? 'Éxito' : 'Error'}</strong>
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
    let container = document.querySelector('.toast-container');
    if (!container) {
        container = document.createElement('div');
        container.className = 'toast-container position-fixed top-0 end-0 p-3';
        container.style.zIndex = '1055';
        document.body.appendChild(container);
    }
    return container;
}

// Inicializar filtros collapse
initializeFiltersCollapse('treatment-plans');
</script>
@endsection
