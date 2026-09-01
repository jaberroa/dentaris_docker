@extends('layouts.master')

@section('title', 'Tratamientos')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Tratamientos</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Panel de Control</a></li>
                        <li class="breadcrumb-item active">Tratamientos</li>
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
                        <h5 class="card-title mb-0">Lista de Tratamientos</h5>
                        <a href="{{ route('treatments.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-middle me-1"></i> Nuevo Tratamiento
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Descripción</th>
                                    <th>Precio</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($treatments as $treatment)
                                <tr>
                                    <td>{{ $treatment->id }}</td>
                                    <td>{{ $treatment->name }}</td>
                                    <td>{{ Str::limit($treatment->description ?? 'Sin descripción', 50) }}</td>
                                    <td>${{ number_format($treatment->price, 2) }}</td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('treatments.show', $treatment) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('treatments.edit', $treatment) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay tratamientos registrados</td>
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





