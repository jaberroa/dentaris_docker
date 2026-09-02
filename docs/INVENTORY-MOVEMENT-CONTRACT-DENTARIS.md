# Contrato de movimientos de inventario Dentaris

Fecha de revisión: 2026-09-01.

## Estado actual

La tabla `inventory` mantiene existencias agregadas por producto y ubicación textual, pero no existe una tabla de movimientos. Los modelos exponen actualización, reserva, liberación y consumo de stock; esas operaciones no sustituyen un historial auditable.

## Propuesta de registro

La futura entidad `inventory_movements` deberá considerar, como mínimo:

| Campo | Propósito |
|---|---|
| `id` | Identificador del movimiento |
| `inventory_id` / `product_id` | Recurso afectado |
| `type` | `adjustment`, `transfer_in`, `transfer_out`, `restock`, `consumption`, `reservation` o `release` |
| `quantity` | Cantidad positiva del movimiento |
| `stock_before` / `stock_after` | Evidencia de consistencia |
| `source_location` / `destination_location` | Origen y destino para transferencias |
| `reason` | Motivo obligatorio para ajuste y transferencia |
| `reference_type` / `reference_id` | Vinculación con compra, factura u operación |
| `user_id` | Actor responsable |
| `metadata` | Contexto no sensible adicional |
| timestamps | Momento de creación y actualización |

## Reglas de negocio

1. La cantidad debe ser positiva; el signo lo determina `type`.
2. El stock disponible no puede quedar negativo.
3. Una transferencia crea salida y entrada en una sola transacción.
4. El movimiento y la actualización agregada deben persistirse atómicamente.
5. Los movimientos no se editan ni eliminan; una corrección genera un movimiento compensatorio.
6. El actor y el motivo son obligatorios para acciones manuales.
7. Ajustes, transferencias y consumos deben bloquear concurrencia o usar actualización condicional.
8. Las operaciones deben emitir auditoría de negocio, además del log técnico de seguridad cuando corresponda.

## Autorización propuesta

- Ajuste: `adjust_inventory`.
- Transferencia: permiso específico por definir; provisionalmente `manage_inventory` solo en contrato, no en implementación automática.
- Consulta de movimientos: `view_inventory`.
- Exportación: `export_inventory`.

## Criterios de aceptación

- Migración, modelo, Repository, Service, Policy, Request y pruebas se entregan como una unidad revisable.
- Se prueban rollback, stock insuficiente, ubicaciones inválidas, concurrencia y auditoría.
- No se modifica la tabla `inventory` ni se agrega una migración hasta aprobar este contrato.
