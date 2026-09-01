@extends('layouts.master')

@section('title', 'Historias Clínicas')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Historias Clínicas</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Panel de Control</a></li>
                        <li class="breadcrumb-item active">Historias Clínicas</li>
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
                        <h5 class="card-title mb-0">Lista de Historias Clínicas</h5>
                        <a href="{{ route('medical-records.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-middle me-1"></i> Nueva Historia
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
                                    <th>Fecha</th>
                                    <th>Diagnóstico</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($medicalRegistros as $record)
                                <tr>
                                    <td>{{ $record->id }}</td>
                                    <td>{{ $record->patient->name ?? 'N/A' }}</td>
                                    <td>{{ $record->created_at->format('d/m/Y') }}</td>
                                    <td>{{ Str::limit($record->diagnosis ?? 'Sin diagnóstico', 50) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('medical-records.show', $record) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('medical-records.edit', $record) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay historias clínicas registradas</td>
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





