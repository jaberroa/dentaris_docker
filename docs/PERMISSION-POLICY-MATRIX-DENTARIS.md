# Matriz de permisos y Policies: facturación e inventario

Fecha de revisión: 2026-09-01.

## Permisos declarados

| Permiso | `config/permissions.php` | Seeder de roles | Uso directo observado en rutas |
|---|---|---|---|
| `view_billing` | Sí | Sí | No explícito en las rutas de lectura revisadas |
| `manage_billing` | Sí | Sí | Grupo de creación y mutación de facturas |
| `process_payments` | Sí | Parcial según rol | No aplicado al flujo de facturación revisado |
| `view_inventory` | Sí | Sí | No explícito en las rutas de lectura revisadas |
| `manage_inventory` | Sí | Sí | Grupo de actualización, ajuste, transferencia y exportación |
| `adjust_inventory` | Sí | Sí en rol de inventario | No aplicado; ajuste comparte `manage_inventory` |
| `export_inventory` | Sí | No confirmado en el seeder revisado | No aplicado; exportación comparte `manage_inventory` |

## Matriz de decisión propuesta

| Operación | Permiso mínimo propuesto | Policy/Gate | Estado |
|---|---|---|---|
| Ver factura | `view_billing` | `InvoicePolicy::view` | Falta definir e implementar |
| Crear/editar factura | `manage_billing` | `InvoicePolicy::create/update` | Ruta declara permiso; Policy ausente |
| Anular factura | `manage_billing` + permiso específico si se confirma | `InvoicePolicy::delete` | Contrato pendiente |
| Descargar factura | `view_billing` | `InvoicePolicy::view` | Acción de controlador ausente |
| Enviar factura | `manage_billing` | `InvoicePolicy::send` | Acción de controlador ausente |
| Ver inventario | `view_inventory` | `InventoryPolicy::view` | Ruta no declara permiso explícito |
| Actualizar stock | `manage_inventory` | `InventoryPolicy::update` | Ruta declara permiso |
| Ajustar stock | `adjust_inventory` | `InventoryPolicy::adjust` | Ruta usa permiso más amplio; decisión pendiente |
| Transferir stock | Permiso de transferencia por definir | `InventoryPolicy::transfer` | Acción ausente |
| Exportar inventario | `export_inventory` | `InventoryPolicy::export` | Ruta usa permiso más amplio |

## Hallazgos

1. Existen permisos específicos para ajustar y exportar inventario, pero las rutas usan únicamente `manage_inventory`.
2. Existen permisos de lectura de facturación e inventario, pero las rutas GET revisadas no los aplican de forma explícita.
3. No existe `app/Policies/`; la matriz no debe convertirse en clases hasta confirmar el sistema efectivo de Gates/permisos y la compatibilidad con los roles existentes.
4. `RoleSeeder` y `config/permissions.php` no deben asumirse equivalentes sin verificación ejecutable de cómo se cargan y evalúan los permisos.

## Criterio de aceptación

La autorización queda lista para implementación cuando cada operación tenga permiso único, Policy/Gate resoluble, rol autorizado y prueba positiva/negativa. Cualquier ampliación de permisos debe ser un cambio separado, revisado, committeado y publicado.
