# Contratos funcionales preliminares: facturación e inventario

Fecha de revisión: 2026-09-01.

Estos contratos son criterios previos de implementación, no autorizan todavía cambios en controladores ni modelos.

## Facturación

| Acción | Entrada mínima | Resultado esperado | Reglas obligatorias |
|---|---|---|---|
| `edit` | `Invoice` existente | Formulario de edición solo para factura editable | No permitir alterar factura pagada o con pagos sin decisión explícita |
| `update` | Datos validados de factura e ítems | Totales recalculados y persistencia atómica | Recalcular subtotal, impuesto, descuento y saldo; auditar cambios |
| `destroy` | `Invoice` existente | Anulación o eliminación según política | Preferir anulación trazable; prohibir pérdida de historial financiero |
| `downloadPdf` | `Invoice` autorizada | Documento PDF descargable | Verificar permiso, no exponer ruta pública y registrar acceso |
| `sendInvoice` | `Invoice` y canal autorizado | Envío con estado y resultado trazable | Idempotencia, auditoría y manejo de error/reintento |

Evidencia existente: `Invoice` contiene totales, pagos, saldo y estados; `BillingController` ya usa transacciones para operaciones existentes. Debe definirse si las acciones faltantes completan el flujo o si las rutas deben retirarse/documentarse como no disponibles.

## Inventario

| Acción | Entrada mínima | Resultado esperado | Reglas obligatorias |
|---|---|---|---|
| `adjust` | Inventario, cantidad, motivo y tipo | Stock actualizado | Cantidad positiva, no permitir stock negativo, registrar actor y motivo |
| `transfer` | Producto, origen, destino y cantidad | Movimiento entre ubicaciones | Validar disponibilidad, operación atómica y auditoría de ambos lados |
| `export` | Filtros autorizados | Archivo de inventario | Limitar volumen, proteger datos y definir formato |

Evidencia existente: `Inventory` mantiene stock actual, reservado y disponible; `Product` y `Inventory` exponen operaciones de stock. No existe todavía un contrato de movimiento/ajuste independiente que garantice trazabilidad.

## Autorización común

- Facturación: `manage_billing` para mutaciones y un permiso de lectura separado por definir.
- Inventario: `manage_inventory` existe; el permiso `adjust_inventory` aparece en configuración y debe decidirse para ajustes.
- Exportaciones y documentos deben tener autorización específica o quedar cubiertos por una Policy verificable.

## Criterios de aceptación de implementación futura

1. Cada acción tiene Form Request, Service y prueba de autorización.
2. Las operaciones financieras y de stock son atómicas y auditables.
3. Los estados inválidos producen error controlado sin alterar datos.
4. PDF y exportaciones no usan almacenamiento público para datos sensibles.
5. Se verifica `route:list`, pruebas de regresión y diff antes de cada commit.
