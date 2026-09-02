# Matriz de pruebas de autorización Dentaris

Fecha de revisión: 2026-09-01.

Esta matriz prepara la validación de la decisión de autorización. No agrega pruebas ejecutables todavía.

## Roles y capacidades

| Caso | Preparación | Resultado esperado |
|---|---|---|
| Superadministrador | Usuario con rol `super_admin` | Accede solo a permisos expresamente asignados por el contrato; no depende de bypass hardcodeado |
| Administrador | Usuario con rol `admin` | Accede a capacidades asignadas al rol; el comportamiento debe ser explícito y auditable |
| Inventario | Usuario con `manage_inventory` | Puede actualizar stock, pero ajuste/exportación deben respetar permisos específicos cuando se adopten |
| Ajustador | Usuario con `adjust_inventory` | Puede ajustar stock sin obtener automáticamente acceso a facturación o exportación |
| Exportador | Usuario con `export_inventory` | Puede exportar inventario dentro de límites, sin mutar existencias |
| Facturación | Usuario con `manage_billing` | Puede ejecutar operaciones financieras autorizadas, no operaciones de inventario |
| Lectura | Usuario con `view_billing` o `view_inventory` | Puede consultar el dominio correspondiente sin mutar datos |
| Sin permiso | Usuario autenticado sin capacidad requerida | Respuesta 403 y ningún cambio persistido |
| Invitado | Usuario no autenticado | Redirección o respuesta API de autenticación según superficie |
| Multirrol | Usuario con dos roles complementarios | Permisos efectivos son la unión controlada de capacidades, sin bypass inesperado |

## Operaciones críticas

Cada operación debe probarse con autorización positiva y negativa:

- editar, anular, descargar y enviar factura;
- ajustar, transferir y exportar inventario;
- consultar historias clínicas y documentos;
- marcar una notificación como leída y marcar todas como leídas.

## Propiedades de seguridad

1. Una denegación no ejecuta Service, transacción ni auditoría de éxito.
2. Un usuario sin permiso no puede inferir datos sensibles mediante mensajes de error.
3. El rol administrativo no concede capacidades fuera de la matriz aprobada.
4. Las rutas web y API aplican reglas equivalentes para la misma operación.
5. Cada operación crítica autorizada deja evidencia de auditoría con actor, recurso, resultado y timestamp.

## Orden de implementación de pruebas

1. Pruebas unitarias de resolución de permisos y roles.
2. Pruebas de middleware `can` y `permission`.
3. Pruebas de rutas sensibles con usuarios autorizados y no autorizados.
4. Pruebas de regresión de 2FA y autenticación.
5. Pruebas de auditoría y no mutación ante rechazo.

## Criterio de aceptación

No se crea una Policy ni se modifica una ruta crítica hasta que los casos de autorización positiva, negativa, invitado y multirrol estén implementados y pasen en WSL.
