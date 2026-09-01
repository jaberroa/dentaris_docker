# Guía de Uso - Módulo de Pacientes

## Funcionalidades Principales

### 1. Gestión de Pacientes
- **Crear paciente**: Formulario completo con validaciones
- **Editar paciente**: Modificación de información existente
- **Ver detalles**: Vista detallada con historial médico
- **Eliminar paciente**: Con validaciones de integridad

### 2. Búsqueda y Filtros
- Búsqueda por nombre, email, teléfono o código
- Filtros por género, edad, fecha de registro
- Filtros por alergias y consentimientos
- Ordenamiento por múltiples campos

### 3. Exportación
- **Excel**: Exportación completa con filtros aplicados
- **PDF**: Reporte formateado para impresión
- **Historial médico**: PDF del historial específico del paciente

### 4. Información Médica
- Historial médico y dental
- Alergias y medicamentos actuales
- Tipo de sangre y ocupación
- Contactos de emergencia
- Consentimientos de privacidad

## Validaciones Implementadas

### Campos Obligatorios
- Nombre y apellido
- Fecha de nacimiento (debe ser anterior a hoy)
- Género (male, female, other)

### Validaciones Específicas
- Email único en el sistema
- Tipo de sangre válido (A+, A-, B+, B-, AB+, AB-, O+, O-)
- Estado civil válido (single, married, divorced, widowed)
- Fecha de nacimiento válida (edad entre 0 y 120 años)

### Seguridad
- Protección CSRF en todos los formularios
- Validación de entrada contra inyecciones
- Auditoría de cambios (quién y cuándo)
- Middleware de autenticación obligatorio

## Estructura de Archivos

```
app/
├── Models/
│   └── Patient.php                 # Modelo principal
├── Http/
│   ├── Controllers/
│   │   ├── PatientController.php  # Controlador web
│   │   └── Api/
│   │       └── PatientApiController.php # Controlador API
│   ├── Requests/
│   │   └── PatientRequest.php      # Validaciones
│   └── Resources/
│       └── PatientResource.php    # Transformación API
├── Exports/
│   └── PatientsExport.php         # Exportación Excel
database/
├── factories/
│   └── PatientFactory.php         # Factory para pruebas
└── migrations/
    └── create_patients_table.php  # Migración de BD
resources/
└── views/
    └── patients/
        ├── index.blade.php        # Lista de pacientes
        ├── create.blade.php       # Formulario crear
        ├── edit.blade.php         # Formulario editar
        └── show.blade.php         # Vista detallada
tests/
├── Unit/
│   └── PatientTest.php            # Pruebas unitarias
└── Feature/
    ├── PatientTest.php            # Pruebas de integración
    └── PatientApiTest.php         # Pruebas de API
```

## Uso del Módulo

### Crear Paciente
1. Acceder a `/patients/create`
2. Llenar información personal obligatoria
3. Completar información médica (opcional)
4. Agregar contacto de emergencia (opcional)
5. Configurar consentimientos
6. Guardar

### Buscar Pacientes
1. Acceder a `/patients`
2. Usar el campo de búsqueda para buscar por:
   - Nombre completo
   - Email
   - Teléfono
   - Código de paciente
3. Aplicar filtros adicionales:
   - Género
   - Rango de edad
   - Fecha de registro
   - Alergias
   - Consentimientos

### Exportar Datos
1. Desde la lista de pacientes
2. Seleccionar formato (Excel o PDF)
3. Aplicar filtros si es necesario
4. Descargar archivo

### Ver Historial Médico
1. Acceder a `/patients/{id}`
2. Ver información completa del paciente
3. Revisar historial médico
4. Ver citas recientes
5. Exportar historial completo

## API Endpoints

### Autenticación
Todos los endpoints requieren autenticación via token Bearer.

### Endpoints Principales
- `GET /api/patients` - Listar pacientes
- `GET /api/patients/{id}` - Obtener paciente
- `POST /api/patients` - Crear paciente
- `PUT /api/patients/{id}` - Actualizar paciente
- `DELETE /api/patients/{id}` - Eliminar paciente

### Ejemplo de Uso API
```bash
# Obtener lista de pacientes
curl -H "Authorization: Bearer {token}" \
  "https://api.dentaris.com/api/patients"

# Crear paciente
curl -X POST \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Juan",
    "last_name": "Pérez",
    "birth_date": "1990-01-01",
    "gender": "male",
    "consent_data_processing": true
  }' \
  "https://api.dentaris.com/api/patients"
```

## Pruebas

### Ejecutar Pruebas
```bash
# Todas las pruebas del módulo
php artisan test --filter=Patient

# Pruebas unitarias
php artisan test tests/Unit/PatientTest.php

# Pruebas de integración
php artisan test tests/Feature/PatientTest.php

# Pruebas de API
php artisan test tests/Feature/PatientApiTest.php

# Con cobertura
php artisan test --filter=Patient --coverage
```

### Cobertura Mínima Requerida
- **Modelo Patient**: 80%
- **PatientController**: 70%
- **PatientRequest**: 90%

## Configuración

### Variables de Entorno
```env
# Base de datos
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dentaris
DB_USERNAME=root
DB_PASSWORD=

# Cache para optimización
CACHE_DRIVER=redis
SESSION_DRIVER=redis
```

### Configuración de Exportación
```php
// config/excel.php
'export' => [
    'patients' => [
        'max_rows' => 10000,
        'chunk_size' => 1000,
    ]
]
```

## Mantenimiento

### Limpieza de Datos
```bash
# Limpiar pacientes inactivos antiguos
php artisan patients:cleanup --days=365

# Optimizar base de datos
php artisan optimize
```

### Backup de Datos
```bash
# Backup de pacientes
php artisan backup:run --only-db
```

## Troubleshooting

### Problemas Comunes

1. **Error de validación de email**
   - Verificar que el email sea único
   - Revisar formato del email

2. **Error al exportar**
   - Verificar permisos de escritura
   - Revisar memoria disponible

3. **Error de autenticación API**
   - Verificar token Bearer
   - Revisar middleware de autenticación

### Logs
```bash
# Ver logs de pacientes
tail -f storage/logs/laravel.log | grep Patient
```

## Contribución

### Estándares de Código
- PSR-12 para PHP
- PSR-4 para autoloading
- Convenciones de Laravel

### Testing
- Escribir pruebas para nuevas funcionalidades
- Mantener cobertura mínima
- Documentar casos de prueba

### Documentación
- Actualizar README.md
- Documentar cambios en API
- Mantener ejemplos actualizados
