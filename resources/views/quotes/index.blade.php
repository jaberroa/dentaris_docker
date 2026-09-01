@extends('layouts.master')

@section('title', 'Cotizaciones')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Cotizaciones</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Panel de Control</a></li>
                        <li class="breadcrumb-item active">Cotizaciones</li>
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
                        <h5 class="card-title mb-0">Lista de Cotizaciones</h5>
                        <a href="{{ route('quotes.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-middle me-1"></i> Nueva Cotización
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($quotes as $quote)
                                <tr>
                                    <td>{{ $quote->id }}</td>
                                    <td>{{ $quote->patient->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($quote->total_amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $quote->status === 'approved' ? 'success' : 'warning' }}">
                                            {{ ucfirst($quote->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('quotes.show', $quote) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('quotes.edit', $quote) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="5" class="text-center">No hay cotizaciones registradas</td>
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





