# API de Pacientes - Documentación

## Endpoints Disponibles

### GET /api/patients
Lista todos los pacientes con paginación y filtros.

**Parámetros de consulta:**
- `search`: Búsqueda por nombre, email, teléfono o código
- `gender`: Filtro por género (male, female, other)
- `age_min`, `age_max`: Rango de edad
- `has_allergies`: Filtro por pacientes con alergias (1/0)
- `consent_marketing`: Filtro por consentimiento de marketing (1/0)
- `created_from`, `created_to`: Rango de fechas de creación
- `per_page`: Número de registros por página (default: 15)
- `page`: Número de página (default: 1)

**Respuesta:**
```json
{
  "data": [
    {
      "id": 1,
      "patient_code": "JD00001",
      "first_name": "Juan",
      "last_name": "Pérez",
      "full_name": "Juan Pérez",
      "email": "juan@example.com",
      "phone": "555-1234",
      "birth_date": "1990-01-01",
      "age": 34,
      "gender": "male",
      "address": "Calle 123",
      "city": "Ciudad",
      "state": "Estado",
      "postal_code": "12345",
      "country": "México",
      "is_active": true,
      "created_at": "2024-01-01 10:00:00",
      "updated_at": "2024-01-01 10:00:00"
    }
  ],
  "current_page": 1,
  "last_page": 5,
  "per_page": 15,
  "total": 75
}
```

### GET /api/patients/{id}
Obtiene los detalles de un paciente específico.

**Respuesta:**
```json
{
  "data": {
    "id": 1,
    "patient_code": "JD00001",
    "first_name": "Juan",
    "last_name": "Pérez",
    "full_name": "Juan Pérez",
    "email": "juan@example.com",
    "phone": "555-1234",
    "phone_secondary": "555-5678",
    "birth_date": "1990-01-01",
    "age": 34,
    "gender": "male",
    "address": "Calle 123",
    "city": "Ciudad",
    "state": "Estado",
    "postal_code": "12345",
    "country": "México",
    "full_address": "Calle 123, Ciudad, Estado, 12345, México",
    "medical_history": "Sin antecedentes",
    "dental_history": "Limpieza regular",
    "allergies": "Ninguna",
    "medications": "Ninguno",
    "family_history": null,
    "social_history": null,
    "blood_type": "O+",
    "occupation": "Ingeniero",
    "marital_status": "single",
    "has_allergies": false,
    "emergency_contact_name": "María Pérez",
    "emergency_contact_phone": "555-9999",
    "emergency_contact_relationship": "spouse",
    "emergency_contact_address": "Calle 456",
    "emergency_contact_info": {
      "name": "María Pérez",
      "phone": "555-9999",
      "relationship": "spouse",
      "address": "Calle 456"
    },
    "notes": "Paciente nuevo",
    "preferences": ["Horario matutino", "Recordatorios por SMS"],
    "consent_marketing": false,
    "consent_data_processing": true,
    "is_active": true,
    "created_at": "2024-01-01 10:00:00",
    "updated_at": "2024-01-01 10:00:00"
  }
}
```

### POST /api/patients
Crea un nuevo paciente.

**Datos requeridos:**
```json
{
  "first_name": "Juan",
  "last_name": "Pérez",
  "birth_date": "1990-01-01",
  "gender": "male",
  "consent_data_processing": true
}
```

**Datos opcionales:**
```json
{
  "email": "juan@example.com",
  "phone": "555-1234",
  "phone_secondary": "555-5678",
  "address": "Calle 123",
  "city": "Ciudad",
  "state": "Estado",
  "postal_code": "12345",
  "country": "México",
  "medical_history": "Sin antecedentes",
  "dental_history": "Limpieza regular",
  "allergies": "Ninguna",
  "medications": "Ninguno",
  "family_history": "Sin antecedentes familiares",
  "social_history": "No fumador",
  "blood_type": "O+",
  "occupation": "Ingeniero",
  "marital_status": "single",
  "emergency_contact_name": "María Pérez",
  "emergency_contact_phone": "555-9999",
  "emergency_contact_relationship": "spouse",
  "emergency_contact_address": "Calle 456",
  "notes": "Paciente nuevo",
  "preferences": ["Horario matutino"],
  "consent_marketing": false,
  "is_active": true
}
```

**Respuesta exitosa (201):**
```json
{
  "data": {
    "id": 1,
    "patient_code": "JP00001",
    "first_name": "Juan",
    "last_name": "Pérez",
    // ... resto de datos del paciente
  }
}
```

### PUT /api/patients/{id}
Actualiza un paciente existente.

**Datos:** Mismos que en POST, todos opcionales excepto los campos de validación.

**Respuesta exitosa (200):**
```json
{
  "data": {
    "id": 1,
    "patient_code": "JP00001",
    "first_name": "Juan Carlos",
    "last_name": "Pérez García",
    // ... datos actualizados
  }
}
```

### DELETE /api/patients/{id}
Elimina un paciente.

**Respuesta exitosa (200):**
```json
{
  "message": "Paciente eliminado exitosamente."
}
```

## Códigos de Respuesta

- **200**: Operación exitosa
- **201**: Recurso creado exitosamente
- **400**: Solicitud incorrecta
- **401**: No autenticado
- **403**: No autorizado
- **404**: Recurso no encontrado
- **422**: Errores de validación
- **500**: Error interno del servidor

## Ejemplos de Uso

### Búsqueda de pacientes
```bash
curl -H "Authorization: Bearer {token}" \
  "https://api.dentaris.com/api/patients?search=Juan&gender=male"
```

### Filtro por edad
```bash
curl -H "Authorization: Bearer {token}" \
  "https://api.dentaris.com/api/patients?age_min=18&age_max=65"
```

### Crear paciente
```bash
curl -X POST \
  -H "Authorization: Bearer {token}" \
  -H "Content-Type: application/json" \
  -d '{
    "first_name": "Juan",
    "last_name": "Pérez",
    "birth_date": "1990-01-01",
    "gender": "male",
    "email": "juan@example.com",
    "consent_data_processing": true
  }' \
  "https://api.dentaris.com/api/patients"
```

## Validaciones

### Campos Obligatorios
- `first_name`: String, máximo 255 caracteres
- `last_name`: String, máximo 255 caracteres
- `birth_date`: Fecha válida, debe ser anterior a hoy
- `gender`: Enum (male, female, other)
- `consent_data_processing`: Boolean, debe ser true

### Validaciones Específicas
- `email`: Email válido, único en el sistema
- `phone`: String, máximo 20 caracteres
- `blood_type`: Enum (A+, A-, B+, B-, AB+, AB-, O+, O-)
- `marital_status`: Enum (single, married, divorced, widowed)
- `emergency_contact_relationship`: Enum (spouse, parent, child, sibling, friend, other)

### Errores de Validación (422)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "first_name": ["El nombre es obligatorio."],
    "email": ["Este email ya está registrado."],
    "birth_date": ["La fecha de nacimiento debe ser anterior a hoy."]
  }
}
```
