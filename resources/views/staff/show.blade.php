@extends('layouts.master')

@section('title')
    Perfil del Personal
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('css')
@include('components.success-toast-styles')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Gestión de Personal</h4>
                    <p class="text-muted mb-3">Perfil del personal: {{ $staff->user->name ?? 'N/A' }}</p>
                </div>
                <div class="page-title-right">
                    <div class="d-flex gap-2">
                        <a href="{{ route('staff.edit', $staff) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit me-1"></i>
                            Editar
                        </a>
                        <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Mensaje de éxito flotante -->
    @include('components.success-toast')

    <div class="row">
        <!-- Información del Personal -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl avatar-label-primary mx-auto mb-3">
                        @if($staff->user->gender === 'male')
                            <i class="fas fa-male text-primary fs-144"></i>
                        @elseif($staff->user->gender === 'female')
                            <i class="fas fa-female text-danger fs-144"></i>
                        @else
                            <i class="fas fa-user text-secondary fs-144"></i>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $staff->user->name ?? 'N/A' }}</h4>
                    <p class="text-muted mb-3">
                        <span class="fw-semibold">{{ strtoupper(substr($staff->user->name ?? 'N', 0, 1)) }}{{ strtoupper(substr($staff->user->name ?? 'A', 1, 1)) }}-{{ str_pad($staff->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </p>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="mb-1">{{ $staff->specialty ?? 'N/A' }}</h5>
                                <p class="text-muted mb-0">Especialidad</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-1">
                                @if($staff->is_active)
                                    <span class="badge bg-success-subtle text-success">
                                        <i class="fas fa-check me-1"></i>Activo
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="fas fa-times me-1"></i>Inactivo
                                    </span>
                                @endif
                            </div>
                            <p class="text-muted mb-0">Estado</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Contacto -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-phone fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Contacto</h4>
                </div>
                <div class="card-body">
                    @if($staff->user->email)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-circle me-3">
                                <i class="fas fa-envelope text-success"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $staff->user->email }}</div>
                                <small class="text-muted">Email principal</small>
                            </div>
                        </div>
                    @endif

                    @if($staff->phone)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-label-success me-3">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $staff->phone }}</div>
                                <small class="text-muted">Teléfono</small>
                            </div>
                        </div>
                    @endif

                    @if($staff->address)
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm avatar-label-warning me-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $staff->address }}</div>
                                <small class="text-muted">Dirección</small>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Información Detallada -->
        <div class="col-lg-8">
            <!-- Información Personal -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Información Personal</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Nombre Completo</label>
                                <p class="text-muted mb-0">{{ $staff->user->name ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Especialidad</label>
                                <p class="text-muted mb-0">{{ $staff->specialty ?? 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Rol</label>
                                <p class="text-muted mb-0">
                                    @switch($staff->role)
                                        @case('doctor')
                                            <span class="badge bg-primary-subtle text-primary">Doctor</span>
                                            @break
                                        @case('nurse')
                                            <span class="badge bg-info-subtle text-info">Enfermero</span>
                                            @break
                                        @case('receptionist')
                                            <span class="badge bg-success-subtle text-success">Recepcionista</span>
                                            @break
                                        @default
                                            <span class="badge bg-secondary-subtle text-secondary">{{ ucfirst($staff->role) }}</span>
                                    @endswitch
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Estado</label>
                                <p class="text-muted mb-0">
                                    @if($staff->is_active)
                                        <span class="badge bg-success-subtle text-success">
                                            <i class="fas fa-check me-1"></i>Activo
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger">
                                            <i class="fas fa-times me-1"></i>Inactivo
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Fecha de Registro</label>
                                <p class="text-muted mb-0">{{ $staff->created_at ? $staff->created_at->format('d/m/Y H:i') : 'N/A' }}</p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-medium">Última Actualización</label>
                                <p class="text-muted mb-0">{{ $staff->updated_at ? $staff->updated_at->format('d/m/Y H:i') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información Adicional -->
            @if($staff->notes)
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-sticky-note fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Notas</h4>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-0">{{ $staff->notes }}</p>
                </div>
            </div>
            @endif
        </div>
    </div>
    <!-- end row -->
@endsection

@section('scripts')
@include('components.success-toast-scripts')
@endsection

