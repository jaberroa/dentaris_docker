@extends('layouts.master')

@section('title')
    Gestión de Citas
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
    
    
    .table tbody tr {
        transition: all 0.2s ease;
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
    
    /* Estilos para el color púrpura personalizado */
    .bg-purple-subtle {
        background-color: #f3e8ff !important;
    }
    
    .text-purple {
        color: #8b5cf6 !important;
    }
    
    /* Estilos para dropdown de estado */
    .dropdown-menu {
        border: none;
        box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
        border-radius: 0.5rem;
        padding: 0.5rem 0;
    }
    
    .dropdown-item {
        padding: 0.5rem 1rem;
        border-radius: 0.25rem;
        margin: 0 0.25rem;
        transition: all 0.15s ease-in-out;
    }
    
    .dropdown-item:hover {
        background-color: #f8f9fa;
        transform: translateX(2px);
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
    
    /* Estilos para dropdown de estado - evitar superposición */
    .table .dropdown-menu {
        z-index: 9999 !important;
        position: absolute !important;
        min-width: 200px;
        box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.1), 0 0 0 0.5rem rgba(0, 0, 0, 0.05);
        border: 1px solid rgba(0, 0, 0, 0.1);
        border-radius: 0.375rem;
        overflow: hidden;
    }
    
    .table .dropdown {
        position: relative;
    }
    
    .table .dropdown .btn-link {
        background: transparent;
        border: none;
        padding: 0;
        margin: 0;
    }
    
    .table .dropdown .btn-link:focus {
        box-shadow: none;
    }
    
    .table .dropdown .badge {
        transition: all 0.15s ease-in-out;
    }
    
    .table .dropdown .badge:hover {
        transform: scale(1.05);
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
    }
    
    /* Asegurar que el dropdown no se superponga con otros elementos */
    .table tbody tr {
        position: relative;
        z-index: 1;
    }
    
    .table tbody tr:hover {
        z-index: 2;
    }
    
    .table tbody tr:has(.dropdown.show) {
        z-index: 9998;
    }
    
    .table tbody tr:has(.dropdown.show) ~ tr {
        z-index: 1;
    }
    
    .table tbody tr:has(.dropdown.show) ~ tr:hover {
        z-index: 1;
    }
    
    /* Forzar que todas las filas tengan z-index bajo cuando hay dropdown abierto */
    .table:has(.dropdown.show) tbody tr {
        z-index: 1 !important;
    }
    
    .table:has(.dropdown.show) tbody tr:hover {
        z-index: 1 !important;
    }
    
    .table:has(.dropdown.show) tbody tr:has(.dropdown.show) {
        z-index: 9998 !important;
    }
    
    .table .dropdown.show {
        z-index: 9999;
    }
    
    .table .dropdown.show .badge {
        z-index: 10000;
    }
</style>
@endsection

@php
$getAppointmentSortUrl = static function ($field, $currentSort, $currentDirection) {
    $direction = ($currentSort === $field && $currentDirection === 'asc') ? 'desc' : 'asc';
    $params = request()->query();
    $params['sort'] = $field;
    $params['direction'] = $direction;
    return request()->url() . '?' . http_build_query($params);
};

$getAppointmentSortIcon = static function ($field, $currentSort, $currentDirection) {
    if ($currentSort !== $field) {
        return '<i class="fas fa-sort sort-icon"></i>';
    }
    if ($currentDirection === 'asc') {
        return '<i class="fas fa-sort-up sort-icon active"></i>';
    } else {
        return '<i class="fas fa-sort-down sort-icon active"></i>';
    }
};
@endphp

@section('content')
<div class="container-fluid">

    <!-- Header Principal -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">GESTIÓN DE CITAS</h4>
                    <p class="text-muted mb-3">Administra las citas médicas de la clínica y su programación.</p>
                </div>
                <div class="page-title-right">
                    <div class="d-flex gap-2">
                        <div class="dropdown">
                            <button class="btn btn-outline-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                <i class="fas fa-calendar-alt me-1"></i>
                                Vistas
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="{{ route('appointments.index') }}">
                                    <i class="fas fa-list me-2"></i>Lista
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('appointments.weekly') }}">
                                    <i class="fas fa-calendar-week me-2"></i>Semanal
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('appointments.monthly') }}">
                                    <i class="fas fa-calendar-alt me-2"></i>Mensual
                                </a></li>
                                <li><a class="dropdown-item" href="{{ route('appointments.yearly') }}">
                                    <i class="fas fa-calendar me-2"></i>Anual
                                </a></li>
                            </ul>
                        </div>
                        <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            + Nueva Cita
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Filtros y Búsqueda -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center gap-2">
                        <h6 class="card-title mb-0">Filtros de Búsqueda</h6>
                        <button class="btn btn-sm btn-outline-primary" type="button" data-bs-toggle="collapse" data-bs-target="#appointmentFiltersCollapse" aria-expanded="false" aria-controls="filtersCollapse">
                            <i class="fas fa-filter me-1"></i>
                            <span class="filter-text">Mostrar Filtros</span>
                        </button>
                    </div>
                </div>
                <div class="collapse" id="appointmentFiltersCollapse">
                    <div class="card-body">
                        <form method="GET" action="{{ route('appointments.index') }}" class="row g-3">
                        <div class="col-md-4">
                            <label for="appointment-search" class="form-label">Buscar</label>
                            <input type="text" class="form-control" id="appointment-search" name="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Paciente, código, doctor o notas">
                        </div>
                        <div class="col-md-2">
                            <label for="appointment-status" class="form-label">Estado</label>
                            <select class="form-select" id="appointment-status" name="status">
                                <option value="">Todos</option>
                                <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Programada</option>
                                <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmada</option>
                                <option value="in_progress" {{ request('status') == 'in_progress' ? 'selected' : '' }}>En Progreso</option>
                                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completada</option>
                                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelarada</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="appointment-from" class="form-label">Desde</label>
                            <input type="date" class="form-control" id="appointment-from" name="created_from" 
                                   value="{{ request('created_from') }}">
                        </div>
                        <div class="col-md-2">
                            <label for="appointment-to" class="form-label">Hasta</label>
                            <input type="date" class="form-control" id="appointment-to" name="created_to" 
                                   value="{{ request('created_to') }}">
                        </div>
                        <div class="col-auto">
                            <label class="form-label">&nbsp;</label>
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i>
                                </button>
                                <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
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

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="appointmentSuccessToast">
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
                            <i class="fas fa-calendar-alt fs-12"></i>
                        </div>
                        <span class="text-muted">{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Listado de Citas -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-calendar-alt fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Lista de Citas</h4>
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
                                Registros por página
                            </div>
                        </div>
                        
                        <!-- Botón de exportar -->
                        <div class="dropdown">
                            <button class="btn btn-sm btn-outline-primary dropdown-toggle" data-bs-toggle="dropdown">
                                <i class="fas fa-download me-1"></i> Exportarar
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
                    @if($appointments->count() > 0)
                    <div class="table-responsive">
                            <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>
                                        <a href="{{ $getAppointmentSortUrl('id', $sortField ?? '', $sortDirection ?? '') }}">
                                            Código
                                            {!! $getAppointmentSortIcon('id', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getAppointmentSortUrl('patient_name', $sortField ?? '', $sortDirection ?? '') }}">
                                            Paciente
                                            {!! $getAppointmentSortIcon('patient_name', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getAppointmentSortUrl('appointment_date', $sortField ?? '', $sortDirection ?? '') }}">
                                            Fecha y Hora
                                            {!! $getAppointmentSortIcon('appointment_date', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getAppointmentSortUrl('staff_name', $sortField ?? '', $sortDirection ?? '') }}">
                                            Odontólogo
                                            {!! $getAppointmentSortIcon('staff_name', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getAppointmentSortUrl('type', $sortField ?? '', $sortDirection ?? '') }}">
                                            Tipo
                                            {!! $getAppointmentSortIcon('type', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>
                                        <a href="{{ $getAppointmentSortUrl('status', $sortField ?? '', $sortDirection ?? '') }}">
                                            Estado
                                            {!! $getAppointmentSortIcon('status', $sortField ?? '', $sortDirection ?? '') !!}
                                        </a>
                                    </th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                    @foreach($appointments as $appointment)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold">{{ $appointment->patient->patient_code ?? 'N/A' }}</span>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-sm avatar-label-primary me-3">
                                                    @if($appointment->patient->gender === 'male')
                                                        <i class="fas fa-male text-primary"></i>
                                                    @elseif($appointment->patient->gender === 'female')
                                                        <i class="fas fa-female text-danger"></i>
                                                    @else
                                                        <i class="fas fa-user text-secondary"></i>
                                                    @endif
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $appointment->patient->first_name ?? 'N/A' }} {{ $appointment->patient->last_name ?? '' }}</h6>
                                                    <small class="text-muted"><i class="fas fa-phone me-1"></i>{{ $appointment->patient->phone ?? 'Sin teléfono' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <div>
                                                <h6 class="mb-0">{{ $appointment->appointment_date ? $appointment->appointment_date->format('d/m/Y') : 'N/A' }}</h6>
                                                <small class="text-muted">{{ $appointment->appointment_time ?? 'Sin hora' }}</small>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div class="avatar avatar-xs me-2">
                                                    <i class="fas fa-user-md text-primary"></i>
                                                </div>
                                                <div>
                                                    <h6 class="mb-0">{{ $appointment->staff->user->name ?? 'N/A' }}</h6>
                                                    <small class="text-muted">{{ $appointment->staff->specialty ?? 'Sin especialidad' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $typeColors = [
                                                    'consultation' => 'primary',
                                                    'consulta' => 'primary',
                                                    'treatment' => 'success',
                                                    'tratamiento' => 'success',
                                                    'cleaning' => 'info',
                                                    'limpieza' => 'info',
                                                    'emergency' => 'danger',
                                                    'emergencia' => 'danger',
                                                    'follow_up' => 'warning',
                                                    'seguimiento' => 'warning',
                                                    'ortodoncia' => 'primary',
                                                    'blanqueamiento' => 'info',
                                                    'puente' => 'success',
                                                    'implante' => 'success',
                                                    'extracción' => 'warning',
                                                    'endodoncia' => 'danger'
                                                ];
                                                $appointmentTipo = strtolower($appointment->type ?? '');
                                                $typeColor = $typeColors[$appointmentTipo] ?? 'secondary';
                                            @endphp
                                            <span class="badge bg-{{ $typeColor }}-subtle text-{{ $typeColor }}">
                                                {{ $appointment->type ?? 'Sin tipo' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $statusConfig = [
                                                    'scheduled' => ['color' => 'warning', 'icon' => 'fa-clock', 'text' => 'Programadas'],
                                                    'confirmed' => ['color' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Confirmadas'],
                                                    'waiting' => ['color' => 'info', 'icon' => 'fa-hourglass-half', 'text' => 'En Espera'],
                                                    'in_progress' => ['color' => 'info', 'icon' => 'fa-play-circle', 'text' => 'En Progreso'],
                                                    'completed' => ['color' => 'primary', 'icon' => 'fa-check-double', 'text' => 'Completadas'],
                                                    'cancelled' => ['color' => 'danger', 'icon' => 'fa-times-circle', 'text' => 'Cancelaradas'],
                                                    'rescheduled' => ['color' => 'secondary', 'icon' => 'fa-sync-alt', 'text' => 'Reprogramadas'],
                                                    'no_show' => ['color' => 'dark', 'icon' => 'fa-user-times', 'text' => 'No Asistió']
                                                ];
                                                $status = $appointment->status->name ?? 'Pendiente';
                                                $config = $statusConfig[$status] ?? ['color' => 'secondary', 'icon' => 'fa-question-circle', 'text' => 'Sin estado'];
                                            @endphp
                                            
                                            <div class="dropdown" onclick="event.stopPropagation();">
                                                <button class="btn btn-link p-0 border-0" type="button" data-bs-toggle="dropdown" aria-expanded="false" data-bs-auto-close="true">
                                                    <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }}" style="cursor: pointer;">
                                                <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                        </span>
                                                </button>
                                                <ul class="dropdown-menu dropdown-menu-end" style="z-index: 1050;">
                                                    @php
                                                        $validStatuses = ['scheduled', 'confirmed', 'waiting', 'in_progress', 'completed', 'cancelled', 'rescheduled', 'no_show'];
                                                        $filteredStatuses = $statuses->whereIn('name', $validStatuses);
                                                        
                                                        // Definir estados permitidos según el estado actual
                                                        $currentStatus = $appointment->status->name ?? 'scheduled';
                                                        $allowedStatuses = [];
                                                        
                                                        // Verificar si es admin
                                                        $isAdmin = auth()->user()->email === 'admin@dentaris.com';
                                                        
                                                        if ($isAdmin) {
                                                            // Admin puede cambiar a cualquier estado
                                                            $allowedStatuses = $validStatuses;
                                                        } else {
                                                            // Lógica normal para otros roles
                                                            switch($currentStatus) {
                                                                case 'scheduled':
                                                                    $allowedStatuses = ['confirmed', 'cancelled', 'rescheduled', 'no_show'];
                                                                    break;
                                                                case 'confirmed':
                                                                    $allowedStatuses = ['waiting', 'in_progress', 'cancelled', 'rescheduled', 'no_show', 'completed'];
                                                                    break;
                                                                case 'waiting':
                                                                    $allowedStatuses = ['in_progress', 'cancelled', 'rescheduled', 'no_show'];
                                                                    break;
                                                                case 'in_progress':
                                                                    $allowedStatuses = ['completed', 'cancelled', 'rescheduled'];
                                                                    break;
                                                                case 'completed':
                                                                    // Solo admin puede revertir
                                                                    $allowedStatuses = [];
                                                                    break;
                                                                case 'cancelled':
                                                                case 'rescheduled':
                                                                    // Reactivación
                                                                    $allowedStatuses = ['scheduled', 'confirmed'];
                                                                    break;
                                                                case 'no_show':
                                                                    // Segunda oportunidad
                                                                    $allowedStatuses = ['scheduled', 'confirmed', 'cancelled'];
                                                                    break;
                                                                default:
                                                                    $allowedStatuses = ['confirmed', 'waiting', 'cancelled', 'rescheduled', 'no_show'];
                                                            }
                                                        }
                                                        
                                                        // Filtrar estados según los permitidos
                                                        $allowedStatusesCollection = $filteredStatuses->whereIn('name', $allowedStatuses);
                                                    @endphp
                                                    
                                                    @if($allowedStatusesCollection->count() > 0)
                                                        @foreach($allowedStatusesCollection as $statusOption)
                                                            @php
                                                                $optionConfig = $statusConfig[$statusOption->name] ?? ['color' => 'secondary', 'icon' => 'fa-question-circle', 'text' => $statusOption->display_name];
                                                            @endphp
                                                            <li>
                                                                <a class="dropdown-item d-flex align-items-center" 
                                                                   href="#" 
                                                                   onclick="updateAppointmentStatus({{ $appointment->id }}, {{ $statusOption->id }}, '{{ $statusOption->name }}', '{{ $optionConfig['color'] }}', '{{ $optionConfig['icon'] }}', '{{ $optionConfig['text'] }}'); return false;">
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
                                        <div class="d-flex gap-1" onclick="event.stopPropagation();">
                                            <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-sm btn-outline-warning" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger" title="Eliminar" 
                                                    data-bs-toggle="modal" data-bs-target="#deleteModal{{ $appointment->id }}">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                <!-- Modal de confirmación de eliminación -->
                                <div class="modal fade" id="deleteModal{{ $appointment->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Confirmar Eliminación</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>¿Estás seguro de que deseas eliminar la cita del paciente <strong>{{ $appointment->patient->first_name ?? 'N/A' }} {{ $appointment->patient->last_name ?? '' }}</strong>?</p>
                                                <p class="text-muted">Esta acción no se puede deshacer.</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelarar</button>
                                                <form method="POST" action="{{ route('appointments.destroy', $appointment) }}" class="d-inline">
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
                                    Mostrando {{ $appointments->firstItem() }} a {{ $appointments->lastItem() }} 
                                    de {{ $appointments->total() }} registros
                                </div>
                            </div>
                            <div class="col-sm-12 col-md-7">
                                <div class="dataTables_paginate paging_simple_numbers">
                                    {{ $appointments->links('vendor.pagination.bootstrap-5') }}
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar avatar-xl avatar-label-primary mx-auto mb-3">
                                <i class="fas fa-calendar-times fs-24"></i>
                            </div>
                            <h5 class="text-muted">No hay citas programadas</h5>
                            <p class="text-muted mb-4">Comienza programando tu primera cita.</p>
                            <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                                <i class="fas fa-plus me-1"></i>
                                Programar Primera Cita
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal de Confirmación de Eliminación -->
<div class="modal fade" id="deleteConfirmationModal" tabindex="-1" aria-labelledby="deleteConfirmationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteConfirmationModalLabel">Confirmar Eliminación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas eliminar esta cita? Esta acción no se puede deshacer.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelarar</button>
                <form id="appointmentDeleteForm" method="POST" style="display: inline;">
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
document.addEventListener('DOMContentLoaded', function() {
    // Gestión de estado de filtros con localStorage
    const filtersCollapse = document.getElementById('appointmentFiltersCollapse');
    const filterButton = document.querySelector('[data-bs-target="#appointmentFiltersCollapse"]');
    const filterText = document.querySelector('.filter-text');
    
    // Recuperar estado del localStorage
    const savedState = localStorage.getItem('appointments-filters-collapse');
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
        localStorage.setItem('appointments-filters-collapse', 'true');
            filterText.textContent = 'Ocultar Filtros';
        filterButton.setAttribute('aria-expanded', 'true');
        });
        
    filtersCollapse.addEventListener('hidden.bs.collapse', function() {
        localStorage.setItem('appointments-filters-collapse', 'false');
            filterText.textContent = 'Mostrar Filtros';
        filterButton.setAttribute('aria-expanded', 'false');
        });
});

// Inicializar toast de éxito
document.addEventListener('DOMContentLoaded', function() {
        const successToast = document.getElementById('appointmentSuccessToast');
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
        
        // Configurar dropdowns para evitar superposición
        const dropdowns = document.querySelectorAll('.table .dropdown');
        dropdowns.forEach(dropdown => {
            const button = dropdown.querySelector('[data-bs-toggle="dropdown"]');
            const menu = dropdown.querySelector('.dropdown-menu');
            
            if (button && menu) {
                // Asegurar que el dropdown se cierre al hacer clic fuera
                button.addEventListener('click', function(e) {
                    e.stopPropagation();
                });
                
                // Cerrar otros dropdowns cuando se abre uno nuevo
                button.addEventListener('show.bs.dropdown', function() {
                    // Cerrar todos los otros dropdowns
                    dropdowns.forEach(otherDropdown => {
                        if (otherDropdown !== dropdown) {
                            const otherButton = otherDropdown.querySelector('[data-bs-toggle="dropdown"]');
                            if (otherButton) {
                                const bsDropdown = bootstrap.Dropdown.getInstance(otherButton);
                                if (bsDropdown) {
                                    bsDropdown.hide();
                                }
                            }
                        }
                    });
                });
                
                // Cerrar dropdown solo al hacer clic fuera o en otro estado
                document.addEventListener('click', function(e) {
                    // Si el clic es en otro dropdown de estado, cerrar el actual
                    if (e.target.closest('.table .dropdown') && !dropdown.contains(e.target)) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(button);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                    // Si el clic es completamente fuera de cualquier dropdown, cerrar todos
                    else if (!e.target.closest('.table .dropdown')) {
                        const bsDropdown = bootstrap.Dropdown.getInstance(button);
                        if (bsDropdown) {
                            bsDropdown.hide();
                        }
                    }
                });
            }
        });
});

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

    function confirmEliminar(appointmentId) {
        const form = document.getElementById('appointmentDeleteForm');
    form.action = `/appointments/${appointmentId}`;
    const deleteModal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
    deleteModal.show();
}

// Función para actualizar estado de cita
function updateAppointmentStatus(appointmentId, statusId, statusName, statusColor, statusIcon, statusText) {
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
    
    fetch(`/appointments/${appointmentId}/status`, {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            status_id: statusId
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
        showToast('Error al actualizar el estado', 'error');
    });
}

// Función auxiliar para mostrar toasts
function showToast(message, type = 'success') {
    // Crear el toast dinámicamente
    const toastContainer = document.querySelector('.toast-container') || createToastContainer();
    const toastId = 'toast-' + Date.now();
    
    const toastHTML = `
        <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="${toastId}">
            <div class="toast-header text-white border-0 bg-${type === 'success' ? 'success' : 'danger'}">
                <div class="avatar avatar-xs avatar-label-light me-2">
                    <i class="fas fa-${type === 'success' ? 'check' : 'exclamation-triangle'} fs-12"></i>
                </div>
                <strong class="me-auto">${type === 'success' ? '¡Éxito!' : 'Error'}</strong>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
            <div class="toast-body bg-light">
                <div class="d-flex align-items-center">
                    <div class="avatar avatar-xs avatar-label-${type === 'success' ? 'success' : 'danger'} me-2">
                        <i class="fas fa-calendar fs-12"></i>
                    </div>
                    <span class="text-muted">${message}</span>
                </div>
            </div>
        </div>
    `;
    
    toastContainer.insertAdjacentHTML('beforeend', toastHTML);
    
    // Inicializar el toast de Bootstrap
    const toastElement = document.getElementById(toastId);
    const toast = new bootstrap.Toast(toastElement, {
        autohide: false,
        delay: 0
    });
    toast.show();
    
    // Auto-ocultar después de 3 segundos
    setTimeout(() => {
        if (toastElement) {
            toastElement.classList.add('fade-out');
            setTimeout(() => toastElement.remove(), 500);
        }
    }, 3000);
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
