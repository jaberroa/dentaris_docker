# Guía de CI/CD - Dentaris

## 🚀 Sistema de CI/CD Implementado

### **Workflows de GitHub Actions**

#### **1. Tests Automáticos (tests.yml)**
- ✅ **PHP 8.2 y 8.3** - Soporte múltiple
- ✅ **MySQL 8.0** - Base de datos de testing
- ✅ **Tests de Seguridad** - Suite completa
- ✅ **Tests Unitarios** - Cobertura de código
- ✅ **Tests de Features** - Funcionalidades
- ✅ **Auditoría de Seguridad** - Composer audit
- ✅ **Code Style** - PSR-12 compliance
- ✅ **Code Coverage** - Reportes de cobertura

#### **2. Security Scanning (security.yml)**
- ✅ **Security Audit** - Composer audit
- ✅ **Security Tests** - Suite de seguridad
- ✅ **Security Report** - Reportes JSON
- ✅ **Secrets Detection** - TruffleHog
- ✅ **CodeQL Analysis** - Análisis de código
- ✅ **Dependency Review** - Revisión de dependencias
- ✅ **Security Headers** - Verificación de headers
- ✅ **Vulnerability Scan** - Trivy scanner

#### **3. Deployment (deploy.yml)**
- ✅ **Production Deployment** - Deploy automático
- ✅ **Security Tests** - Pre-deployment
- ✅ **Docker Build** - Imágenes optimizadas
- ✅ **Security Scan** - Escaneo de imágenes
- ✅ **Health Checks** - Verificación post-deploy
- ✅ **Notifications** - Alertas de deployment
- ✅ **Rollback** - Rollback automático

#### **4. Monitoring (monitoring.yml)**
- ✅ **Health Checks** - Cada 5 minutos
- ✅ **Performance Monitoring** - Métricas de rendimiento
- ✅ **Security Monitoring** - Monitoreo de seguridad
- ✅ **Database Checks** - Estado de BD
- ✅ **Cache Checks** - Estado de caché
- ✅ **Log Analysis** - Análisis de logs
- ✅ **Alerting** - Notificaciones automáticas

### **Configuración de Docker**

#### **1. Dockerfile Multi-stage**
- ✅ **Base Stage** - Configuración base
- ✅ **Development Stage** - Para desarrollo
- ✅ **Production Stage** - Para producción
- ✅ **Security Hardening** - Hardening de seguridad
- ✅ **User Permissions** - Permisos de usuario
- ✅ **Health Checks** - Verificaciones de salud

#### **2. Docker Compose**
- ✅ **Production** - docker-compose.yml
- ✅ **Development** - docker-compose.dev.yml
- ✅ **Services** - App, MySQL, Redis, Nginx
- ✅ **Monitoring** - Prometheus, Grafana
- ✅ **Volumes** - Persistencia de datos
- ✅ **Networks** - Redes aisladas

#### **3. Configuración de Servicios**
- ✅ **Nginx** - Proxy reverso con seguridad
- ✅ **PHP-FPM** - Procesamiento PHP
- ✅ **MySQL** - Base de datos
- ✅ **Redis** - Caché y sesiones
- ✅ **Supervisor** - Gestión de procesos
- ✅ **Prometheus** - Métricas
- ✅ **Grafana** - Dashboards

### **Scripts de Deployment**

#### **1. Deploy Script (Linux/Mac)**
- ✅ **Prerequisites Check** - Verificación de requisitos
- ✅ **Backup Creation** - Respaldo automático
- ✅ **Code Pull** - Actualización de código
- ✅ **Docker Build** - Construcción de imagen
- ✅ **Security Tests** - Tests de seguridad
- ✅ **Deployment** - Despliegue automático
- ✅ **Migrations** - Migraciones de BD
- ✅ **Cache Clear** - Limpieza de caché
- ✅ **Health Check** - Verificación de salud
- ✅ **Cleanup** - Limpieza de recursos
- ✅ **Notifications** - Notificaciones

#### **2. Health Check Script (Windows)**
- ✅ **Application Health** - Estado de aplicación
- ✅ **API Health** - Estado de APIs
- ✅ **Database Connection** - Conexión a BD
- ✅ **Redis Connection** - Conexión a Redis
- ✅ **Security Headers** - Headers de seguridad
- ✅ **Response Time** - Tiempo de respuesta
- ✅ **SSL Certificate** - Certificado SSL
- ✅ **Log Analysis** - Análisis de logs

