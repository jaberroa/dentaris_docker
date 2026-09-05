<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Denegado - Dentaris</title>
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
            max-width: 600px;
            width: 90%;
        }
        .icon {
            font-size: 64px;
            margin-bottom: 20px;
        }
        .title {
            font-size: 28px;
            color: #333;
            margin-bottom: 20px;
        }
        .message {
            color: #666;
            margin-bottom: 30px;
            line-height: 1.6;
            font-size: 16px;
        }
        .steps {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
            text-align: left;
        }
        .steps h4 {
            margin: 0 0 15px 0;
            color: #374151;
        }
        .steps ol {
            margin: 0;
            padding-left: 20px;
        }
        .steps li {
            margin: 8px 0;
            color: #6b7280;
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
            font-size: 16px;
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
        .warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon">🚫</div>
        <h1 class="title">Acceso Denegado</h1>
        <p class="message">
            No tienes permisos para acceder a esta página o no has iniciado sesión.
        </p>
        
        <div class="warning">
            <strong>⚠️ Importarante:</strong> Debes iniciar sesión primero para acceder a los módulos del sistema.
        </div>
        
        <div class="steps">
            <h4>Pasos para acceder:</h4>
            <ol>
                <li>Haz clic en "Iniciar Sesión"</li>
                <li>Ingresa tus credenciales personales</li>
                <li>Una vez autenticado, podrás acceder solo a los módulos autorizados</li>
                <li>Navega desde el dashboard a cualquier sección</li>
            </ol>
        </div>
        
        <a href="{{ route('login') }}" class="btn">Iniciar Sesión</a>
        <a href="{{ url('/') }}" class="btn btn-secondary">Volver al Inicio</a>
    </div>
</body>
</html>





