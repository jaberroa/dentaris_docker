@extends('layouts.master')

@section('title')
    Plan de Tratamiento
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('css')
<style>
    /* Estilos personalizados para el toast de éxito */
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
        from {
            opacity: 1;
            transform: scale(1);
        }
        to {
            opacity: 0;
            transform: scale(0.9);
        }
    }
    
    /* Responsive */
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

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Gestión de Planes de Tratamiento</h4>
                    <p class="text-muted mb-3">Detalles del plan: {{ $treatmentPlan->treatment_name ?? 'Sin nombre' }}</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('treatment-plans.index') }}" class="btn btn-outline-secondary me-2">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver
                    </a>
                    <a href="{{ route('treatment-plans.edit', $treatmentPlan) }}" class="btn btn-warning me-2">
                        <i class="fas fa-edit me-1"></i>
                        Editar Plan
                    </a>
                    <a href="{{ route('patients.show', $treatmentPlan->patient) }}" class="btn btn-info">
                        <i class="fas fa-user me-1"></i>
                        Ver Paciente
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Mensaje de éxito flotante -->
    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="treatmentSuccessToast">
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
                            <i class="fas fa-clipboard-list fs-12"></i>
                        </div>
                        <span class="text-muted">{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <!-- Información del Plan -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Información del Plan</h4>
                </div>
                <div class="card-body">
                    <div class="d-flex align-items-center mb-3">
                        <div class="avatar avatar-lg avatar-label-primary me-3">
                            <i class="fas fa-clipboard-list fs-18"></i>
                        </div>
                        <div>
                            <h6 class="mb-0">{{ $treatmentPlan->treatment_name ?? 'Sin nombre' }}</h6>
                            <small class="text-muted">PLAN-{{ str_pad($treatmentPlan->id, 3, '0', STR_PAD_LEFT) }}</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Estado:</label>
                        <div>
                            @if($treatmentPlan->status === 'active')
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check me-1"></i>Activo
                                </span>
                            @elseif($treatmentPlan->status === 'completed')
                                <span class="badge bg-primary-subtle text-primary">
                                    <i class="fas fa-check-circle me-1"></i>Completado
                                </span>
                            @elseif($treatmentPlan->status === 'cancelled')
                                <span class="badge bg-danger-subtle text-danger">
                                    <i class="fas fa-times me-1"></i>Cancelado
                                </span>
                            @else
                                <span class="badge bg-warning-subtle text-warning">
                                    <i class="fas fa-clock me-1"></i>Pendiente
                                </span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Progreso:</label>
                        <div class="d-flex align-items-center">
                            <div class="progress flex-grow-1 me-2" style="height: 8px;">
                                <div class="progress-bar bg-success" style="width: {{ $treatmentPlan->progress ?? 0 }}%"></div>
                            </div>
                            <small class="text-muted">{{ $treatmentPlan->progress ?? 0 }}%</small>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Prioridad:</label>
                        <div>
                            @if($treatmentPlan->priority === 'high')
                                <span class="badge bg-danger-subtle text-danger">
                                    <i class="fas fa-exclamation-triangle me-1"></i>Alta
                                </span>
                            @elseif($treatmentPlan->priority === 'medium')
                                <span class="badge bg-warning-subtle text-warning">
                                    <i class="fas fa-exclamation me-1"></i>Media
                                </span>
                            @else
                                <span class="badge bg-success-subtle text-success">
                                    <i class="fas fa-check me-1"></i>Baja
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Detalles del Plan -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-info-circle fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Detalles del Plan</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Paciente:</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-label-primary me-2">
                                        @if($treatmentPlan->patient->gender === 'male')
                                            <i class="fas fa-male text-primary"></i>
                                        @elseif($treatmentPlan->patient->gender === 'female')
                                            <i class="fas fa-female text-danger"></i>
                                        @else
                                            <i class="fas fa-user text-secondary"></i>
                                        @endif
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $treatmentPlan->patient->first_name ?? 'N/A' }} {{ $treatmentPlan->patient->last_name ?? '' }}</h6>
                                        <small class="text-muted">{{ $treatmentPlan->patient->email ?? 'Sin email' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Profesional:</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle me-3">
                                        <i class="fas fa-user-md text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">Dr(a). {{ $treatmentPlan->staff->user->first_name ?? 'N/A' }} {{ $treatmentPlan->staff->user->last_name ?? '' }}</h6>
                                        <small class="text-muted">{{ $treatmentPlan->staff->specialty ?? 'Sin especialidad' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Fecha de Inicio:</label>
                                <p class="mb-0">{{ $treatmentPlan->start_date ? \Carbon\Carbon::parse($treatmentPlan->start_date)->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Fecha de Finalización:</label>
                                <p class="mb-0">{{ $treatmentPlan->end_date ? \Carbon\Carbon::parse($treatmentPlan->end_date)->format('d/m/Y') : 'N/A' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Descripción:</label>
                        <p class="mb-0">{{ $treatmentPlan->description ?? 'Sin descripción' }}</p>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Costo Total:</label>
                                <p class="mb-0">${{ number_format($treatmentPlan->total_cost ?? 0, 2) }}</p>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Plan de Pago:</label>
                                <p class="mb-0">{{ ucfirst($treatmentPlan->payment_plan ?? 'N/A') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Items del Plan -->
            @if($treatmentPlan->items && $treatmentPlan->items->count() > 0)
            <div class="card mt-3">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-list fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Items del Plan</h4>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Tratamiento</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($treatmentPlan->items as $item)
                                <tr>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="fw-semibold">{{ $item->cdtCatalog->name ?? 'Sin nombre' }}</span>
                                            <small class="text-muted">{{ $item->cdtCatalog->description ?? 'Sin descripción' }}</small>
                                        </div>
                                    </td>
                                    <td>{{ $item->quantity ?? 0 }}</td>
                                    <td>${{ number_format($item->unit_price ?? 0, 2) }}</td>
                                    <td>${{ number_format(($item->quantity ?? 0) * ($item->unit_price ?? 0), 2) }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
    <!-- end row -->
@endsection

@section('scripts')
<script>
// Auto-ocultar toast de éxito después de 3 segundos con transición suave
document.addEventListener('DOMContentLoaded', function() {
    const successToast = document.getElementById('successToast');
    if (successToast) {
        const toast = new bootstrap.Toast(successToast, {
            autohide: false,
            delay: 0
        });
        toast.show();
        
        // Aplicar transición de desvanecimiento después de 3 segundos
        setTimeout(function() {
            successToast.classList.add('fade-out');
            
            // Remover el elemento después de la animación
            setTimeout(function() {
                successToast.remove();
            }, 500); // Duración de la animación fadeOut
        }, 3000);
    }
});
</script>
@endsection
