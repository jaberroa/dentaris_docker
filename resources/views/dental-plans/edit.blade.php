@extends('layouts.master')

@section('title') Editar Plan {{ $plan->plan_code }} @endsection

@section('css')
<style>
    .fade-out {
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
</style>
@endsection

@section('content')
    @include('components.success-toast')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-tooth fs-14 text-muted"></i></div>
                    <h4 class="card-title mb-0">Editar Plan</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dental-plans.update', ['dental_plan' => $plan->id]) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label class="form-label">Paciente <span class="text-danger">*</span></label>
                            <select class="form-select" name="patient_id" required>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}" {{ $plan->patient_id == $p->id ? 'selected' : '' }}>{{ $p->last_name }}, {{ $p->first_name }} ({{ $p->patient_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Profesional</label>
                            <select class="form-select" name="staff_id">
                                <option value="">Seleccionar...</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}" {{ $plan->staff_id == $s->id ? 'selected' : '' }}>{{ $s->user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre del plan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="plan_name" value="{{ $plan->plan_name }}" required>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de paciente</label>
                                <select class="form-select" name="patient_type" required>
                                    <option value="adult" {{ $plan->patient_type === 'adult' ? 'selected' : '' }}>Adulto (32 dientes)</option>
                                    <option value="child" {{ $plan->patient_type === 'child' ? 'selected' : '' }}>Niño (20 dientes)</option>
                                    <option value="mixed" {{ $plan->patient_type === 'mixed' ? 'selected' : '' }}>Mixto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Esquema de trabajo</label>
                                <select class="form-select" name="work_schema" required>
                                    <option value="odontogram" {{ $plan->work_schema === 'odontogram' ? 'selected' : '' }}>Odontograma</option>
                                    <option value="periodontogram" {{ $plan->work_schema === 'periodontogram' ? 'selected' : '' }}>Periodontograma</option>
                                    <option value="both" {{ $plan->work_schema === 'both' ? 'selected' : '' }}>Ambos</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Estado</label>
                            <select class="form-select" name="status" required>
                                @foreach(['draft' => 'Borrador', 'active' => 'Activo', 'on_hold' => 'En espera', 'completed' => 'Completado', 'cancelled' => 'Cancelado'] as $key => $label)
                                    <option value="{{ $key }}" {{ $plan->status === $key ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('dental-plans.show', ['dental_plan' => $plan->id]) }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Guardar cambios</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <div class="text-muted">Código</div>
                    <div class="fw-semibold">{{ $plan->plan_code }}</div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
@include('components.success-toast-scripts')
@endsection
