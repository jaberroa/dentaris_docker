@extends('layouts.master')

@section('title')
    Nueva Cita
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Nueva Cita</h4>
                    <p class="text-muted mb-0">Programa una nueva cita médica</p>
                </div>
                <div class="page-title-right">
                    <div class="d-flex gap-2">
                        <a href="{{ route('appointments.index') }}" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-1"></i>
                            Volver
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
                <div class="card-header">
                    <h4 class="card-title mb-0">Información de la Cita</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('appointments.store') }}" method="POST">
                        @csrf
                        
                        <div class="row">
                            <div class="col-md-6">
                                <x-patient-select 
                                    name="patient_id" 
                                    id="appointment_patient_id" 
                                    label="Paciente" 
                                    :patients="$patients" 
                                />
                            </div>
                            
                            <div class="col-md-6">
                                <x-staff-select 
                                    name="staff_id" 
                                    id="appointment_staff_id" 
                                    label="Profesional" 
                                    :staff="$staff" 
                                />
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="appointment_date" class="form-label">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('appointment_date') is-invalid @enderror" 
                                           id="appointment_date" name="appointment_date" 
                                           value="{{ old('appointment_date', now()->format('Y-m-d')) }}" required>
                                    @error('appointment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Hora de Inicio <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror" 
                                           id="start_time" name="start_time" 
                                           value="{{ old('start_time') }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">Hora de Fin <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror" 
                                           id="end_time" name="end_time" 
                                           value="{{ old('end_time') }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Campo hidden para duration -->
                        <input type="hidden" id="duration" name="duration" value="60">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="appointment_type" class="form-label">Tipo de Cita</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="type" name="type">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="consultation" {{ old('type') == 'consultation' ? 'selected' : '' }}>Consulta</option>
                                        <option value="treatment" {{ old('type') == 'treatment' ? 'selected' : '' }}>Tratamiento</option>
                                        <option value="cleaning" {{ old('type') == 'cleaning' ? 'selected' : '' }}>Limpieza</option>
                                        <option value="emergency" {{ old('type') == 'emergency' ? 'selected' : '' }}>Emergencia</option>
                                        <option value="follow_up" {{ old('type') == 'follow_up' ? 'selected' : '' }}>Seguimiento</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status_id" class="form-label">Estado</label>
                                    <select class="form-select @error('appointment_status_id') is-invalid @enderror" id="appointment_status_id" name="appointment_status_id">
                                        @foreach(\App\Models\AppointmentStatus::all() as $status)
                                            <option value="{{ $status->id }}" {{ old('appointment_status_id', 1) == $status->id ? 'selected' : '' }}>
                                                {{ ucfirst($status->name) }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('appointment_status_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="4" 
                                      placeholder="Notas adicionales sobre la cita...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="reminder_sent" class="form-label">Registroatorio</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="reminder_sent" name="reminder_sent" value="1" {{ old('reminder_sent') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="reminder_sent">
                                            Enviar recordatorio al paciente
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_recurring" class="form-label">Cita Recurrente</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring') ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_recurring">
                                            Programar como cita recurrente
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('appointments.index') }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancelarar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Crear Cita
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection

@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="{{ asset('css/select2-custom.css') }}" rel="stylesheet" />
<link href="{{ asset('css/toast-custom.css') }}" rel="stylesheet" />

<style>

/* Eliminar TODOS los fondos predeterminados de Select2 (verde, azul, gris) */
.select2-results__option {
    background-color: transparent !important;
    color: inherit !important;
}

/* Eliminar fondo azul de opciones resaltadas */
.select2-results__option--highlighted {
    background-color: transparent !important;
    color: inherit !important;
}

/* Eliminar fondo gris de opciones seleccionadas */
.select2-results__option[aria-selected="true"] {
    background-color: transparent !important;
    color: inherit !important;
}

/* Eliminar fondo verde de opciones seleccionadas */
.select2-results__option--selected {
    background-color: transparent !important;
    color: inherit !important;
}

/* Eliminar fondo azul de opciones seleccionables */
.select2-results__option--selectable {
    background-color: transparent !important;
    color: inherit !important;
}


/* SOLO hover effect gris - reemplazar todos los fondos con gris claro */
.select2-results__option:hover {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    transition: all 0.3s ease;
}

/* Hover effect específico para opciones de pacientes */
#appointment_patient_id + .select2-container .select2-results__option:hover {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    transition: all 0.3s ease;
}

/* Hover effect específico para opciones de profesionales */
#appointment_staff_id + .select2-container .select2-results__option:hover {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    transition: all 0.3s ease;
}
</style>
@endsection

@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/appointments.js') }}"></script>
<script>
    $(document).ready(function() {
        // Esperar un poco para asegurar que todo esté cargado
        setTimeout(function() {
            // Inicializar módulo de appointments
            AppointmentsModule.initialize();
            
            // Inicializar Select2 para paciente
            $('#appointment_patient_id').select2({
                placeholder: 'Buscar paciente...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron pacientes";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                },
                templateResult: function(patient) {
                    if (patient.loading) {
                        return patient.text;
                    }
                    
                    // Obtener el género del paciente desde el elemento option
                    var $option = $('#appointment_patient_id option[value="' + patient.id + '"]');
                    var gender = $option.data('gender') || 'male';
                    
                    // Definir icono y color según el género
                    var iconClass, colorClass;
                    if (gender === 'male') {
                        iconClass = 'fas fa-male';
                        colorClass = 'text-primary';
                    } else if (gender === 'female') {
                        iconClass = 'fas fa-female';
                        colorClass = 'text-danger';
                    } else {
                        iconClass = 'fas fa-user';
                        colorClass = 'text-secondary';
                    }
                    
                    var $result = $(
                        '<div class="d-flex align-items-center">' +
                            '<div class="avatar avatar-xs avatar-label-primary me-2">' +
                                '<i class="' + iconClass + ' ' + colorClass + '"></i>' +
                            '</div>' +
                            '<div>' +
                                '<div class="fw-semibold">' + patient.text + '</div>' +
                            '</div>' +
                        '</div>'
                    );
                    
                    return $result;
                }
            });
            
            // Inicializar Select2 para profesional
            $('#appointment_staff_id').select2({
                placeholder: 'Buscar profesional...',
                allowClear: true,
                language: {
                    noResults: function() {
                        return "No se encontraron profesionales";
                    },
                    searching: function() {
                        return "Buscando...";
                    }
                },
                templateResult: function(staff) {
                    if (staff.loading) {
                        return staff.text;
                    }
                    
                    var $result = $(
                        '<div class="d-flex align-items-center">' +
                            '<div class="avatar avatar-xs avatar-label-success me-2">' +
                                '<i class="fas fa-user-md text-success"></i>' +
                            '</div>' +
                            '<div>' +
                                '<div class="fw-semibold">' + staff.text + '</div>' +
                            '</div>' +
                        '</div>'
                    );
                    
                    return $result;
                }
            });
            
            // Inicializar validaciones
            AppointmentsModule.initializeTimeCalculation('#start_time', '#end_time', '#duration', 60);
            AppointmentsModule.initializeDateValidation('#appointment_date');
            AppointmentsModule.initializeTimeValidation('#start_time', '#end_time');
        }, 100);
    });
</script>
@endsection