### **Monitoreo y Alertas**

#### **1. Métricas de Aplicación**
- ✅ **Response Time** - Tiempo de respuesta
- ✅ **Memory Usage** - Uso de memoria
- ✅ **CPU Usage** - Uso de CPU
- ✅ **Disk Usage** - Uso de disco
- ✅ **Database Performance** - Rendimiento de BD
- ✅ **Cache Hit Rate** - Tasa de aciertos

#### **2. Métricas de Seguridad**
- ✅ **Failed Logins** - Intentos fallidos
- ✅ **Suspicious Activity** - Actividad sospechosa
- ✅ **Security Events** - Eventos de seguridad
- ✅ **Vulnerability Scans** - Escaneos de vulnerabilidades
- ✅ **Access Patterns** - Patrones de acceso

#### **3. Alertas Automáticas**
- ✅ **Health Check Failures** - Fallos de salud
- ✅ **Performance Issues** - Problemas de rendimiento
- ✅ **Security Incidents** - Incidentes de seguridad
- ✅ **Deployment Status** - Estado de deployment
- ✅ **Resource Usage** - Uso de recursos

### **Configuración de Entorno**

#### **1. Variables de Entorno**
```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://dentaris.com

# Database
DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=dentaris
DB_USERNAME=dentaris
DB_PASSWORD=dentaris_password

# Redis
REDIS_HOST=redis
REDIS_PORT=6379
REDIS_PASSWORD=redis_password

# Security
SECURITY_TESTING_ENABLED=true
AUDIT_LOGGING_ENABLED=true
XSS_PROTECTION_ENABLED=true
CSRF_PROTECTION_ENABLED=true

# Monitoring
PROMETHEUS_ENABLED=true
GRAFANA_ENABLED=true
HEALTH_CHECK_ENABLED=true

# Notifications
SLACK_WEBHOOK_URL=https://hooks.slack.com/services/...
EMAIL_RECIPIENTS=admin@dentaris.com
```

#### **2. Configuración de Nginx**
- ✅ **Security Headers** - Headers de seguridad
- ✅ **Rate Limiting** - Limitación de velocidad
- ✅ **Gzip Compression** - Compresión Gzip
- ✅ **SSL/TLS** - Configuración SSL
- ✅ **Caching** - Configuración de caché
- ✅ **Access Control** - Control de acceso

#### **3. Configuración de PHP**
- ✅ **Security Settings** - Configuración de seguridad
- ✅ **Performance Settings** - Configuración de rendimiento
- ✅ **Session Settings** - Configuración de sesiones
- ✅ **OPcache** - Configuración de OPcache
- ✅ **Error Handling** - Manejo de errores

### **Comandos de CI/CD**

#### **1. Desarrollo Local**
```bash
# Desarrollo con Docker
docker-compose -f docker-compose.dev.yml up -d

# Acceso a servicios
# App: http://localhost:8000
# phpMyAdmin: http://localhost:8080
# MailHog: http://localhost:8025
```

#### **2. Testing**
```bash
# Tests unitarios
php artisan test tests/Unit/

# Tests de seguridad
php artisan security:test-suite

# Tests de features
php artisan test tests/Feature/

# Cobertura de código
php artisan test --coverage
```

#### **3. Deployment**
```bash
# Deploy automático
./scripts/deploy.sh deploy

# Rollback
./scripts/deploy.sh rollback

# Health check
./scripts/deploy.sh health

# Backup
./scripts/deploy.sh backup
```

#### **4. Monitoreo**
```bash
# Health check (Windows)
.\scripts\health-check.ps1

# Health check con URL personalizada
.\scripts\health-check.ps1 -BaseUrl "https://dentaris.com"

# Health check con timeout
.\scripts\health-check.ps1 -Timeout 60
```

### **Configuración de GitHub Actions**

#### **1. Secrets Requeridos**
```
SLACK_WEBHOOK_URL
EMAIL_RECIPIENTS
MYSQL_ROOT_PASSWORD
REDIS_PASSWORD
```

#### **2. Environments**
- ✅ **Production** - Ambiente de producción
- ✅ **Staging** - Ambiente de staging
- ✅ **Development** - Ambiente de desarrollo

