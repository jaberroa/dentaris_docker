# Decisión de alcance para acciones de facturación

Fecha de revisión: 2026-09-01.

## Evidencia

- `BillingController` implementa consulta, creación, detalle, pagos, marcado como pagada y reporte.
- No implementa `edit`, `update`, `destroy`, `downloadPdf` ni `sendInvoice`, aunque las rutas los declaran.
- `resources/views/billing/index.blade.php` enlaza a `billing.show` y `billing.pdf`; no existe vista de edición ni plantilla PDF específica en `resources/views/billing/`.
- `Invoice` contiene estados, totales, saldo, pagos y relaciones financieras.
- No existe un servicio de envío de facturas identificado en el dominio de facturación.

## Decisión

Las cinco capacidades quedan clasificadas como pendientes de implementación controlada. No se crearán stubs, respuestas simuladas ni eliminación de rutas en esta fase, porque cualquiera de esas acciones cambiaría el contrato visible sin haber definido reglas contables y de comunicación.

| Acción | Estado | Condición para implementar |
|---|---|---|
| Editar/actualizar | Pendiente | Reglas para facturas draft, sent, paid y con pagos |
| Anular/eliminar | Pendiente | Política de conservación del historial financiero |
| Descargar PDF | Pendiente | Plantilla, almacenamiento privado y permiso de lectura |
| Enviar factura | Pendiente | Canal, idempotencia, errores, reintentos y auditoría |

## Bloqueador de UI

El enlace `billing.pdf` apunta a una acción inexistente. Antes de habilitar la pantalla para usuarios finales debe elegirse una de estas alternativas, mediante cambio aprobado separado:

1. Implementar el contrato PDF completo.
2. Ocultar temporalmente el enlace y documentar la capacidad como no disponible.

No se aplicará ninguna alternativa automáticamente.

## Criterio de aceptación

Cada acción solo podrá pasar a implementación cuando tenga contrato aprobado, Form Request si recibe entrada, Service, autorización por Policy, auditoría, prueba y verificación runtime de la ruta.
