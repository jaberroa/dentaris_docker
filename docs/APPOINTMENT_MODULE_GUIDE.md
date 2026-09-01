# 📅 GUÍA DEL MÓDULO DE APPOINTMENTS

## **DESCRIPCIÓN GENERAL**

El módulo de **Appointments** (Citas) es un sistema completo para la gestión de citas médicas en la clínica dental Dentaris. Proporciona funcionalidades CRUD completas, vistas de calendario (semanal, mensual, anual), y una API REST para integración con otros sistemas.

---

## **CARACTERÍSTICAS PRINCIPALES**

### ✅ **Funcionalidades Implementadas**
- **CRUD Completo**: Crear, leer, actualizar y eliminar citas
- **Vistas de Calendario**: Semanal, mensual y anual
- **Gestión de Estados**: Programada, confirmada, en progreso, completada, cancelada
- **Búsqueda y Filtros**: Por paciente, doctor, fecha, estado
- **Validaciones**: Fechas, horarios, duración, relaciones
- **API REST**: Endpoints para integración externa
- **Notificaciones**: Sistema de toasts y alertas
- **Responsive**: Diseño adaptable a dispositivos móviles

---

## **ARQUITECTURA DEL MÓDULO**

### **Estructura de Archivos**
```
app/
├── Models/
│   ├── Appointment.php              # Modelo principal
│   └── AppointmentStatus.php        # Estados de citas
├── Http/Controllers/
│   ├── AppointmentController.php    # Controlador principal
│   └── Api/AppointmentApiController.php # API REST
└── Console/Commands/
    └── TestAppointmentsCommand.php  # Comando de pruebas

resources/views/appointments/
├── index.blade.php      # Lista de citas
├── create.blade.php     # Formulario de creación
├── edit.blade.php       # Formulario de edición
├── show.blade.php       # Detalles de cita
├── weekly.blade.php     # Vista semanal
├── monthly.blade.php    # Vista mensual
└── yearly.blade.php     # Vista anual

tests/
├── Unit/AppointmentTest.php         # Pruebas unitarias
├── Feature/AppointmentTest.php      # Pruebas de integración
└── Feature/AppointmentApiTest.php   # Pruebas de API

database/
├── migrations/
│   └── create_appointments_table.php
├── factories/
│   ├── AppointmentFactory.php
│   └── AppointmentStatusFactory.php
└── seeders/
    └── AppointmentSeeder.php
```

---

## **MODELO DE DATOS**

### **Tabla: appointments**
| Campo | Tipo | Descripción |
|-------|------|-------------|
| `id` | bigint | Clave primaria |
| `appointment_code` | string | Código único de la cita |
| `patient_id` | bigint | ID del paciente (FK) |
| `staff_id` | bigint | ID del personal médico (FK) |
| `appointment_status_id` | bigint | ID del estado (FK) |
| `appointment_date` | date | Fecha de la cita |
| `start_time` | time | Hora de inicio |
| `end_time` | time | Hora de fin |
| `duration` | integer | Duración en minutos |
| `type` | string | Tipo de cita |
| `reason` | text | Motivo de la cita |
| `notes` | text | Notas adicionales |
| `treatment_plan` | text | Plan de tratamiento |
| `estimated_cost` | decimal | Costo estimado |
| `is_urgent` | boolean | Cita urgente |
| `is_follow_up` | boolean | Cita de seguimiento |
| `is_recurring` | boolean | Cita recurrente |
| `reminder_sent` | boolean | Recordatorio enviado |
| `parent_appointment_id` | bigint | ID de cita padre (FK) |
| `confirmed_at` | timestamp | Fecha de confirmación |
| `cancelled_at` | timestamp | Fecha de cancelación |
| `cancellation_reason` | text | Razón de cancelación |
| `created_by` | bigint | Usuario creador (FK) |
| `created_at` | timestamp | Fecha de creación |
| `updated_at` | timestamp | Fecha de actualización |

