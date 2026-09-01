@extends('layouts.master')

@section('title') Planes Odontológicos @endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-flex align-items-center justify-content-between">
                <h4 class="mb-0">Planes Odontológicos</h4>
                <a href="{{ route('dental-plans.create') }}" class="btn btn-primary">
                    <i class="fas fa-plus me-1"></i> Nuevo Plan
                </a>
            </div>
        </div>
    </div>

    @include('components.success-toast')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table align-middle table-hover">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Paciente</th>
                            <th>Profesional</th>
                            <th>Estado</th>
                            <th>Progreso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($plans as $plan)
                            <tr>
                                <td>{{ $plan->plan_code }}</td>
                                <td>{{ $plan->patient->first_name ?? 'N/A' }} {{ $plan->patient->last_name ?? '' }}</td>
                                <td>{{ $plan->staff->user->name ?? 'N/A' }}</td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary text-capitalize">{{ str_replace('_',' ', $plan->status) }}</span>
                                </td>
                                <td style="min-width: 160px;">
                                    <div class="progress" style="height: 6px;">
                                        <div class="progress-bar bg-{{ $plan->getProgressColor() }}" style="width: {{ $plan->progress_percentage }}%"></div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="{{ route('dental-plans.show', ['dental_plan' => $plan->id]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                        <a href="{{ route('dental-plans.edit', ['dental_plan' => $plan->id]) }}" class="btn btn-sm btn-outline-warning"><i class="fas fa-edit"></i></a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-5">Sin registros</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="d-flex justify-content-center mt-3">
                {{ $plans->links('vendor.pagination.bootstrap-5') }}
            </div>
        </div>
    </div>
@endsection


