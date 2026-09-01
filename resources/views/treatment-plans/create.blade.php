@extends('layouts.master')

@section('title')
    Nuevo Plan de Tratamiento
@endsection

@section('topbar-title')
    Dentaris
@endsection

@section('css')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="{{ asset('css/select2-custom.css') }}" rel="stylesheet" />

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
#create_patient_id + .select2-container .select2-results__option:hover {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    transition: all 0.3s ease;
}

/* Hover effect específico para opciones de profesionales */
#create_staff_id + .select2-container .select2-results__option:hover {
    background-color: #f8f9fa !important;
    color: #495057 !important;
    transition: all 0.3s ease;
}
</style>
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <div>
                    <h4 class="fs-16 fw-semibold mb-1 mb-md-2">Nuevo Plan de Tratamiento</h4>
                    <p class="text-muted mb-0">Crea un nuevo plan de tratamiento para un paciente.</p>
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


    <form action="{{ route('treatment-plans.store') }}" method="POST">
        @csrf
        
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
                                <x-patient-select 
                                    name="patient_id" 
                                    id="create_patient_id" 
                                    label="Paciente" 
                                    :patients="$patients" 
                                />
                            </div>
                            
                            <div class="col-md-6">
                                <x-staff-select 
                                    name="staff_id" 
                                    id="create_staff_id" 
                                    label="Profesional" 
                                    :staff="$staff" 
                                />
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="create_plan_name" class="form-label">Nombre del Plan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('plan_name') is-invalid @enderror" 
                                   id="create_plan_name" name="plan_name" 
                                   value="{{ old('plan_name') }}" 
                                   placeholder="Ej: Plan de Ortodoncia, Plan de Implantes, etc." required>
                            @error('plan_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="mb-3">
                            <label for="create_description" class="form-label">Descripción</label>
                            <textarea class="form-control @error('description') is-invalid @enderror" 
                                      id="create_description" name="description" rows="3" 
                                      placeholder="Descripción detallada del plan de tratamiento">{{ old('description') }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_estimated_duration" class="form-label">Duración Estimada (semanas) <span class="text-danger">*</span></label>
                                    <input type="number" class="form-control @error('estimated_duration') is-invalid @enderror" 
                                           id="create_estimated_duration" name="estimated_duration" 
                                           value="{{ old('estimated_duration', 1) }}" min="1" required>
                                    @error('estimated_duration')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_warranty_period" class="form-label">Período de Garantía (meses)</label>
                                    <input type="number" class="form-control @error('warranty_period') is-invalid @enderror" 
                                           id="create_warranty_period" name="warranty_period" 
                                           value="{{ old('warranty_period') }}" min="0">
                                    @error('warranty_period')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_priority" class="form-label">Prioridad</label>
                                    <select class="form-select @error('priority') is-invalid @enderror" id="create_priority" name="priority">
                                        <option value="low" {{ old('priority', 'low') == 'low' ? 'selected' : '' }}>Baja</option>
                                        <option value="medium" {{ old('priority') == 'medium' ? 'selected' : '' }}>Media</option>
                                        <option value="high" {{ old('priority') == 'high' ? 'selected' : '' }}>Alta</option>
                                    </select>
                                    @error('priority')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="create_payment_plan" class="form-label">Plan de Pago</label>
                                    <select class="form-select @error('payment_plan') is-invalid @enderror" id="create_payment_plan" name="payment_plan">
                                        <option value="cash" {{ old('payment_plan', 'cash') == 'cash' ? 'selected' : '' }}>Contado</option>
                                        <option value="installments" {{ old('payment_plan') == 'installments' ? 'selected' : '' }}>Cuotas</option>
                                        <option value="insurance" {{ old('payment_plan') == 'insurance' ? 'selected' : '' }}>Seguro</option>
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
                            <label for="create_estimated_cost" class="form-label">Costo Estimado</label>
                            <input type="number" class="form-control @error('estimated_cost') is-invalid @enderror" 
                                   id="create_estimated_cost" name="estimated_cost" 
                                   value="{{ old('estimated_cost') }}" 
                                   step="0.01" min="0" 
                                   placeholder="0.00">
                            @error('estimated_cost')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        
                        
                        <div class="mb-3">
                            <label for="create_notes" class="form-label">Notas</label>
                            <textarea class="form-control @error('notes') is-invalid @enderror" 
                                      id="create_notes" name="notes" rows="3" 
                                      placeholder="Notas adicionales">{{ old('notes') }}</textarea>
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
                                Guardar Plan
                            </button>
                            <a href="{{ route('treatment-plans.index') }}" class="btn btn-outline-secondary">
                                <i class="fas fa-times me-1"></i>
                                Cancelar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Sección de Items del Plan -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <div class="card-icon">
                            <i class="fas fa-list fs-14 text-muted"></i>
                        </div>
                        <h4 class="card-title mb-0">Items del Plan de Tratamiento</h4>
                    </div>
                    <div class="card-body">
                        <div id="items-container">
                            <div class="item-row border p-3 mb-3 rounded">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Procedimiento <span class="text-danger">*</span></label>
                                            <select class="form-select" name="items[0][cdt_catalog_id]" required>
                                                <option value="">Seleccionar procedimiento</option>
                                                @foreach($cdtCatalog as $item)
                                                    <option value="{{ $item->id }}">{{ $item->name }} - ${{ number_format($item->price, 2) }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">Orden <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="items[0][sequence_order]" value="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">Diente</label>
                                            <input type="text" class="form-control" name="items[0][tooth_number]" placeholder="Ej: 11, 12">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">Superficie</label>
                                            <input type="text" class="form-control" name="items[0][surface]" placeholder="Ej: O, M, D">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="mb-3">
                                            <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="items[0][quantity]" value="1" min="1" required>
                                        </div>
                                    </div>
                                    <div class="col-md-1">
                                        <div class="mb-3">
                                            <label class="form-label">Acción</label>
                                            <button type="button" class="btn btn-outline-danger btn-sm remove-item" disabled>
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label class="form-label">Descripción <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="items[0][description]" rows="2" required></textarea>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Precio Unitario <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control" name="items[0][unit_price]" step="0.01" min="0" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="mb-3">
                                            <label class="form-label">Notas</label>
                                            <textarea class="form-control" name="items[0][notes]" rows="2"></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="text-center">
                            <button type="button" class="btn btn-outline-primary" id="add-item">
                                <i class="fas fa-plus me-1"></i>
                                Agregar Item
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@section('scripts')
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let itemIndex = 0;

    // Inicializar Select2 para paciente
    $('#create_patient_id').select2({
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
            var $option = $('#create_patient_id option[value="' + patient.id + '"]');
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
    $('#create_staff_id').select2({
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

    // Formateo automático de costo estimado
    const estimatedCost = document.getElementById('create_estimated_cost');
    if (estimatedCost) {
        estimatedCost.addEventListener('input', function() {
            let value = parseFloat(this.value);
            if (isNaN(value) || value < 0) {
                this.value = '';
            } else {
                this.value = value.toFixed(2);
            }
        });
    }

    // Agregar nuevo item
    document.getElementById('add-item').addEventListener('click', function() {
        itemIndex++;
        const container = document.getElementById('items-container');
        const newItem = createItemRow(itemIndex);
        container.appendChild(newItem);
        updateRemoveButtons();
    });

    // Remover item
    document.addEventListener('click', function(e) {
        if (e.target.closest('.remove-item')) {
            const itemRow = e.target.closest('.item-row');
            itemRow.remove();
            updateRemoveButtons();
        }
    });

    // Función para crear una fila de item
    function createItemRow(index) {
        const itemRow = document.createElement('div');
        itemRow.className = 'item-row border p-3 mb-3 rounded';
        itemRow.innerHTML = `
            <div class="row">
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Procedimiento <span class="text-danger">*</span></label>
                        <select class="form-select" name="items[${index}][cdt_catalog_id]" required>
                            <option value="">Seleccionar procedimiento</option>
                            @foreach($cdtCatalog as $item)
                                <option value="{{ $item->id }}">{{ $item->name }} - ${{ number_format($item->price, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Orden <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="items[${index}][sequence_order]" value="${index + 1}" min="1" required>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Diente</label>
                        <input type="text" class="form-control" name="items[${index}][tooth_number]" placeholder="Ej: 11, 12">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Superficie</label>
                        <input type="text" class="form-control" name="items[${index}][surface]" placeholder="Ej: O, M, D">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="mb-3">
                        <label class="form-label">Cantidad <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="items[${index}][quantity]" value="1" min="1" required>
                    </div>
                </div>
                <div class="col-md-1">
                    <div class="mb-3">
                        <label class="form-label">Acción</label>
                        <button type="button" class="btn btn-outline-danger btn-sm remove-item">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="mb-3">
                        <label class="form-label">Descripción <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="items[${index}][description]" rows="2" required></textarea>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Precio Unitario <span class="text-danger">*</span></label>
                        <input type="number" class="form-control" name="items[${index}][unit_price]" step="0.01" min="0" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <label class="form-label">Notas</label>
                        <textarea class="form-control" name="items[${index}][notes]" rows="2"></textarea>
                    </div>
                </div>
            </div>
        `;
        return itemRow;
    }

    // Función para actualizar botones de eliminar
    function updateRemoveButtons() {
        const itemRows = document.querySelectorAll('.item-row');
        const removeButtons = document.querySelectorAll('.remove-item');
        
        removeButtons.forEach((button, index) => {
            if (itemRows.length === 1) {
                button.disabled = true;
            } else {
                button.disabled = false;
            }
        });
    }

    // Actualizar botones al cargar
    updateRemoveButtons();
});
</script>
@endsection
