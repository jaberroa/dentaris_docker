@extends('layouts.master')

@section('title')
    Vista Anual - Citas
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('css')
<link href="{{ asset('css/calendar-custom.css') }}" rel="stylesheet" />
<style>
    .calendar-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        color: #495057;
        border-radius: 10px 10px 0 0;
        padding: 20px;
        border-bottom: 1px solid #dee2e6;
    }
    
    .calendar-nav {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 20px;
    }
    
    .calendar-title {
        font-size: 24px;
        font-weight: 600;
        margin: 0;
    }
    
    .calendar-controls {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    
    .view-selector {
        position: relative;
    }
    
    .view-selector .btn {
        background: #0d6efd;
        border: 1px solid #0d6efd;
        color: white;
        transition: all 0.3s ease;
    }
    
    .view-selector .btn:hover {
        background: #0b5ed7;
        border-color: #0b5ed7;
        transform: translateY(-1px);
        box-shadow: 0 4px 8px rgba(13, 110, 253, 0.3);
    }
    
    .year-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        padding: 20px;
        background-color: #f8f9fa;
        border-radius: 0 0 10px 10px;
    }
    
    .month-card {
        background: white;
        border-radius: 10px;
        padding: 20px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        border: 1px solid #dee2e6;
    }
    
    .month-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 20px rgba(0,0,0,0.15);
    }
    
    .month-header {
        text-align: center;
        margin-bottom: 15px;
        padding-bottom: 10px;
        border-bottom: 2px solid #e9ecef;
    }
    
    .month-name {
        font-size: 18px;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: 5px;
    }
    
    .month-stats {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 15px;
    }
    
    .appointment-count {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 8px 12px;
        border-radius: 20px;
        font-weight: 600;
        font-size: 14px;
    }
    
    .month-mini-calendar {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 2px;
        margin-bottom: 15px;
    }
    
    .mini-day-header {
        text-align: center;
        font-size: 10px;
        font-weight: 600;
        color: #6c757d;
        padding: 5px 0;
    }
    
    .mini-day {
        aspect-ratio: 1;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 10px;
        border-radius: 3px;
        cursor: pointer;
        transition: all 0.2s ease;
        position: relative;
    }
    
    .mini-day:hover {
        background-color: #e9ecef;
    }
    
    .mini-day.has-appointments {
        background-color: #0d6efd;
        color: white;
    }
    
    .mini-day.has-appointments:hover {
        background-color: #0056b3;
    }
    
    .mini-day.today {
        background-color: #28a745;
        color: white;
        font-weight: 700;
    }
    
    .mini-day.other-month {
        color: #adb5bd;
    }
    
    .appointment-list {
        max-height: 120px;
        overflow-y: auto;
    }
    
    .appointment-item {
        background: #f8f9fa;
        border-left: 3px solid #0d6efd;
        padding: 8px 10px;
        margin-bottom: 5px;
        border-radius: 0 4px 4px 0;
        font-size: 11px;
        transition: all 0.2s ease;
    }
    
    .appointment-item:hover {
        background: #e9ecef;
        transform: translateX(2px);
    }
    
    .appointment-item.confirmed {
        border-left-color: #28a745;
    }
    
    .appointment-item.scheduled {
        border-left-color: #0d6efd;
    }
    
    .appointment-item.cancelled {
        border-left-color: #dc3545;
    }
    
    .appointment-item.completed {
        border-left-color: #6c757d;
    }
    
    .appointment-date {
        font-weight: 600;
        color: #495057;
        margin-bottom: 2px;
    }
    
    .appointment-patient {
        color: #6c757d;
        font-size: 10px;
    }
    
    .year-stats {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 20px;
        margin-bottom: 20px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 20px;
    }
    
    .stat-item {
        text-align: center;
        padding: 15px;
        background: white;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .stat-number {
        font-size: 32px;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: 8px;
    }
    
    .stat-label {
        font-size: 14px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    
    .empty-month {
        text-align: center;
        color: #6c757d;
        font-style: italic;
        padding: 20px;
    }
    
    .month-legend {
        display: flex;
        justify-content: center;
        gap: 15px;
        margin-top: 15px;
        font-size: 11px;
    }
    
    .legend-item {
        display: flex;
        align-items: center;
        gap: 5px;
    }
    
    .legend-color {
        width: 12px;
        height: 12px;
        border-radius: 2px;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Vista Anual - Citas</h4>
                    <p class="text-muted mb-0">Resumen anual de citas para el año {{ $year }}</p>
                </div>
                <div class="page-title-right">
                    <div class="d-flex gap-2">
                        <a href="{{ route('appointments.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus me-1"></i>
                            Nueva Cita
                        </a>
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-list me-1"></i>
                            Lista
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    @php
        $totalCitas = collect($months)->sum('appointmentCount');
        $totalConfirmed = collect($months)->sum(function($month) {
            return $month['appointments']->where('status.name', 'confirmed')->count();
        });
        $totalScheduled = collect($months)->sum(function($month) {
            return $month['appointments']->where('status.name', 'scheduled')->count();
        });
        $totalCompleted = collect($months)->sum(function($month) {
            return $month['appointments']->where('status.name', 'completed')->count();
        });
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="year-stats">
                <h5 class="mb-3">Estadísticas del Año {{ $year }}</h5>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">{{ $totalCitas }}</div>
                        <div class="stat-label">Total Citas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $totalConfirmed }}</div>
                        <div class="stat-label">Confirmadas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $totalScheduled }}</div>
                        <div class="stat-label">Programadas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $totalCompleted }}</div>
                        <div class="stat-label">Completadas</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="calendar-header">
                    <div class="calendar-nav">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('appointments.yearly', ['year' => $year - 1]) }}" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <h3 class="calendar-title mb-0">
                                {{ $year }}
                            </h3>
                            <a href="{{ route('appointments.yearly', ['year' => $year + 1]) }}" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                        
                        <div class="calendar-controls">
                            <a href="{{ route('appointments.yearly', ['year' => now()->year]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-calendar-day me-1"></i>
                                Año Actual
                            </a>
                            
                            <div class="view-selector">
                                <div class="dropdown">
                                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-calendar me-1"></i>
                                        Anual
                                    </button>
                                    <ul class="dropdown-menu">
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
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="year-grid">
                    @foreach($months as $month)
                        <div class="month-card">
                            <div class="month-header">
                                <div class="month-name">{{ $month['monthName'] }}</div>
                                <div class="month-stats">
                                    <span class="appointment-count">{{ $month['appointmentCount'] }} citas</span>
                                </div>
                            </div>
                            
                            @if($month['appointmentCount'] > 0)
                                <div class="appointment-list">
                                    @foreach($month['appointments']->take(5) as $appointment)
                                        <a href="{{ route('appointments.show', $appointment) }}" 
                                           class="appointment-item {{ $appointment->status->name ?? 'scheduled' }}" 
                                           data-bs-toggle="tooltip" 
                                           title="{{ $appointment->patient->first_name ?? 'N/A' }} {{ $appointment->patient->last_name ?? '' }} - {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') }} - Click para ver detalles"
                                           style="text-decoration: none; display: block;">
                                            <div class="appointment-date">
                                                {{ \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m') }} - {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}
                                            </div>
                                            <div class="appointment-patient">
                                                {{ $appointment->patient->first_name ?? 'N/A' }} {{ $appointment->patient->last_name ?? '' }}
                                            </div>
                                        </a>
                                    @endforeach
                                    
                                    @if($month['appointmentCount'] > 5)
                                        <div class="text-center mt-2">
                                            <a href="{{ route('appointments.monthly', ['date' => $year . '-' . str_pad($month['month'], 2, '0', STR_PAD_LEFT)]) }}" 
                                               class="text-muted text-decoration-none"
                                               data-bs-toggle="tooltip" 
                                               title="Ver todas las citas de {{ $month['monthName'] }}">
                                                <small>+{{ $month['appointmentCount'] - 5 }} más citas</small>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @else
                                <div class="empty-month">
                                    <i class="fas fa-calendar-times fa-2x mb-2"></i>
                                    <div>Sin citas programadas</div>
                                </div>
                            @endif
                            
                            <div class="month-legend">
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #0d6efd;"></div>
                                    <span>Programada</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #28a745;"></div>
                                    <span>Confirmada</span>
                                </div>
                                <div class="legend-item">
                                    <div class="legend-color" style="background-color: #6c757d;"></div>
                                    <span>Completada</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection

@section('scripts')
<script>
    // Inicializar tooltips
    var tooltipTriggerLista = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    var tooltipLista = tooltipTriggerLista.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
    
    // Navegación con teclado
    document.addEventListener('keydown', function(e) {
        if (e.altKey) {
            switch(e.key) {
                case 'ArrowLeft':
                    e.preventDefault();
                    window.location.href = '{{ route("appointments.yearly", ["year" => $year - 1]) }}';
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    window.location.href = '{{ route("appointments.yearly", ["year" => $year + 1]) }}';
                    break;
                case 't':
                    e.preventDefault();
                    window.location.href = '{{ route("appointments.yearly", ["year" => now()->year]) }}';
                    break;
            }
        }
    });
</script>
@endsection
