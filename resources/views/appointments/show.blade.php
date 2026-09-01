@extends('layouts.master')

@section('title')
    Detalles de Cita
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('content')
    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="appointmentShowSuccessToast">
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

    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Detalles de Cita</h4>
                    <p class="text-muted mb-0">Información completa de la cita #{{ $appointment->id }}</p>
                </div>
                <div class="page-title-right">
                    <div class="d-flex gap-2">
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver
                        </a>
                        <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-outline-primary">
                            <i class="fas fa-edit me-1"></i>
                            Editar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Información de la Cita</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Paciente</label>
                                <div class="d-flex align-items-center">
                                    <div class="avatar avatar-sm avatar-circle me-3">
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
                                        <small class="text-muted">{{ $appointment->patient->patient_code ?? 'Sin código' }}</small>
                                    </div>
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
                                        <h6 class="mb-0">{{ $appointment->staff->user->name ?? 'N/A' }}</h6>
                                        <small class="text-muted">{{ $appointment->staff->specialty ?? 'Sin especialidad' }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Fecha</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <span>{{ $appointment->appointment_date ? \Carbon\Carbon::parse($appointment->appointment_date)->format('d/m/Y') : 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Hora de Inicio</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-success me-2"></i>
                                    <span>{{ $appointment->start_time ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Hora de Fin</label>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-clock text-danger me-2"></i>
                                    <span>{{ $appointment->end_time ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Tipo de Cita</label>
                                <div>
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
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Estado</label>
                                <div>
                                    @php
                                        $statusConfig = [
                                            'Pendiente' => ['color' => 'primary', 'icon' => 'fa-clock'],
                                            'Confirmada' => ['color' => 'success', 'icon' => 'fa-check-circle'],
                                            'En Curso' => ['color' => 'warning', 'icon' => 'fa-play-circle'],
                                            'Completada' => ['color' => 'info', 'icon' => 'fa-check-double'],
                                            'Cancelada' => ['color' => 'danger', 'icon' => 'fa-times-circle'],
                                            'No Asistió' => ['color' => 'secondary', 'icon' => 'fa-user-times'],
                                            'Reprogramada' => ['color' => 'purple', 'icon' => 'fa-sync-alt']
                                        ];
                                        $status = $appointment->status->name ?? 'Pendiente';
                                        $config = $statusConfig[$status] ?? ['color' => 'secondary', 'icon' => 'fa-question-circle'];
                                    @endphp
                                    <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }}">
                                        <i class="fas {{ $config['icon'] }} me-1"></i>{{ $appointment->status->display_name ?? 'Sin estado' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    @if($appointment->notes)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Notas</label>
                            <div class="border rounded p-3 bg-light">
                                {{ $appointment->notes }}
                            </div>
                        </div>
                    @endif

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Recordatorio Enviado</label>
                                <div>
                                    @if($appointment->reminder_sent)
                                        <span class="badge bg-success">
                                            <i class="fas fa-check me-1"></i>Sí
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>No
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Cita Recurrente</label>
                                <div>
                                    @if($appointment->is_recurring)
                                        <span class="badge bg-info">
                                            <i class="fas fa-repeat me-1"></i>Sí
                                        </span>
                                    @else
                                        <span class="badge bg-secondary">
                                            <i class="fas fa-times me-1"></i>No
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Acciones</h4>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        @if($appointment->status->name == 'Pendiente')
                            <form action="{{ route('appointments.confirm', $appointment) }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-success w-100">
                                    <i class="fas fa-check me-1"></i>
                                    Confirmar Cita
                                </button>
                            </form>
                        @endif

                        @if(in_array($appointment->status->name, ['Pendiente', 'Confirmada']))
                            <button type="button" class="btn btn-warning w-100" data-bs-toggle="modal" data-bs-target="#cancelModal">
                                <i class="fas fa-times me-1"></i>
                                Cancelar Cita
                            </button>
                        @endif

                        <a href="{{ route('appointments.edit', $appointment) }}" class="btn btn-primary w-100">
                            <i class="fas fa-edit me-1"></i>
                            Editar Cita
                        </a>

                        <a href="{{ route('patients.show', $appointment->patient) }}" class="btn btn-outline-info w-100">
                            <i class="fas fa-user me-1"></i>
                            Ver Paciente
                        </a>
                    </div>
                </div>
            </div>

            <div class="card mt-3">
                <div class="card-header">
                    <h4 class="card-title mb-0">Información del Sistema</h4>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Creado por</label>
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-xs me-2">
                                @if($appointment->creator->gender === 'male')
                                    <i class="fas fa-male text-primary"></i>
                                @elseif($appointment->creator->gender === 'female')
                                    <i class="fas fa-female text-danger"></i>
                                @else
                                    <i class="fas fa-user text-secondary"></i>
                                @endif
                            </div>
                            <span>{{ $appointment->creator->name ?? 'Sistema' }}</span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Fecha de Creación</label>
                        <div>
                            <i class="fas fa-calendar-plus text-muted me-1"></i>
                            {{ $appointment->created_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Última Actualización</label>
                        <div>
                            <i class="fas fa-edit text-muted me-1"></i>
                            {{ $appointment->updated_at->format('d/m/Y H:i') }}
                        </div>
                    </div>

                    @if($appointment->cancelled_at)
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Fecha de Cancelación</label>
                            <div>
                                <i class="fas fa-times-circle text-danger me-1"></i>
                                {{ \Carbon\Carbon::parse($appointment->cancelled_at)->format('d/m/Y H:i') }}
                            </div>
                        </div>
                        
                        @if($appointment->cancellation_reason)
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Motivo de Cancelación</label>
                                <div class="border rounded p-2 bg-light">
                                    {{ $appointment->cancellation_reason }}
                                </div>
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Modal de Cancelación -->
    <div class="modal fade" id="cancelModal" tabindex="-1" aria-labelledby="cancelModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancelModalLabel">Cancelar Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('appointments.cancel', $appointment) }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="cancellation_reason" class="form-label">Motivo de cancelación <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="cancellation_reason" name="cancellation_reason" rows="3" required placeholder="Ingresa el motivo de la cancelación..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-danger">Cancelar Cita</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('css')
<link href="{{ asset('css/toast-custom.css') }}" rel="stylesheet" />
<style>
    /* Colores para estados */
    .bg-purple-subtle {
        background-color: #e7d6ff !important;
    }
    .text-purple {
        color: #8b5cf6 !important;
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
    
    /* Animación para toast fade-out */
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
</style>
@endsection

@section('scripts')
<script src="{{ asset('js/appointments.js') }}"></script>
<script>
    // Inicializar toast de éxito inmediatamente
    document.addEventListener('DOMContentLoaded', function() {
        const successToast = document.getElementById('appointmentShowSuccessToast');
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

    $(document).ready(function() {
        // Inicializar módulo de appointments
        AppointmentsModule.initialize();

    // Auto-focus en el textarea del modal
    $('#cancelModal').on('shown.bs.modal', function () {
        $('#cancellation_reason').focus();
        });
    });
</script>
@endsection
