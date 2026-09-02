# Decisión de alcance para acciones de inventario

Fecha de revisión: 2026-09-01.

## Evidencia

- `InventoryController` implementa listado, detalle, actualización, alertas y reporte.
- No implementa `adjust`, `transfer` ni `export`, aunque las tres rutas están declaradas.
- `resources/views/inventory/index.blade.php` presenta un botón JavaScript para ajustar stock, pero no se observa un formulario/flujo HTTP completo para la acción.
- `Inventory` y `Product` contienen operaciones de stock, reservas, consumo, alertas y ubicación, pero no existe un registro de movimientos que documente actor, motivo, origen y destino.
- El controlador realiza una consulta agregada directa para el valor del inventario; deberá trasladarse a Repository/Service durante la implementación.

## Decisión

Las tres acciones permanecen pendientes de implementación controlada. No se crearán stubs, no se eliminarán rutas y no se modificará la vista en esta fase.

| Acción | Estado | Condición para implementar |
|---|---|---|
| Ajustar stock | Pendiente | Formato de cantidad, tipo, motivo, permiso específico y movimiento auditable |
| Transferir stock | Pendiente | Ubicaciones, disponibilidad, atomicidad y rollback |
| Exportar inventario | Pendiente | Formato, filtros, límite, permiso específico y protección de datos |

## Riesgos a resolver

1. Las operaciones de modelo pueden actualizar cantidades sin un ledger de movimientos verificable.
2. `adjust_inventory` y `export_inventory` existen en configuración, pero las rutas usan `manage_inventory`.
3. La interfaz sugiere ajuste de stock aunque la acción HTTP no existe.
4. No debe implementarse transferencia sobre una sola fila sin definir origen y destino.

## Criterio de aceptación

Cada acción debe tener Request, Service, Policy, persistencia transaccional, auditoría, pruebas de autorización y consistencia de stock, además de una vista o respuesta API que no invoque rutas inexistentes.
