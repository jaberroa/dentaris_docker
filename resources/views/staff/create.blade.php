@extends('layouts.master')

@section('title')
    Nuevo Personal
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
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Nuevo Personal</h4>
                    <p class="text-muted mb-0">Registra un nuevo miembro del personal en el sistema.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <!-- Mensaje de éxito flotante -->
    @include('components.success-toast')

    <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data">
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
                                    <label for="name" class="form-label">Nombre Completo <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror" 
                                           id="name" name="name" value="{{ old('name') }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email') }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="phone" class="form-label">Teléfono</label>
                                    <input type="tel" class="form-control @error('phone') is-invalid @enderror" 
                                           id="phone" name="phone" value="{{ old('phone') }}">
                                    @error('phone')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="specialty" class="form-label">Especialidad</label>
                                    <select class="form-select @error('specialty') is-invalid @enderror" id="specialty" name="specialty">
                                        <option value="">Seleccionar...</option>
                                        <option value="Odontólogo General" {{ old('specialty') == 'Odontólogo General' ? 'selected' : '' }}>Odontólogo General</option>
                                        <option value="Ortodoncista" {{ old('specialty') == 'Ortodoncista' ? 'selected' : '' }}>Ortodoncista</option>
                                        <option value="Cirujano Oral" {{ old('specialty') == 'Cirujano Oral' ? 'selected' : '' }}>Cirujano Oral</option>
                                        <option value="Endodoncista" {{ old('specialty') == 'Endodoncista' ? 'selected' : '' }}>Endodoncista</option>
                                        <option value="Higienista Dental" {{ old('specialty') == 'Higienista Dental' ? 'selected' : '' }}>Higienista Dental</option>
                                        <option value="Recepcionista" {{ old('specialty') == 'Recepcionista' ? 'selected' : '' }}>Recepcionista</option>
                                    </select>
                                    @error('specialty')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role" class="form-label">Rol <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role') is-invalid @enderror" id="role" name="role" required>
                                        <option value="">Seleccionar...</option>
                                        <option value="doctor" {{ old('role') == 'doctor' ? 'selected' : '' }}>Doctor</option>
                                        <option value="nurse" {{ old('role') == 'nurse' ? 'selected' : '' }}>Enfermero</option>
                                        <option value="receptionist" {{ old('role') == 'receptionist' ? 'selected' : '' }}>Recepcionista</option>
                                        <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrador</option>
                                    </select>
                                    @error('role')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="is_active" class="form-label">Estado</label>
                                    <select class="form-select @error('is_active') is-invalid @enderror" id="is_active" name="is_active">
                                        <option value="1" {{ old('is_active', '1') == '1' ? 'selected' : '' }}>Activo</option>
                                        <option value="0" {{ old('is_active') == '0' ? 'selected' : '' }}>Inactivo</option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="address" class="form-label">Dirección</label>
                                    <textarea class="form-control @error('address') is-invalid @enderror" 
                                              id="address" name="address" rows="3" 
                                              placeholder="Dirección completa">{{ old('address') }}</textarea>
                                    @error('address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="mb-3">
                                    <label for="notes" class="form-label">Notas</label>
                                    <textarea class="form-control @error('notes') is-invalid @enderror" 
                                              id="notes" name="notes" rows="3" 
                                              placeholder="Notas adicionales sobre el personal">{{ old('notes') }}</textarea>
                                    @error('notes')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información de Acceso -->
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-key fs-14 text-muted"></i>
                        </div>
                        <h4 class="card-title mb-0">Información de Acceso</h4>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label for="password" class="form-label">Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" required>
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Contraseña <span class="text-danger">*</span></label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" name="password_confirmation" required>
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>La contraseña debe tener al menos 8 caracteres.</small>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Guardar Personal
                            </button>
                            <a href="{{ route('staff.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
@include('components.success-toast-scripts')

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de contraseñas
    const password = document.getElementById('password');
    const passwordConfirmation = document.getElementById('password_confirmation');
    
    function validatePasswords() {
        if (password.value !== passwordConfirmation.value) {
            passwordConfirmation.setCustomValidity('Las contraseñas no coinciden');
        } else {
            passwordConfirmation.setCustomValidity('');
        }
    }
    
    password.addEventListener('input', validatePasswords);
    passwordConfirmation.addEventListener('input', validatePasswords);
    
    // Validación de email único
    const emailInput = document.getElementById('email');
    emailInput.addEventListener('blur', function() {
        const email = this.value;
        if (email) {
            // Aquí podrías agregar validación AJAX para verificar si el email ya existe
            console.log('Validando email:', email);
        }
    });
});
</script>
@endsection