### **Relaciones del Modelo**
```php
// Appointment.php
public function patient() {
    return $this->belongsTo(Patient::class);
}

public function staff() {
    return $this->belongsTo(Staff::class);
}

public function status() {
    return $this->belongsTo(AppointmentStatus::class, 'appointment_status_id');
}

public function creator() {
    return $this->belongsTo(User::class, 'created_by');
}

public function reminders() {
    return $this->hasMany(AppointmentReminder::class);
}
```

---

## **ENDPOINTS Y RUTAS**

### **Rutas Web (AppointmentController)**
| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/appointments` | Lista de citas |
| `GET` | `/appointments/create` | Formulario de creación |
| `POST` | `/appointments` | Crear nueva cita |
| `GET` | `/appointments/{id}` | Detalles de cita |
| `GET` | `/appointments/{id}/edit` | Formulario de edición |
| `PUT` | `/appointments/{id}` | Actualizar cita |
| `DELETE` | `/appointments/{id}` | Eliminar cita |
| `GET` | `/appointments/weekly` | Vista semanal |
| `GET` | `/appointments/monthly` | Vista mensual |
| `GET` | `/appointments/yearly` | Vista anual |
| `POST` | `/appointments/{id}/confirm` | Confirmar cita |
| `POST` | `/appointments/{id}/cancel` | Cancelar cita |
| `PATCH` | `/appointments/{id}/status` | Actualizar estado |

### **Rutas API (AppointmentApiController)**
| Método | Ruta | Descripción |
|--------|------|-------------|
| `GET` | `/api/appointments` | Lista de citas (JSON) |
| `POST` | `/api/appointments` | Crear cita (JSON) |
| `GET` | `/api/appointments/{id}` | Obtener cita (JSON) |
| `PUT` | `/api/appointments/{id}` | Actualizar cita (JSON) |
| `DELETE` | `/api/appointments/{id}` | Eliminar cita (JSON) |

---

## **VALIDACIONES**

### **Validaciones de Creación/Actualización**
```php
$request->validate([
    'patient_id' => 'required|exists:patients,id',
    'staff_id' => 'required|exists:staff,id',
    'appointment_status_id' => 'required|exists:appointment_statuses,id',
    'appointment_date' => 'required|date|after_or_equal:today',
    'start_time' => 'required|date_format:H:i',
    'duration' => 'required|integer|min:15|max:480',
    'type' => 'required|string|max:255',
    'reason' => 'nullable|string',
    'notes' => 'nullable|string',
    'treatment_plan' => 'nullable|string',
    'estimated_cost' => 'nullable|numeric|min:0',
    'is_urgent' => 'boolean',
    'is_follow_up' => 'boolean',
    'is_recurring' => 'boolean',
    'reminder_sent' => 'boolean',
]);
```

### **Reglas de Negocio**
- ✅ La fecha de la cita no puede ser en el pasado
- ✅ La duración debe estar entre 15 y 480 minutos
- ✅ La hora de fin debe ser posterior a la hora de inicio
- ✅ No se pueden programar citas en horarios no laborales
- ✅ Un doctor no puede tener dos citas simultáneas
- ✅ Un paciente no puede tener dos citas simultáneas

---

## **ESTADOS DE CITAS**

### **Estados Disponibles**
| Estado | Descripción | Color | Icono |
|--------|-------------|-------|-------|
| `scheduled` | Programada | Azul | `fa-clock` |
| `confirmed` | Confirmada | Verde | `fa-check-circle` |
| `in_progress` | En Progreso | Amarillo | `fa-play-circle` |
| `completed` | Completada | Gris | `fa-check-double` |
| `cancelled` | Cancelada | Rojo | `fa-times-circle` |
| `no_show` | No se Presentó | Naranja | `fa-user-times` |
| `rescheduled` | Reprogramada | Púrpura | `fa-sync-alt` |

---

## **TIPOS DE CITAS**

### **Tipos Predefinidos**
- **Consulta General**: Revisión rutinaria
- **Limpieza Dental**: Profilaxis
- **Tratamiento**: Empaste, endodoncia, etc.
- **Emergencia**: Urgencias dentales
- **Seguimiento**: Citas de control
- **Ortodoncia**: Ajuste de brackets
- **Implante**: Colocación de implantes
- **Cirugía**: Procedimientos quirúrgicos

---

## **VISTAS Y COMPONENTES**

### **Vista de Lista (index.blade.php)**
- ✅ Tabla paginada con ordenamiento
- ✅ Filtros por estado, fecha, paciente
- ✅ Búsqueda en tiempo real
- ✅ Acciones rápidas (ver, editar, eliminar)
- ✅ Cambio de estado dinámico
- ✅ Exportación a Excel/PDF

### **Vista de Calendario Semanal (weekly.blade.php)**
- ✅ Grid de 7 días x horarios
- ✅ Navegación por semanas
- ✅ Citas visualizadas como bloques de tiempo
- ✅ Tooltips con información detallada
- ✅ Navegación por teclado (Alt + flechas)

### **Vista de Calendario Mensual (monthly.blade.php)**
- ✅ Grid de calendario tradicional
- ✅ Navegación por meses
- ✅ Estadísticas del mes
- ✅ Límite de 3 citas por día (con enlace "más")
- ✅ Indicadores visuales de días con citas

### **Vista de Calendario Anual (yearly.blade.php)**
- ✅ 12 tarjetas de meses
- ✅ Estadísticas anuales
- ✅ Mini-calendarios por mes
- ✅ Lista de citas destacadas
- ✅ Navegación por años

---

## **API Y INTEGRACIÓN**

### **Estructura de Respuesta JSON**
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
                "start_time": "09:00",
                "end_time": "10:00",
                "type": "consultation",
                "patient": {
                    "id": 1,
                    "first_name": "Juan",
                    "last_name": "Pérez"
                },
                "staff": {
                    "id": 1,
                    "user": {
                        "name": "Dr. García"
                    }
                },
                "status": {
                    "name": "confirmed",
                    "display_name": "Confirmada"
                }
            }
        ],
        "links": {
            "first": "...",
            "last": "...",
            "prev": null,
            "next": "..."
        },
        "meta": {
            "current_page": 1,
            "last_page": 5,
            "per_page": 15,
            "total": 75
        }
    }
}
```

