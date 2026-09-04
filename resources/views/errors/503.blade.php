<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Módulo en preparación - Dentaris</title>
    <style>
        * { box-sizing: border-box; }
        body {
            margin: 0;
            min-height: 100vh;
            display: grid;
            place-items: center;
            padding: 24px;
            font-family: Inter, -apple-system, BlinkMacSystemFont, "Segoe UI", sans-serif;
            color: #26334d;
            background: #f3f6fb;
        }
        .status-card {
            width: min(580px, 100%);
            padding: 40px;
            border: 1px solid #e5eaf2;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 18px 45px rgba(37, 55, 90, .10);
            text-align: center;
        }
        .status-code { margin: 0 0 8px; color: #5b73e8; font-size: 64px; line-height: 1; }
        h1 { margin: 0 0 12px; font-size: 26px; }
        p { margin: 0 auto 28px; max-width: 460px; color: #68748a; line-height: 1.6; }
        .actions { display: flex; flex-wrap: wrap; justify-content: center; gap: 12px; }
        .button {
            display: inline-flex;
            align-items: center;
            min-height: 42px;
            padding: 10px 18px;
            border: 1px solid #d8deea;
            border-radius: 8px;
            color: #34415b;
            background: #fff;
            font-weight: 600;
            text-decoration: none;
        }
        .button-primary { border-color: #5b73e8; color: #fff; background: #5b73e8; }
    </style>
</head>
<body>
    <main class="status-card" role="main">
        <div class="status-code" aria-hidden="true">503</div>
        <h1>Módulo en preparación clínica</h1>
        <p>{{ $message ?? 'Este módulo todavía no está disponible para la clínica activa.' }}</p>
        <div class="actions">
            <a class="button button-primary" href="{{ route('dashboard') }}">Volver al panel</a>
            <a class="button" href="{{ route('clinics.select') }}">Cambiar clínica</a>
        </div>
    </main>
</body>
</html>
