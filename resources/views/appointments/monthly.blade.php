@extends('layouts.master')

@section('title')
    Vista Mensual - Citas
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
    
    .month-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 1px;
        background-color: #dee2e6;
        border-radius: 0 0 10px 10px;
        overflow: hidden;
    }
    
    .day-header {
        background-color: #e9ecef;
        padding: 15px 10px;
        text-align: center;
        border-bottom: 2px solid #dee2e6;
        font-weight: 600;
        color: #495057;
        font-size: 14px;
    }
    
    .day-cell {
        background-color: white;
        min-height: 120px;
        padding: 8px;
        border-bottom: 1px solid #dee2e6;
        position: relative;
        transition: background-color 0.2s ease;
    }
    
    .day-cell:hover {
        background-color: #f8f9fa;
    }
    
    .day-cell.other-month {
        background-color: #f8f9fa;
        color: #6c757d;
    }
    
    .day-cell.today {
        background-color: #e3f2fd;
        border: 2px solid #0d6efd;
    }
    
    .day-number {
        font-weight: 600;
        font-size: 16px;
        margin-bottom: 5px;
        color: #495057;
    }
    
    .day-cell.today .day-number {
        color: #0d6efd;
        font-weight: 700;
    }
    
    .day-cell.other-month .day-number {
        color: #adb5bd;
    }
    
    .appointment-item {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 10px;
        margin-bottom: 2px;
        cursor: pointer;
        transition: all 0.2s ease;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    
    .appointment-item:hover {
        transform: scale(1.02);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    
    .appointment-item.confirmed {
        background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
    }
    
    .appointment-item.scheduled {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
    }
    
    .appointment-item.cancelled {
        background: linear-gradient(135deg, #dc3545 0%, #fd7e14 100%);
    }
    
    .appointment-item.completed {
        background: linear-gradient(135deg, #6c757d 0%, #495057 100%);
    }
    
    .appointment-time {
        font-weight: 600;
        font-size: 9px;
    }
    
    .appointment-patient {
        font-size: 9px;
        opacity: 0.9;
    }
    
    .more-appointments {
        background-color: #6c757d;
        color: white;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 9px;
        text-align: center;
        cursor: pointer;
        margin-top: 2px;
    }
    
    .more-appointments:hover {
        background-color: #495057;
    }
    
    .empty-day {
        display: flex;
        align-items: center;
        justify-content: center;
        height: 100%;
        color: #6c757d;
        font-style: italic;
        font-size: 12px;
    }
    
    .month-stats {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        padding: 15px;
        margin-bottom: 20px;
    }
    
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
        gap: 15px;
    }
    
    .stat-item {
        text-align: center;
        padding: 10px;
        background: white;
        border-radius: 6px;
        border: 1px solid #dee2e6;
    }
    
    .stat-number {
        font-size: 24px;
        font-weight: 700;
        color: #0d6efd;
        margin-bottom: 5px;
    }
    
    .stat-label {
        font-size: 12px;
        color: #6c757d;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Vista Mensual - Citas</h4>
                    <p class="text-muted mb-0">Gestiona las citas del mes de {{ $startOfMonth->format('F Y') }}</p>
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
        $totalCitas = collect($calendarDays)->sum(function($day) {
            return $day['appointments']->count();
        });
        $confirmedCitas = collect($calendarDays)->sum(function($day) {
            return $day['appointments']->where('status.name', 'confirmed')->count();
        });
        $scheduledCitas = collect($calendarDays)->sum(function($day) {
            return $day['appointments']->where('status.name', 'scheduled')->count();
        });
        $completedCitas = collect($calendarDays)->sum(function($day) {
            return $day['appointments']->where('status.name', 'completed')->count();
        });
    @endphp

    <div class="row">
        <div class="col-12">
            <div class="month-stats">
                <h5 class="mb-3">Estadísticas del Mes</h5>
                <div class="stats-grid">
                    <div class="stat-item">
                        <div class="stat-number">{{ $totalCitas }}</div>
                        <div class="stat-label">Total Citas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $confirmedCitas }}</div>
                        <div class="stat-label">Confirmadas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $scheduledCitas }}</div>
                        <div class="stat-label">Programadas</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number">{{ $completedCitas }}</div>
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
                            <a href="{{ route('appointments.monthly', ['date' => $startOfMonth->copy()->subMonth()->format('Y-m')]) }}" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <h3 class="calendar-title mb-0">
                                {{ $startOfMonth->format('F Y') }}
                            </h3>
                            <a href="{{ route('appointments.monthly', ['date' => $startOfMonth->copy()->addMonth()->format('Y-m')]) }}" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                        
                        <div class="calendar-controls">
                            <a href="{{ route('appointments.monthly', ['date' => now()->format('Y-m')]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-calendar-day me-1"></i>
                                Hoy
                            </a>
                            
                            <div class="view-selector">
                                <div class="dropdown">
                                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-calendar-alt me-1"></i>
                                        Mensual
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
                
                <div class="month-grid">
                    <!-- Headers de días de la semana -->
                    <div class="day-header">Dom</div>
                    <div class="day-header">Lun</div>
                    <div class="day-header">Mar</div>
                    <div class="day-header">Mié</div>
                    <div class="day-header">Jue</div>
                    <div class="day-header">Vie</div>
                    <div class="day-header">Sáb</div>
                    
                    <!-- Días del calendario -->
                    @foreach($calendarDays as $day)
                        <div class="day-cell {{ !$day['isCurrentMonth'] ? 'other-month' : '' }} {{ $day['isToday'] ? 'today' : '' }}">
                            <div class="day-number">{{ $day['day'] }}</div>
                            
                            @if($day['appointments']->count() > 0)
                                @foreach($day['appointments']->take(3) as $appointment)
                                    <a href="{{ route('appointments.show', $appointment) }}" 
                                       class="appointment-item {{ $appointment->status->name ?? 'scheduled' }}" 
                                       data-bs-toggle="tooltip" 
                                       title="{{ $appointment->patient->first_name ?? 'N/A' }} {{ $appointment->patient->last_name ?? '' }} - {{ $appointment->start_time }} - Click para ver detalles"
                                       style="text-decoration: none; display: block;">
                                        <div class="appointment-time">
                                            {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}
                                        </div>
                                        <div class="appointment-patient">
                                            {{ Str::limit($appointment->patient->first_name ?? 'N/A', 8) }}
                                        </div>
                                    </a>
                                @endforeach
                                
                                @if($day['appointments']->count() > 3)
                                    <a href="{{ route('appointments.index', ['date' => $day['date']]) }}" 
                                       class="more-appointments" 
                                       style="text-decoration: none; display: block;"
                                       data-bs-toggle="tooltip" 
                                       title="Ver todas las citas del {{ \Carbon\Carbon::parse($day['date'])->format('d/m/Y') }}">
                                        +{{ $day['appointments']->count() - 3 }} más
                                    </a>
                                @endif
                            @endif
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
                    window.location.href = '{{ route("appointments.monthly", ["date" => $startOfMonth->copy()->subMonth()->format("Y-m")]) }}';
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    window.location.href = '{{ route("appointments.monthly", ["date" => $startOfMonth->copy()->addMonth()->format("Y-m")]) }}';
                    break;
                case 't':
                    e.preventDefault();
                    window.location.href = '{{ route("appointments.monthly", ["date" => now()->format("Y-m")]) }}';
                    break;
            }
        }
    });
</script>
@endsection
