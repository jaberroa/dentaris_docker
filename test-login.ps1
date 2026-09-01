# Script de prueba para el sistema de login
Write-Host "Probando Sistema de Login - Dentaris" -ForegroundColor Green
Write-Host ""

# URL base
$baseUrl = "http://127.0.0.1:8001"

Write-Host "1. Probando página de login..." -ForegroundColor Yellow
try {
    $loginPage = Invoke-WebRequest -Uri "$baseUrl/" -UseBasicParsing
    if ($loginPage.Content -match "Dentaris") {
        Write-Host "OK - Pagina de login cargada correctamente" -ForegroundColor Green
    } else {
        Write-Host "❌ Error: No se encontró 'Dentaris' en la página" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Error al cargar página de login: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "2. Probando acceso al dashboard sin autenticación..." -ForegroundColor Yellow
try {
    $dashboardPage = Invoke-WebRequest -Uri "$baseUrl/dashboard" -UseBasicParsing
    if ($dashboardPage.Content -match "Login - Dentaris") {
        Write-Host "OK - Redireccion a login funcionando (proteccion activa)" -ForegroundColor Green
    } else {
        Write-Host "❌ Error: No se redirigió al login" -ForegroundColor Red
    }
} catch {
    Write-Host "❌ Error al probar dashboard: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "3. Probando login con credenciales de prueba..." -ForegroundColor Yellow

# Crear sesión para mantener cookies
$session = New-Object Microsoft.PowerShell.Commands.WebRequestSession

try {
    # Obtener página de login para obtener CSRF token
    $loginPage = Invoke-WebRequest -Uri "$baseUrl/" -WebSession $session -UseBasicParsing
    
    # Extraer CSRF token
    $csrfToken = ""
    if ($loginPage.Content -match 'name="_token" value="([^"]+)"') {
        $csrfToken = $matches[1]
        Write-Host "OK - CSRF token obtenido: $($csrfToken.Substring(0,10))..." -ForegroundColor Green
    } else {
        Write-Host "❌ Error: No se pudo obtener CSRF token" -ForegroundColor Red
        exit 1
    }
    
    # Datos de login
    $loginData = @{
        email = "admin@dentaris.com"
        password = "password"
        _token = $csrfToken
    }
    
    # Realizar login
    $loginResponse = Invoke-WebRequest -Uri "$baseUrl/login" -Method POST -Body $loginData -WebSession $session -UseBasicParsing
    
    if ($loginResponse.StatusCode -eq 302) {
        Write-Host "OK - Login exitoso - Redireccion recibida" -ForegroundColor Green
        
        # Probar acceso al dashboard
        $dashboardResponse = Invoke-WebRequest -Uri "$baseUrl/dashboard" -WebSession $session -UseBasicParsing
        
        if ($dashboardResponse.Content -match "Dashboard") {
            Write-Host "OK - Acceso al dashboard exitoso" -ForegroundColor Green
        } else {
            Write-Host "❌ Error: No se pudo acceder al dashboard" -ForegroundColor Red
        }
    } else {
        Write-Host "❌ Error: Login falló - Status: $($loginResponse.StatusCode)" -ForegroundColor Red
    }
    
} catch {
    Write-Host "❌ Error en el proceso de login: $($_.Exception.Message)" -ForegroundColor Red
}

Write-Host ""
Write-Host "4. Probando diferentes usuarios..." -ForegroundColor Yellow

$testUsers = @(
    @{email="admin@dentaris.com"; password="password"; role="Administrador"},
    @{email="dentist@dentaris.com"; password="password"; role="Odontólogo"},
    @{email="reception@dentaris.com"; password="password"; role="Recepcionista"}
)

foreach ($user in $testUsers) {
    Write-Host "   Probando $($user.email) ($($user.role))..." -ForegroundColor Cyan
    
    $session = New-Object Microsoft.PowerShell.Commands.WebRequestSession
    
    try {
        # Obtener CSRF token
        $loginPage = Invoke-WebRequest -Uri "$baseUrl/" -WebSession $session -UseBasicParsing
        $csrfToken = ""
        if ($loginPage.Content -match 'name="_token" value="([^"]+)"') {
            $csrfToken = $matches[1]
        }
        
        # Login
        $loginData = @{
            email = $user.email
            password = $user.password
            _token = $csrfToken
        }
        
        $loginResponse = Invoke-WebRequest -Uri "$baseUrl/login" -Method POST -Body $loginData -WebSession $session -UseBasicParsing
        
        if ($loginResponse.StatusCode -eq 302) {
            Write-Host "   OK - Login exitoso para $($user.role)" -ForegroundColor Green
        } else {
            Write-Host "   ❌ Login falló para $($user.role)" -ForegroundColor Red
        }
        
    } catch {
        Write-Host "   ❌ Error para $($user.role): $($_.Exception.Message)" -ForegroundColor Red
    }
}

Write-Host ""
Write-Host "Pruebas completadas!" -ForegroundColor Green
Write-Host ""
Write-Host "📋 Resumen:" -ForegroundColor Yellow
Write-Host "• URL de Login: $baseUrl/" -ForegroundColor White
Write-Host "• URL de Dashboard: $baseUrl/dashboard" -ForegroundColor White
Write-Host "• Usuarios de prueba disponibles" -ForegroundColor White
Write-Host ""
Write-Host "El sistema esta listo para usar!" -ForegroundColor Green
