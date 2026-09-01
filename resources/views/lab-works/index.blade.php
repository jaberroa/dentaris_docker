@extends('layouts.master')

@section('title', 'Trabajos de Laboratorio')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Trabajos de Laboratorio</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Panel de Control</a></li>
                        <li class="breadcrumb-item active">Laboratorio</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0">Lista de Trabajos de Laboratorio</h5>
                        <a href="{{ route('lab-works.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-middle me-1"></i> Nuevo Trabajo
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Paciente</th>
                                    <th>Tipo</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($labWorks as $labWork)
                                <tr>
                                    <td>{{ $labWork->id }}</td>
                                    <td>{{ $labWork->patient->name ?? 'N/A' }}</td>
                                    <td>{{ $labWork->work_type ?? 'N/A' }}</td>
                                    <td>
                                        <span class="badge bg-{{ $labWork->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($labWork->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('lab-works.show', $labWork) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('lab-works.edit', $labWork) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay trabajos de laboratorio registrados</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection





