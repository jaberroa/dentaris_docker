# Especificación técnica de movimientos de inventario

Fecha de revisión: 2026-09-01.

## Componentes propuestos

| Componente | Responsabilidad | No debe hacer |
|---|---|---|
| `InventoryMovement` | Representar un movimiento inmutable y sus relaciones | Calcular por sí solo transferencias ni autorizar usuarios |
| `InventoryMovementRepository` | Consultar historial, saldos y movimientos por filtros | Recibir reglas HTTP o decidir permisos |
| `InventoryMovementService` | Ejecutar ajustes, transferencias y movimientos compensatorios en transacción | Renderizar respuestas o leer directamente el request |
| `InventoryMovementPolicy` | Autorizar consulta, ajuste, transferencia y exportación | Mutar stock o resolver roles hardcodeados |
| `CreateInventoryAdjustmentRequest` | Validar producto, cantidad, tipo y motivo | Actualizar modelos |
| `TransferInventoryRequest` | Validar origen, destino, producto y cantidad | Ejecutar la transferencia |
| `ExportInventoryRequest` | Validar filtros, formato y límites | Generar consultas complejas |

## Contrato del Service

### `adjust(AdjustmentData $data, User $actor): InventoryMovement`

- Bloquea la fila de inventario.
- Valida stock resultante y tipo de operación.
- Persiste movimiento y stock agregado en una transacción.
- Registra actor, motivo y valores antes/después.
- Devuelve el movimiento creado.

### `transfer(TransferData $data, User $actor): TransferResult`

- Valida ubicaciones distintas y disponibilidad.
- Bloquea origen y destino en orden determinista.
- Persiste salida, entrada y actualización agregada atómicamente.
- Revierte todo ante cualquier fallo.
- Devuelve ambos movimientos relacionados.

## DTOs y tipos controlados

- `type` debe ser un enum o conjunto cerrado; no aceptar texto arbitrario.
- `quantity` debe normalizarse a entero positivo.
- `reason` es obligatorio para acciones manuales.
- `reference_type` y `reference_id` son opcionales, pero deben apuntar a entidades existentes cuando se envíen.
- No incluir datos clínicos ni secretos en `metadata`.

## Integración HTTP futura

El controlador deberá limitarse a recibir Request, invocar Policy y Service, y devolver respuesta web/API. No se debe trasladar cálculo de stock, locking, consultas complejas o auditoría al controlador.

## Criterios de aceptación técnica

1. El Service es testeable sin HTTP.
2. El Repository permite consultar historial por producto, ubicación, tipo, actor y fechas.
3. La Policy separa `view`, `adjust`, `transfer` y `export`.
4. Los Requests rechazan cantidades no positivas, ubicaciones iguales y motivos vacíos.
5. La transacción garantiza que el ledger y el stock agregado no diverjan.
6. La implementación se entrega por unidad: migración, modelo, DTO, Repository, Service, Policy, Requests, pruebas y documentación.
