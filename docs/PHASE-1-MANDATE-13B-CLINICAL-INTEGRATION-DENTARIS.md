# Fase 1 — Mandato 13B: integración clínica de citas e historias

Fecha de cierre técnico: 2026-09-03.

## Dictamen

**APROBADO CON RESTRICCIONES.** Se cierra el alcance técnico del Mandato 13B porque las pruebas focalizadas y la regresión clínica completa validan el contexto, los permisos de membresía, el aislamiento de relaciones y la corrección de renderizado. La Fase 1 permanece abierta: inventario y facturación aún no tienen propiedad clínica persistida y su deuda histórica de pruebas debe abordarse en un mandato posterior.

Las restricciones de cierre son:

1. La regresión global contiene deuda previa y pruebas incompatibles con el contrato multiclínica vigente.
2. La sesión real autenticada disponible no tenía `clinic_id` activo y la aplicación no expone todavía un selector web de clínica; las tres pantallas protegidas fallaron cerradas con `403` antes de renderizar.
3. La exportación PDF de historias clínicas continúa como una capacidad no implementada y responde `501`; el acceso al recurso sí queda aislado antes de esa respuesta.

## Cambios publicados

- `e793fd5` — integración de `AppointmentRequest`, `MedicalRecordRequest` y `ClinicalRelatedRecordAccessService`; rutas clínicas; orden de middleware; pruebas; cierre de funciones Blade globales.
- `f50f5ae` — corrección del contrato heredado entre `MedicalRecordController` y `medical-records/index.blade.php` detectada por la primera ejecución focalizada.

Antes de publicar este cierre documental, `HEAD`, `main` y `origin/main` apuntaban a `f50f5ae`.

## Contratos implementados

### Citas

- Todas las rutas web de citas requieren `clinic.context` y permiso clínico de lectura o gestión.
- Listados, calendarios, búsquedas de personal y selectores nacen de `ClinicalRelatedRecordAccessService` y quedan limitados a paciente y personal de la clínica activa.
- `AppointmentRequest` valida paciente y personal activos dentro de la clínica y prohíbe `clinic_id` enviado por el cliente.
- Mostrar, editar, actualizar, eliminar, confirmar, cancelar y cambiar estado resuelven nuevamente la cita dentro del contexto y devuelven `404` para una cita ajena.
- Se retiró la autorización heredada basada en correo o especialidad. La autorización clínica proviene del permiso asignado a la membresía activa.
- Las respuestas de error de escritura ya no incorporan el mensaje interno de la excepción.

### Historias clínicas

- Todas las rutas web requieren `clinic.context` y `view_medical_records` o `manage_medical_records`.
- Listados, búsquedas por paciente, selectores y acceso directo usan consultas clínicas limitadas.
- `MedicalRecordRequest` valida paciente y personal activos, prohíbe `clinic_id` y exige que una cita opcional pertenezca a la misma clínica, paciente y profesional.
- Mostrar, editar, actualizar, eliminar, exportar y consultar por paciente devuelven `404` para recursos ajenos.
- Crear, actualizar y eliminar generan actividad auditable.
- La vista de índice usa el contrato real `$records`, `patient.full_name` y `diagnostic_impression`.

### Inventario y facturación

- Sus grupos web ahora ejecutan `clinic.context` antes de cualquier `permission:*`.
- `CheckPermission` conserva el contrato de 13A: consulta permisos exclusivamente desde roles activos de la membresía clínica activa.
- La presencia de contexto no equivale todavía a propiedad multiclínica de inventario o facturación; esa separación queda fuera de 13B.

### Vistas heredadas

- Personal, citas y planes de tratamiento ya no declaran `getSortUrl()`, `getSortIcon()` o `getSortClass()` como funciones PHP globales.
- Los helpers de ordenamiento son closures locales con nombre por vista; renderizar personal varias veces en el mismo proceso no redeclara símbolos.

## Evidencia de pruebas

Ejecución en WSL, dentro del contenedor `app`, sobre la copia sincronizada por `git pull --ff-only origin main`:

| Alcance | Resultado | Aserciones |
|---|---:|---:|
| Suite focalizada 13B + rutas + permisos 13A + personal | 35 aprobadas, 0 fallos | 306 |
| Regresión completa `tests/Feature/Clinics` | 65 aprobadas, 0 fallos | 432 |
| Regresión global disponible | 334 ejecutadas; 64 errores y 115 fallos | 886 |

La suite focalizada prueba expresamente:

