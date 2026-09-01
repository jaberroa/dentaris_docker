@extends('layouts.master')

@section('title')
    Detalles del Registro Médico
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
        animation: slideInRight 0.3s ease-out;
    }
    
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
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
</style>
@endsection

@section('content')
    <!-- Mensaje de éxito flotante -->
    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="successToast">
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
                            <i class="fas fa-file-medical fs-12"></i>
                        </div>
                        <span class="text-muted">{{ session('success') }}</span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Detalles del Registro Médico</h4>
                    <p class="text-muted mb-3">Información completa del registro médico #{{ $medicalRecord->id }}.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('patients.show', $medicalRecord->patient) }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver
                    </a>
                    <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="btn btn-outline-primary">
                        <i class="fas fa-edit me-1"></i>
                        Editar
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-file-medical fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Información del Registro Médico</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Paciente</label>
                                <div>
                                    <i class="fas fa-user text-primary me-1"></i>
                                    <span class="fw-medium">{{ $medicalRecord->patient->first_name }} {{ $medicalRecord->patient->last_name }}</span>
                                    <small class="text-muted ms-2">({{ $medicalRecord->patient->patient_code }})</small>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Fecha del Registro</label>
                                <div>
                                    <i class="fas fa-calendar text-primary me-1"></i>
                                    {{ $medicalRecord->created_at->format('d/m/Y H:i') }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tipo de Consulta</label>
                                <div>
                                    <span class="badge bg-primary-subtle text-primary">
                                        {{ $medicalRecord->consultation_type ?? 'Consulta General' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Odontólogo</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle me-3">
                                        <i class="fas fa-user-md text-success"></i>
                                    </div>
                                    <div>
                                        <h6 class="mb-0">{{ $medicalRecord->staff->first_name ?? 'No asignado' }} {{ $medicalRecord->staff->last_name ?? '' }}</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Motivo de Consulta</label>
                                <div class="border rounded p-3 bg-light">
                                    {{ $medicalRecord->consultation_reason ?? 'No especificado' }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Diagnóstico</label>
                                <div class="border rounded p-3 bg-light">
                                    {{ $medicalRecord->diagnosis ?? 'Sin diagnóstico' }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tratamiento</label>
                                <div class="border rounded p-3 bg-light">
                                    {{ $medicalRecord->treatment ?? 'Sin tratamiento especificado' }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-12">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Observaciones</label>
                                <div class="border rounded p-3 bg-light">
                                    {{ $medicalRecord->observations ?? 'Sin observaciones' }}
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Próxima Cita</label>
                                <div>
                                    @if($medicalRecord->next_appointment)
                                        <i class="fas fa-calendar-check text-info me-1"></i>
                                        {{ \Carbon\Carbon::parse($medicalRecord->next_appointment)->format('d/m/Y H:i') }}
                                    @else
                                        <span class="text-muted">No programada</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Estado</label>
                                <div>
                                    @php
                                        $statusColors = [
                                            'active' => 'success',
                                            'completed' => 'info',
                                            'pending' => 'warning',
                                            'cancelled' => 'danger'
                                        ];
                                        $statusColor = $statusColors[$medicalRecord->status] ?? 'secondary';
                                    @endphp
                                    <span class="badge bg-{{ $statusColor }}-subtle text-{{ $statusColor }}">
                                        {{ ucfirst($medicalRecord->status ?? 'Activo') }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-user fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Información del Paciente</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Nombre Completo</label>
                        <div>
                            <i class="fas fa-user text-primary me-1"></i>
                            {{ $medicalRecord->patient->first_name }} {{ $medicalRecord->patient->last_name }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Código de Paciente</label>
                        <div>
                            <span class="badge bg-primary-subtle text-primary">
                                {{ $medicalRecord->patient->patient_code }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Teléfono</label>
                        <div>
                            <i class="fas fa-phone text-success me-1"></i>
                            {{ $medicalRecord->patient->phone ?? 'No especificado' }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Correo Electrónico</label>
                        <div>
                            <i class="fas fa-envelope text-info me-1"></i>
                            {{ $medicalRecord->patient->email ?? 'No especificado' }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fecha de Nacimiento</label>
                        <div>
                            <i class="fas fa-birthday-cake text-warning me-1"></i>
                            @if($medicalRecord->patient->birth_date)
                                {{ \Carbon\Carbon::parse($medicalRecord->patient->birth_date)->format('d/m/Y') }}
                                <small class="text-muted ms-2">
                                    ({{ \Carbon\Carbon::parse($medicalRecord->patient->birth_date)->age }} años)
                                </small>
                            @else
                                No especificado
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-info-circle fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Información del Sistema</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Creado por</label>
                        <div>
                            <i class="fas fa-user text-primary me-1"></i>
                            {{ $medicalRecord->createdBy->name ?? 'Sistema' }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fecha de Creación</label>
                        <div>
                            <i class="fas fa-calendar text-success me-1"></i>
                            {{ $medicalRecord->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Última Actualización</label>
                        <div>
                            <i class="fas fa-edit text-info me-1"></i>
                            {{ $medicalRecord->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-semibold">ID del Registro</label>
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary">
                                #{{ $medicalRecord->id }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-cogs fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Acciones</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <a href="{{ route('medical-records.edit', $medicalRecord) }}" class="btn btn-primary w-100">
                                <i class="fas fa-edit me-1"></i>
                                Editar Registro
                            </a>
                        </div>
                        
                        <div class="col-md-3">
                            <a href="{{ route('patients.show', $medicalRecord->patient) }}" class="btn btn-info w-100">
                                <i class="fas fa-user me-1"></i>
                                Ver Paciente
                            </a>
                        </div>
                        
                        <div class="col-md-3">
                            <a href="{{ route('appointments.create', ['patient_id' => $medicalRecord->patient->id]) }}" class="btn btn-success w-100">
                                <i class="fas fa-calendar-plus me-1"></i>
                                Nueva Cita
                            </a>
                        </div>
                        
                        <div class="col-md-3">
                            <button type="button" class="btn btn-danger w-100" data-bs-toggle="modal" data-bs-target="#deleteModal">
                                <i class="fas fa-trash me-1"></i>
                                Eliminar
                            </button>
                        </div>
                    </div>
                </div>
            </div>
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

