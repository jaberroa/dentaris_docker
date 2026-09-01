<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso requerido - Dentaris</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 90%;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 24px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 5px;
            transition: background 0.3s;
            margin: 0 10px;
        }
        .btn:hover {
            background: #5a6fd8;
        }
        .btn-secondary {
            background: #6b7280;
        }
        .btn-secondary:hover {
            background: #4b5563;
        }
        .credentials {
            background: #f3f4f6;
            padding: 20px;
            border-radius: 5px;
            margin: 20px 0;
            text-align: left;
        }
        .credentials h4 {
            margin: 0 0 10px 0;
            color: #374151;
        }
        .credentials p {
            margin: 5px 0;
            color: #6b7280;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🔒</div>
        <h1 class="title">Acceso Requerido</h1>
        <p class="message">
            Para acceder a esta página, necesitas iniciar sesión en el sistema Dentaris.
        </p>
        
        <div class="credentials">
            <h4>Credenciales de prueba:</h4>
            <p><strong>Correo:</strong> admin@dentaris.com</p>
            <p><strong>Contraseña:</strong> password</p>
        </div>
        
        <a href="{{ route('login') }}" class="btn">Iniciar Sesión</a>
        <a href="{{ url('/') }}" class="btn btn-secondary">Volver al Inicio</a>
    </div>
</body>
</html>





