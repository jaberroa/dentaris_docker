<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Personal - Dentaris</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
            color: #333;
            margin: 0;
            padding: 20px;
        }
        
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #0d6efd;
            padding-bottom: 20px;
        }
        
        .header h1 {
            color: #0d6efd;
            font-size: 24px;
            margin: 0 0 10px 0;
            font-weight: bold;
        }
        
        .header p {
            margin: 5px 0;
            color: #666;
        }
        
        .filters {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .filters h3 {
            margin: 0 0 10px 0;
            color: #0d6efd;
            font-size: 14px;
        }
        
        .filters p {
            margin: 5px 0;
            font-size: 11px;
        }
        
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        
        .table th {
            background-color: #0d6efd;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #0d6efd;
        }
        
        .table td {
            padding: 10px 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
        }
        
        .table tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        .table tr:hover {
            background-color: #e9ecef;
        }
        
        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .badge-success {
            background-color: #d1e7dd;
            color: #0f5132;
        }
        
        .badge-danger {
            background-color: #f8d7da;
            color: #842029;
        }
        
        .badge-info {
            background-color: #cff4fc;
            color: #055160;
        }
        
        .badge-secondary {
            background-color: #e2e3e5;
            color: #41464b;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        .summary {
            background-color: #e7f3ff;
            border: 1px solid #b3d9ff;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 20px;
        }
        
        .summary h3 {
            margin: 0 0 10px 0;
            color: #0d6efd;
            font-size: 14px;
        }
        
        .summary-stats {
            display: flex;
            justify-content: space-around;
            margin-top: 10px;
        }
        
        .stat {
            text-align: center;
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: bold;
            color: #0d6efd;
        }
        
        .stat-label {
            font-size: 10px;
            color: #666;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte de Personal</h1>
        <p><strong>Dentaris - Sistema de Gestión Dental</strong></p>
        <p>Generado el: {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>

    @if(!empty($filters) && array_filter($filters))
        <div class="filters">
            <h3>Filtros Aplicados</h3>
            @if(!empty($filters['search']))
                <p><strong>Búsqueda:</strong> {{ $filters['search'] }}</p>
            @endif
            @if(!empty($filters['specialty']))
                <p><strong>Especialidad:</strong> {{ $filters['specialty'] }}</p>
            @endif
            @if(!empty($filters['is_active']))
                <p><strong>Estado:</strong> {{ $filters['is_active'] == '1' ? 'Activo' : 'Inactivo' }}</p>
            @endif
        </div>
    @endif

    <div class="summary">
        <h3>Resumen del Reporte</h3>
        <div class="summary-stats">
            <div class="stat">
                <div class="stat-number">{{ $staff->count() }}</div>
                <div class="stat-label">Total Personal</div>
            </div>
            <div class="stat">
                <div class="stat-number">{{ $staff->where('is_active', true)->count() }}</div>
                <div class="stat-label">Activos</div>
            </div>
            <div class="stat">
                <div class="stat-number">{{ $staff->where('is_active', false)->count() }}</div>
                <div class="stat-label">Inactivos</div>
            </div>
            <div class="stat">
                <div class="stat-number">{{ $staff->groupBy('specialty')->count() }}</div>
                <div class="stat-label">Especialidades</div>
            </div>
        </div>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>Código</th>
                <th>Nombre</th>
                <th>Correo</th>
                <th>Teléfono</th>
                <th>Especialidad</th>
                <th>Rol</th>
                <th>Estado</th>
                <th>Fecha Registro</th>
            </tr>
        </thead>
        <tbody>
            @forelse($staff as $employee)
                <tr>
                    <td><strong>{{ $employee->employee_id }}</strong></td>
                    <td>{{ $employee->user->name ?? 'N/A' }}</td>
                    <td>{{ $employee->user->email ?? 'N/A' }}</td>
                    <td>{{ $employee->user->phone ?? 'N/A' }}</td>
                    <td>
                        <span class="badge badge-info">
                            {{ $employee->specialty ?? 'General' }}
                        </span>
                    </td>
                    <td>
                        <span class="badge badge-secondary">
                            {{ $employee->user->roles->first()->display_name ?? 'Sin rol' }}
                        </span>
                    </td>
                    <td>
                        @if($employee->is_active)
                            <span class="badge badge-success">Activo</span>
                        @else
                            <span class="badge badge-danger">Inactivo</span>
                        @endif
                    </td>
                    <td>{{ $employee->created_at->format('d/m/Y') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align: center; padding: 20px; color: #666;">
                        No hay personal registrado con los filtros aplicados.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema Dentaris</p>
        <p>Para más información, contacte al administrador del sistema</p>
    </div>
</body>
</html>





