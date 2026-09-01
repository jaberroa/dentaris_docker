# 🔌 API DOCUMENTATION - APPOINTMENTS MODULE

## **DESCRIPCIÓN GENERAL**

La API del módulo de **Appointments** proporciona endpoints RESTful para la gestión completa de citas médicas. Todos los endpoints requieren autenticación y devuelven respuestas en formato JSON.

---

## **CONFIGURACIÓN BASE**

### **Base URL**
```
https://dentaris.com/api
```

### **Autenticación**
```http
Authorization: Bearer {token}
Content-Type: application/json
Accept: application/json
```

### **Headers Requeridos**
```http
Content-Type: application/json
Accept: application/json
X-Requested-With: XMLHttpRequest
```

---

## **ENDPOINTS DISPONIBLES**

### **1. OBTENER LISTA DE CITAS**

#### **GET** `/api/appointments`

Obtiene una lista paginada de citas con filtros opcionales.

**Parámetros de Query:**
| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `date` | string | Filtrar por fecha (Y-m-d) | `2024-01-15` |
| `staff_id` | integer | Filtrar por doctor | `1` |
| `patient_id` | integer | Filtrar por paciente | `2` |
| `status` | string | Filtrar por estado | `confirmed` |
| `sort_by` | string | Campo de ordenamiento | `appointment_date` |
| `sort_order` | string | Dirección (asc/desc) | `asc` |
| `per_page` | integer | Registros por página | `15` |
| `page` | integer | Número de página | `1` |

