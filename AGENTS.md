# Dentaris — instrucciones del proyecto

Este repositorio corresponde exclusivamente a Dentaris, un sistema de gestión dental.

## Stack

- Laravel 12
- PHP 8.2+
- MySQL
- Blade
- Vite
- Metronic Tailwind demo1 como referencia visual objetivo

## Arquitectura

- Monolito modular dentro de `app/Modules/`.
- La lógica de negocio debe vivir en Services.
- Las consultas complejas deben vivir en Repositories.
- Las validaciones deben utilizar Form Requests.
- La autorización debe utilizar Roles, Permisos, Policies y Gates.
- Las acciones críticas deben auditarse.
- Los documentos clínicos y sensibles deben almacenarse en discos privados.

## Referencias

- `clinipro/` es una referencia funcional NodeJS/ReactJS. Solo se adaptan sus módulos y reglas de negocio al stack Laravel.
- `Clivax_Laravel_v1.1.0/` es una referencia visual. No se copia su lógica ni su arquitectura.

## Reglas de alcance

- No introducir nombres, módulos, documentación o reglas institucionales ajenas a Dentaris.
- No trasladar secretos, datos de ejemplo sensibles ni archivos de `uploads/` desde las referencias.
- Mantener la documentación del proyecto en `docs/`.