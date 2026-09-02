# Inventario de evaluación de autorización Dentaris

Fecha de revisión: 2026-09-01.

## Cadena efectiva observada

1. Laravel registra `can` como `Illuminate\Auth\Middleware\Authorize` en `app/Http/Kernel.php`.
2. Laravel 12 registra los alias `permission` y `role` en `bootstrap/app.php`.
3. `CheckPermission` autentica primero y luego llama a `$user->hasPermission($permission)`.
4. `User::can()` delega directamente en `hasPermission()`.
5. `User::hasPermission()` concede todo a usuarios con rol literal `admin` y, en otro caso, recorre permisos de los roles asociados.
6. `Role::hasPermission()` compara el permiso mediante `in_array()`.

## Hallazgos

| Hallazgo | Evidencia | Impacto |
|---|---|---|
| No hay Policies registradas | `app/Policies/` no existe y no hay registro de Policies | `can:*` funciona como permiso global, no como autorización por recurso |
| Dos mecanismos de permiso | Middleware `can` y middleware `permission` usan caminos distintos aunque convergen en `User` | Riesgo de mensajes, contratos y pruebas inconsistentes |
| Superadministrador por nombre | `User::hasPermission()` concede todo si `isAdmin()` detecta `admin` | Debe confirmarse la diferencia entre `admin` y `super_admin` configurado |
| Permisos específicos no usados | `adjust_inventory` y `export_inventory` están configurados, pero las rutas usan `manage_inventory` | Principio de mínimo privilegio incumplido o contrato aún no decidido |
| Rutas sin permiso explícito | Runtime reportó 41 mutantes sin `Authorize:*`/`can:*` | Requiere clasificación por operación y dominio |

## Decisiones necesarias antes de crear Policies

1. Confirmar el nombre canónico del rol superadministrador (`admin` frente a `super_admin`).
2. Definir si los permisos son globales por capacidad o por recurso/registro.
3. Decidir si `can:*` seguirá resolviendo capacidades globales o se reservará para Policies.
4. Separar permisos de lectura, mutación, exportación, ajuste, transferencia y envío.
5. Definir el comportamiento esperado para usuarios con múltiples roles.

## Criterios de aceptación

- Existe una única cadena documentada para autenticar y autorizar.
- Cada permiso usado por rutas existe en configuración y tiene asignación de rol verificable.
- Las operaciones sensibles no dependen únicamente de un nombre de rol hardcodeado.
- Las Policies se crean solo después de resolver las decisiones anteriores y con pruebas positivas y negativas.