**Ejemplo de Request:**
```http
GET /api/appointments?date=2024-01-15&status=confirmed&per_page=10
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Ejemplo de Response:**
```json
{
    "success": true,
    "message": "Appointments retrieved successfully",
    "data": {
        "data": [
            {
                "id": 1,
                "appointment_code": "APT-0001",
                "appointment_date": "2024-01-15",
                "start_time": "09:00:00",
                "end_time": "10:00:00",
                "duration": 60,
                "type": "consultation",
                "reason": "Routine checkup",
                "notes": "First visit",
                "treatment_plan": null,
                "estimated_cost": "150.00",
                "is_urgent": false,
                "is_follow_up": false,
                "is_recurring": false,
                "reminder_sent": true,
                "confirmed_at": "2024-01-14T10:30:00.000000Z",
                "cancelled_at": null,
                "cancellation_reason": null,
                "created_at": "2024-01-10T08:00:00.000000Z",
                "updated_at": "2024-01-14T10:30:00.000000Z",
                "patient": {
                    "id": 1,
                    "first_name": "Juan",
                    "last_name": "Pérez",
                    "email": "juan.perez@email.com",
                    "phone": "+1234567890",
                    "display_code": "PA-0001"
                },
                "staff": {
                    "id": 1,
                    "user": {
                        "id": 1,
                        "name": "Dr. María García",
                        "email": "maria.garcia@dentaris.com"
                    },
                    "specialty": "General Dentistry",
                    "license_number": "DENT-12345"
                },
                "status": {
                    "id": 2,
                    "name": "confirmed",
                    "display_name": "Confirmada",
                    "color": "#28a745"
                },
                "creator": {
                    "id": 1,
                    "name": "Admin User",
                    "email": "admin@dentaris.com"
                }
            }
        ],
        "links": {
            "first": "https://dentaris.com/api/appointments?page=1",
            "last": "https://dentaris.com/api/appointments?page=5",
            "prev": null,
            "next": "https://dentaris.com/api/appointments?page=2"
        },
        "meta": {
            "current_page": 1,
            "from": 1,
            "last_page": 5,
            "path": "https://dentaris.com/api/appointments",
            "per_page": 15,
            "to": 15,
            "total": 75
        }
    }
}
```

---

### **2. CREAR NUEVA CITA**

#### **POST** `/api/appointments`

Crea una nueva cita médica.

**Request Body:**
```json
{
    "patient_id": 1,
    "staff_id": 1,
    "appointment_date": "2024-01-20",
    "start_time": "09:00",
    "duration": 60,
    "type": "consultation",
    "reason": "Routine dental checkup",
    "notes": "Patient has anxiety about dental procedures",
    "treatment_plan": "Complete examination and cleaning",
    "estimated_cost": 150.00,
    "is_urgent": false,
    "is_follow_up": false,
    "is_recurring": false
}
```

**Campos Requeridos:**
- `patient_id` (integer): ID del paciente
- `staff_id` (integer): ID del doctor
- `appointment_date` (date): Fecha de la cita (Y-m-d)
- `start_time` (time): Hora de inicio (H:i)
- `duration` (integer): Duración en minutos (15-480)
- `type` (string): Tipo de cita

**Campos Opcionales:**
- `reason` (string): Motivo de la cita
- `notes` (string): Notas adicionales
- `treatment_plan` (string): Plan de tratamiento
- `estimated_cost` (decimal): Costo estimado
- `is_urgent` (boolean): Cita urgente
- `is_follow_up` (boolean): Cita de seguimiento
- `is_recurring` (boolean): Cita recurrente

**Ejemplo de Response (201 Created):**
```json
{
    "success": true,
    "message": "Appointment created successfully",
    "data": {
        "id": 76,
        "appointment_code": "APT-0076",
        "appointment_date": "2024-01-20",
        "start_time": "09:00:00",
        "end_time": "10:00:00",
        "duration": 60,
        "type": "consultation",
        "reason": "Routine dental checkup",
        "notes": "Patient has anxiety about dental procedures",
        "treatment_plan": "Complete examination and cleaning",
        "estimated_cost": "150.00",
        "is_urgent": false,
        "is_follow_up": false,
        "is_recurring": false,
        "reminder_sent": false,
        "confirmed_at": null,
        "cancelled_at": null,
        "cancellation_reason": null,
        "created_at": "2024-01-15T10:30:00.000000Z",
        "updated_at": "2024-01-15T10:30:00.000000Z",
        "patient": {
            "id": 1,
            "first_name": "Juan",
            "last_name": "Pérez"
        },
        "staff": {
            "id": 1,
            "user": {
                "name": "Dr. María García"
            },
            "specialty": "General Dentistry"
        },
        "status": {
            "id": 1,
            "name": "scheduled",
            "display_name": "Programada"
        }
    }
}
```

**Errores de Validación (422 Unprocessable Entity):**
```json
{
    "success": false,
    "message": "Validation failed",
    "errors": {
        "patient_id": ["The patient id field is required."],
        "appointment_date": ["The appointment date must be a date after or equal to today."],
        "duration": ["The duration must be between 15 and 480."]
    }
}
```

---

### **3. OBTENER CITA ESPECÍFICA**

#### **GET** `/api/appointments/{id}`

Obtiene los detalles de una cita específica.

**Parámetros de Ruta:**
- `id` (integer): ID de la cita

**Ejemplo de Request:**
```http
GET /api/appointments/1
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Ejemplo de Response (200 OK):**
```json
{
    "success": true,
    "message": "Appointment retrieved successfully",
    "data": {
        "id": 1,
        "appointment_code": "APT-0001",
        "appointment_date": "2024-01-15",
        "start_time": "09:00:00",
        "end_time": "10:00:00",
        "duration": 60,
        "type": "consultation",
        "reason": "Routine checkup",
        "notes": "First visit",
        "treatment_plan": "Complete examination",
        "estimated_cost": "150.00",
        "is_urgent": false,
        "is_follow_up": false,
        "is_recurring": false,
        "reminder_sent": true,
        "confirmed_at": "2024-01-14T10:30:00.000000Z",
        "cancelled_at": null,
        "cancellation_reason": null,
        "created_at": "2024-01-10T08:00:00.000000Z",
        "updated_at": "2024-01-14T10:30:00.000000Z",
        "patient": {
            "id": 1,
            "first_name": "Juan",
            "last_name": "Pérez",
            "email": "juan.perez@email.com",
            "phone": "+1234567890"
        },
        "staff": {
            "id": 1,
            "user": {
                "name": "Dr. María García",
                "email": "maria.garcia@dentaris.com"
            },
            "specialty": "General Dentistry"
        },
        "status": {
            "id": 2,
            "name": "confirmed",
            "display_name": "Confirmada",
            "color": "#28a745"
        }
    }
}
```

**Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Appointment not found"
}
```

---

### **4. ACTUALIZAR CITA**

#### **PUT** `/api/appointments/{id}`

Actualiza una cita existente.

**Parámetros de Ruta:**
- `id` (integer): ID de la cita

**Request Body:**
```json
{
    "type": "treatment",
    "reason": "Updated reason for appointment",
    "notes": "Updated notes",
    "treatment_plan": "Updated treatment plan",
    "estimated_cost": 200.00,
    "is_urgent": true
}
```

**Ejemplo de Response (200 OK):**
```json
{
    "success": true,
    "message": "Appointment updated successfully",
    "data": {
        "id": 1,
        "appointment_code": "APT-0001",
        "type": "treatment",
        "reason": "Updated reason for appointment",
        "notes": "Updated notes",
        "treatment_plan": "Updated treatment plan",
        "estimated_cost": "200.00",
        "is_urgent": true,
        "updated_at": "2024-01-15T11:00:00.000000Z"
    }
}
```

---

### **5. ELIMINAR CITA**

#### **DELETE** `/api/appointments/{id}`

Elimina una cita específica.

**Parámetros de Ruta:**
- `id` (integer): ID de la cita

**Ejemplo de Request:**
```http
DELETE /api/appointments/1
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Ejemplo de Response (200 OK):**
```json
{
    "success": true,
    "message": "Appointment deleted successfully"
}
```

**Error (404 Not Found):**
```json
{
    "success": false,
    "message": "Appointment not found"
}
```

---

### **6. BUSCAR PERSONAL MÉDICO**

#### **GET** `/api/appointments/search-staff`

Busca personal médico disponible para citas.

**Parámetros de Query:**
| Parámetro | Tipo | Descripción | Ejemplo |
|-----------|------|-------------|---------|
| `search` | string | Término de búsqueda | `dr garcia` |
| `page` | integer | Número de página | `1` |
| `per_page` | integer | Registros por página | `10` |

**Ejemplo de Request:**
```http
GET /api/appointments/search-staff?search=dr garcia&per_page=5
Authorization: Bearer eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9...
```

**Ejemplo de Response:**
```json
{
    "success": true,
    "data": {
        "data": [
            {
                "id": 1,
                "display_name": "Dr. María García",
                "specialty": "General Dentistry",
                "license_number": "DENT-12345",
                "email": "maria.garcia@dentaris.com",
                "phone": "+1234567890",
                "user": {
                    "name": "Dr. María García",
                    "email": "maria.garcia@dentaris.com"
                }
            }
        ],
        "meta": {
            "current_page": 1,
            "last_page": 1,
            "per_page": 5,
            "total": 1
        }
    }
}
```

---

## **CÓDIGOS DE RESPUESTA HTTP**

| Código | Descripción | Cuándo se usa |
|--------|-------------|---------------|
| `200` | OK | Operación exitosa |
| `201` | Created | Recurso creado exitosamente |
| `400` | Bad Request | Solicitud malformada |
| `401` | Unauthorized | Token de autenticación inválido |
| `403` | Forbidden | Sin permisos para la operación |
| `404` | Not Found | Recurso no encontrado |
| `422` | Unprocessable Entity | Error de validación |
| `500` | Internal Server Error | Error interno del servidor |

---

## **ESTADOS DE CITAS**

### **Estados Disponibles**
| Estado | Descripción | ID |
|--------|-------------|-----|
| `scheduled` | Programada | 1 |
| `confirmed` | Confirmada | 2 |
| `in_progress` | En Progreso | 3 |
| `completed` | Completada | 4 |
| `cancelled` | Cancelada | 5 |
| `no_show` | No se Presentó | 6 |
| `rescheduled` | Reprogramada | 7 |

---

## **TIPOS DE CITAS**

### **Tipos Predefinidos**
- `consultation` - Consulta General
- `treatment` - Tratamiento
- `cleaning` - Limpieza Dental
- `emergency` - Emergencia
- `follow_up` - Seguimiento
- `orthodontics` - Ortodoncia
- `implant` - Implante
- `surgery` - Cirugía
- `whitening` - Blanqueamiento
- `extraction` - Extracción

---

## **EJEMPLOS DE USO**

### **JavaScript (Fetch API)**
```javascript
// Obtener lista de citas
async function getAppointments() {
    try {
        const response = await fetch('/api/appointments', {
            method: 'GET',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
        
        const data = await response.json();
        
        if (data.success) {
            console.log('Citas:', data.data.data);
        } else {
            console.error('Error:', data.message);
        }
    } catch (error) {
        console.error('Error de red:', error);
    }
}

// Crear nueva cita
async function createAppointment(appointmentData) {
    try {
        const response = await fetch('/api/appointments', {
            method: 'POST',
            headers: {
                'Authorization': 'Bearer ' + token,
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify(appointmentData)
        });
        
        const data = await response.json();
        
        if (response.ok && data.success) {
            console.log('Cita creada:', data.data);
            return data.data;
        } else {
            console.error('Error:', data.message, data.errors);
            throw new Error(data.message);
        }
    } catch (error) {
        console.error('Error:', error);
        throw error;
    }
}
```

