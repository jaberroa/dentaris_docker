<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pacientes - Dentaris</title>
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
        
        .header .subtitle {
            color: #6c757d;
            font-size: 14px;
            margin: 0;
        }
        
        .report-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid #0d6efd;
        }
        
        .report-info h3 {
            margin: 0 0 10px 0;
            color: #0d6efd;
            font-size: 16px;
        }
        
        .report-info p {
            margin: 5px 0;
            color: #6c757d;
        }
        
        .stats {
            display: flex;
            justify-content: space-around;
            margin: 20px 0;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        
        .stat-item {
            text-align: center;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #0d6efd;
            display: block;
        }
        
        .stat-label {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            background-color: white;
        }
        
        th {
            background-color: #0d6efd;
            color: white;
            padding: 12px 8px;
            text-align: left;
            font-weight: bold;
            font-size: 11px;
            border: 1px solid #0d6efd;
        }
        
        td {
            padding: 10px 8px;
            border: 1px solid #dee2e6;
            font-size: 10px;
            vertical-align: top;
        }
        
        tr:nth-child(even) {
            background-color: #f8f9fa;
        }
        
        tr:hover {
            background-color: #e9ecef;
        }
        
        .status-active {
            color: #198754;
            font-weight: bold;
        }
        
        .status-inactive {
            color: #dc3545;
            font-weight: bold;
        }
        
        .gender-male {
            color: #0d6efd;
        }
        
        .gender-female {
            color: #e91e63;
        }
        
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #6c757d;
            border-top: 1px solid #dee2e6;
            padding-top: 15px;
        }
        
        .page-break {
            page-break-before: always;
        }
        
        .no-data {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🦷 Dentaris</h1>
        <p class="subtitle">Sistema de Gestión Clínica Dental</p>
        <h2>Reporte de Pacientes</h2>
    </div>

    <div class="report-info">
        <h3>Información del Reporte</h3>
        <p><strong>Fecha de generación:</strong> {{ now()->format('d/m/Y H:i:s') }}</p>
        <p><strong>Generado por:</strong> {{ Auth::user()->name }}</p>
        @if(!empty($filters))
            <p><strong>Filtros aplicados:</strong></p>
            <ul>
                @if(!empty($filters['search']))
                    <li>Búsqueda: "{{ $filters['search'] }}"</li>
                @endif
                @if(!empty($filters['gender']))
                    <li>Género: {{ ucfirst($filters['gender']) }}</li>
                @endif
                @if(!empty($filters['is_active']))
                    <li>Estado: {{ $filters['is_active'] ? 'Activos' : 'Inactivos' }}</li>
                @endif
                @if(!empty($filters['date_from']) || !empty($filters['date_to']))
                    <li>Rango de fechas: 
                        {{ $filters['date_from'] ?? 'Inicio' }} - {{ $filters['date_to'] ?? 'Actual' }}
                    </li>
                @endif
            </ul>
        @endif
    </div>

    <div class="stats">
        <div class="stat-item">
            <span class="stat-number">{{ $patients->count() }}</span>
            <div class="stat-label">Total Pacientes</div>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $patients->where('is_active', true)->count() }}</span>
            <div class="stat-label">Pacientes Activos</div>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $patients->where('gender', 'female')->count() }}</span>
            <div class="stat-label">Mujeres</div>
        </div>
        <div class="stat-item">
            <span class="stat-number">{{ $patients->where('gender', 'male')->count() }}</span>
            <div class="stat-label">Hombres</div>
        </div>
    </div>

    @if($patients->count() > 0)
        <table>
            <thead>
                <tr>
                    <th style="width: 8%;">Código</th>
                    <th style="width: 20%;">Nombre Completo</th>
                    <th style="width: 15%;">Contacto</th>
                    <th style="width: 8%;">Edad</th>
                    <th style="width: 8%;">Género</th>
                    <th style="width: 15%;">Ubicación</th>
                    <th style="width: 10%;">Tipo Sangre</th>
                    <th style="width: 8%;">Estado</th>
                    <th style="width: 8%;">Registro</th>
                </tr>
            </thead>
            <tbody>
                @foreach($patients as $patient)
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $patient->patient_code }}</td>
                    <td>
                        <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>
                        @if($patient->occupation)
                            <br><small style="color: #6c757d;">{{ $patient->occupation }}</small>
                        @endif
                    </td>
                    <td>
                        @if($patient->email)
                            <strong>📧</strong> {{ $patient->email }}<br>
                        @endif
                        @if($patient->phone)
                            <strong>📱</strong> {{ $patient->phone }}
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($patient->birth_date)
                            {{ $patient->birth_date->age }} años
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center;" class="gender-{{ $patient->gender }}">
                        @if($patient->gender === 'male')
                            👨 Masculino
                        @elseif($patient->gender === 'female')
                            👩 Femenino
                        @else
                            👤 Otro
                        @endif
                    </td>
                    <td>
                        @if($patient->city)
                            {{ $patient->city }}
                        @endif
                        @if($patient->state)
                            <br><small>{{ $patient->state }}</small>
                        @endif
                    </td>
                    <td style="text-align: center;">
                        @if($patient->blood_type)
                            <strong>{{ $patient->blood_type }}</strong>
                        @else
                            -
                        @endif
                    </td>
                    <td style="text-align: center;" class="status-{{ $patient->is_active ? 'active' : 'inactive' }}">
                        {{ $patient->is_active ? '✅ Activo' : '❌ Inactivo' }}
                    </td>
                    <td style="text-align: center;">
                        {{ $patient->created_at->format('d/m/Y') }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <div class="no-data">
            <h3>No se encontraron pacientes</h3>
            <p>No hay pacientes que coincidan con los criterios de búsqueda especificados.</p>
        </div>
    @endif

    <div class="footer">
        <p>Este reporte fue generado automáticamente por el sistema Dentaris</p>
        <p>Para más información, contacte al administrador del sistema</p>
        <p>Página 1 de 1 - {{ now()->format('d/m/Y H:i:s') }}</p>
    </div>
</body>
</html>





