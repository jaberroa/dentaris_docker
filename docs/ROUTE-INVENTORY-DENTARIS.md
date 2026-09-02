# Inventario detallado de rutas Dentaris

Fecha de revisión: 2026-09-01.

## Conteo verificable

| Archivo | Declaraciones `Route::` | Protección base |
|---|---:|---|
| `routes/web.php` | 215 | invitado para autenticación; `auth` para la aplicación |
| `routes/api.php` | 6 | `auth:sanctum` |

El conteo es estático sobre los archivos actuales y no sustituye la salida de `route:list`, que queda para la fase de verificación ejecutable.

## Catálogo por superficie

| Superficie | Recursos observados | Autorización explícita observada |
|---|---|---|
| Autenticación | login, registro, recuperación de contraseña, logout | `guest` y `auth` |
| Clínica | pacientes, citas, historias clínicas, planes dentales, tratamientos | permisos de gestión en operaciones administrativas; completar matriz por ruta |
| Administración | personal, usuarios, proveedores, inventario, productos | `manage_*` en varios grupos |
| Financiera | facturación, pagos, compras, cotizaciones | `manage_billing`, `manage_payments`, `manage_purchases`, `manage_quotes` |
| Laboratorio | trabajos de laboratorio | `manage_lab_works` |
| Reportes | financieros, citas, pacientes, inventario y KPIs | `view_reports` |
| Notificaciones | consulta, lectura, marcado y eliminación | pendiente de permiso específico |
| API | pacientes, citas, inventario, reportes y dashboard | `auth:sanctum`; permisos por endpoint pendientes |

## Permisos nombrados encontrados

`manage_inventory`, `manage_billing`, `view_reports`, `manage_lab_works`, `manage_quotes`, `manage_suppliers`, `manage_treatments`, `manage_payments`, `manage_purchases`, `view_products` y `manage_products`.

## Hallazgos y pendientes

1. `routes/web.php` contiene bloques administrativos repetidos después del fallback; debe verificarse si generan rutas duplicadas o inalcanzables.
2. La autorización está expresada parcialmente por middleware `can:*` y parcialmente en constructores de controladores; requiere un estándar único.
3. El API tiene autenticación global, pero no una matriz explícita de permisos por operación.
4. Hay un `Route::resource` para planes dentales junto con rutas adicionales; debe verificarse la correspondencia exacta de métodos y nombres.
5. La aceptación de esta fase exige ejecutar `route:list --json` en el entorno disponible, comparar URI/nombre/controlador/middleware y registrar duplicidades.

## Criterio de salida

La matriz queda aprobada cuando cada declaración mutante tiene controlador, middleware, permiso, validación, auditoría y prueba identificables, y no existen rutas duplicadas sin decisión documentada.
