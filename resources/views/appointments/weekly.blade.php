@extends('layouts.master')

@section('title')
    Vista Semanal - Citas
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('css')
<link href="{{ asset('css/calendar-custom.css') }}" rel="stylesheet" />
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Vista Semanal - Citas</h4>
                    <p class="text-muted mb-0">Gestiona las citas de la semana del {{ is_object($startOfWeek) ? $startOfWeek->format('d/m/Y') : \Carbon\Carbon::parse($startOfWeek)->format('d/m/Y') }} al {{ is_object($endOfWeek) ? $endOfWeek->format('d/m/Y') : \Carbon\Carbon::parse($endOfWeek)->format('d/m/Y') }}</p>
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

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="calendar-header">
                    <div class="calendar-nav">
                        <div class="d-flex align-items-center gap-3">
                            <a href="{{ route('appointments.weekly', ['date' => (is_object($startOfWeek) ? $startOfWeek->copy()->subWeek() : \Carbon\Carbon::parse($startOfWeek)->copy()->subWeek())->format('Y-m-d')]) }}" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                            <h3 class="calendar-title mb-0">
                                {{ is_object($startOfWeek) ? $startOfWeek->format('d M') : \Carbon\Carbon::parse($startOfWeek)->format('d M') }} - {{ is_object($endOfWeek) ? $endOfWeek->format('d M Y') : \Carbon\Carbon::parse($endOfWeek)->format('d M Y') }}
                            </h3>
                            <a href="{{ route('appointments.weekly', ['date' => (is_object($startOfWeek) ? $startOfWeek->copy()->addWeek() : \Carbon\Carbon::parse($startOfWeek)->copy()->addWeek())->format('Y-m-d')]) }}" 
                               class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </div>
                        
                        <div class="calendar-controls">
                            <a href="{{ route('appointments.weekly', ['date' => \Carbon\Carbon::now()->format('Y-m-d')]) }}" 
                               class="btn btn-sm btn-outline-primary">
                                <i class="fas fa-calendar-day me-1"></i>
                                Hoy
                            </a>
                            
                            <div class="view-selector">
                                <div class="dropdown">
                                    <button class="btn btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                        <i class="fas fa-calendar-week me-1"></i>
                                        Semanal
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
                
                <div class="calendar-container">
                    <!-- Header de días de la semana -->
                    <div class="calendar-week-header">
                        <div class="time-header"></div>
                        @foreach($weekDays as $day)
                            <div class="day-header {{ $day['isToday'] ? 'today' : '' }}">
                                <div class="day-name">{{ $day['dayNameShort'] }}</div>
                                <div class="day-number">{{ $day['day'] }}</div>
                            </div>
                        @endforeach
                    </div>
                    
                    <!-- Grid de tiempo y citas -->
                    <div class="calendar-grid">
                        @for($hour = 7; $hour <= 19; $hour++)
                            <div class="time-row">
                                <!-- Columna de hora -->
                                <div class="time-label">
                                    {{ sprintf('%02d:00', $hour) }}
                                </div>
                                
                                <!-- Celdas de cada día para esta hora -->
                                @foreach($weekDays as $day)
                                    <div class="time-cell" data-day="{{ is_object($day['date']) ? $day['date']->format('Y-m-d') : $day['date'] }}" data-hour="{{ $hour }}">
                                        @php
                                            $hourAppointments = $day['appointments']->filter(function($appointment) use ($hour) {
                                                $appointmentHour = \Carbon\Carbon::parse($appointment->start_time)->hour;
                                                return $appointmentHour == $hour;
                                            });
                                        @endphp
                                        
                                        @if($hourAppointments->count() > 0)
                                            @foreach($hourAppointments as $appointment)
                                                @php
                                                    $startMinute = \Carbon\Carbon::parse($appointment->start_time)->minute;
                                                    $duration = \Carbon\Carbon::parse($appointment->end_time)->diffInMinutes(\Carbon\Carbon::parse($appointment->start_time));
                                                    $top = ($startMinute / 60) * 60; // Posición dentro del slot de 1 hora
                                                    $height = max(($duration / 60) * 60, 20); // Altura mínima de 20px
                                                @endphp
                                                <div class="appointment-block {{ $appointment->status->name ?? 'scheduled' }}" 
                                                     style="top: {{ $top }}px; height: {{ $height }}px;"
                                                     data-status="{{ $appointment->status->name ?? 'scheduled' }}">
                                                    <a href="{{ route('appointments.show', $appointment) }}" 
                                                       class="appointment-link"
                                                       data-bs-toggle="tooltip" 
                                                       title="{{ $appointment->patient->first_name ?? 'N/A' }} {{ $appointment->patient->last_name ?? '' }} - {{ $appointment->start_time }} a {{ $appointment->end_time }}">
                                                        <div class="appointment-time">
                                                            {{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}
                                                        </div>
                                                        <div class="appointment-patient">
                                                            {{ Str::limit($appointment->patient->first_name ?? 'N/A', 8) }} {{ Str::limit($appointment->patient->last_name ?? '', 8) }}
                                                        </div>
                                                    </a>
                                                </div>
                                            @endforeach
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        @endfor
                    </div>
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
                    window.location.href = '{{ route("appointments.weekly", ["date" => (is_object($startOfWeek) ? $startOfWeek->copy()->subWeek() : \Carbon\Carbon::parse($startOfWeek)->copy()->subWeek())->format("Y-m-d")]) }}';
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    window.location.href = '{{ route("appointments.weekly", ["date" => (is_object($startOfWeek) ? $startOfWeek->copy()->addWeek() : \Carbon\Carbon::parse($startOfWeek)->copy()->addWeek())->format("Y-m-d")]) }}';
                    break;
                case 't':
                    e.preventDefault();
                    window.location.href = '{{ route("appointments.weekly", ["date" => \Carbon\Carbon::now()->format("Y-m-d")]) }}';
                    break;
            }
        }
    });
</script>
@endsection
