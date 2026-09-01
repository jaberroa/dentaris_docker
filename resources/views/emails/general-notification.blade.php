<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notificación General</title>
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
            background-color: #6c757d;
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
        .message-content {
            background-color: white;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            border-left: 4px solid #6c757d;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            color: #666;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>🦷 {{ $clinicNombre }}</h1>
        <h2>Notificación</h2>
    </div>

    <div class="content">
        <div class="message-content">
            {!! nl2br(e($message)) !!}
        </div>

        <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
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





