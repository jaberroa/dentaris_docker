@extends('layouts.master')

@section('title')
    Perfil del Paciente
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
    
    /* Colores para estados de citas */
    .bg-purple-subtle {
        background-color: #e7d6ff !important;
    }
    .text-purple {
        color: #8b5cf6 !important;
    }
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">Gestión de Pacientes</h4>
                    <p class="text-muted mb-3">Perfil del paciente: {{ $patient->first_name }} {{ $patient->last_name }}</p>
                </div>
                <div class="page-title-right">
                    <div class="d-flex gap-2">
                        <a href="{{ route('patients.edit', $patient) }}" class="btn btn-outline-warning">
                            <i class="fas fa-edit me-1"></i>
                            Editar
                        </a>
                        <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
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
        <!-- Información del Paciente -->
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body text-center">
                    <div class="avatar avatar-xl avatar-label-primary mx-auto mb-3">
                        @if($patient->gender === 'male')
                            <i class="fas fa-male text-primary fs-144"></i>
                        @elseif($patient->gender === 'female')
                            <i class="fas fa-female text-danger fs-144"></i>
                        @else
                            <i class="fas fa-user text-secondary fs-144"></i>
                        @endif
                    </div>
                    <h4 class="mb-1">{{ $patient->first_name }} {{ $patient->last_name }}</h4>
                    <p class="text-muted mb-3">
                        <span class="fw-semibold">{{ strtoupper(substr($patient->first_name, 0, 1)) }}{{ strtoupper(substr($patient->last_name, 0, 1)) }}-{{ str_pad($patient->id, 4, '0', STR_PAD_LEFT) }}</span>
                    </p>
                    
                    <div class="row text-center">
                        <div class="col-6">
                            <div class="border-end">
                                <h5 class="mb-1">{{ \Carbon\Carbon::parse($patient->birth_date)->age }}</h5>
                                <p class="text-muted mb-0">Años</p>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="mb-1">
                                @if($patient->gender == 'male')
                                    <span class="badge bg-primary-subtle text-primary">
                                        <i class="fas fa-male me-1"></i>Masculino
                                    </span>
                                @elseif($patient->gender == 'female')
                                    <span class="badge bg-danger-subtle text-danger">
                                        <i class="fas fa-female me-1"></i>Femenino
                                    </span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">
                                        <i class="fas fa-question me-1"></i>Otro
                                    </span>
                                @endif
                            </div>
                            <p class="text-muted mb-0">Género</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado del Paciente -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-info-circle fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Estado</h4>
                </div>
                <div class="card-body text-center">
                    @php
                        $statusConfig = [
                            'active' => ['color' => 'success', 'icon' => 'fa-check', 'text' => 'Activo'],
                            'inactive' => ['color' => 'secondary', 'icon' => 'fa-times', 'text' => 'Inactivo']
                        ];
                        $currentStatus = $patient->is_active ? 'active' : 'inactive';
                        $config = $statusConfig[$currentStatus];
                    @endphp
                    <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }} fs-6 px-3 py-2">
                        <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                    </span>
                    <p class="text-muted mt-2 mb-0">
                        {{ $patient->is_active ? 'Paciente activo en el sistema' : 'Paciente inactivo temporalmente' }}
                    </p>
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
                    @if($patient->phone)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-label-success me-3">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $patient->phone }}</div>
                                <small class="text-muted">Teléfono principal</small>
                            </div>
                        </div>
                    @endif

                    @if($patient->phone_secondary)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-label-info me-3">
                                <i class="fas fa-mobile-alt"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $patient->phone_secondary }}</div>
                                <small class="text-muted">Teléfono secundario</small>
                            </div>
                        </div>
                    @endif

                    @if($patient->email)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-label-warning me-3">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $patient->email }}</div>
                                <small class="text-muted">Correo electrónico</small>
                            </div>
                        </div>
                    @endif

                    @if($patient->address)
                        <div class="d-flex align-items-start">
                            <div class="avatar avatar-sm avatar-label-secondary me-3">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $patient->address }}</div>
                                @if($patient->city || $patient->state)
                                    <small class="text-muted">{{ $patient->city }}{{ $patient->city && $patient->state ? ', ' : '' }}{{ $patient->state }}</small>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Información Médica -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-stethoscope fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Información Médica</h4>
                </div>
                <div class="card-body">
                    @if($patient->blood_type)
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-label-danger me-3">
                                <i class="fas fa-tint"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $patient->blood_type }}</div>
                                <small class="text-muted">Tipo de sangre</small>
                            </div>
                        </div>
                    @endif

                    @if($patient->allergies)
                        <div class="mb-3">
                            <h6 class="text-danger mb-2">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Alergias
                            </h6>
                            <p class="text-muted mb-0">{{ $patient->allergies }}</p>
                        </div>
                    @endif

                    @if($patient->medications)
                        <div class="mb-3">
                            <h6 class="text-info mb-2">
                                <i class="fas fa-pills me-1"></i>
                                Medicamentos
                            </h6>
                            <p class="text-muted mb-0">{{ $patient->medications }}</p>
                        </div>
                    @endif

                    @if($patient->medical_history)
                        <div class="mb-3">
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-history me-1"></i>
                                Historial Médico
                            </h6>
                            <p class="text-muted mb-0">{{ Str::limit($patient->medical_history, 100) }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Contacto de Emergencia -->
            @if($patient->emergency_contact_name)
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-phone-alt fs-14 text-muted"></i>
                        </div>
                        <h4 class="card-title mb-0">Contacto de Emergencia</h4>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar avatar-sm avatar-label-warning me-3">
                                <i class="fas fa-user"></i>
                            </div>
                            <div>
                                <div class="fw-medium">{{ $patient->emergency_contact_name }}</div>
                                <small class="text-muted">{{ $patient->emergency_contact_relationship ?? 'Contacto' }}</small>
                            </div>
                        </div>

                        @if($patient->emergency_contact_phone)
                            <div class="d-flex align-items-center">
                                <div class="avatar avatar-sm avatar-label-danger me-3">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div>
                                    <div class="fw-medium">{{ $patient->emergency_contact_phone }}</div>
                                    <small class="text-muted">Teléfono de emergencia</small>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Historial Clínico -->
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-clipboard-list fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Historial Clínico</h4>
                    <div class="card-addon">
                        <button class="btn btn-primary btn-sm" data-bs-toggle="modal" data-bs-target="#newRegistroModal">
                            <i class="fas fa-plus me-1"></i>
                            Nueva Historia
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if($patient->medicalRecords && $patient->medicalRecords->count() > 0)
                        <div class="timeline timeline-timed">
                            @foreach($patient->medicalRecords->sortByDesc('created_at') as $record)
                                <div class="timeline-item">
                                    <span class="timeline-time">{{ $record->created_at->format('d/m/Y H:i') }}</span>
                                    <div class="timeline-pin">
                                        <i class="marker marker-circle text-primary"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div>
                                                <h6 class="mb-1">{{ ucfirst($record->record_type) }}</h6>
                                                <p class="text-muted mb-2">{{ Str::limit($record->chief_complaint, 100) }}</p>
                                                <div class="d-flex align-items-center">
                                                    <div class="avatar avatar-sm avatar-circle me-3">
                                                        <i class="fas fa-user-md text-success"></i>
                                                    </div>
                                                    <small class="text-muted">Dr. {{ $record->staff->user->name }}</small>
                                                </div>
                                            </div>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-outline-secondary dropdown-toggle" data-bs-toggle="dropdown">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                                <div class="dropdown-menu dropdown-menu-end">
                                                    <a href="javascript:void(0)" class="dropdown-item" onclick="viewRegistro({{ $record->id }})">
                                                        <i class="fas fa-eye me-2"></i>Ver detalles
                                                    </a>
                                                    <a href="javascript:void(0)" class="dropdown-item" onclick="editRegistro({{ $record->id }})">
                                                        <i class="fas fa-edit me-2"></i>Editar
                                                    </a>
                                                    @if($record->is_confidential)
                                                        <div class="dropdown-divider"></div>
                                                        <span class="dropdown-item-text text-warning">
                                                            <i class="fas fa-lock me-2"></i>Confidencial
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-5">
                            <div class="avatar avatar-xl avatar-label-primary mx-auto mb-3">
                                <i class="fas fa-clipboard-list fs-144"></i>
                            </div>
                            <h5 class="text-muted">No hay historias clínicas</h5>
                            <p class="text-muted mb-4">Comienza agregando la primera historia clínica del paciente.</p>
                            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newRegistroModal">
                                <i class="fas fa-plus me-1"></i>
                                Agregar Primera Historia
                            </button>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Citas Recientes -->
            <div class="card">
                <div class="card-header">
                    <div class="card-icon">
                        <i class="fas fa-calendar-alt fs-14 text-muted"></i>
                    </div>
                    <h4 class="card-title mb-0">Citas Recientes</h4>
                    <div class="card-addon">
                        <a href="{{ route('appointments.create', ['patient_id' => $patient->id]) }}" class="btn btn-outline-primary btn-sm">
                            <i class="fas fa-plus me-1"></i>
                            Nueva Cita
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if($patient->appointments && $patient->appointments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Fecha</th>
                                        <th>Hora</th>
                                        <th>Doctor</th>
                                        <th>Estado</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($patient->appointments->sortByDesc('start_time')->take(5) as $appointment)
                                        <tr>
                                            <td>{{ $appointment->start_time->format('d/m/Y') }}</td>
                                            <td>{{ $appointment->start_time->format('H:i') }}</td>
                                            <td>{{ $appointment->staff->user->name }}</td>
                                            <td>
                                                @php
                                                    $statusConfig = [
                                                        'Pendiente' => ['color' => 'primary', 'icon' => 'fa-clock', 'text' => 'Pendiente'],
                                                        'Confirmada' => ['color' => 'success', 'icon' => 'fa-check-circle', 'text' => 'Confirmada'],
                                                        'En Curso' => ['color' => 'warning', 'icon' => 'fa-play-circle', 'text' => 'En Curso'],
                                                        'Completada' => ['color' => 'info', 'icon' => 'fa-check-double', 'text' => 'Completada'],
                                                        'Cancelada' => ['color' => 'danger', 'icon' => 'fa-times-circle', 'text' => 'Cancelada'],
                                                        'No Asistió' => ['color' => 'secondary', 'icon' => 'fa-user-times', 'text' => 'No Asistió'],
                                                        'Reprogramada' => ['color' => 'purple', 'icon' => 'fa-sync-alt', 'text' => 'Reprogramada']
                                                    ];
                                                    $status = $appointment->status->name ?? 'Pendiente';
                                                    $config = $statusConfig[$status] ?? ['color' => 'secondary', 'icon' => 'fa-question-circle', 'text' => 'Sin estado'];
                                                @endphp
                                                <span class="badge bg-{{ $config['color'] }}-subtle text-{{ $config['color'] }}">
                                                    <i class="fas {{ $config['icon'] }} me-1"></i>{{ $config['text'] }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-1">
                                                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-sm btn-outline-primary" title="Ver">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-3">
                            <p class="text-muted mb-0">No hay citas registradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Nueva Historia Clínica -->
    <div class="modal fade" id="newRegistroModal" tabindex="-1" aria-labelledby="newRegistroModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="newRegistroModalLabel">Nueva Historia Clínica</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('medical-records.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="patient_id" value="{{ $patient->id }}">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="record_type" class="form-label">Tipo de Consulta <span class="text-danger">*</span></label>
                                    <select class="form-select" id="record_type" name="record_type" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="consulta">Consulta</option>
                                        <option value="tratamiento">Tratamiento</option>
                                        <option value="seguimiento">Seguimiento</option>
                                        <option value="urgencia">Urgencia</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="staff_id" class="form-label">Doctor <span class="text-danger">*</span></label>
                                    <select class="form-select" id="staff_id" name="staff_id" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach(\App\Models\Staff::with('user')->get() as $staff)
                                            <option value="{{ $staff->id }}">{{ $staff->user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="chief_complaint" class="form-label">Motivo de Consulta <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="chief_complaint" name="chief_complaint" rows="2" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="present_illness" class="form-label">Enfermedad Actual</label>
                            <textarea class="form-control" id="present_illness" name="present_illness" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="clinical_examination" class="form-label">Examen Clínico</label>
                            <textarea class="form-control" id="clinical_examination" name="clinical_examination" rows="3"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="diagnostic_impression" class="form-label">Impresión Diagnóstica</label>
                            <textarea class="form-control" id="diagnostic_impression" name="diagnostic_impression" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="treatment_plan" class="form-label">Plan de Tratamiento</label>
                            <textarea class="form-control" id="treatment_plan" name="treatment_plan" rows="2"></textarea>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_confidential" name="is_confidential" value="1">
                                <label class="form-check-label" for="is_confidential">
                                    Información confidencial
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelarar</button>
                        <button type="submit" class="btn btn-primary">Guardar Historia</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
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

function viewRegistro(recordId) {
    // Redirigir a la vista de detalles del registro médico
    window.location.href = `/medical-records/${recordId}`;
}

function editRegistro(recordId) {
    // Redirigir a la vista de edición del registro médico
    window.location.href = `/medical-records/${recordId}/edit`;
}
</script>
@endsection
