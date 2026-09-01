# Guía de Seguridad - Dentaris

## 🔒 Sistema de Seguridad Avanzado

### **Características de Seguridad Implementadas**

#### **1. Autenticación de Dos Factores (2FA)**
- ✅ **Google Authenticator** - Integración completa
- ✅ **Códigos de Respaldo** - 10 códigos de emergencia
- ✅ **QR Code Generation** - Configuración fácil
- ✅ **API Endpoints** - Para integración frontend
- ✅ **Auditoría 2FA** - Logs de habilitación/deshabilitación

```php
// Configuración 2FA
$user->google2fa_secret = Google2FA::generateSecretKey();
$user->google2fa_enabled = true;
$user->backup_codes = ['CODE1', 'CODE2', ...];
```

#### **2. Encriptación de Datos Sensibles**
- ✅ **AES-256-CBC** - Encriptación robusta
- ✅ **Campos Sensibles** - Email, teléfono, dirección, datos médicos
- ✅ **Middleware Automático** - Encriptación transparente
- ✅ **Configuración Flexible** - Campos personalizables

```php
// Campos encriptados automáticamente
$sensitiveFields = [
    'email', 'phone', 'address', 'medical_conditions',
    'allergies', 'medications', 'payment_method'
];
```

#### **3. Auditoría de Seguridad Completa**
- ✅ **Logs Detallados** - Todos los eventos de seguridad
- ✅ **Detección de Amenazas** - Actividades sospechosas
- ✅ **Análisis de Riesgo** - Niveles low/medium/high/critical
- ✅ **Estadísticas** - Dashboard de seguridad
- ✅ **Retención** - 7 años de logs

```php
// Eventos auditados
- successful_login, failed_login
- password_change, 2fa_enabled/disabled
- data_access, suspicious_activity
- system_access, admin_actions
```

#### **4. Protección CSRF Mejorada**
- ✅ **Validación Avanzada** - Tokens con expiración
- ✅ **Detección de Patrones** - Scripts maliciosos
- ✅ **Rate Limiting** - Prevención de ataques
- ✅ **Logging de Violaciones** - Auditoría completa

#### **5. Protección XSS**
- ✅ **Sanitización Automática** - Input cleaning
- ✅ **Detección de Patrones** - Scripts peligrosos
- ✅ **Strip Dangerous Tags** - Eliminación de HTML malicioso
- ✅ **Encoding Especial** - Caracteres seguros

#### **6. Headers de Seguridad**
- ✅ **X-Frame-Options** - Prevención clickjacking
- ✅ **X-Content-Type-Options** - Prevención MIME sniffing
- ✅ **X-XSS-Protection** - Protección XSS del navegador
- ✅ **Content Security Policy** - Política de contenido
- ✅ **Strict Transport Security** - HTTPS obligatorio

## 🛡️ Middleware de Seguridad

### **Middleware Implementados**

#### **1. EncryptSensitiveData**
```php
// Encripta automáticamente datos sensibles
Route::middleware(['encrypt.sensitive'])->group(function () {
    // Rutas protegidas
});
```

#### **2. EnhancedCsrfProtection**
```php
// Protección CSRF avanzada
Route::middleware(['csrf.enhanced'])->group(function () {
    // Rutas con CSRF mejorado
});
```

#### **3. XssProtection**
```php
// Protección contra XSS
Route::middleware(['xss.protection'])->group(function () {
    // Rutas con sanitización XSS
});
```

#### **4. SecurityHeaders**
```php
// Headers de seguridad automáticos
Route::middleware(['security.headers'])->group(function () {
    // Todas las rutas protegidas
});
```

#### **5. PerformanceMonitor**
```php
// Monitoreo de rendimiento y seguridad
Route::middleware(['performance.monitor'])->group(function () {
    // Rutas monitoreadas
});
```

## 🔐 Configuración de Seguridad