#### **3. Branch Protection**
- ✅ **Main Branch** - Protección de rama principal
- ✅ **Required Reviews** - Revisiones requeridas
- ✅ **Status Checks** - Verificaciones de estado
- ✅ **Dismiss Stale Reviews** - Descartar revisiones obsoletas

### **Dashboard de Monitoreo**

#### **1. Grafana Dashboards**
- ✅ **Application Metrics** - Métricas de aplicación
- ✅ **Database Metrics** - Métricas de base de datos
- ✅ **Security Metrics** - Métricas de seguridad
- ✅ **Performance Metrics** - Métricas de rendimiento
- ✅ **Error Tracking** - Seguimiento de errores

#### **2. Prometheus Metrics**
- ✅ **HTTP Requests** - Solicitudes HTTP
- ✅ **Database Queries** - Consultas de BD
- ✅ **Cache Operations** - Operaciones de caché
- ✅ **Queue Jobs** - Trabajos en cola
- ✅ **Security Events** - Eventos de seguridad

### **Troubleshooting**

#### **1. Problemas Comunes**
- **Docker Build Failures** - Verificar Dockerfile
- **Database Connection Issues** - Verificar configuración
- **Permission Issues** - Verificar permisos de archivos
- **Memory Issues** - Aumentar límites de memoria
- **Network Issues** - Verificar configuración de red

#### **2. Soluciones**
```bash
# Limpiar Docker
docker system prune -a

# Recrear contenedores
docker-compose down -v
docker-compose up -d

# Verificar logs
docker-compose logs app
docker-compose logs mysql
docker-compose logs redis

# Verificar estado
docker-compose ps
```

### **Mejores Prácticas**

#### **1. Seguridad**
- ✅ **Secrets Management** - Gestión de secretos
- ✅ **Access Control** - Control de acceso
- ✅ **Audit Logging** - Registro de auditoría
- ✅ **Vulnerability Scanning** - Escaneo de vulnerabilidades
- ✅ **Security Headers** - Headers de seguridad

#### **2. Performance**
- ✅ **Caching** - Implementación de caché
- ✅ **Database Optimization** - Optimización de BD
- ✅ **Image Optimization** - Optimización de imágenes
- ✅ **CDN Usage** - Uso de CDN
- ✅ **Load Balancing** - Balanceo de carga

#### **3. Monitoring**
- ✅ **Health Checks** - Verificaciones de salud
- ✅ **Alerting** - Sistema de alertas
- ✅ **Logging** - Sistema de logs
- ✅ **Metrics** - Métricas de aplicación
- ✅ **Dashboards** - Dashboards de monitoreo

---

## 📊 Resumen de CI/CD

### **✅ Workflows Implementados:**
- **4 Workflows** - Tests, Security, Deploy, Monitoring
- **8 Jobs** - Cobertura completa
- **20+ Steps** - Procesos automatizados
- **5 Services** - MySQL, Redis, Nginx, Prometheus, Grafana

### **✅ Docker Configuration:**
- **Multi-stage Build** - Optimización de imágenes
- **Security Hardening** - Hardening de seguridad
- **Health Checks** - Verificaciones de salud
- **Production Ready** - Listo para producción

### **✅ Monitoring & Alerting:**
- **Health Checks** - Cada 5 minutos
- **Performance Monitoring** - Métricas en tiempo real
- **Security Monitoring** - Monitoreo de seguridad
- **Automated Alerting** - Alertas automáticas

### **✅ Deployment Automation:**
- **Automated Deployment** - Deploy automático
- **Rollback Capability** - Capacidad de rollback
- **Health Verification** - Verificación de salud
- **Notification System** - Sistema de notificaciones

---

## 🚀 Próximos Pasos

### **CI/CD Avanzado**
- [ ] Kubernetes Deployment
- [ ] Blue-Green Deployment
- [ ] Canary Releases
- [ ] A/B Testing

### **Monitoreo Avanzado**
- [ ] APM Integration
- [ ] Log Aggregation
- [ ] Distributed Tracing
- [ ] Real-time Dashboards

### **Seguridad Avanzada**
- [ ] SAST/DAST Integration
- [ ] Container Security
- [ ] Runtime Protection
- [ ] Compliance Monitoring





