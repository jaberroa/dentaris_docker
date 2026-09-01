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
        
        .test-accounts {
            margin-top: 2rem;
            padding: 1rem;
            background: #f3f4f6;
            border-radius: 6px;
        }
        
        .test-accounts h3 {
            color: #374151;
            font-size: 0.875rem;
            margin-bottom: 0.5rem;
        }
        
        .test-accounts p {
            color: #6b7280;
            font-size: 0.75rem;
            margin-bottom: 0.25rem;
        }
        
        .test-accounts h4 {
            color: #374151;
            font-size: 0.8rem;
            margin: 1rem 0 0.5rem 0;
            border-top: 1px solid #d1d5db;
            padding-top: 0.5rem;
        }
        
        .quick-access {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        
        .quick-access.doctors {
            gap: 0.3rem;
        }
        
        .quick-btn {
            flex: 1;
            min-width: 80px;
            padding: 0.5rem 0.75rem;
            border: 2px solid transparent;
            border-radius: 6px;
            font-size: 0.75rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            text-align: center;
        }
        
        .quick-btn.admin {
            background: #dc2626;
            color: white;
        }
        
        .quick-btn.admin:hover {
            background: #b91c1c;
            transform: translateY(-1px);
        }
        
        .quick-btn.doctor {
            background: #059669;
            color: white;
        }
        
        .quick-btn.doctor:hover {
            background: #047857;
            transform: translateY(-1px);
        }
        
        .quick-btn.reception {
            background: #7c3aed;
            color: white;
        }
        
        .quick-btn.reception:hover {
            background: #6d28d9;
            transform: translateY(-1px);
        }
        
        .quick-btn.specialty {
            background: #2563eb;
            color: white;
            min-width: 60px;
            font-size: 0.7rem;
            padding: 0.4rem 0.5rem;
        }
        
        .quick-btn.specialty:hover {
            background: #1d4ed8;
            transform: translateY(-1px);
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
        
        <div class="test-accounts">
            <h3>🧪 Acceso Rápido</h3>
            
            <!-- Botones de acceso rápido -->
            <div class="quick-access">
                <button class="quick-btn admin" onclick="quickLogin('admin@dentaris.com', 'password')">
                    👑 Admin
                </button>
                <button class="quick-btn doctor" onclick="quickLogin('dentist@dentaris.com', 'password')">
                    🦷 Odontólogo
                </button>
                <button class="quick-btn reception" onclick="quickLogin('reception@dentaris.com', 'password')">
                    📞 Recepcionista
                </button>
            </div>
            
            <h4>👨‍⚕️ Doctores Especializados</h4>
            <div class="quick-access doctors">
                <button class="quick-btn specialty" onclick="quickLogin('carlos.mendoza@dentaris.com', 'password123')">
                    🔬 Endodoncia
                </button>
                <button class="quick-btn specialty" onclick="quickLogin('ana.silva@dentaris.com', 'password123')">
                    🦷 Periodoncia
                </button>
                <button class="quick-btn specialty" onclick="quickLogin('roberto.herrera@dentaris.com', 'password123')">
                    ⚕️ Cirugía Oral
                </button>
                <button class="quick-btn specialty" onclick="quickLogin('laura.vega@dentaris.com', 'password123')">
                    🦷 Prótesis
                </button>
                <button class="quick-btn specialty" onclick="quickLogin('miguel.torres@dentaris.com', 'password123')">
                    👶 Odontopediatría
                </button>
            </div>
        </div>
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
        
        function quickLogin(email, password) {
            // Llenar los campos automáticamente
            document.getElementById('email').value = email;
            document.getElementById('password').value = password;
            
            // Mostrar feedback visual
            const btn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            btnText.textContent = 'Acceso rápido...';
            btn.style.background = '#059669';
            
            // Enviar formulario después de un breve delay para mostrar el feedback
            setTimeout(() => {
                document.getElementById('loginForm').submit();
            }, 300);
        }
    </script>
</body>
</html>
