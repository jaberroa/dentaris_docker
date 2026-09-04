@extends('layouts.master')

@section('title', 'Seleccionar clínica')
@section('topbar-title', 'Contexto clínico')

@section('content')
    <div class="row justify-content-center py-4">
        <div class="col-xl-8 col-lg-9">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-lg-5">
                    <div class="d-flex align-items-start gap-3 mb-4">
                        <div class="avatar avatar-lg avatar-label-primary rounded-circle flex-shrink-0">
                            <span class="avatar-display"><i class="fas fa-clinic-medical"></i></span>
                        </div>
                        <div>
                            <span class="badge bg-primary-subtle text-primary mb-2">Contexto seguro</span>
                            <h2 class="mb-2">Selecciona tu clínica</h2>
                            <p class="text-muted mb-0">
                                Solo aparecen clínicas activas vinculadas a una membresía autorizada de tu cuenta.
                            </p>
                        </div>
                    </div>

                    @if (session('status'))
                        <div class="alert alert-info" role="status">{{ session('status') }}</div>
                    @endif

                    @error('clinic_id')
                        <div class="alert alert-danger" role="alert">{{ $message }}</div>
                    @enderror

                    @forelse ($clinics as $clinic)
                        <form method="POST" action="{{ route('clinics.context.store') }}" class="mb-3">
                            @csrf
                            <input type="hidden" name="clinic_id" value="{{ $clinic->id }}">
                            <button type="submit" class="btn btn-light border w-100 p-3 text-start d-flex align-items-center justify-content-between">
                                <span class="d-flex align-items-center gap-3">
                                    <span class="avatar avatar-sm avatar-label-primary rounded-circle">
                                        <span class="avatar-display"><i class="fas fa-tooth"></i></span>
                                    </span>
                                    <span>
                                        <strong class="d-block text-dark">{{ $clinic->name }}</strong>
                                        <small class="text-muted">Código: {{ $clinic->code }}</small>
                                    </span>
                                </span>
                                <span class="text-primary fw-semibold">Ingresar <i class="mdi mdi-arrow-right ms-1"></i></span>
                            </button>
                        </form>
                    @empty
                        <div class="alert alert-warning mb-0" role="alert">
                            <h5 class="alert-heading">No tienes clínicas disponibles</h5>
                            <p class="mb-0">Solicita al administrador una membresía clínica activa. No se creó ningún acceso automáticamente.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection
