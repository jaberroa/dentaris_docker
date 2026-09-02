# Clasificación de acciones inexistentes y autorización

Fecha de revisión: 2026-09-01.

## Acciones de controlador inexistentes

| Dominio | Rutas afectadas | Acción | Clasificación | Decisión previa requerida |
|---|---|---|---|---|
| Facturación | `GET /billing/{invoice}/edit`, `PUT /billing/{invoice}`, `DELETE /billing/{invoice}` | `edit`, `update`, `destroy` | Contrato CRUD declarado pero no implementado | Confirmar si facturas son editables/eliminables y bajo qué reglas contables |
| Facturación | `GET /billing/{invoice}/pdf` | `downloadPdf` | Exportación declarada sin acción | Confirmar formato, almacenamiento y permisos del documento |
| Facturación | `POST /billing/{invoice}/send` | `sendInvoice` | Integración declarada sin acción | Confirmar canal, auditoría y reintentos |
| Inventario | `POST /inventory/{inventory}/adjust` | `adjust` | Mutación crítica declarada sin acción | Definir trazabilidad de ajuste y permiso específico |
| Inventario | `POST /inventory/transfer` | `transfer` | Mutación declarada sin acción | Definir origen, destino, atomicidad y auditoría |
| Inventario | `POST /inventory/export` | `export` | Exportación declarada sin acción | Confirmar formato, límites y protección de datos |
| Notificaciones | `POST /notifications/{notification}/mark-read` | `markAsRead` | Cambio de estado declarado sin acción | Alinear entidad `Notification` frente a `NotificationTemplate` |
| Notificaciones | `POST /notifications/mark-all-read` | `markAllAsRead` | Cambio masivo declarado sin acción | Definir alcance por usuario y auditoría |

El runtime reportó 10 referencias inexistentes porque las tres rutas CRUD de facturación y las dos acciones de exportación/envío completan diez referencias de acción según el detalle de `route:list`; las rutas duplicadas en el archivo fuente no se cuentan como URI duplicadas en runtime.

## Autorización

- 62 rutas no incluyen `Authenticate`; 14 corresponden a flujos públicos o auxiliares esperados.
- 12 rutas GET de dominios sensibles no muestran autenticación ni permiso explícito y requieren clasificación por intención de lectura.
- 36 rutas mutantes tienen autenticación, pero no `Authorize:*`/`can:*` explícito en la definición analizada.
- Las acciones de escritura de pacientes, citas, historias clínicas, planes dentales, notificaciones y perfil deben revisarse contra permisos existentes antes de añadir middleware.

## Orden seguro de saneamiento

1. Confirmar contrato funcional y entidad correcta de cada acción inexistente.
2. Definir autorización y auditoría antes de implementar cualquier acción.
3. Crear pruebas de contrato para respuestas, permisos y errores.
4. Implementar por dominio, comenzando por facturación e inventario, sin agregar rutas nuevas.

## Criterio de aceptación

No se considera cerrada una acción hasta que exista método resoluble, validación dedicada, autorización explícita, auditoría cuando corresponda, prueba y documentación de su efecto sobre el dominio.