- acceso válido dentro de la clínica;
- contexto ausente, clínica ajena, clínica o usuario inactivo;
- membresía no activada o suspendida;
- permiso clínico insuficiente y rechazo de un rol global;
- paciente, personal, cita e historia de otra clínica;
- intento de relación cruzada entre cita, paciente y personal;
- binding ajeno con respuesta `404`;
- listado, búsqueda, creación, actualización y eliminación;
- exportación ajena de historia con respuesta `404`;
- orden `clinic.context` antes de `permission:*` en citas, historias, inventario y facturación;
- rutas estáticas no ocultadas por parámetros dinámicos;
- segundo renderizado de personal sin redeclaración.

## Fallos heredados y compatibilidad pendiente

No se modificaron pruebas para ocultar fallos. El informe JUnit global clasifica:

- 22 errores de `AppointmentTest` unitario por factories heredadas que intentan crear `staff` sin `user_id`.
- 3 errores de `AuthorizationMiddlewareTest` por instanciar `CheckPermission` sin la dependencia introducida y cubierta en 13A.
- 39 errores en las suites web/API históricas de citas antes de ejecutar sus aserciones, asociados a fixtures heredados.
- Pruebas HTTP antiguas de inventario y facturación esperan acceso con roles globales y sin contexto; ahora reciben `403`, que es el comportamiento requerido por 13B.
- Permanecen fallos previos en seguridad genérica, 2FA, pacientes heredados y pruebas de penetración. Las 65 pruebas clínicas actuales no reproducen esos fallos.
- PHPUnit advierte que varias pruebas antiguas aún usan metadata en docblocks, formato que quedará obsoleto en PHPUnit 12.
- El entorno no dispone de driver de cobertura; no se generó porcentaje de cobertura.

## Validación visual

La sesión real entregada quedó autenticada y el panel respondió correctamente en `http://localhost:8080/dashboard`, identificando al usuario visible como Administrador Dentaris. Después se navegaron, sin enviar formularios ni modificar datos, las tres superficies del mandato:

| Ruta | Resultado observado |
|---|---|
| `http://localhost:8080/staff` | `403` con texto controlado `El contexto clínico no está disponible.` |
| `http://localhost:8080/appointments` | `403` con el mismo texto controlado |
| `http://localhost:8080/medical-records` | `403` con el mismo texto controlado |

El resultado confirma que `clinic.context` falla cerrado cuando la sesión carece de `clinic_id`, sin filtrar detalles internos. También deja una deuda operativa verificable: las rutas web inspeccionadas no ofrecen un selector que establezca el contexto clínico, por lo que una autenticación válida no basta para acceder a los módulos.

La composición final de tablas, ordenamiento e impresión diagnóstica no pudo inspeccionarse con datos reales. Sí quedó cubierta por las pruebas de renderizado e integración en SQLite en memoria. Para completar esa parte visual en un mandato posterior será necesario implementar o habilitar un flujo legítimo de selección de clínica y usar una membresía existente con `view_staff`, `view_appointments` y `view_medical_records`; no se debe inyectar `clinic_id` ni modificar datos manualmente para sortear el contrato.

## Restricciones operativas confirmadas

- No se instalaron dependencias.
- No se ejecutaron migraciones.
- No se alteraron usuarios, clínicas, membresías, roles ni datos reales.
- Las escrituras observadas durante pruebas ocurrieron únicamente en SQLite en memoria mediante `RefreshDatabase`.
- No se modificaron `clinipro/` ni `Clivax_Laravel_v1.1.0/`.
- No se añadió ni hizo obligatorio `clinic_id` en citas o historias; la propiedad continúa inferida desde relaciones clínicas y `clinic_id` de paciente/personal conserva su transición nullable existente.

## Riesgos residuales y siguiente mandato

El mayor riesgo residual es que inventario, movimientos, ubicaciones, facturas y pagos aún carecen de una propiedad clínica persistida que permita un `404` inequívoco y consultas tenant-scoped. Además, sus Policies heredadas todavía consultan permisos globales en varias rutas `can:*`.

Se recomienda que el siguiente trabajo sea **Mandato 14 — propiedad, políticas y regresión multiclínica de inventario y facturación**, incluyendo migración nullable controlada, backfill auditable, scopes/repositorios, Policies basadas en membresía y actualización honesta de pruebas HTTP antiguas.

Modelo recomendado: `gpt-5.6-sol`. Esfuerzo recomendado: `ultra`, por el impacto transversal en datos financieros, inventario, bindings y autorización.
