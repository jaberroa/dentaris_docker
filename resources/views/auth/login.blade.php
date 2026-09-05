<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Dentaris</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .logo {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo h1 {
            color: #2563eb;
            font-size: 2rem;
            font-weight: 700;
        }
        
        .logo p {
            color: #6b7280;
            margin-top: 0.5rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #374151;
            font-weight: 500;
        }
        
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e5e7eb;
            border-radius: 6px;
            font-size: 1rem;
            transition: border-color 0.2s;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
        }
        
        .btn {
            width: 100%;
            padding: 0.75rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .btn:hover {
            background: #1d4ed8;
        }
        
        .btn:disabled {
            background: #9ca3af;
            cursor: not-allowed;
        }
        
        .error {
            color: #dc2626;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .success {
            color: #059669;
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        
        .loading {
            display: none;
        }
        
        .loading.show {
            display: inline-block;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <h1>🦷 Dentaris</h1>
            <p>Sistema de Gestión Clínica Dental</p>
        </div>

        @if (session('error'))
            <div class="alert alert-danger" style="background: #fee2e2; color: #dc2626; padding: 1rem; border-radius: 5px; margin-bottom: 1rem; border: 1px solid #fecaca;">
                {{ session('error') }}
            </div>
        @endif
        
        <form method="POST" action="/login" id="loginForm">
            @csrf
            
            <div class="form-group">
                <label for="email">Correo</label>
                <input 
                    type="email" 
                    id="email" 
                    name="email" 
                    value="{{ old('email') }}" 
                    required 
                    autofocus
                    placeholder="tu@email.com"
                >
                @error('email')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input 
                    type="password" 
                    id="password" 
                    name="password" 
                    required
                    placeholder="••••••••"
                >
                @error('password')
                    <div class="error">{{ $message }}</div>
                @enderror
            </div>
            
            <button type="submit" class="btn" id="submitBtn">
                <span class="loading" id="loading">⏳</span>
                <span id="btnText">Iniciar Sesión</span>
            </button>
        </form>
        
        @if (session('status'))
            <div class="success">{{ session('status') }}</div>
        @endif
        
    </div>
    
    <script>
        document.getElementById('loginForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const loading = document.getElementById('loading');
            const btnText = document.getElementById('btnText');
            
            btn.disabled = true;
            loading.classList.add('show');
            btnText.textContent = 'Iniciando sesión...';
        });
        
    </script>
</body>
</html>
