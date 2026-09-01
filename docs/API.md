# API Documentation - Dentaris

## Autenticación

### Login
```http
POST /api/v1/login
Content-Type: application/json

{
    "email": "admin@dentaris.com",
    "password": "password"
}
```

**Respuesta:**
```json
{
    "success": true,
    "message": "Login successful",
    "data": {
        "user": {
            "id": 1,
            "name": "Dr. Juan Pérez",
            "email": "admin@dentaris.com"
        },
        "token": "1|abc123..."
    }
}
```

### Logout
```http
POST /api/v1/logout
Authorization: Bearer {token}
```

## Dashboard

### KPIs
```http
GET /api/v1/dashboard/kpis
Authorization: Bearer {token}
```

**Parámetros opcionales:**
- `date_from` - Fecha inicio (Y-m-d)
- `date_to` - Fecha fin (Y-m-d)

### Alertas
```http
GET /api/v1/dashboard/alerts
Authorization: Bearer {token}
```

## Pacientes

### Listar Pacientes
```http
GET /api/v1/patients
Authorization: Bearer {token}
```

**Parámetros opcionales:**
- `search` - Búsqueda por nombre, código o email
- `gender` - Filtrar por género
- `status` - Filtrar por estado
- `per_page` - Elementos por página (default: 15)
- `sort_by` - Campo de ordenamiento
- `sort_order` - Orden (asc/desc)

### Crear Paciente
```http
POST /api/v1/patients
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "Juan",
    "last_name": "Pérez",
    "email": "juan@example.com",
    "phone": "+1234567890",
    "birth_date": "1990-01-01",
    "gender": "male",
    "address": "Calle Principal 123"
}
```

### Obtener Paciente
```http
GET /api/v1/patients/{id}
Authorization: Bearer {token}
```

### Actualizar Paciente
```http
PUT /api/v1/patients/{id}
Authorization: Bearer {token}
Content-Type: application/json

{
    "first_name": "Juan Carlos",
    "last_name": "Pérez García"
}
```

### Eliminar Paciente
```http
DELETE /api/v1/patients/{id}
Authorization: Bearer {token}
```

### Buscar Pacientes
```http
GET /api/v1/patients/search?q=Juan
Authorization: Bearer {token}
```

### Estadísticas de Pacientes
```http
GET /api/v1/patients/statistics
Authorization: Bearer {token}
```

## Citas

### Listar Citas
```http
GET /api/v1/appointments
Authorization: Bearer {token}
```

**Parámetros opcionales:**
- `date` - Filtrar por fecha
- `staff_id` - Filtrar por staff
- `patient_id` - Filtrar por paciente
- `status` - Filtrar por estado

### Crear Cita
```http
POST /api/v1/appointments
Authorization: Bearer {token}
Content-Type: application/json

{
    "patient_id": 1,
    "staff_id": 1,
    "appointment_date": "2025-09-25",
    "start_time": "10:00",
    "duration": 60,
    "type": "Consulta",
    "reason": "Revisión general"
}
```

### Confirmar Cita
```http
POST /api/v1/appointments/{id}/confirm
Authorization: Bearer {token}
```

### Cancelar Cita
```http
POST /api/v1/appointments/{id}/cancel
Authorization: Bearer {token}
Content-Type: application/json

{
    "cancellation_reason": "Paciente no disponible"
}
```

### Calendario
```http
GET /api/v1/appointments/calendar?date=2025-09-25&staff_id=1
Authorization: Bearer {token}
```

## Inventario

### Listar Inventario
```http
GET /api/v1/inventory
Authorization: Bearer {token}
```

**Parámetros opcionales:**
- `search` - Búsqueda por nombre o código
- `category` - Filtrar por categoría
- `stock_level` - Filtrar por nivel de stock (low/out)

### Stock Bajo
```http
GET /api/v1/inventory/low-stock
Authorization: Bearer {token}
```

### Stock Agotado
```http
GET /api/v1/inventory/out-of-stock
Authorization: Bearer {token}
```

### Estadísticas de Inventario
```http
GET /api/v1/inventory/statistics
Authorization: Bearer {token}
```

### Alertas de Inventario
```http
GET /api/v1/inventory/alerts
Authorization: Bearer {token}
```

## Reportes

### Reporte Financiero
```http
GET /api/v1/reports/financial?date_from=2025-09-01&date_to=2025-09-30
Authorization: Bearer {token}
```

### Reporte de Citas
```http
GET /api/v1/reports/appointments?date_from=2025-09-01&date_to=2025-09-30
Authorization: Bearer {token}
```

### Reporte de Pacientes
```http
GET /api/v1/reports/patients
Authorization: Bearer {token}
```

### Reporte de Inventario
```http
GET /api/v1/reports/inventory
Authorization: Bearer {token}
```

### KPIs
```http
GET /api/v1/reports/kpis?date_from=2025-09-01&date_to=2025-09-30
Authorization: Bearer {token}
```

## Configuración

### Estados de Citas
```http
GET /api/v1/config/appointments/statuses
Authorization: Bearer {token}
```

### Categorías de Inventario
```http
GET /api/v1/config/inventory/categories
Authorization: Bearer {token}
```

### Métodos de Pago
```http
GET /api/v1/config/payment-methods
Authorization: Bearer {token}
```

## Búsqueda Global

```http
GET /api/v1/search?q=Juan&type=all
Authorization: Bearer {token}
```

**Parámetros:**
- `q` - Término de búsqueda
- `type` - Tipo de búsqueda (all/patients/products)

## Respuestas de Error

### 400 Bad Request
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email field is required."]
    },
    "timestamp": "2025-09-23T06:15:00.000Z"
}
```

### 401 Unauthorized
```json
{
    "success": false,
    "message": "Unauthorized",
    "timestamp": "2025-09-23T06:15:00.000Z"
}
```

### 404 Not Found
```json
{
    "success": false,
    "message": "Resource not found",
    "timestamp": "2025-09-23T06:15:00.000Z"
}
```

### 422 Unprocessable Entity
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "email": ["The email has already been taken."]
    },
    "timestamp": "2025-09-23T06:15:00.000Z"
}
```

### 429 Too Many Requests
```json
{
    "success": false,
    "message": "Too many requests. Please try again later.",
    "retry_after": 60,
    "timestamp": "2025-09-23T06:15:00.000Z"
}
```

## Paginación

Las respuestas con listas incluyen información de paginación:

```json
{
    "success": true,
    "message": "Success",
    "data": [...],
    "pagination": {
        "current_page": 1,
        "last_page": 5,
        "per_page": 15,
        "total": 75,
        "from": 1,
        "to": 15
    },
    "timestamp": "2025-09-23T06:15:00.000Z"
}
```

## Rate Limiting

- **Límite:** 60 requests por minuto por IP
- **Headers de respuesta:**
  - `X-RateLimit-Limit` - Límite de requests
  - `X-RateLimit-Remaining` - Requests restantes
  - `Retry-After` - Segundos hasta el siguiente intento