### **Variables de Entorno**
```env
# 2FA Configuration
2FA_ENABLED=true
2FA_REQUIRED_ADMIN=true
2FA_REQUIRED_STAFF=false
2FA_BACKUP_CODES_COUNT=10

# Login Security
MAX_LOGIN_ATTEMPTS=5
LOCKOUT_DURATION=15
PASSWORD_MIN_LENGTH=8
PASSWORD_REQUIRE_SPECIAL=true
PASSWORD_REQUIRE_NUMBERS=true
PASSWORD_REQUIRE_UPPERCASE=true

# Session Security
SESSION_TIMEOUT=120
SESSION_SECURE_COOKIES=true
SESSION_HTTP_ONLY=true
SESSION_SAME_SITE=strict

# Audit Logging
AUDIT_LOGGING_ENABLED=true
AUDIT_LOG_FAILED_LOGINS=true
AUDIT_LOG_SUCCESSFUL_LOGINS=true
AUDIT_LOG_PASSWORD_CHANGES=true
AUDIT_LOG_RETENTION_DAYS=365

# XSS Protection
XSS_PROTECTION_ENABLED=true
XSS_SANITIZE_INPUT=true
XSS_STRIP_DANGEROUS_TAGS=true
XSS_LOG_ATTEMPTS=true

# CSRF Protection
CSRF_PROTECTION_ENABLED=true
CSRF_TOKEN_LIFETIME=7200
CSRF_CHECK_REFERER=true
CSRF_LOG_VIOLATIONS=true

# Rate Limiting
RATE_LIMITING_ENABLED=true
RATE_LIMIT_LOGIN_ATTEMPTS=5
RATE_LIMIT_API_REQUESTS=60
RATE_LIMIT_PASSWORD_RESET=3

# Security Headers
SECURITY_X_FRAME_OPTIONS=DENY
SECURITY_X_CONTENT_TYPE_OPTIONS=nosniff
SECURITY_X_XSS_PROTECTION=1; mode=block
SECURITY_REFERRER_POLICY=strict-origin-when-cross-origin
SECURITY_CSP_ENABLED=true
SECURITY_HSTS_ENABLED=true

# Data Protection
ENCRYPT_SENSITIVE_FIELDS=true
ANONYMIZE_DATA_AFTER_DAYS=2555
RIGHT_TO_BE_FORGOTTEN=true
DATA_PORTABILITY=true

# Monitoring
SECURITY_MONITORING_ENABLED=true
ALERT_SUSPICIOUS_ACTIVITY=true
ALERT_FAILED_LOGINS=true
ALERT_ADMIN_ACTIONS=true
SECURITY_NOTIFICATION_EMAIL=admin@dentaris.com
```

## 📊 Monitoreo y Reportes

### **Comandos de Seguridad**

#### **1. Pruebas de Seguridad**
```bash
# Probar sistema de seguridad
php artisan security:test

# Probar con usuario específico
php artisan security:test --user=1
```

#### **2. Reportes de Seguridad**
```bash
# Reporte básico (30 días)
php artisan security:report

# Reporte personalizado
php artisan security:report --days=90 --format=html --output=security-report.html

# Formatos disponibles: html, json, txt
```

#### **3. Auditoría de Seguridad**
```bash
# Ver logs de seguridad
php artisan tinker
>>> SecurityAuditLog::latest()->take(10)->get();

# Estadísticas de seguridad
>>> SecurityAuditLog::getSecurityStats(30);
```

### **Dashboard de Seguridad**

#### **Métricas Disponibles**
- **Total de Eventos** - Todos los eventos de seguridad
- **Eventos Sospechosos** - Actividades marcadas como sospechosas
- **Eventos de Alto Riesgo** - Nivel high/critical
- **Logins Fallidos** - Intentos de acceso fallidos
- **IPs Únicas** - Direcciones IP diferentes
- **Score de Seguridad** - Puntuación 0-100

#### **Tipos de Eventos**
```php
// Eventos de autenticación
'successful_login'     // Login exitoso
'failed_login'        // Login fallido
'password_change'     // Cambio de contraseña
'2fa_enabled'         // 2FA habilitado
'2fa_disabled'        // 2FA deshabilitado

// Eventos de acceso
'data_access'         // Acceso a datos
'system_access'       // Acceso al sistema
'admin_actions'       // Acciones de admin

// Eventos de seguridad
'suspicious_activity' // Actividad sospechosa
'security_violation'  // Violación de seguridad
```

## 🚨 Detección de Amenazas

### **Actividades Sospechosas Detectadas**

#### **1. Múltiples Logins Fallidos**
- **Umbral**: 5 intentos en 15 minutos
- **Acción**: Marcar como sospechoso
- **Notificación**: Email al admin

#### **2. Login desde Nueva Ubicación**
- **Detección**: IP no vista en 30 días
- **Acción**: Marcar como sospechoso
- **Recomendación**: Verificar con usuario

