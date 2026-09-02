# Contratos de validación, autorización, auditoría y pruebas

Fecha de revisión: 2026-09-01.

Este documento define el contrato mínimo para implementar posteriormente las acciones faltantes de facturación e inventario. No modifica el comportamiento actual.

## Facturación

| Acción | Validación mínima | Autorización | Auditoría | Prueba requerida |
|---|---|---|---|---|
| Editar/actualizar | Factura editable; fechas, importes, impuestos y partidas válidos | `manage_billing` + Policy de factura | Antes/después, actor y motivo | éxito, factura pagada, totales y no autorizado |
| Anular/eliminar | Motivo obligatorio; impedir pérdida de pagos e historial | `manage_billing` + permiso de anulación por definir | Estado anterior/nuevo y motivo | éxito, pagada, inexistente y no autorizado |
| Descargar PDF | Factura existente y acceso al paciente/rol | permiso de lectura financiera por definir | Acceso al documento | descarga autorizada, prohibida y documento inexistente |
| Enviar factura | Destinatario y canal válidos; estado enviable | `manage_billing` | Intento, resultado, canal y error | éxito, fallo, reintento e idempotencia |

## Inventario

| Acción | Validación mínima | Autorización | Auditoría | Prueba requerida |
|---|---|---|---|---|
| Ajustar | Cantidad positiva, tipo válido, motivo obligatorio y stock resultante válido | `adjust_inventory` o decisión equivalente | Producto, stock anterior/nuevo, actor y motivo | entrada, salida, stock insuficiente y concurrencia |
| Transferir | Origen/destino distintos, cantidad disponible y producto válido | `manage_inventory` + permiso de transferencia por definir | Ambos movimientos y actor | éxito, ubicación inválida, insuficiente y rollback |
| Exportar | Filtros limitados y formato permitido | permiso de exportación por definir | Actor, filtros y volumen | éxito, sin datos, límite y no autorizado |

## Contrato transversal

1. La validación debe vivir en Form Requests y no en Blade.
2. La decisión de negocio debe vivir en Services; las consultas complejas, en Repositories.
3. Las operaciones financieras y de stock deben usar transacción cuando afecten más de un registro.
4. Las respuestas de error no deben revelar datos clínicos o financieros innecesarios.
5. Toda acción crítica debe producir un evento de auditoría consultable.
6. Las pruebas deben cubrir autorización positiva y negativa, validación, consistencia transaccional y auditoría.

## Criterio de salida

No implementar una acción hasta que tenga Form Request, permiso resuelto, Policy o Gate definido, evento de auditoría, Service, prueba de regresión y evidencia de `route:list` sin referencias inexistentes.
