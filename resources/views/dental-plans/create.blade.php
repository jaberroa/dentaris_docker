@extends('layouts.master')

@section('title') Nuevo Plan Odontológico @endsection

@section('content')
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <div class="card-icon"><i class="fas fa-tooth fs-14 text-muted"></i></div>
                    <h4 class="card-title mb-0">Crear Plan</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('dental-plans.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Paciente <span class="text-danger">*</span></label>
                            <select class="form-select" name="patient_id" required>
                                <option value="">Seleccionar...</option>
                                @foreach($patients as $p)
                                    <option value="{{ $p->id }}">{{ $p->last_name }}, {{ $p->first_name }} ({{ $p->patient_code }})</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Profesional</label>
                            <select class="form-select" name="staff_id">
                                <option value="">Seleccionar...</option>
                                @foreach($staff as $s)
                                    <option value="{{ $s->id }}">{{ $s->user->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nombre del plan <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="plan_name" required>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Tipo de paciente</label>
                                <select class="form-select" name="patient_type" required>
                                    <option value="adult">Adulto (32 dientes)</option>
                                    <option value="child">Niño (20 dientes)</option>
                                    <option value="mixed">Mixto</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Esquema de trabajo</label>
                                <select class="form-select" name="work_schema" required>
                                    <option value="odontogram">Odontograma</option>
                                    <option value="periodontogram">Periodontograma</option>
                                    <option value="both">Ambos</option>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('dental-plans.index') }}" class="btn btn-outline-secondary me-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary">Crear plan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <p class="text-muted mb-0">Esta es una versión inicial del módulo. Mantiene el estilo actual y no interfiere con los planes existentes.</p>
                </div>
            </div>
        </div>
    </div>
@endsection