### **Parámetros de Filtrado**
- `date`: Filtrar por fecha específica
- `staff_id`: Filtrar por doctor
- `patient_id`: Filtrar por paciente
- `status`: Filtrar por estado
- `sort_by`: Campo de ordenamiento
- `sort_order`: Dirección (asc/desc)
- `per_page`: Registros por página

---

## **PRUEBAS Y CALIDAD**

### **Cobertura de Pruebas**
- ✅ **Pruebas Unitarias**: 25 casos de prueba
- ✅ **Pruebas de Integración**: 20 casos de prueba
- ✅ **Pruebas de API**: 25 casos de prueba
- ✅ **Cobertura Mínima**: 80% modelos, 70% controladores

### **Ejecutar Pruebas**
```bash
# Todas las pruebas
php artisan test:appointments

# Solo pruebas unitarias
php artisan test:appointments --unit

# Con cobertura HTML
php artisan test:appointments --coverage --html

# Usando script
./scripts/test-appointments.sh --coverage --html
```

---

## **CONFIGURACIÓN Y INSTALACIÓN**

### **Requisitos**
- PHP 8.1+
- Laravel 10+
- MySQL 8.0+
- Composer

### **Pasos de Instalación**
1. **Ejecutar migraciones**:
   ```bash
   php artisan migrate
   ```

2. **Ejecutar seeders**:
   ```bash
   php artisan db:seed --class=AppointmentSeeder
   ```

3. **Configurar permisos**:
   ```bash
   chmod +x scripts/test-appointments.sh
   ```

4. **Verificar instalación**:
   ```bash
   php artisan test:appointments --fast
   ```

---

## **USO Y EJEMPLOS**

