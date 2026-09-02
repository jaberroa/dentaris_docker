# Inventario verificable de rutas y seguridad Dentaris

Fecha de revisión: 2026-09-01.

## Rutas

| Superficie | Ubicación | Evidencia | Riesgo o pendiente |
|---|---|---|---|
| Web autenticada | `routes/web.php` | Grupo `auth` con dashboard y módulos clínicos/administrativos | Debe consolidarse el inventario de nombres, métodos y permisos |
| API | `routes/api.php` | Grupo `auth:sanctum` con operaciones de pacientes | Debe completarse la matriz endpoint-permiso-prueba |
| Reportes | `routes/web.php` y `app/Http/Controllers/Api/ReportApiController.php` | Existen superficie web y API | Debe verificarse que no diverjan reglas de negocio |
| Recursos | `routes/web.php` | `Route::resource('dental-plans', ...)` | Validar autorización por operación y políticas de recurso |
| Fallback | `routes/web.php` | Hay un fallback y bloques administrativos repetidos en el mismo archivo | Revisar duplicidad y precedencia de rutas |

## Autorización observada

- Las rutas usan capacidades nombradas como `manage_inventory`, `manage_billing`, `view_reports`, `manage_lab_works`, `manage_quotes`, `manage_suppliers`, `manage_treatments`, `manage_payments` y `manage_purchases`.
- `ProductController` declara `can:view_products` y `can:manage_products` en constructor.
- Existen `CheckRole` y `CheckPermission` en middleware, además de configuración de permisos.
- No existe `app/Policies/`; la autorización por modelo debe verificarse y normalizarse antes de ampliar funcionalidades.

## Validación y auditoría

- Solo cuatro Form Requests están presentes: autenticación y pacientes.
- Se observan numerosas llamadas `$request->validate(...)` y `Validator::make(...)` dentro de controladores, por lo que la extracción a Form Requests es una brecha prioritaria.
- `SecurityAuditService`, `SecurityAuditLog`, middleware de seguridad y `TwoFactorAuthController` están presentes.
- La auditoría se implementa mediante varios mecanismos (`activity()` y `SecurityAuditService`); debe definirse un contrato único y comprobar cobertura de acciones críticas.

## Criterios de aceptación para el saneamiento

1. Cada ruta mutante tiene autorización explícita, validación dedicada y auditoría definida.
2. No existen bloques duplicados o rutas ambiguas para la misma operación.
3. Cada endpoint API tiene contrato, permiso, respuesta esperada y prueba.
4. Las Policies faltantes se incorporan solo después de inventariar los modelos y reglas existentes.
5. La verificación se realiza sobre el código actual y queda registrada con rutas y líneas concretas.
