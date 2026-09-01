@extends('layouts.master')

@section('title', 'Reportes')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Reportes</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Panel de Control</a></li>
                        <li class="breadcrumb-item active">Reportes</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Módulo de Reportes</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar-sm mx-auto mb-4">
                                        <div class="avatar-title bg-primary-subtle text-primary rounded-circle fs-20">
                                            <i class="ri-file-chart-line"></i>
                                        </div>
                                    </div>
                                    <h5 class="card-title">Reportes Financieros</h5>
                                    <p class="text-muted">Ingresos, gastos y análisis financiero</p>
                                    <a href="#" class="btn btn-primary btn-sm">Ver Reportes</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar-sm mx-auto mb-4">
                                        <div class="avatar-title bg-success-subtle text-success rounded-circle fs-20">
                                            <i class="ri-user-heart-line"></i>
                                        </div>
                                    </div>
                                    <h5 class="card-title">Reportes de Pacientes</h5>
                                    <p class="text-muted">Estadísticas y seguimiento de pacientes</p>
                                    <a href="#" class="btn btn-success btn-sm">Ver Reportes</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar-sm mx-auto mb-4">
                                        <div class="avatar-title bg-info-subtle text-info rounded-circle fs-20">
                                            <i class="ri-calendar-check-line"></i>
                                        </div>
                                    </div>
                                    <h5 class="card-title">Reportes de Citas</h5>
                                    <p class="text-muted">Análisis de citas y ocupación</p>
                                    <a href="#" class="btn btn-info btn-sm">Ver Reportes</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar-sm mx-auto mb-4">
                                        <div class="avatar-title bg-warning-subtle text-warning rounded-circle fs-20">
                                            <i class="ri-medicine-bottle-line"></i>
                                        </div>
                                    </div>
                                    <h5 class="card-title">Reportes de Inventario</h5>
                                    <p class="text-muted">Stock, movimientos y alertas</p>
                                    <a href="#" class="btn btn-warning btn-sm">Ver Reportes</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar-sm mx-auto mb-4">
                                        <div class="avatar-title bg-danger-subtle text-danger rounded-circle fs-20">
                                            <i class="ri-team-line"></i>
                                        </div>
                                    </div>
                                    <h5 class="card-title">Reportes de Personal</h5>
                                    <p class="text-muted">Productoividad y rendimiento del equipo</p>
                                    <a href="#" class="btn btn-danger btn-sm">Ver Reportes</a>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card border">
                                <div class="card-body text-center">
                                    <div class="avatar-sm mx-auto mb-4">
                                        <div class="avatar-title bg-secondary-subtle text-secondary rounded-circle fs-20">
                                            <i class="ri-bar-chart-line"></i>
                                        </div>
                                    </div>
                                    <h5 class="card-title">Reportes Generales</h5>
                                    <p class="text-muted">Estadísticas generales del sistema</p>
                                    <a href="#" class="btn btn-secondary btn-sm">Ver Reportes</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-4">
                        <div class="col-12">
                            <div class="alert alert-info">
                                <h5 class="alert-heading">Información</h5>
                                <p class="mb-0">El módulo de reportes está en desarrollo. Próximamente estarán disponibles todos los reportes y análisis detallados del sistema.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