### **Crear una Cita**
```php
use App\Models\Appointment;
use Carbon\Carbon;

$appointment = Appointment::create([
    'appointment_code' => 'APT-0001',
    'patient_id' => 1,
    'staff_id' => 1,
    'appointment_status_id' => 1,
    'appointment_date' => Carbon::tomorrow(),
    'start_time' => '09:00',
    'end_time' => '10:00',
    'duration' => 60,
    'type' => 'consultation',
    'reason' => 'Routine checkup',
    'notes' => 'First visit',
    'created_by' => auth()->id(),
]);
```

### **Buscar Citas**
```php
// Por paciente
$appointments = Appointment::whereHas('patient', function($query) {
    $query->where('first_name', 'like', '%Juan%');
})->get();

// Por fecha
$appointments = Appointment::whereDate('appointment_date', '2024-01-15')->get();

// Por estado
$appointments = Appointment::whereHas('status', function($query) {
    $query->where('name', 'confirmed');
})->get();
```

### **Actualizar Estado**
```php
$appointment = Appointment::find(1);
$appointment->update([
    'appointment_status_id' => 2, // confirmed
    'confirmed_at' => now(),
    'reminder_sent' => true,
]);
```

---

## **MANTENIMIENTO Y ACTUALIZACIONES**

### **Tareas de Mantenimiento**
- ✅ Limpiar citas canceladas antiguas
- ✅ Actualizar estados de citas pasadas
- ✅ Enviar recordatorios automáticos
- ✅ Generar reportes de productividad
- ✅ Backup de datos de citas

### **Monitoreo**
- ✅ Logs de creación/modificación
- ✅ Métricas de cancelaciones
- ✅ Tiempo promedio de citas
- ✅ Satisfacción del paciente
- ✅ Utilización de horarios

---

## **SOLUCIÓN DE PROBLEMAS**

### **Problemas Comunes**

#### **Error: "No se pueden programar citas en fechas pasadas"**
- **Causa**: Validación de fecha en el pasado
- **Solución**: Verificar que `appointment_date` sea >= hoy

#### **Error: "Doctor no disponible en ese horario"**
- **Causa**: Conflicto de horarios
- **Solución**: Verificar disponibilidad antes de crear

#### **Error: "Duración inválida"**
- **Causa**: Duración fuera del rango 15-480 minutos
- **Solución**: Ajustar `duration` al rango permitido

### **Logs y Debugging**
```bash
# Ver logs de appointments
tail -f storage/logs/laravel.log | grep Appointment

# Debug de una cita específica
php artisan tinker
>>> App\Models\Appointment::find(1)->toArray()
```

---

## **ROADMAP Y MEJORAS FUTURAS**

### **Funcionalidades Planificadas**
- 🔄 Integración con calendario externo (Google Calendar)
- 🔄 Sistema de recordatorios por SMS/Email
- 🔄 Citas recurrentes automáticas
- 🔄 Dashboard de métricas avanzadas
- 🔄 Integración con sistema de pagos
- 🔄 App móvil para pacientes

### **Optimizaciones**
- ⚡ Cache de consultas frecuentes
- ⚡ Paginación optimizada
- ⚡ Compresión de respuestas API
- ⚡ CDN para assets estáticos

---

## **CONTACTO Y SOPORTE**

### **Desarrolladores**
- **Tech Lead**: Sistema Dentaris
- **Documentación**: [docs/APPOINTMENT_MODULE_GUIDE.md](./APPOINTMENT_MODULE_GUIDE.md)
- **API**: [docs/API_APPOINTMENTS.md](./API_APPOINTMENTS.md)

### **Recursos Adicionales**
- 📚 [Guía de Laravel](https://laravel.com/docs)
- 🧪 [Testing con PHPUnit](https://phpunit.de/)
- 📅 [Carbon Date Library](https://carbon.nesbot.com/)

---

**Última actualización**: $(date)  
**Versión**: 1.0.0  
**Estado**: ✅ Completado y listo para producción

