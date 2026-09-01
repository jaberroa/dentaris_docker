<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Historial Médico - {{ $patient->first_name }} {{ $patient->last_name }}</title>
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
        
        .patient-info {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 5px;
            margin-bottom: 30px;
            border-left: 4px solid #0d6efd;
        }
        
        .patient-info h2 {
            margin: 0 0 15px 0;
            color: #0d6efd;
            font-size: 18px;
        }
        
        .info-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        
        .info-item {
            display: flex;
            flex-direction: column;
        }
        
        .info-label {
            font-weight: bold;
            color: #495057;
            font-size: 11px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }
        
        .info-value {
            color: #333;
            font-size: 12px;
        }
        
        .section {
            margin-bottom: 30px;
            page-break-inside: avoid;
        }
        
        .section-title {
            background-color: #0d6efd;
            color: white;
            padding: 10px 15px;
            margin: 0 0 15px 0;
            font-size: 14px;
            font-weight: bold;
            border-radius: 3px;
        }
        
        .medical-record {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 15px;
            overflow: hidden;
        }
        
        .record-header {
            background-color: #f8f9fa;
            padding: 10px 15px;
            border-bottom: 1px solid #dee2e6;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .record-type {
            font-weight: bold;
            color: #0d6efd;
            text-transform: uppercase;
            font-size: 11px;
        }
        
        .record-date {
            color: #6c757d;
            font-size: 11px;
        }
        
        .record-body {
            padding: 15px;
        }
        
        .record-field {
            margin-bottom: 10px;
        }
        
        .field-label {
            font-weight: bold;
            color: #495057;
            font-size: 11px;
            margin-bottom: 3px;
        }
        
        .field-value {
            color: #333;
            font-size: 12px;
            line-height: 1.4;
        }
        
        .appointment {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
        }
        
        .appointment-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 8px;
        }
        
        .appointment-date {
            font-weight: bold;
            color: #0d6efd;
        }
        
        .appointment-status {
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-completed {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-scheduled {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .status-cancelled {
            background-color: #f8d7da;
            color: #721c24;
        }
        
        .treatment-plan {
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 10px;
            padding: 10px;
            background-color: #f8f9fa;
        }
        
        .plan-header {
            font-weight: bold;
            color: #0d6efd;
            margin-bottom: 8px;
        }
        
        .plan-status {
            display: inline-block;
            padding: 2px 8px;
            border-radius: 3px;
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
        }
        
        .status-active {
            background-color: #d4edda;
            color: #155724;
        }
        
        .status-completed {
            background-color: #cce5ff;
            color: #004085;
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: #6c757d;
            font-style: italic;
            background-color: #f8f9fa;
            border-radius: 5px;
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
    </style>
</head>
<body>
    <div class="header">
        <h1>🦷 Dentaris</h1>
        <p class="subtitle">Sistema de Gestión Clínica Dental</p>
        <h2>Historial Médico del Paciente</h2>
    </div>

    <div class="patient-info">
        <h2>{{ $patient->first_name }} {{ $patient->last_name }}</h2>
        <div class="info-grid">
            <div class="info-item">
                <span class="info-label">Código de Paciente</span>
                <span class="info-value">{{ $patient->patient_code }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Fecha de Nacimiento</span>
                <span class="info-value">
                    @if($patient->birth_date)
                        {{ $patient->birth_date->format('d/m/Y') }} ({{ $patient->birth_date->age }} años)
                    @else
                        No especificada
                    @endif
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Género</span>
                <span class="info-value">{{ ucfirst($patient->gender) }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Teléfono</span>
                <span class="info-value">{{ $patient->phone ?? 'No especificado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Correo</span>
                <span class="info-value">{{ $patient->email ?? 'No especificado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Tipo de Sangre</span>
                <span class="info-value">{{ $patient->blood_type ?? 'No especificado' }}</span>
            </div>
            <div class="info-item">
                <span class="info-label">Dirección</span>
                <span class="info-value">
                    @if($patient->address)
                        {{ $patient->address }}
                        @if($patient->city), {{ $patient->city }}@endif
                        @if($patient->state), {{ $patient->state }}@endif
                    @else
                        No especificada
                    @endif
                </span>
            </div>
            <div class="info-item">
                <span class="info-label">Contacto de Emergencia</span>
                <span class="info-value">
                    @if($patient->emergency_contact_name)
                        {{ $patient->emergency_contact_name }}
                        @if($patient->emergency_contact_phone) - {{ $patient->emergency_contact_phone }}@endif
                    @else
                        No especificado
                    @endif
                </span>
            </div>
        </div>
    </div>

    <!-- Información Médica -->
    @if($patient->medical_history || $patient->dental_history || $patient->allergies || $patient->medications)
    <div class="section">
        <h3 class="section-title">Información Médica</h3>
        
        @if($patient->medical_history)
        <div class="record-field">
            <div class="field-label">Historial Médico</div>
            <div class="field-value">{{ $patient->medical_history }}</div>
        </div>
        @endif
        
        @if($patient->dental_history)
        <div class="record-field">
            <div class="field-label">Historial Dental</div>
            <div class="field-value">{{ $patient->dental_history }}</div>
        </div>
        @endif
        
        @if($patient->allergies)
        <div class="record-field">
            <div class="field-label">Alergias</div>
            <div class="field-value">{{ $patient->allergies }}</div>
        </div>
        @endif
        
        @if($patient->medications)
        <div class="record-field">
            <div class="field-label">Medicamentos</div>
            <div class="field-value">{{ $patient->medications }}</div>
        </div>
        @endif
    </div>
    @endif

    <!-- Historias Clínicas -->
    @if($patient->medicalRegistros && $patient->medicalRegistros->count() > 0)
    <div class="section">
        <h3 class="section-title">Historias Clínicas ({{ $patient->medicalRegistros->count() }} registros)</h3>
        
        @foreach($patient->medicalRegistros as $record)
        <div class="medical-record">
            <div class="record-header">
                <span class="record-type">{{ ucfirst($record->record_type) }}</span>
                <span class="record-date">{{ $record->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="record-body">
                @if($record->chief_complaint)
                <div class="record-field">
                    <div class="field-label">Motivo de Consulta</div>
                    <div class="field-value">{{ $record->chief_complaint }}</div>
                </div>
                @endif
                
                @if($record->diagnostic_impression)
                <div class="record-field">
                    <div class="field-label">Diagnóstico</div>
                    <div class="field-value">{{ $record->diagnostic_impression }}</div>
                </div>
                @endif
                
                @if($record->treatment_plan)
                <div class="record-field">
                    <div class="field-label">Plan de Tratamiento</div>
                    <div class="field-value">{{ $record->treatment_plan }}</div>
                </div>
                @endif
                
                @if($record->notes)
                <div class="record-field">
                    <div class="field-label">Notas</div>
                    <div class="field-value">{{ $record->notes }}</div>
                </div>
                @endif
                
                @if($record->staff)
                <div class="record-field">
                    <div class="field-label">Profesional</div>
                    <div class="field-value">{{ $record->staff->user->name ?? 'N/A' }}</div>
                </div>
                @endif
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <!-- Citas -->
    @if($patient->appointments && $patient->appointments->count() > 0)
    <div class="section">
        <h3 class="section-title">Citas Médicas ({{ $patient->appointments->count() }} citas)</h3>
        
        @foreach($patient->appointments->take(10) as $appointment)
        <div class="appointment">
            <div class="appointment-header">
                <span class="appointment-date">{{ $appointment->start_time->format('d/m/Y H:i') }}</span>
                <span class="appointment-status status-{{ $appointment->appointmentEstado->name ?? 'scheduled' }}">
                    {{ ucfirst($appointment->appointmentEstado->name ?? 'Programada') }}
                </span>
            </div>
            @if($appointment->staff)
            <div class="field-value">Profesional: {{ $appointment->staff->user->name ?? 'N/A' }}</div>
            @endif
            @if($appointment->notes)
            <div class="field-value">Notas: {{ $appointment->notes }}</div>
            @endif
        </div>
        @endforeach
        
        @if($patient->appointments->count() > 10)
        <div class="no-data">
            ... y {{ $patient->appointments->count() - 10 }} citas más
        </div>
        @endif
    </div>
    @endif

    <!-- Planes de Tratamiento -->
    @if($patient->treatmentPlans && $patient->treatmentPlans->count() > 0)
    <div class="section">
        <h3 class="section-title">Planes de Tratamiento ({{ $patient->treatmentPlans->count() }} planes)</h3>
        
        @foreach($patient->treatmentPlans as $plan)
        <div class="treatment-plan">
            <div class="plan-header">
                {{ $plan->name ?? 'Plan de Tratamiento' }}
                <span class="plan-status status-{{ $plan->status ?? 'active' }}">
                    {{ ucfirst($plan->status ?? 'Activo') }}
                </span>
            </div>
            @if($plan->description)
            <div class="field-value">{{ $plan->description }}</div>
            @endif
            <div class="field-value">
                <strong>Fecha de inicio:</strong> {{ $plan->created_at->format('d/m/Y') }}
            </div>
        </div>
        @endforeach
    </div>
    @endif

    <div class="footer">
        <p>Este historial médico fue generado automáticamente por el sistema Dentaris</p>
        <p>Fecha de generación: {{ now()->format('d/m/Y H:i:s') }}</p>
        <p>Para más información, contacte al administrador del sistema</p>
    </div>
</body>
</html>





