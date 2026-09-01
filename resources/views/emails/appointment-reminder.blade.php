<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registroatorio de Cita</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #007bff;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 30px;
            border-radius: 0 0 8px 8px;
        }
        .appointment-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #007bff;
        }
        .detail-row {
            display: flex;
            margin: 10px 0;
        }
        .detail-label {
            font-weight: bold;
            width: 120px;
            color: #666;
        }
        .detail-value {
            flex: 1;
        }
        .button {
            display: inline-block;
            background-color: #28a745;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
        .alert {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🦷 {{ $clinicNombre }}</h1>
        <h2>Registroatorio de Cita</h2>
    </div>

    <div class="content">
        <p>Hola <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>,</p>

        @if($reminderTipo === '24_hour')
            <div class="alert">
                <strong>📅 Registroatorio:</strong> Tienes una cita programada para mañana.
            </div>
        @elseif($reminderTipo === '1_hour')
            <div class="alert">
                <strong>⏰ Registroatorio:</strong> Tienes una cita en 1 hora.
            </div>
        @elseif($reminderTipo === 'same_day')
            <div class="alert">
                <strong>📅 Registroatorio:</strong> Tienes una cita programada para hoy.
            </div>
        @endif

        <div class="appointment-details">
            <h3>📋 Detalles de la Cita</h3>
            
            <div class="detail-row">
                <div class="detail-label">📅 Fecha:</div>
                <div class="detail-value">{{ $appointmentFecha }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">🕐 Hora:</div>
                <div class="detail-value">{{ $appointmentHora }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">👨‍⚕️ Doctor:</div>
                <div class="detail-value">{{ $staff->user->name }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">📋 Tipo:</div>
                <div class="detail-value">{{ $appointment->type }}</div>
            </div>
            
            @if($appointment->reason)
            <div class="detail-row">
                <div class="detail-label">📝 Motivo:</div>
                <div class="detail-value">{{ $appointment->reason }}</div>
            </div>
            @endif
            
            <div class="detail-row">
                <div class="detail-label">📍 Ubicación:</div>
                <div class="detail-value">{{ $clinicAgregarress }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">📞 Teléfono:</div>
                <div class="detail-value">{{ $clinicTeléfono }}</div>
            </div>
        </div>

        <p><strong>Importarante:</strong> Por favor, confirma tu asistencia o contacta con nosotros si necesitas reprogramar la cita.</p>

        <a href="{{ url('/appointments/' . $appointment->id) }}" class="button">
            Ver Detalles de la Cita
        </a>

        <p>Si tienes alguna pregunta o necesitas reprogramar tu cita, no dudes en contactarnos.</p>
    </div>

    <div class="footer">
        <p><strong>{{ $clinicNombre }}</strong></p>
        <p>📍 {{ $clinicAgregarress }}</p>
        <p>📞 {{ $clinicTeléfono }}</p>
        <p>🌐 {{ config('app.url') }}</p>
        <p><small>Este es un mensaje automático, por favor no responda a este email.</small></p>
    </div>
</body>
</html>





