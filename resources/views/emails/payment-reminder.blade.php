<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registroatorio de Pago</title>
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
            background-color: #dc3545;
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
        .payment-details {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #dc3545;
        }
        .detail-row {
            display: flex;
            margin: 10px 0;
        }
        .detail-label {
            font-weight: bold;
            width: 150px;
            color: #666;
        }
        .detail-value {
            flex: 1;
        }
        .amount {
            font-size: 18px;
            font-weight: bold;
            color: #dc3545;
        }
        .button {
            display: inline-block;
            background-color: #007bff;
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
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
        .success {
            background-color: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🦷 {{ $clinicNombre }}</h1>
        <h2>Registroatorio de Pago</h2>
    </div>

    <div class="content">
        <p>Hola <strong>{{ $patient->first_name }} {{ $patient->last_name }}</strong>,</p>

        @if($reminderTipo === 'overdue')
            <div class="alert">
                <strong>⚠️ Pago Vencido:</strong> Tienes un pago vencido que requiere atención inmediata.
            </div>
        @elseif($reminderTipo === 'due_soon')
            <div class="alert">
                <strong>📅 Próximo a Vencer:</strong> Tienes un pago próximo a vencer.
            </div>
        @elseif($reminderTipo === 'payment_received')
            <div class="success">
                <strong>✅ Pago Recibido:</strong> Hemos recibido tu pago exitosamente.
            </div>
        @endif

        <div class="payment-details">
            <h3>💰 Detalles del Pago</h3>
            
            <div class="detail-row">
                <div class="detail-label">📄 Factura:</div>
                <div class="detail-value">{{ $invoiceNumber }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">💰 Monto Total:</div>
                <div class="detail-value amount">${{ number_format($totalMonto, 2) }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">💳 Saldo Pendiente:</div>
                <div class="detail-value amount">${{ number_format($balanceDue, 2) }}</div>
            </div>
            
            <div class="detail-row">
                <div class="detail-label">📅 Fecha de Vencimiento:</div>
                <div class="detail-value">{{ $dueFecha }}</div>
            </div>
            
            @if($reminderTipo === 'overdue' && $daysOverdue > 0)
            <div class="detail-row">
                <div class="detail-label">⚠️ Días de Retraso:</div>
                <div class="detail-value">{{ $daysOverdue }} días</div>
            </div>
            @endif
        </div>

        @if($reminderTipo !== 'payment_received')
        <p><strong>Importarante:</strong> Por favor, realiza el pago lo antes posible o contacta con nosotros para hacer arreglos de pago.</p>

        <a href="{{ url('/billing/' . $invoice->id) }}" class="button">
            Ver Detalles de la Factura
        </a>
        @endif

        <p>Si tienes alguna pregunta sobre tu factura o necesitas hacer arreglos de pago, no dudes en contactarnos.</p>
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





