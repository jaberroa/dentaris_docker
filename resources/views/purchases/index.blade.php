@extends('layouts.master')

@section('title', 'Compras')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="page-title-box d-sm-flex align-items-center justify-content-between">
                <h4 class="mb-sm-0">Gestión de Compras</h4>
                <div class="page-title-right">
                    <ol class="breadcrumb m-0">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Panel de Control</a></li>
                        <li class="breadcrumb-item active">Compras</li>
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
                        <h5 class="card-title mb-0">Lista de Compras</h5>
                        <a href="{{ route('purchases.create') }}" class="btn btn-primary">
                            <i class="ri-add-line align-middle me-1"></i> Nueva Compra
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Proveedor</th>
                                    <th>Total</th>
                                    <th>Fecha</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($purchases as $purchase)
                                <tr>
                                    <td>{{ $purchase->id }}</td>
                                    <td>{{ $purchase->supplier->name ?? 'N/A' }}</td>
                                    <td>${{ number_format($purchase->total_amount, 2) }}</td>
                                    <td>{{ $purchase->created_at->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge bg-{{ $purchase->status === 'completed' ? 'success' : 'warning' }}">
                                            {{ ucfirst($purchase->status) }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <a href="{{ route('purchases.show', $purchase) }}" class="btn btn-sm btn-outline-primary">
                                                <i class="ri-eye-line"></i>
                                            </a>
                                            <a href="{{ route('purchases.edit', $purchase) }}" class="btn btn-sm btn-outline-warning">
                                                <i class="ri-edit-line"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center">No hay compras registradas</td>
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





