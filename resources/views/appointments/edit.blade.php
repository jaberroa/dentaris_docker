@extends('layouts.master')

@section('title')
    Editar Cita
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('content')
    <!-- Header Principal -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">EDITAR CITA</h4>
                    <p class="text-muted mb-3">Modifica la información de la cita {{ $appointment->appointment_code }}</p>
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
                    <form action="{{ route('appointments.update', $appointment) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_patient_id" class="form-label">Paciente <span class="text-danger">*</span></label>
                                    <select class="form-select @error('patient_id') is-invalid @enderror" id="edit_patient_id" name="patient_id" required>
                                        <option value="">Seleccionar paciente</option>
                                        @if(old('patient_id', $appointment->patient_id))
                                            @php
                                                $selectedPatient = \App\Models\Patient::find(old('patient_id', $appointment->patient_id));
                                            @endphp
                                            @if($selectedPatient)
                                                <option value="{{ $selectedPatient->id }}" selected>
                                                    {{ $selectedPatient->first_name }} {{ $selectedPatient->last_name }} - {{ $selectedPatient->display_code }}
                                                </option>
                                            @endif
                                        @endif
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_staff_id" class="form-label">Odontólogo <span class="text-danger">*</span></label>
                                    <select class="form-select @error('staff_id') is-invalid @enderror" id="edit_staff_id" name="staff_id" required>
                                        <option value="">Seleccionar odontólogo</option>
                                        @if(old('staff_id', $appointment->staff_id))
                                            @php
                                                $selectedStaff = \App\Models\Staff::with('user')->find(old('staff_id', $appointment->staff_id));
                                            @endphp
                                            @if($selectedStaff)
                                                <option value="{{ $selectedStaff->id }}" selected>
                                                    {{ $selectedStaff->user->name }} - {{ $selectedStaff->specialty }}
                                                </option>
                                            @endif
                                        @endif
                                    </select>
                                    @error('staff_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_appointment_date" class="form-label">Fecha <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('appointment_date') is-invalid @enderror"
                                           id="edit_appointment_date" name="appointment_date"
                                           value="{{ old('appointment_date', $appointment->appointment_date->format('Y-m-d')) }}" required>
                                    @error('appointment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_start_time" class="form-label">Hora de Inicio <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                           id="edit_start_time" name="start_time"
                                           value="{{ old('start_time', $appointment->start_time) }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="edit_end_time" class="form-label">Hora de Fin <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                           id="edit_end_time" name="end_time"
                                           value="{{ old('end_time', $appointment->end_time) }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Campo hidden para duration -->
                        <input type="hidden" id="edit_duration" name="duration" value="{{ old('duration', $appointment->duration) }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_type" class="form-label">Tipo de Cita</label>
                                    <select class="form-select @error('type') is-invalid @enderror" id="edit_type" name="type">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="consultation" {{ old('type', $appointment->type) == 'consultation' ? 'selected' : '' }}>Consulta</option>
                                        <option value="treatment" {{ old('type', $appointment->type) == 'treatment' ? 'selected' : '' }}>Tratamiento</option>
                                        <option value="cleaning" {{ old('type', $appointment->type) == 'cleaning' ? 'selected' : '' }}>Limpieza</option>
                                        <option value="emergency" {{ old('type', $appointment->type) == 'emergency' ? 'selected' : '' }}>Emergencia</option>
                                        <option value="follow_up" {{ old('type', $appointment->type) == 'follow_up' ? 'selected' : '' }}>Seguimiento</option>
                                    </select>
                                    @error('type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_appointment_status_id" class="form-label">Estado</label>
                                    <select class="form-select @error('appointment_status_id') is-invalid @enderror" id="edit_appointment_status_id" name="appointment_status_id">
                                        @foreach(\App\Models\AppointmentStatus::orderBy('id')->get() as $status)
                                            <option value="{{ $status->id }}" {{ old('appointment_status_id', $appointment->appointment_status_id) == $status->id ? 'selected' : '' }}>
                                                {{ $status->display_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('appointment_status_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="edit_notes" class="form-label">Notas</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror"
                                              id="edit_notes" name="notes" rows="4"
                                              placeholder="Notas adicionales sobre la cita...">{{ old('notes', $appointment->notes) }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_reminder_sent" class="form-label">Registroatorio</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="edit_reminder_sent" name="reminder_sent" value="1" {{ old('reminder_sent', $appointment->reminder_sent) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="edit_reminder_sent">
                                            Enviar recordatorio al paciente
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_recurring" class="form-label">Cita Recurrente</label>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_recurring" name="is_recurring" value="1" {{ old('is_recurring', $appointment->is_recurring) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_recurring">
                                            Programar como cita recurrente
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_urgent" name="is_urgent" value="1" {{ old('is_urgent', $appointment->is_urgent) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_urgent">
                                            Cita urgente
                                        </label>
                                </div>
                            </div>
                        </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_follow_up" name="is_follow_up" value="1" {{ old('is_follow_up', $appointment->is_follow_up) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_follow_up">
                                            Cita de seguimiento
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="d-flex justify-content-end gap-2">
                                    <a href="{{ route('appointments.show', $appointment) }}" class="btn btn-outline-secondary">
                                        <i class="fas fa-eye me-1"></i>
                                        Ver Detalles
                                    </a>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="fas fa-save me-1"></i>
                                        Actualizar Cita
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensajes de éxito/error -->
    @if(session('success'))
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index: 1055;">
            <div class="toast show" role="alert" aria-live="assertive" aria-atomic="true" id="appointmentEditSuccessToast">
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
@endsection

@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="{{ asset('css/select2-custom.css') }}" rel="stylesheet" />
<link href="{{ asset('css/toast-custom.css') }}" rel="stylesheet" />
@endsection

@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="{{ asset('js/appointments.js') }}"></script>
<script>
    $(document).ready(function() {
        // Inicializar módulo de appointments
        AppointmentsModule.initialize();
        
        // Inicializar Select2 para pacientes
        AppointmentsModule.initializePatientSelect2('#edit_patient_id');
        
        // Inicializar Select2 para personal médico
        AppointmentsModule.initializeStaffSelect2('#edit_staff_id');
        
        // Inicializar validaciones
        AppointmentsModule.initializeTimeCalculation('#edit_start_time', '#edit_end_time', '#edit_duration', 60);
        AppointmentsModule.initializeDateValidation('#edit_appointment_date');
        AppointmentsModule.initializeTimeValidation('#edit_start_time', '#edit_end_time');
        
        // Inicializar toast de éxito
        AppointmentsModule.initializeSuccessToast('appointmentEditSuccessToast');
    });
</script>
@endsection
