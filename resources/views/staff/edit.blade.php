@extends('layouts.master')

@section('title')
    Editar Personal
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
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Editar Personal</h4>
                    <p class="text-muted mb-0">Modifica la información de {{ $staff->user->name ?? 'N/A' }}.</p>
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

    <form action="{{ route('staff.update', $staff) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        
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
                                           id="name" name="name" value="{{ old('name', $staff->user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Correo Electrónico <span class="text-danger">*</span></label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror" 
                                           id="email" name="email" value="{{ old('email', $staff->user->email) }}" required>
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
                                           id="phone" name="phone" value="{{ old('phone', $staff->phone) }}">
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
                                        <option value="Odontólogo General" {{ old('specialty', $staff->specialty) == 'Odontólogo General' ? 'selected' : '' }}>Odontólogo General</option>
                                        <option value="Ortodoncista" {{ old('specialty', $staff->specialty) == 'Ortodoncista' ? 'selected' : '' }}>Ortodoncista</option>
                                        <option value="Cirujano Oral" {{ old('specialty', $staff->specialty) == 'Cirujano Oral' ? 'selected' : '' }}>Cirujano Oral</option>
                                        <option value="Endodoncista" {{ old('specialty', $staff->specialty) == 'Endodoncista' ? 'selected' : '' }}>Endodoncista</option>
                                        <option value="Higienista Dental" {{ old('specialty', $staff->specialty) == 'Higienista Dental' ? 'selected' : '' }}>Higienista Dental</option>
                                        <option value="Recepcionista" {{ old('specialty', $staff->specialty) == 'Recepcionista' ? 'selected' : '' }}>Recepcionista</option>
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
                                    <label for="license_number" class="form-label">Número de Licencia</label>
                                    <input type="text" class="form-control @error('license_number') is-invalid @enderror" 
                                           id="license_number" name="license_number" value="{{ old('license_number', $staff->license_number) }}">
                                    @error('license_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="hire_date" class="form-label">Fecha de Contratación</label>
                                    <input type="date" class="form-control @error('hire_date') is-invalid @enderror" 
                                           id="hire_date" name="hire_date" value="{{ old('hire_date', $staff->hire_date) }}">
                                    @error('hire_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="salary" class="form-label">Salario</label>
                                    <input type="number" step="0.01" class="form-control @error('salary') is-invalid @enderror" 
                                           id="salary" name="salary" value="{{ old('salary', $staff->salary) }}">
                                    @error('salary')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <div class="form-check mt-4">
                                        <input type="checkbox" class="form-check-input @error('is_active') is-invalid @enderror" 
                                               id="is_active_checkbox" name="is_active" value="1" 
                                               {{ old('is_active', $staff->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active_checkbox">
                                            Personal Activo
                                        </label>
                                        @error('is_active')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="role_id" class="form-label">Rol <span class="text-danger">*</span></label>
                                    <select class="form-select @error('role_id') is-invalid @enderror" id="role_id" name="role_id" required>
                                        <option value="">Seleccionar...</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id', $staff->user->roles->first()->id ?? '') == $role->id ? 'selected' : '' }}>
                                                {{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('role_id')
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
                            <label for="password" class="form-label">Nueva Contraseña</label>
                            <input type="password" class="form-control @error('password') is-invalid @enderror" 
                                   id="password" name="password" placeholder="Dejar vacío para mantener la actual">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password_confirmation" class="form-label">Confirmar Nueva Contraseña</label>
                            <input type="password" class="form-control @error('password_confirmation') is-invalid @enderror" 
                                   id="password_confirmation" name="password_confirmation" placeholder="Confirmar nueva contraseña">
                            @error('password_confirmation')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-info-circle me-2"></i>
                            <small>Deja la contraseña vacía si no deseas cambiarla. Si la cambias, debe tener al menos 8 caracteres.</small>
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Actualizar Personal
                            </button>
                            <a href="{{ route('staff.show', $staff) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>
                                Ver Perfil
                            </a>
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
        if (password.value && passwordConfirmation.value) {
            if (password.value !== passwordConfirmation.value) {
                passwordConfirmation.setCustomValidity('Las contraseñas no coinciden');
            } else {
                passwordConfirmation.setCustomValidity('');
            }
        } else if (password.value && !passwordConfirmation.value) {
            passwordConfirmation.setCustomValidity('Debe confirmar la nueva contraseña');
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

