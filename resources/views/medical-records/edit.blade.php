@extends('layouts.master')

@section('title')
    Editar Registro Médico
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

    /* Estilos para Select2 */
    .select2-container--default .select2-selection--single {
        height: 38px;
        border: 1px solid #ced4da;
        border-radius: 0.375rem;
        background-color: #f8f9fa;
        transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
    }
    
    .select2-container--default .select2-selection--single:hover {
        border-color: #86b7fe;
        background-color: #fff;
    }
    
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #86b7fe;
        outline: 0;
        box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.25);
        background-color: #fff;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
        padding-left: 12px;
    }
    
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px;
        right: 6px;
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

    <!-- Header Principal -->
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="mb-2">EDITAR REGISTRO MÉDICO</h4>
                    <p class="text-muted mb-3">Modifica la información del registro médico #{{ $medicalRecord->id }}</p>
                </div>
                <div class="page-title-right">
                    <div class="d-flex gap-2">
                        <a href="{{ route('medical-records.show', $medicalRecord) }}" class="btn btn-outline-secondary">
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
                    <h4 class="card-title mb-0">Información del Registro Médico</h4>
                </div>
                <div class="card-body">
                    <form action="{{ route('medical-records.update', $medicalRecord) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="patient_id" class="form-label">Paciente <span class="text-danger">*</span></label>
                                    <select class="form-select @error('patient_id') is-invalid @enderror" id="patient_id" name="patient_id" required>
                                        <option value="">Seleccionar paciente</option>
                                        @if(old('patient_id', $medicalRecord->patient_id))
                                            <option value="{{ old('patient_id', $medicalRecord->patient_id) }}" selected>
                                                {{ $medicalRecord->patient->first_name }} {{ $medicalRecord->patient->last_name }}
                                            </option>
                                        @endif
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="staff_id" class="form-label">Odontólogo <span class="text-danger">*</span></label>
                                    <select class="form-select @error('staff_id') is-invalid @enderror" id="staff_id" name="staff_id" required>
                                        <option value="">Seleccionar odontólogo</option>
                                        @if(old('staff_id', $medicalRecord->staff_id))
                                            <option value="{{ old('staff_id', $medicalRecord->staff_id) }}" selected>
                                                {{ $medicalRecord->staff->first_name ?? '' }} {{ $medicalRecord->staff->last_name ?? '' }}
                                            </option>
                                        @endif
                                    </select>
                                    @error('staff_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="consultation_type" class="form-label">Tipo de Consulta</label>
                                    <select class="form-select @error('consultation_type') is-invalid @enderror" id="consultation_type" name="consultation_type">
                                        <option value="">Seleccionar tipo</option>
                                        <option value="consulta" {{ old('consultation_type', $medicalRecord->consultation_type) == 'consulta' ? 'selected' : '' }}>Consulta General</option>
                                        <option value="tratamiento" {{ old('consultation_type', $medicalRecord->consultation_type) == 'tratamiento' ? 'selected' : '' }}>Tratamiento</option>
                                        <option value="limpieza" {{ old('consultation_type', $medicalRecord->consultation_type) == 'limpieza' ? 'selected' : '' }}>Limpieza</option>
                                        <option value="emergencia" {{ old('consultation_type', $medicalRecord->consultation_type) == 'emergencia' ? 'selected' : '' }}>Emergencia</option>
                                        <option value="seguimiento" {{ old('consultation_type', $medicalRecord->consultation_type) == 'seguimiento' ? 'selected' : '' }}>Seguimiento</option>
                                        <option value="ortodoncia" {{ old('consultation_type', $medicalRecord->consultation_type) == 'ortodoncia' ? 'selected' : '' }}>Ortodoncia</option>
                                        <option value="blanqueamiento" {{ old('consultation_type', $medicalRecord->consultation_type) == 'blanqueamiento' ? 'selected' : '' }}>Blanqueamiento</option>
                                        <option value="puente" {{ old('consultation_type', $medicalRecord->consultation_type) == 'puente' ? 'selected' : '' }}>Puente</option>
                                        <option value="implante" {{ old('consultation_type', $medicalRecord->consultation_type) == 'implante' ? 'selected' : '' }}>Implante</option>
                                        <option value="extracción" {{ old('consultation_type', $medicalRecord->consultation_type) == 'extracción' ? 'selected' : '' }}>Extracción</option>
                                        <option value="endodoncia" {{ old('consultation_type', $medicalRecord->consultation_type) == 'endodoncia' ? 'selected' : '' }}>Endodoncia</option>
                                    </select>
                                    @error('consultation_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="status" class="form-label">Estado</label>
                                    <select class="form-select @error('status') is-invalid @enderror" id="status" name="status">
                                        <option value="active" {{ old('status', $medicalRecord->status) == 'active' ? 'selected' : '' }}>Activo</option>
                                        <option value="completed" {{ old('status', $medicalRecord->status) == 'completed' ? 'selected' : '' }}>Completado</option>
                                        <option value="pending" {{ old('status', $medicalRecord->status) == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                        <option value="cancelled" {{ old('status', $medicalRecord->status) == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                    </select>
                                    @error('status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="record_date" class="form-label">Fecha del Registro <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('record_date') is-invalid @enderror" id="record_date" name="record_date" value="{{ old('record_date', $medicalRecord->record_date ? $medicalRecord->record_date->format('Y-m-d') : '') }}" required>
                                    @error('record_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="next_appointment" class="form-label">Próxima Cita</label>
                                    <input type="datetime-local" class="form-control @error('next_appointment') is-invalid @enderror" id="next_appointment" name="next_appointment" value="{{ old('next_appointment', $medicalRecord->next_appointment ? $medicalRecord->next_appointment->format('Y-m-d\TH:i') : '') }}">
                                    @error('next_appointment')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="consultation_reason" class="form-label">Motivo de Consulta</label>
                            <textarea class="form-control @error('consultation_reason') is-invalid @enderror" id="consultation_reason" name="consultation_reason" rows="3" placeholder="Describa el motivo de la consulta">{{ old('consultation_reason', $medicalRecord->consultation_reason) }}</textarea>
                            @error('consultation_reason')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="diagnosis" class="form-label">Diagnóstico</label>
                            <textarea class="form-control @error('diagnosis') is-invalid @enderror" id="diagnosis" name="diagnosis" rows="3" placeholder="Describa el diagnóstico">{{ old('diagnosis', $medicalRecord->diagnosis) }}</textarea>
                            @error('diagnosis')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="treatment" class="form-label">Tratamiento</label>
                            <textarea class="form-control @error('treatment') is-invalid @enderror" id="treatment" name="treatment" rows="3" placeholder="Describa el tratamiento aplicado">{{ old('treatment', $medicalRecord->treatment) }}</textarea>
                            @error('treatment')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="observations" class="form-label">Observaciones</label>
                            <textarea class="form-control @error('observations') is-invalid @enderror" id="observations" name="observations" rows="3" placeholder="Observaciones adicionales">{{ old('observations', $medicalRecord->observations) }}</textarea>
                            @error('observations')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="requires_follow_up" name="requires_follow_up" value="1" {{ old('requires_follow_up', $medicalRecord->requires_follow_up) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="requires_follow_up">
                                            Requiere seguimiento
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="is_emergency" name="is_emergency" value="1" {{ old('is_emergency', $medicalRecord->is_emergency) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_emergency">
                                            Consulta de emergencia
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('medical-records.show', $medicalRecord) }}" class="btn btn-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Inicializar Select2 para pacientes
    $('#patient_id').select2({
        placeholder: 'Buscar paciente...',
        allowClear: true,
        ajax: {
            url: '{{ route("patients.search") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(patient) {
                        return {
                            id: patient.id,
                            text: patient.first_name + ' ' + patient.last_name + ' (' + patient.patient_code + ')'
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Inicializar Select2 para personal
    $('#staff_id').select2({
        placeholder: 'Buscar odontólogo...',
        allowClear: true,
        ajax: {
            url: '{{ route("appointments.search.staff") }}',
            dataType: 'json',
            delay: 300,
            data: function(params) {
                return {
                    q: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.map(function(staff) {
                        return {
                            id: staff.id,
                            text: staff.first_name + ' ' + staff.last_name + ' - ' + (staff.specialty || 'Odontología General')
                        };
                    })
                };
            },
            cache: true
        }
    });

    // Validación de fechas
    document.getElementById('record_date').addEventListener('change', function() {
        const selectedDate = new Date(this.value);
        const today = new Date();
        today.setHours(0, 0, 0, 0);
        
        if (selectedDate > today) {
            alert('No se pueden crear registros médicos en fechas futuras.');
            this.value = today.toISOString().split('T')[0];
        }
    });

    // Validación de próxima cita
    document.getElementById('next_appointment').addEventListener('change', function() {
        const recordDate = document.getElementById('record_date').value;
        const nextAppointment = this.value;
        
        if (recordDate && nextAppointment) {
            const recordDateObj = new Date(recordDate);
            const nextAppointmentObj = new Date(nextAppointment);
            
            if (nextAppointmentObj <= recordDateObj) {
                alert('La próxima cita debe ser posterior a la fecha del registro.');
                this.value = '';
            }
        }
    });

    // Inicializar toast de éxito
    const successToast = document.getElementById('successToast');
    if (successToast) {
        const toast = new bootstrap.Toast(successToast, {
            autohide: false,
            delay: 0
        });
        toast.show();
        
        setTimeout(function() {
            successToast.classList.add('fade-out');
            setTimeout(function() {
                successToast.remove();
            }, 500);
        }, 3000);
    }
});
</script>
@endsection

