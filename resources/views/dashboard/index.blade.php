@extends('layouts.master')

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">¡Buenos días, <span class="text-primary">{{ Auth::user()->name }}!</span></h4>
                    <p class="text-muted mb-0">Aquí tienes un resumen de tu clínica dental hoy.</p>
                </div>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="javascript: void(0);">Dentaris</a></li>
                        <li class="breadcrumb-item active">Panel de Control</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <!--    end row -->

    <div class="row">
        <div class="col-xxl-9">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-chart-line fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Ingresos Generales</h4>
                    <div class="dropdown card-addon">
                        <a href="#" class="dropdown-toggle arrow-none card-drop" data-bs-toggle="dropdown"
                            aria-expanded="false">
                            <i class="mdi mdi-dots-sidebar"></i>
                        </a>
                        <div class="dropdown-menu dropdown-menu-end">
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">Reporte de Ventas</a>
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">Exportarar Reporte</a>
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">Ganancias</a>
                            <!-- item-->
                            <a href="javascript:void(0);" class="dropdown-item">Acción</a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-sm-4">
                            <div class="d-flex justify-content-between align-content-end shadow-lg p-3">
                                <div>
                                    <p class="text-muted text-truncate mb-2">Citas de hoy</p>
                                    <h5 class="mb-0">{{ $kpis['today_appointments'] }}</h5>
                                </div>
                                <div class="text-success float-end">
                                    <i class="mdi mdi-menu-up"> </i>{{ $appointmentStats['today']['completed'] }} completadas
                                </div>
                            </div>
                        </div>

                        <div class="col-sm-4">
                            <div class="d-flex justify-content-between align-content-end shadow-lg p-3">
                                <div>
                                    <p class="text-muted text-truncate mb-2">Ingresos del mes</p>
                                    <h5 class="mb-0">${{ number_format($kpis['monthly_revenue'], 0) }}</h5>
                                </div>
                                <div class="text-success float-end">
                                    <i class="mdi mdi-menu-up"> </i>{{ $financialStats['profit_margin'] }}%
                                </div>
                            </div>
                        </div>
                        <div class="col-sm-4">
                            <div class="d-flex justify-content-between align-content-end shadow-lg p-3">
                                <div>
                                    <p class="text-muted text-truncate mb-2">Pacientes totales</p>
                                    <h5 class="mb-0">{{ number_format($kpis['total_patients']) }}</h5>
                                </div>
                                <div class="text-success float-end">
                                    <i class="mdi mdi-menu-up"> </i>{{ $kpis['active_treatment_plans'] }} activos
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div id="revenue_chart" data-colors='["--bs-info", "--bs-success"]' class="apex-charts" dir="ltr">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-xl-4">
                    <div class="card bg-danger-subtle"
                        style="background: url('build/images/dashboard/dashboard-shape-1.png'); background-repeat: no-repeat; background-position: bottom center; ">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="avatar avatar-sm avatar-label-danger">
                                    <i class="fas fa-exclamation-triangle mt-1"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-danger mb-1">Productos bajo stock</p>
                                    <h4 class="mb-0">{{ $kpis['low_stock_products'] }}</h4>
                                </div>
                            </div>
                            <div class="hstack gap-2 mt-3">
                                <a href="{{ route('inventory.index') }}" class="btn btn-light">Revisar</a>
                                <a href="{{ route('suppliers.index') }}" class="btn btn-info">Pedir</a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card bg-success-subtle"
                        style="background: url('build/images/dashboard/dashboard-shape-2.png'); background-repeat: no-repeat; background-position: bottom center; ">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="avatar avatar-sm avatar-label-success">
                                    <i class="fas fa-calendar-check mt-1"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-success mb-1">Citas completadas</p>
                                    <h4 class="mb-0">{{ $appointmentStats['today']['completed'] }}</h4>
                                </div>
                            </div>
                            <div class="mt-3 mb-2">
                                <p class="mb-0">{{ $appointmentStats['today']['cancelled'] }} canceladas</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card bg-info-subtle"
                        style="background: url('build/images/dashboard/dashboard-shape-3.png'); background-repeat: no-repeat; background-position: bottom center; ">
                        <div class="card-body">
                            <div class="d-flex">
                                <div class="avatar avatar-sm avatar-label-info">
                                    <i class="fas fa-flask mt-1"></i>
                                </div>
                                <div class="ms-3">
                                    <p class="text-info mb-1">Trabajos de laboratorio</p>
                                    <h4 class="mb-0">{{ $kpis['pending_lab_works'] }}</h4>
                                </div>
                            </div>
                            <div class="mt-3 mb-2">
                                <p class="mb-0"><span class="text-primary me-2 fs-14"><i
                                            class="fas fa-caret-up me-1"></i>3.4%</span>vs mes anterior</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- end row -->
            <div class="row">
                <div class="col-xl-8">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon">
                                <i class="fas fa-chart-pie fs-14 text-muted"></i>
                            </div>
                            <h4 class="card-title mb-0">Citas por Estado</h4>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-sm-6">
                                    @php
                                        $statusColors = [
                                            'scheduled' => 'rgba(232, 149, 14, 1)',
                                            'confirmed' => 'rgba(32, 189, 66, 1)', 
                                            'in_progress' => 'rgba(194, 50, 183, 1)',
                                            'cancelled' => 'rgba(219, 41, 18, 1)',
                                            'completed' => 'rgba(32, 42, 189, 1)',
                                            'rescheduled' => 'rgba(247, 194, 111, 1)',
                                            'no_show' => '#6b7280'
                                        ];
                                        
                                        $statusIcons = [
                                            'scheduled' => 'mdi-calendar-clock',
                                            'confirmed' => 'mdi-check-circle',
                                            'in_progress' => 'mdi-clock-outline', 
                                            'cancelled' => 'mdi-close-circle',
                                            'completed' => 'mdi-check',
                                            'rescheduled' => 'mdi-calendar-refresh',
                                            'no_show' => 'mdi-account-remove'
                                        ];
                                        
                                        $statusLabels = [
                                            'scheduled' => 'Programadas',
                                            'confirmed' => 'Confirmadas',
                                            'in_progress' => 'En Progreso',
                                            'cancelled' => 'Cancelaradas', 
                                            'completed' => 'Completadas',
                                            'rescheduled' => 'Reprogramadas',
                                            'no_show' => 'No Asistió'
                                        ];
                                    @endphp
                                    
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <div>
                                                <p><i class="mdi {{ $statusIcons['scheduled'] }} me-2 fs-5" style="color: {{ $statusColors['scheduled'] }};"></i>{{ $statusLabels['scheduled'] }} <span
                                                        class="text-muted fs-14">{{ $appointmentStats['by_status']['scheduled'] ?? 0 }}</span></p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div>
                                                <p><i class="mdi {{ $statusIcons['confirmed'] }} me-2 fs-5" style="color: {{ $statusColors['confirmed'] }};"></i>{{ $statusLabels['confirmed'] }}
                                                    <span class="text-muted fs-14">{{ $appointmentStats['by_status']['confirmed'] ?? 0 }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <div>
                                                <p><i class="mdi {{ $statusIcons['in_progress'] }} me-2 fs-5" style="color: {{ $statusColors['in_progress'] }};"></i>{{ $statusLabels['in_progress'] }} <span
                                                        class="text-muted fs-14">{{ $appointmentStats['by_status']['in_progress'] ?? 0 }}</span></p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div>
                                                <p><i class="mdi {{ $statusIcons['completed'] }} me-2 fs-5" style="color: {{ $statusColors['completed'] }};"></i>{{ $statusLabels['completed'] }}
                                                    <span class="text-muted fs-14">{{ $appointmentStats['by_status']['completed'] ?? 0 }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <div>
                                                <p><i class="mdi {{ $statusIcons['cancelled'] }} me-2 fs-5" style="color: {{ $statusColors['cancelled'] }};"></i>{{ $statusLabels['cancelled'] }} <span
                                                        class="text-muted fs-14">{{ $appointmentStats['by_status']['cancelled'] ?? 0 }}</span></p>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div>
                                                <p><i class="mdi {{ $statusIcons['rescheduled'] }} me-2 fs-5" style="color: {{ $statusColors['rescheduled'] }};"></i>{{ $statusLabels['rescheduled'] }}
                                                    <span class="text-muted fs-14">{{ $appointmentStats['by_status']['rescheduled'] ?? 0 }}</span>
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mb-2">
                                        <div class="col-6">
                                            <div>
                                                <p><i class="mdi {{ $statusIcons['no_show'] }} me-2 fs-5" style="color: {{ $statusColors['no_show'] }};"></i>{{ $statusLabels['no_show'] }} <span
                                                        class="text-muted fs-14">{{ $appointmentStats['by_status']['no_show'] ?? 0 }}</span></p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-sm-6">
                                    <div>
                                        <div id="appointments_chart"
                                            data-colors='["rgba(232, 149, 14, 1)", "rgba(32, 189, 66, 1)", "rgba(194, 50, 183, 1)", "rgba(32, 42, 189, 1)", "rgba(219, 41, 18, 1)", "rgba(247, 194, 111, 1)", "rgba(107, 114, 128, 1)"]'
                                            class="apex-charts" dir="ltr"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xl-4">
                    <div class="card" style="overflow-y: auto; height: 304px;" data-simplebar="">
                        <div class="card-header card-header-bordered">
                            <div class="card-icon text-muted"><i class="fa fa-clipboard-list fs-14"></i></div>
                            <h3 class="card-title">Actividades Recientes</h3>
                            <div class="card-addon">
                                <a href="{{ route('notifications.index') }}" class="btn btn-sm btn-label-primary">Ver todas</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="timeline timeline-timed">
                                @if($todayAppointments->count() > 0)
                                    @foreach($todayAppointments->take(3) as $appointment)
                                    <div class="timeline-item">
                                        <span class="timeline-time">{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</span>
                                        <div class="timeline-pin"><i class="marker marker-circle text-primary"></i></div>
                                        <div class="timeline-content">
                                            <div>
                                                <span>Cita con {{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</span>
                                                <div class="avatar-group ms-2">
                                                    <div class="avatar avatar-circle">
                                                        <span class="avatar-label-primary">{{ substr($appointment->patient->first_name, 0, 1) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="timeline-item">
                                        <span class="timeline-time">--:--</span>
                                        <div class="timeline-pin"><i class="marker marker-circle text-muted"></i></div>
                                        <div class="timeline-content">
                                            <p class="mb-0">No hay actividades recientes</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <div class="card" style="height: 495px; overflow: hidden auto;" data-simplebar="">
                        <div class="card-header">
                            <div class="card-icon text-muted"><i class="fas fa-calendar-alt fs-14"></i></div>
                            <h3 class="card-title">Citas de Hoy</h3>
                            <div class="card-addon dropdown">
                                <button class="btn btn-label-secondary btn-sm dropdown-toggle" data-bs-toggle="dropdown">
                                    <i class="fas fa-filter fs-12 align-middle ms-1"></i></button>
                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                                    <a class="dropdown-item" href="#">
                                        <div class="dropdown-icon"><i class="fa fa-poll"></i></div>
                                        <span class="dropdown-content">Hoy</span>
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <div class="dropdown-icon"><i class="fa fa-chart-pie"></i></div>
                                        <span class="dropdown-content">Esta Semana</span>
                                    </a>
                                    <a class="dropdown-item" href="#">
                                        <div class="dropdown-icon"><i class="fa fa-chart-line"></i></div>
                                        <span class="dropdown-content">Este Mes</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive-md">
                                <table class="table text-nowrap mb-0">
                                    <thead>
                                        <tr>
                                            <th>Hora</th>
                                            <th>Paciente</th>
                                            <th>Odontólogo</th>
                                            <th>Estado</th>
                                            <th>Progreso</th>
                                            <th>Duración</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if($todayAppointments->count() > 0)
                                            @foreach($todayAppointments as $appointment)
                                            <tr>
                                                <td class="align-middle">{{ \Carbon\Carbon::parse($appointment->start_time)->format('H:i') }}</td>
                                                <td class="align-middle">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar avatar-circle me-2">
                                                            <span class="avatar-label-primary">{{ substr($appointment->patient->first_name, 0, 1) }}</span>
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold">{{ $appointment->patient->first_name }} {{ $appointment->patient->last_name }}</div>
                                                            <small class="text-muted">{{ $appointment->patient->phone }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="fw-bold">{{ $appointment->staff->user->name }}</div>
                                                    <small class="text-muted">{{ $appointment->staff->specialty ?? 'Odontólogo' }}</small>
                                                </td>
                                                <td class="align-middle">
                                                    @php
                                                        $statusColors = [
                                                            'scheduled' => 'primary',
                                                            'confirmed' => 'success',
                                                            'in_progress' => 'warning',
                                                            'completed' => 'success',
                                                            'cancelled' => 'danger'
                                                        ];
                                                        $statusColor = $statusColors[$appointment->status->name] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $statusColor }}">
                                                        {{ ucfirst($appointment->status->name) }}
                                                    </span>
                                                </td>
                                                <td class="align-middle">
                                                    <div class="">
                                                        <h6 class="">{{ $appointment->status->name == 'completed' ? '100' : '75' }}%</h6>
                                                        <div class="progress progress-sm">
                                                            <div class="progress-bar bg-{{ $statusColor }}" style="width: {{ $appointment->status->name == 'completed' ? '100' : '75' }}%"></div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="align-middle">{{ \Carbon\Carbon::parse($appointment->start_time)->diffInMinutes(\Carbon\Carbon::parse($appointment->end_time)) }} min</td>
                                                <td class="align-middle">
                                                    <div class="btn-group btn-group-sm">
                                                        <button class="btn btn-outline-primary btn-sm">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        <button class="btn btn-outline-success btn-sm">
                                                            <i class="fas fa-edit"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                            @endforeach
                                        @else
                                            <tr>
                                                <td colspan="7" class="text-center py-4">
                                                    <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                                    <h5 class="text-muted">No hay citas programadas para hoy</h5>
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xxl-3">
            <div class="row">
                <div class="col-xxl-12 col-xl-6 order-1">
                    <div class="card">
                        <div class="card-body">
                            <div class="float-end">
                                <select class="form-select form-select-sm">
                                    <option selected>{{ now()->format('M') }}</option>
                                    <option value="1">{{ now()->subMonth()->format('M') }}</option>
                                    <option value="2">{{ now()->subMonths(2)->format('M') }}</option>
                                    <option value="3">{{ now()->subMonths(3)->format('M') }}</option>
                                </select>
                            </div>
                            <h4 class="card-title mb-4">Análisis de Citas</h4>
                            <div id="appointments_analytics"
                                data-colors='["--bs-primary", "--bs-success", "--bs-warning", "--bs-danger", "--bs-info"]'
                                class="apex-charts" dir="ltr"></div>

                            <div class="row">
                                <div class="col-4">
                                    <div class="text-center mt-4">
                                        <p class="mb-2 text-truncate"><i
                                                class="mdi mdi-circle text-primary font-size-10 me-1"></i> Programadas</p>
                                        <h5>{{ $appointmentStats['by_status']['scheduled'] ?? 0 }}</h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center mt-4">
                                        <p class="mb-2 text-truncate"><i
                                                class="mdi mdi-circle text-success font-size-10 me-1"></i> Completadas</p>
                                        <h5>{{ $appointmentStats['by_status']['completed'] ?? 0 }}</h5>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="text-center mt-4">
                                        <p class="mb-2 text-truncate"><i
                                                class="mdi mdi-circle text-warning font-size-10 me-1"></i> En Progreso</p>
                                        <h5>{{ $appointmentStats['by_status']['in_progress'] ?? 0 }}</h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-xxl-12 order-4 order-xxl-2">
                    <div class="card">
                        <div class="card-header">
                            <div class="card-icon text-muted"><i class="fa fa-bell"></i></div>
                            <h3 class="card-title">Notificaciones</h3>
                            <div class="card-addon">
                                <div class="dropdown">
                                    <button class="btn btn-sm py-0 btn-label-primary dropdown-toggle"
                                        data-bs-toggle="dropdown">Todas <i
                                            class="mdi mdi-chevron-down fs-17 align-middle ms-1"></i></button>
                                    <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                                        <a class="dropdown-item" href="#"><span
                                                class="badge badge-label-primary">Citas</span> </a>
                                        <a class="dropdown-item" href="#"><span
                                                class="badge badge-label-info">Pagos</span> </a>
                                        <a class="dropdown-item" href="#"><span
                                                class="badge badge-label-success">Inventario</span> </a>
                                        <a class="dropdown-item" href="#"><span
                                                class="badge badge-label-danger">Laboratorio</span></a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="rich-list rich-list-bordered rich-list-action">
                                @if(count($alerts) > 0)
                                    @foreach($alerts as $alert)
                                    <div class="rich-list-item">
                                        <div class="rich-list-prepend">
                                            <div class="avatar avatar-xs avatar-label-{{ $alert['type'] }}">
                                                <div class=""><i class="fa fa-{{ $alert['icon'] }}"></i></div>
                                            </div>
                                        </div>
                                        <div class="rich-list-content">
                                            <h4 class="rich-list-title mb-1">{{ $alert['message'] }}</h4>
                                            <p class="rich-list-subtitle mb-0">Revisar ahora</p>
                                        </div>
                                        <div class="rich-list-append">
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-label-secondary btn-icon"
                                                    data-bs-toggle="dropdown"><i class="fa fa-ellipsis-h fs-12"></i></button>
                                                <div class="dropdown-menu dropdown-menu-end dropdown-menu-animated">
                                                    <a class="dropdown-item" href="#">
                                                        <div class="dropdown-icon"><i class="fa fa-check"></i></div>
                                                        <span class="dropdown-content">Marcar como leído</span>
                                                    </a>
                                                    <a class="dropdown-item" href="#">
                                                        <div class="dropdown-icon"><i class="fa fa-trash-alt"></i></div>
                                                        <span class="dropdown-content">Eliminar</span>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                @else
                                    <div class="rich-list-item">
                                        <div class="rich-list-prepend">
                                            <div class="avatar avatar-xs avatar-label-success">
                                                <div class=""><i class="fa fa-check"></i></div>
                                            </div>
                                        </div>
                                        <div class="rich-list-content">
                                            <h4 class="rich-list-title mb-1">Todo está en orden</h4>
                                            <p class="rich-list-subtitle mb-0">No hay alertas pendientes</p>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection

@section('scripts')
    <!-- apexcharts -->
    <script src="{{ asset('build/libs/apexcharts/dist/apexcharts.min.js') }}"></script>
    <script src="{{ asset('build/js/pages/dashboard.init.js') }}"></script>
    <script>
        // Revenue Chart
        var options = {
            series: [{
                name: 'Ingresos',
                data: @json(array_values($charts['revenue_by_month']))
            }],
            chart: {
                height: 350,
                type: 'area',
                toolbar: {
                    show: false
                }
            },
            colors: ['#3b82f6', '#10b981'],
            dataLabels: {
                enabled: false
            },
            legend: {
                labels: {
                    colors: '#ffffff'
                }
            },
            stroke: {
                curve: 'smooth',
                width: 2
            },
            xaxis: {
                categories: @json(array_keys($charts['revenue_by_month']))
            },
            yaxis: {
                labels: {
                    formatter: function (val) {
                        return '$' + val.toLocaleString();
                    }
                }
            },
            tooltip: {
                y: {
                    formatter: function (val) {
                        return '$' + val.toLocaleString();
                    }
                }
            }
        };
        var chart = new ApexCharts(document.querySelector("#revenue_chart"), options);
        chart.render();

        // Citas Chart - Gradient Donut Chart (Original Clivax)
        @php
            // Definir estados válidos en el mismo orden que los labels
            $validStatuses = ['scheduled', 'confirmed', 'in_progress', 'completed', 'cancelled', 'rescheduled', 'no_show'];
            $validLabels = ['Programadas', 'Confirmadas', 'En Progreso', 'Completadas', 'Cancelaradas', 'Reprogramadas', 'No Asistió'];
            $validSeries = [];
            
            // Solo incluir estados que existen en los datos (usar solo estados en inglés)
            foreach ($validStatuses as $status) {
                $validSeries[] = $charts['appointments_by_status'][$status] ?? 0;
            }

            // Calcular el total de citas relevantes para el gráfico radial (Programadas + En Progreso + Completadas)
            $totalRelevantAppointments = ($charts['appointments_by_status']['scheduled'] ?? 0) +
                                         ($charts['appointments_by_status']['in_progress'] ?? 0) +
                                         ($charts['appointments_by_status']['completed'] ?? 0);

            $totalAppointmentsThisMonth = $appointmentStats['this_month']['total'] ?? 1; // Evitar división por cero
            $percentageRelevant = ($totalAppointmentsThisMonth > 0) ? round(($totalRelevantAppointments / $totalAppointmentsThisMonth) * 100, 1) : 0;
        @endphp
        // Debug: mostrar datos en consola
        console.log('Appointments Chart Data:', {
            series: @json($validSeries),
            labels: @json($validLabels),
            validStatuses: @json($validStatuses)
        });
        
        var appointmentsOptions = {
            series: @json($validSeries),
            chart: {
                height: 250,
                type: 'donut',
            },
            labels: @json($validLabels),
            plotOptions: {
                pie: {
                    startAngle: -90,
                    endAngle: 270,
                }
            },
            stroke: {
                width: 5,
                colors: ['#fff']
            },
            dataLabels: {
                enabled: false
            },
            fill: {
                type: 'gradient',
            },
            legend: {
                show: false
            },
            colors: ['rgba(232, 149, 14, 1)', 'rgba(32, 189, 66, 1)', 'rgba(194, 50, 183, 1)', 'rgba(32, 42, 189, 1)', 'rgba(219, 41, 18, 1)', 'rgba(247, 194, 111, 1)', 'rgba(107, 114, 128, 1)'],
            tooltip: {
                y: {
                    formatter: function (val, opts) {
                        return val + ' citas';
                    }
                }
            },
            responsive: [{
                breakpoint: 480,
                options: {
                    chart: {
                        width: 200
                    },
                    legend: {
                        position: 'bottom'
                    }
                }
            }]
        };
        var appointmentsChart = new ApexCharts(document.querySelector("#appointments_chart"), appointmentsOptions);
        appointmentsChart.render();

        // Citas Analytics
        var analyticsOptions = {
            series: [{{ $percentageRelevant }}],
            chart: {
                height: 200,
                type: 'radialBar'
            },
            colors: ['#10b981'],
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '60%'
                    },
                    dataLabels: {
                        name: {
                            fontSize: '14px',
                            color: '#000000',
                            show: true,
                            offsetY: -10,
                            formatter: function() {
                                return 'Total del Mes';
                            }
                        },
                        value: {
                            fontSize: '20px',
                            color: '#000000',
                            show: true,
                            offsetY: 10,
                            formatter: function(val) {
                                return val + '%';
                            }
                        }
                    }
                }
            },
            labels: ['Total del Mes'],
            legend: {
                show: false
            }
        };
        var analyticsChart = new ApexCharts(document.querySelector("#appointments_analytics"), analyticsOptions);
        analyticsChart.render();
    </script>
    
    <style>
        .status-item {
            cursor: pointer;
            transition: all 0.2s ease;
        }
        
        .status-item:hover {
            background-color: rgba(0, 0, 0, 0.04);
            border-radius: 4px;
        }
        
        .status-item p {
            margin: 0;
            font-size: 14px;
            font-weight: 400;
            font-family: Helvetica, Arial, sans-serif;
            color: #373d3f;
        }
        
        .status-item:hover p {
            color: #373d3f !important;
        }
        
        .status-item:hover .text-muted {
            color: #6c757d !important;
        }
        
        .status-item .mdi-circle {
            transition: all 0.2s ease;
        }
        
        .status-item:hover .mdi-circle {
            opacity: 0.8;
            transform: scale(1.1);
        }
        
        .cursor-pointer {
            cursor: pointer;
        }
        
        /* Scrollbar personalizado para Actividades Recientes - Forzar estilo rojo */
        .simplebar-scrollbar::before {
            background: linear-gradient(180deg, rgba(254, 91, 91, 0.8) 0%, rgba(254, 91, 91, 0.4) 100%) !important;
            border-radius: 10px !important;
        }
        
        .simplebar-scrollbar:hover::before {
            background: linear-gradient(180deg, rgba(254, 91, 91, 1) 0%, rgba(254, 91, 91, 0.6) 100%) !important;
        }
        
        .simplebar-scrollbar.simplebar-visible::before {
            background: linear-gradient(180deg, rgba(254, 91, 91, 0.8) 0%, rgba(254, 91, 91, 0.4) 100%) !important;
            opacity: 0.5 !important;
        }
        
        .simplebar-track {
            background: rgba(254, 91, 91, 0.1) !important;
            border-radius: 10px !important;
        }
        
        /* Selector más específico para sobrescribir Clivax */
        .card[data-simplebar] .simplebar-scrollbar::before {
            background: linear-gradient(180deg, rgba(254, 91, 91, 0.8) 0%, rgba(254, 91, 91, 0.4) 100%) !important;
        }
        
        .card[data-simplebar] .simplebar-scrollbar.simplebar-visible::before {
            background: linear-gradient(180deg, rgba(254, 91, 91, 0.8) 0%, rgba(254, 91, 91, 0.4) 100%) !important;
            opacity: 0.5 !important;
        }
        
        /* Fallback para scrollbar nativo */
        .card[data-simplebar]::-webkit-scrollbar {
            width: 8px !important;
        }
        
        .card[data-simplebar]::-webkit-scrollbar-track {
            background: rgba(254, 91, 91, 0.1) !important;
            border-radius: 10px !important;
        }
        
        .card[data-simplebar]::-webkit-scrollbar-thumb {
            background: linear-gradient(180deg, rgba(254, 91, 91, 0.8) 0%, rgba(254, 91, 91, 0.4) 100%) !important;
            border-radius: 10px !important;
            border: 1px solid rgba(254, 91, 91, 0.2) !important;
        }
        
        .card[data-simplebar]::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(180deg, rgba(254, 91, 91, 1) 0%, rgba(254, 91, 91, 0.6) 100%) !important;
        }
        
        /* Para Firefox */
        .card[data-simplebar] {
            scrollbar-width: thin !important;
            scrollbar-color: rgba(254, 91, 91, 0.8) rgba(254, 91, 91, 0.1) !important;
        }
    </style>
    
    <script src="{{ asset('build/js/app.js') }}"></script>
@endsection