#### **3. Patrones de Bot**
- **Detección**: User-Agent sospechoso
- **Acción**: Logging y análisis
- **Protección**: Rate limiting

#### **4. Inputs Maliciosos**
- **Detección**: Patrones XSS/CSRF
- **Acción**: Sanitización y logging
- **Protección**: Bloqueo automático

### **Niveles de Riesgo**

#### **Low (Verde)**
- Login exitoso
- Acceso normal a datos
- Cambios de configuración

#### **Medium (Amarillo)**
- Login fallido
- Cambio de contraseña
- Acceso a datos sensibles

#### **High (Naranja)**
- Múltiples logins fallidos
- Actividad sospechosa
- Acceso desde nueva IP

#### **Critical (Rojo)**
- Ataques detectados
- Violaciones de seguridad
- Acceso no autorizado

## 🔧 Configuración de Producción

### **Recomendaciones de Seguridad**

#### **1. Servidor Web**
```apache
# Apache .htaccess
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
</IfModule>

# Security Headers
Header always set X-Frame-Options "DENY"
Header always set X-Content-Type-Options "nosniff"
Header always set X-XSS-Protection "1; mode=block"
Header always set Referrer-Policy "strict-origin-when-cross-origin"
```

#### **2. Base de Datos**
```sql
-- Índices de seguridad
CREATE INDEX idx_security_audit_user_time ON security_audit_logs(user_id, event_time);
CREATE INDEX idx_security_audit_ip_time ON security_audit_logs(ip_address, event_time);
CREATE INDEX idx_security_audit_suspicious ON security_audit_logs(is_suspicious, event_time);

-- Limpieza automática (opcional)
DELETE FROM security_audit_logs WHERE event_time < DATE_SUB(NOW(), INTERVAL 7 YEAR);
```

#### **3. Backup Seguro**
```bash
# Backup encriptado
mysqldump -u user -p database | gpg --cipher-algo AES256 --compress-algo 1 --symmetric --output backup.sql.gpg

# Backup automático
0 2 * * * /path/to/backup-script.sh
```

## 📋 Checklist de Seguridad

### **Configuración Inicial**
- [ ] 2FA habilitado para administradores
- [ ] Contraseñas seguras configuradas
- [ ] Headers de seguridad activados
- [ ] Auditoría de seguridad habilitada
- [ ] Rate limiting configurado
- [ ] Encriptación de datos activada

### **Monitoreo Continuo**
- [ ] Revisar logs de seguridad diariamente
- [ ] Verificar eventos sospechosos
- [ ] Monitorear intentos de login fallidos
- [ ] Revisar reportes de seguridad semanalmente
- [ ] Actualizar configuraciones según amenazas

### **Mantenimiento**
- [ ] Rotar claves de encriptación anualmente
- [ ] Actualizar dependencias de seguridad
- [ ] Revisar y actualizar políticas de seguridad
- [ ] Capacitar usuarios en mejores prácticas
- [ ] Realizar auditorías de seguridad trimestrales

## 🆘 Respuesta a Incidentes

### **Procedimientos de Emergencia**

#### **1. Detección de Intrusión**
```bash
# Bloquear IP sospechosa
php artisan security:block-ip 192.168.1.100

# Revisar logs de la IP
php artisan security:check-ip 192.168.1.100

# Generar reporte de incidente
php artisan security:report --days=1 --format=html --output=incident-report.html
```

#### **2. Compromiso de Cuenta**
```bash
# Bloquear usuario
php artisan user:lock 1

# Forzar cambio de contraseña
php artisan user:force-password-reset 1

# Revocar sesiones
php artisan user:revoke-sessions 1
```

#### **3. Datos Comprometidos**
```bash
# Encriptar datos sensibles
php artisan security:encrypt-sensitive-data

# Generar reporte de impacto
php artisan security:data-breach-report

# Notificar a usuarios afectados
php artisan security:notify-affected-users
```

---

## 📞 Soporte de Seguridad

Para reportar vulnerabilidades o consultas de seguridad:

1. **Email**: security@dentaris.com
2. **Logs**: `storage/logs/security.log`
3. **Reportes**: `php artisan security:report`
4. **Monitoreo**: `php artisan security:test`

### **Recursos Adicionales**
- **Documentación**: `docs/SECURITY.md`
- **Configuración**: `config/security.php`
- **Comandos**: `php artisan list security`
- **Middleware**: `app/Http/Middleware/`
- **Servicios**: `app/Services/SecurityAuditService.php`





