@extends('layouts.master')

@section('title')
    Editar Plan de Tratamiento
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
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Editar Plan de Tratamiento</h4>
                    <p class="text-muted mb-0">Modifica la información del plan de tratamiento para {{ $treatmentPlan->patient->first_name ?? 'N/A' }} {{ $treatmentPlan->patient->last_name ?? '' }}.</p>
                </div>
                <div class="page-title-right">
                    <a href="{{ route('treatment-plans.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left me-1"></i>
                        Volver
                    </a>
                </div>
            </div>
        </div>
    </div>
    <!-- end row -->

    <form action="{{ route('treatment-plans.update', $treatmentPlan) }}" method="POST">
        @csrf
        @method('PUT')
        
        <div class="row">
            <!-- Información del Plan -->
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-clipboard-list fs-14 text-muted"></i>
                        </div>
                        <h4 class="card-title mb-0">Información del Plan</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_patient_id" class="form-label">Paciente <span class="text-danger">*</span></label>
                                    <select class="form-select @error('patient_id') is-invalid @enderror" id="edit_patient_id" name="patient_id" required>
                                        <option value="">Seleccionar paciente</option>
                                        @foreach($patients as $patient)
                                            <option value="{{ $patient->id }}" {{ old('patient_id', $treatmentPlan->patient_id) == $patient->id ? 'selected' : '' }}>
                                                {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->patient_code }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('patient_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_staff_id" class="form-label">Profesional <span class="text-danger">*</span></label>
                                    <select class="form-select @error('staff_id') is-invalid @enderror" id="edit_staff_id" name="staff_id" required>
                                        <option value="">Seleccionar profesional</option>
                                        @foreach($staff as $member)
                                            <option value="{{ $member->id }}" {{ old('staff_id', $treatmentPlan->staff_id) == $member->id ? 'selected' : '' }}>
                                                Dr(a). {{ $member->user->first_name }} {{ $member->user->last_name }} - {{ $member->specialty }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('staff_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_treatment_name" class="form-label">Nombre del Tratamiento <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('treatment_name') is-invalid @enderror" 
                                   id="edit_treatment_name" name="treatment_name" 
                                   value="{{ old('treatment_name', $treatmentPlan->treatment_name) }}" 
                                   placeholder="Ej: Ortodoncia, Implante, etc." required>
                            @error('treatment_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="edit_description" name="description" rows="3" 
                                      placeholder="Descripción detallada del plan de tratamiento">{{ old('description', $treatmentPlan->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_start_date" class="form-label">Fecha de Inicio <span class="text-danger">*</span></label>
                                    <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                           id="edit_start_date" name="start_date" 
                                           value="{{ old('start_date', $treatmentPlan->start_date ? $treatmentPlan->start_date->format('Y-m-d') : '') }}" required>
                                    @error('start_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_end_date" class="form-label">Fecha de Finalización</label>
                                    <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                           id="edit_end_date" name="end_date" 
                                           value="{{ old('end_date', $treatmentPlan->end_date ? $treatmentPlan->end_date->format('Y-m-d') : '') }}">
                                    @error('end_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_priority" class="form-label">Prioridad</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="edit_priority" name="priority">
                                        <option value="low" {{ old('priority', $treatmentPlan->priority) == 'low' ? 'selected' : '' }}>Baja</option>
                                        <option value="medium" {{ old('priority', $treatmentPlan->priority) == 'medium' ? 'selected' : '' }}>Media</option>
                                        <option value="high" {{ old('priority', $treatmentPlan->priority) == 'high' ? 'selected' : '' }}>Alta</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="edit_payment_plan" class="form-label">Plan de Pago</label>
                                    <select class="form-select @error('payment_plan') is-invalid @enderror" id="edit_payment_plan" name="payment_plan">
                                        <option value="cash" {{ old('payment_plan', $treatmentPlan->payment_plan) == 'cash' ? 'selected' : '' }}>Contado</option>
                                        <option value="installments" {{ old('payment_plan', $treatmentPlan->payment_plan) == 'installments' ? 'selected' : '' }}>Cuotas</option>
                                        <option value="insurance" {{ old('payment_plan', $treatmentPlan->payment_plan) == 'insurance' ? 'selected' : '' }}>Seguro</option>
                                    </select>
                                    @error('payment_plan')
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
                            <label for="edit_total_cost" class="form-label">Costo Total</label>
                            <input type="number" class="form-control @error('total_cost') is-invalid @enderror" 
                                   id="edit_total_cost" name="total_cost" 
                                   value="{{ old('total_cost', $treatmentPlan->total_cost) }}" 
                                   step="0.01" min="0" 
                                   placeholder="0.00">
                            @error('total_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_progress" class="form-label">Progreso (%)</label>
                            <input type="number" class="form-control @error('progress') is-invalid @enderror" 
                                   id="edit_progress" name="progress" 
                                   value="{{ old('progress', $treatmentPlan->progress ?? 0) }}" 
                                   min="0" max="100" 
                                   placeholder="0">
                            @error('progress')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_status" class="form-label">Estado</label>
                            <select class="form-select @error('status') is-invalid @enderror" id="edit_status" name="status">
                                <option value="draft" {{ old('status', $treatmentPlan->status) == 'draft' ? 'selected' : '' }}>Borrador</option>
                                <option value="active" {{ old('status', $treatmentPlan->status) == 'active' ? 'selected' : '' }}>Activo</option>
                                <option value="completed" {{ old('status', $treatmentPlan->status) == 'completed' ? 'selected' : '' }}>Completado</option>
                                <option value="cancelled" {{ old('status', $treatmentPlan->status) == 'cancelled' ? 'selected' : '' }}>Cancelado</option>
                                <option value="on_hold" {{ old('status', $treatmentPlan->status) == 'on_hold' ? 'selected' : '' }}>En Espera</option>
                            </select>
                            @error('status')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="edit_notes" class="form-label">Notas</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="edit_notes" name="notes" rows="3" 
                                      placeholder="Notas adicionales">{{ old('notes', $treatmentPlan->notes) }}</textarea>
                            @error('notes')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botones de Acción -->
                <div class="card">
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save me-1"></i>
                                Actualizar Plan
                            </button>
                            <a href="{{ route('treatment-plans.show', $treatmentPlan) }}" class="btn btn-outline-info">
                                <i class="fas fa-eye me-1"></i>
                                Ver Detalles
                            </a>
                            <a href="{{ route('treatment-plans.index') }}" class="btn btn-outline-secondary">
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
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Validación de fechas
    const startDate = document.getElementById('edit_start_date');
    const endDate = document.getElementById('edit_end_date');
    
    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            if (this.value && endDate.value && this.value > endDate.value) {
                endDate.value = this.value;
            }
            endDate.min = this.value;
        });
        
        endDate.addEventListener('change', function() {
            if (this.value && startDate.value && this.value < startDate.value) {
                this.value = startDate.value;
            }
        });
    }
    
    // Validación de progreso
    const progress = document.getElementById('edit_progress');
    if (progress) {
        progress.addEventListener('input', function() {
            if (this.value > 100) {
                this.value = 100;
            }
            if (this.value < 0) {
                this.value = 0;
            }
        });
    }
    
    // Formateo automático de costo
    const totalCost = document.getElementById('edit_total_cost');
    if (totalCost) {
        totalCost.addEventListener('input', function() {
            let value = parseFloat(this.value);
            if (isNaN(value) || value < 0) {
                this.value = '';
            } else {
                this.value = value.toFixed(2);
            }
        });
    }
});
</script>
@endsection