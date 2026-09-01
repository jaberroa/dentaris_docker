@extends('layouts.master')

@section('title')
    Nuevo Paciente
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('css')
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Nuevo Paciente</h4>
                    <p class="text-muted mb-0">Registra un nuevo paciente en el sistema.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <form action="{{ route('patients.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        
        <div class="row">
            <!-- Información Personal -->
            <div class="col-lg-8">
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
                                    <label for="first_name" class="form-label">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('first_name') is-invalid @enderror" 
                                           id="first_name" name="first_name" value="{{ old('first_name') }}" required>
                                    @error('first_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="last_name" class="form-label">Apellidos <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('last_name') is-invalid @enderror" 
                                           id="last_name" name="last_name" value="{{ old('last_name') }}" required>
                                    @error('last_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="birth_date" class="form-label">Fecha de Nacimiento <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('birth_date') is-invalid @enderror" 
                                           id="birth_date" name="birth_date" value="{{ old('birth_date') }}" required>
                                    @error('birth_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="gender" class="form-label">Género <span class="text-danger">*</span></label>
                                    <select class="form-select @error('gender') is-invalid @enderror" id="gender" name="gender" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="male" {{ old('gender') == 'male' ? 'selected' : '' }}>Masculino</option>
                                        <option value="female" {{ old('gender') == 'female' ? 'selected' : '' }}>Femenino</option>
                                        <option value="other" {{ old('gender') == 'other' ? 'selected' : '' }}>Otro</option>
                                    </select>
                                    @error('gender')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}">
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Teléfono Principal</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone_secondary" class="form-label">ID/Cédula</label>
                                    <input type="text" class="form-control @error('phone_secondary') is-invalid @enderror" 
                                           id="phone_secondary" name="phone_secondary" value="{{ old('phone_secondary') }}"
                                           placeholder="Número de identificación">
                                    @error('phone_secondary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="blood_type" class="form-label">Tipo de Sangre</label>
                                    <select class="form-select @error('blood_type') is-invalid @enderror" id="blood_type" name="blood_type">
                                        <option value="">Seleccionar...</option>
                                        <option value="A+" {{ old('blood_type') == 'A+' ? 'selected' : '' }}>A+</option>
                                        <option value="A-" {{ old('blood_type') == 'A-' ? 'selected' : '' }}>A-</option>
                                        <option value="B+" {{ old('blood_type') == 'B+' ? 'selected' : '' }}>B+</option>
                                        <option value="B-" {{ old('blood_type') == 'B-' ? 'selected' : '' }}>B-</option>
                                        <option value="AB+" {{ old('blood_type') == 'AB+' ? 'selected' : '' }}>AB+</option>
                                        <option value="AB-" {{ old('blood_type') == 'AB-' ? 'selected' : '' }}>AB-</option>
                                        <option value="O+" {{ old('blood_type') == 'O+' ? 'selected' : '' }}>O+</option>
                                        <option value="O-" {{ old('blood_type') == 'O-' ? 'selected' : '' }}>O-</option>
                                    </select>
                                    @error('blood_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Dirección -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-map-marker-alt fs-14 text-muted"></i>
                        </div>
                        <h4 class="card-title mb-0">Dirección</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Dirección</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="2">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="city" class="form-label">Ciudad</label>
                                    <input type="text" class="form-control @error('city') is-invalid @enderror" 
                                           id="city" name="city" value="{{ old('city') }}">
                                    @error('city')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="state" class="form-label">Estado</label>
                                    <input type="text" class="form-control @error('state') is-invalid @enderror" 
                                           id="state" name="state" value="{{ old('state') }}">
                                    @error('state')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="mb-3">
                                    <label for="postal_code" class="form-label">Código Postal</label>
                                    <input type="text" class="form-control @error('postal_code') is-invalid @enderror" 
                                           id="postal_code" name="postal_code" value="{{ old('postal_code') }}">
                                    @error('postal_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
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
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="occupation" class="form-label">Ocupación</label>
                                    <input type="text" class="form-control @error('occupation') is-invalid @enderror" 
                                           id="occupation" name="occupation" value="{{ old('occupation') }}">
                                    @error('occupation')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="marital_status" class="form-label">Estado Civil</label>
                                    <select class="form-select @error('marital_status') is-invalid @enderror" id="marital_status" name="marital_status">
                                        <option value="">Seleccionar...</option>
                                        <option value="single" {{ old('marital_status') == 'single' ? 'selected' : '' }}>Soltero(a)</option>
                                        <option value="married" {{ old('marital_status') == 'married' ? 'selected' : '' }}>Casado(a)</option>
                                        <option value="divorced" {{ old('marital_status') == 'divorced' ? 'selected' : '' }}>Divorciado(a)</option>
                                        <option value="widowed" {{ old('marital_status') == 'widowed' ? 'selected' : '' }}>Viudo(a)</option>
                                    </select>
                                    @error('marital_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="medical_history" class="form-label">Historial Médico</label>
                                    <textarea class="form-control @error('medical_history') is-invalid @enderror" 
                                              id="medical_history" name="medical_history" rows="3" 
                                              placeholder="Enfermedades, cirugías, hospitalizaciones...">{{ old('medical_history') }}</textarea>
                                    @error('medical_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="dental_history" class="form-label">Historial Dental</label>
                                    <textarea class="form-control @error('dental_history') is-invalid @enderror" 
                                              id="dental_history" name="dental_history" rows="3" 
                                              placeholder="Tratamientos anteriores, prótesis, ortodoncia...">{{ old('dental_history') }}</textarea>
                                    @error('dental_history')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="allergies" class="form-label">Alergias</label>
                                    <textarea class="form-control @error('allergies') is-invalid @enderror" 
                                              id="allergies" name="allergies" rows="2" 
                                              placeholder="Medicamentos, materiales, alimentos...">{{ old('allergies') }}</textarea>
                                    @error('allergies')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="medications" class="form-label">Medicamentos Actuales</label>
                                    <textarea class="form-control @error('medications') is-invalid @enderror" 
                                              id="medications" name="medications" rows="2" 
                                              placeholder="Medicamentos que toma actualmente...">{{ old('medications') }}</textarea>
                                    @error('medications')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-lg-4">
                <!-- Contacto de Emergencia -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-phone-alt fs-14 text-muted"></i>
                        </div>
                        <h4 class="card-title mb-0">Contacto de Emergencia</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="emergency_contact_name" class="form-label">Nombre Completo</label>
                            <input type="text" class="form-control @error('emergency_contact_name') is-invalid @enderror" 
                                   id="emergency_contact_name" name="emergency_contact_name" value="{{ old('emergency_contact_name') }}">
                            @error('emergency_contact_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="emergency_contact_phone" class="form-label">Teléfono</label>
                            <input type="tel" class="form-control @error('emergency_contact_phone') is-invalid @enderror" 
                                   id="emergency_contact_phone" name="emergency_contact_phone" value="{{ old('emergency_contact_phone') }}">
                            @error('emergency_contact_phone')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="emergency_contact_relationship" class="form-label">Relación</label>
                            <select class="form-select @error('emergency_contact_relationship') is-invalid @enderror" 
                                    id="emergency_contact_relationship" name="emergency_contact_relationship">
                                <option value="">Seleccionar...</option>
                                <option value="spouse" {{ old('emergency_contact_relationship') == 'spouse' ? 'selected' : '' }}>Cónyuge</option>
                                <option value="parent" {{ old('emergency_contact_relationship') == 'parent' ? 'selected' : '' }}>Padre/Madre</option>
                                <option value="child" {{ old('emergency_contact_relationship') == 'child' ? 'selected' : '' }}>Hijo(a)</option>
                                <option value="sibling" {{ old('emergency_contact_relationship') == 'sibling' ? 'selected' : '' }}>Hermano(a)</option>
                                <option value="friend" {{ old('emergency_contact_relationship') == 'friend' ? 'selected' : '' }}>Amigo(a)</option>
                                <option value="other" {{ old('emergency_contact_relationship') == 'other' ? 'selected' : '' }}>Otro</option>
                            </select>
                            @error('emergency_contact_relationship')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="emergency_contact_address" class="form-label">Dirección</label>
                            <textarea class="form-control @error('emergency_contact_address') is-invalid @enderror" 
                                      id="emergency_contact_address" name="emergency_contact_address" rows="2">{{ old('emergency_contact_address') }}</textarea>
                            @error('emergency_contact_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Información Adicional -->
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-info-circle fs-14 text-muted"></i>
                        </div>
                        <h4 class="card-title mb-0">Información Adicional</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="notes" class="form-label">Notas</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="notes" name="notes" rows="3" 
                                      placeholder="Información adicional sobre el paciente...">{{ old('notes') }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="consent_marketing" 
                                       name="consent_marketing" value="1" {{ old('consent_marketing') ? 'checked' : '' }}>
                                <label class="form-check-label" for="consent_marketing">
                                    Consentimiento para marketing
                                </label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="consent_data_processing" 
                                       name="consent_data_processing" value="1" {{ old('consent_data_processing', true) ? 'checked' : '' }}>
                                <label class="form-check-label" for="consent_data_processing">
                                    Consentimiento para procesamiento de datos
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Guardar Paciente
                            </button>
                            <a href="{{ route('patients.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancelarar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de fecha de nacimiento
    const birthDateInput = document.getElementById('birth_date');
    if (birthDateInput) {
        birthDateInput.addEventListener('change', function() {
            const birthDate = new Date(this.value);
            const today = new Date();
            const age = today.getFullYear() - birthDate.getFullYear();
            
            if (age < 0 || age > 120) {
                this.setCustomValidity('La fecha de nacimiento no es válida');
            } else {
                this.setCustomValidity('');
            }
        });
    }

    // Formateo automático de teléfonos
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0, 3) + '-' + value.slice(3);
                } else if (value.length <= 10) {
                    value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6);
                } else {
                    value = value.slice(0, 3) + '-' + value.slice(3, 6) + '-' + value.slice(6, 10);
                }
            }
            this.value = value;
        });
    });

    // Formateo automático de código postal
    const postalCodeInput = document.getElementById('postal_code');
    if (postalCodeInput) {
        postalCodeInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value.length > 5) {
                value = value.slice(0, 5);
            }
            this.value = value;
        });
    }
});
</script>
@endsection



