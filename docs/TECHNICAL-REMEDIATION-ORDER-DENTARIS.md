# Orden de saneamiento técnico Dentaris

Fecha de revisión: 2026-09-01.

Este orden se deriva del inventario y de los contratos aprobados. Es una hoja de ruta de saneamiento; no autoriza todavía la implementación de acciones faltantes.

## Secuencia por fases

| Fase | Trabajo | Dependencias | Salida verificable |
|---|---|---|---|
| 1. Contratos | Confirmar estados, permisos, entradas, auditoría y errores de facturación e inventario | Contratos documentados | Contrato aprobado por dominio |
| 2. Validación | Crear Form Requests para mutaciones y exportaciones | Fase 1 | Reglas centralizadas y pruebas de validación |
| 3. Persistencia | Identificar consultas complejas y encapsularlas en Repositories | Fases 1-2 | Consultas reutilizables sin lógica compleja en controladores |
| 4. Servicios | Mover reglas transaccionales a Services | Repositories y Requests | Casos de uso atómicos, auditables y testeables |
| 5. Autorización | Definir Policies/Gates y permisos por operación | Contratos de seguridad | Matriz rol-permiso-recurso aprobada |
| 6. Controladores | Reducir controladores a coordinación HTTP | Requests, Services y Policies | Acciones resolubles y controladores delgados |
| 7. Pruebas | Cubrir éxito, rechazo, errores, transacciones y auditoría | Fases 2-6 | Suite reproducible de regresión |
| 8. Runtime | Ejecutar `route:list`, pruebas y revisión del diff | Suite y entorno WSL | Sin referencias inexistentes y estado Git limpio |

## Priorización

1. Facturación: acciones inexistentes y riesgo de pérdida de historial financiero.
2. Inventario: ajustes y transferencias afectan stock y requieren atomicidad.
3. Autorización transversal: rutas mutantes sin permiso explícito.
4. Notificaciones: acciones de cambio de estado y posible confusión entre `Notification` y `NotificationTemplate`.
5. Modularización: extraer límites a `app/Modules/` solo después de estabilizar contratos y pruebas.

## Reglas de ejecución

- Una fase por cambio revisable; cada cambio debe tener commit y push.
- No instalar dependencias ni ejecutar migraciones durante el saneamiento documental.
- No eliminar archivos respaldados, referencias ni artefactos untracked sin autorización explícita.
- No corregir rutas faltantes con stubs que oculten la decisión funcional.
- Validar siempre contra el repositorio Windows y sincronizar WSL mediante fast-forward.

## Criterio de aceptación global

La fase de saneamiento termina cuando las rutas críticas tienen contrato, Request, Service, Repository cuando aplique, autorización, auditoría, pruebas y evidencia runtime; el repositorio no presenta acciones inexistentes ni cambios no explicados.

## Siguiente acción concreta

Preparar la matriz detallada de permisos y Policies para facturación e inventario, comparando configuración, seeders, middleware y rutas existentes antes de crear clases nuevas.