### **PHP (cURL)**
```php
<?php
// Obtener lista de citas
function getAppointments($token) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://dentaris.com/api/appointments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $data = json_decode($response, true);
        return $data['data']['data'];
    } else {
        throw new Exception('Error al obtener citas: ' . $response);
    }
}

// Crear nueva cita
function createAppointment($token, $appointmentData) {
    $ch = curl_init();
    
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://dentaris.com/api/appointments',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($appointmentData),
        CURLOPT_HTTPHEADER => [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Accept: application/json'
        ]
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 201) {
        $data = json_decode($response, true);
        return $data['data'];
    } else {
        $errorData = json_decode($response, true);
        throw new Exception('Error al crear cita: ' . $errorData['message']);
    }
}
?>
```

### **Python (Requests)**
```python
import requests
import json

# Obtener lista de citas
def get_appointments(token):
    headers = {
        'Authorization': f'Bearer {token}',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    
    response = requests.get(
        'https://dentaris.com/api/appointments',
        headers=headers
    )
    
    if response.status_code == 200:
        data = response.json()
        return data['data']['data']
    else:
        raise Exception(f'Error al obtener citas: {response.text}')

# Crear nueva cita
def create_appointment(token, appointment_data):
    headers = {
        'Authorization': f'Bearer {token}',
        'Content-Type': 'application/json',
        'Accept': 'application/json'
    }
    
    response = requests.post(
        'https://dentaris.com/api/appointments',
        headers=headers,
        data=json.dumps(appointment_data)
    )
    
    if response.status_code == 201:
        data = response.json()
        return data['data']
    else:
        error_data = response.json()
        raise Exception(f'Error al crear cita: {error_data["message"]}')
```

---

## **RATE LIMITING**

### **Límites de Uso**
- **Límite General**: 1000 requests por hora por usuario
- **Límite de Creación**: 100 citas por hora por usuario
- **Límite de Búsqueda**: 500 requests por hora por usuario

### **Headers de Rate Limiting**
```http
X-RateLimit-Limit: 1000
X-RateLimit-Remaining: 999
X-RateLimit-Reset: 1640995200
```

---

## **MANEJO DE ERRORES**

### **Estructura de Error**
```json
{
    "success": false,
    "message": "Descripción del error",
    "errors": {
        "campo": ["Mensaje de error específico"]
    },
    "code": "ERROR_CODE",
    "timestamp": "2024-01-15T10:30:00Z"
}
```

### **Códigos de Error Comunes**
- `VALIDATION_ERROR`: Error de validación de datos
- `NOT_FOUND`: Recurso no encontrado
- `UNAUTHORIZED`: No autenticado
- `FORBIDDEN`: Sin permisos
- `CONFLICT`: Conflicto de datos (ej: horario ocupado)
- `SERVER_ERROR`: Error interno del servidor

---

## **VERSIONADO**

### **Versión Actual**
- **API Version**: v1
- **Laravel Version**: 10.x
- **PHP Version**: 8.1+

### **Compatibilidad**
- ✅ Backward compatible con versiones anteriores
- ✅ Nuevas funcionalidades en versiones menores
- ✅ Breaking changes solo en versiones mayores

---

## **CHANGELOG**

### **v1.0.0** (2024-01-15)
- ✅ CRUD completo de citas
- ✅ Filtros y búsqueda
- ✅ Paginación
- ✅ Validaciones robustas
- ✅ Manejo de errores
- ✅ Documentación completa

---

## **SOPORTE Y CONTACTO**

### **Recursos de Ayuda**
- 📚 [Guía del Módulo](./APPOINTMENT_MODULE_GUIDE.md)
- 🧪 [Documentación de Pruebas](./TESTING.md)
- 🔧 [Configuración del Sistema](./CONFIGURATION.md)

### **Contacto Técnico**
- **Email**: dev@dentaris.com
- **Documentación**: [docs/API_APPOINTMENTS.md](./API_APPOINTMENTS.md)
- **Issues**: GitHub Issues del proyecto

---

**Última actualización**: 2024-01-15  
**Versión**: 1.0.0  
**Estado**: ✅ Completado y listo para producción

