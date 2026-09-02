# Decisiones de autorización Dentaris

Fecha de revisión: 2026-09-01.

## Decisiones adoptadas para la siguiente implementación

| Tema | Decisión | Motivo |
|---|---|---|
| Rol superadministrador | El nombre canónico será `super_admin` | `config/permissions.php` lo declara como `super_admin_role`; se evita depender de nombres alternos |
| Rol `admin` | Será un rol administrativo con permisos explícitos, no bypass universal | El bypass actual de `User::hasPermission()` depende de `admin` y debe eliminarse o limitarse en una fase de código separada |
| Permisos de capacidad | Se mantienen como permisos globales temporales (`view_*`, `manage_*`, `process_*`, `adjust_*`, `export_*`) | Permite estabilizar rutas sin inventar todavía autorización por registro |
| `can:*` | Se reservará progresivamente para Policies/Gates y no se ampliará sin contrato | El middleware Laravel está registrado, pero hoy `User::can()` resuelve capacidades globales |
| `permission:*` | Se conservará para compatibilidad durante la transición | `CheckPermission` autentica y devuelve respuestas web/JSON específicas |
| Lectura sensible | Requerirá autenticación y permiso `view_*` | Evita que módulos clínicos, financieros e inventario dependan solo de `web` |
| Mutaciones específicas | Ajuste y exportación usarán `adjust_inventory` y `export_inventory` cuando se implemente | Respeta mínimo privilegio ya declarado en configuración |

## Inconsistencias que deben corregirse en código, no ocultarse en documentación

- `config/permissions.php` define `super_admin`, pero `User::isAdmin()` reconoce `admin` como bypass universal.
- `RoleSeeder.php` crea `admin` y no demuestra la creación de `super_admin`.
- `app/Policies/` no existe; por tanto, no hay autorización por recurso actualmente verificable.
- `app/Http/Kernel.php` y `bootstrap/app.php` registran aliases similares; debe mantenerse una fuente efectiva compatible con Laravel 12.

## Orden de implementación posterior

1. Crear pruebas que documenten el comportamiento deseado de `super_admin`, `admin` y usuarios multirol.
2. Normalizar la resolución de permisos en un único servicio o contrato.
3. Incorporar Policies por recurso crítico sin romper permisos globales existentes.
4. Aplicar permisos de lectura y permisos específicos a rutas sensibles.
5. Retirar el bypass hardcodeado solo cuando las pruebas demuestren equivalencia segura.

## Criterio de aceptación

La decisión se considera lista cuando la misma operación produce el mismo resultado web/API, los roles configurados coinciden con los seeders y cada permiso crítico tiene prueba positiva y negativa. No se crean Policies ni se modifican rutas hasta completar la fase de pruebas de autorización.
