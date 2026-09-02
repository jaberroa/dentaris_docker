# Reglas operativas del proyecto Dentaris

Fecha de revisión: 2026-09-02.

## Referencias

1. Dentaris es el sistema principal y la fuente de verdad de su implementación.
2. Clinipro aporta requisitos y lógica funcional a evaluar.
3. Clivax aporta exclusivamente UI/UX.
4. No se copian código, arquitectura, dependencias, secretos, uploads ni datos de ejemplo desde las referencias.
5. No se introducen nombres o reglas institucionales ajenos a Dentaris.

## Desarrollo

- Laravel y Blade son el stack oficial.
- La lógica de negocio vive en Services.
- Las consultas complejas viven en Repositories.
- Las entradas mutables usan Form Requests.
- Los recursos sensibles usan Policies, Gates y middleware coherentes.
- Las acciones críticas generan auditoría con actor, entidad, evento y contexto.
- Blade presenta datos; no decide reglas de negocio ni ejecuta consultas complejas.
- Cada fase debe tener criterios de aceptación antes de codificar.

## Datos y seguridad

- No se eliminan facturas, pagos, movimientos ni auditorías para corregir un error; se usan estados o reversas trazables.
- Los documentos clínicos y sensibles se almacenan en discos privados.
- Nunca se imprimen secretos, contraseñas, tokens o credenciales en logs o documentación.
- Los seeders demo deben ser idempotentes y no borrar ni resetear datos reales.
- Las migraciones se aplican solo con autorización explícita y se verifican antes y después.
- No se instalan dependencias ni herramientas durante una auditoría sin aprobación.

## Git y entornos

- Codex edita únicamente C:\Users\LENOVO\Projects\dentaris_docker.
- WSL se usa para Docker, pruebas y revisión visual mediante git pull --ff-only origin main.
- No se edita simultáneamente la copia Windows y la copia WSL.
- Cada cambio terminado requiere diff revisado, commit y push a origin/main, según la autorización operativa vigente.
- Nunca se usa reset --hard, clean, borrado masivo o checkout destructivo sin autorización explícita.
- Antes de publicar se verifica archivo modificado, diff, hash del commit y estado Git.

## Calidad

- Pruebas verdes no sustituyen revisión visual, de seguridad ni de datos.
- Todo fallo se reproduce, clasifica y corrige antes de cerrar la fase.
- Los cambios de una fase no se mezclan con trabajo no relacionado.
- La documentación debe indicar fecha, evidencia, decisión y siguiente fase.
