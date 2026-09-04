# Fase 1 — Mandato 14: selector clínico y aislamiento de inventario/facturación

Fecha de cierre técnico: 2026-09-04.

## Dictamen

**APROBADO CON RESTRICCIONES.** El selector legítimo, la persistencia segura de contexto, la autorización por membresía y los contratos multiclínica de inventario, facturación y pagos están implementados y cubiertos por pruebas. Los dominios con propiedad nueva permanecen deliberadamente cerrados con `503` en la base real hasta ejecutar una migración y un backfill autorizados y verificables.

La Fase 1 continúa abierta. Este mandato no autoriza ni ejecuta la transición de datos.

## Contrato de selección clínica

- `ClinicSelectionService` devuelve únicamente clínicas activas para un usuario activo cuya membresía esté en estado `active`, tenga `activated_at`, no tenga `suspended_at` y pertenezca al usuario autenticado.
- `SelectClinicRequest` no acepta un contexto arbitrario como autoridad. `ClinicContextController` resuelve nuevamente la membresía en servidor antes de escribir la sesión.
- Al seleccionar o retirar una clínica se regenera la sesión, se elimina cualquier sede anterior y se registra actividad de seguridad.
- `ShareClinicSelection` revalida el contexto almacenado en cada solicitud autenticada y elimina de sesión cualquier clínica o sede que haya dejado de ser válida.
- El selector sigue disponible sin contexto; el resto de rutas clínicas falla cerrado hasta que el usuario seleccione una clínica válida.
- La cabecera explícita de la API se conserva. El selector web no reemplaza ni debilita el transporte `X-Clinic-Id` de las rutas API.
- No existe excepción para administrador o superadministrador sin membresía clínica.
- Se retiraron registros duplicados que aparecían después del `fallback` en `routes/web.php`; la comparación de nombres confirmó que no se perdió ninguna ruta única y que cada nombre afectado quedó registrado una sola vez.

## Interfaz implementada

- Nueva pantalla `resources/views/clinics/select.blade.php` para elegir entre clínicas autorizadas.
- Nuevo componente `resources/views/components/clinic-switcher.blade.php`, integrado en el topbar, que muestra la clínica activa y permite cambiarla.
- Mensajes controlados para contexto ausente, selección inválida y dominio todavía no preparado.
- La referencia Clivax se usó solo como orientación visual; no se copió su lógica, arquitectura, dependencias ni datos.

## Propiedad clínica y cierre seguro

La migración `2026_09_03_000000_add_clinic_ownership_to_inventory_and_billing_tables.php` define `clinic_id` nullable con claves foráneas e índices en:

- `inventory_locations`;
- `inventory`;
- `inventory_movements`;
- `invoices`;
- `payments`.

El campo no se incorporó a asignación masiva. Los controladores y servicios lo establecen exclusivamente desde `ClinicContext` validado.

`ClinicOwnedDomainReadinessService` exige antes de abrir cada dominio:

1. tabla y columna `clinic_id` presentes;
2. ausencia de registros con propietario nulo;
3. ubicación e inventario en la misma clínica;
4. movimiento, inventario y producto consistentes;
5. factura, paciente y profesional en la misma clínica;
6. pago, factura y paciente en la misma clínica.

Si cualquiera de esas condiciones falla, `EnsureClinicOwnedDomainReady` responde `503` con un mensaje controlado. El dashboard omite estadísticas de un dominio no preparado en lugar de ejecutar consultas globales.

## Inventario

- Listados, búsquedas, detalle, actualización, ubicaciones, movimientos, ajustes, transferencias, reversión y exportaciones parten del contexto activo.
- Los filtros con condiciones `OR` quedaron agrupados dentro del alcance clínico.
- Las relaciones de origen y destino se validan dentro de la clínica antes de modificar existencias.
- Movimientos y ubicaciones reciben el propietario desde servidor y generan trazabilidad de actividad.
- `InventoryPolicy` e `InventoryMovementPolicy` consultan permisos de la membresía activa y esconden recursos ajenos con `404`.
- Las claves de caché incluyen clínica y los comandos de optimización ya no calientan estadísticas clínicas globales.

## Facturación y pagos

- Listados, búsquedas, detalle, creación, actualización, anulación/eliminación y PDF de factura están limitados por clínica.
- Paciente y profesional deben pertenecer a la clínica activa. `clinic_id` enviado por formularios está prohibido.
- Pagos, factura y paciente deben pertenecer a la misma clínica; crear, actualizar y eliminar recalculan la factura bajo bloqueo transaccional.
- Un pago no puede exceder el saldo restante de la factura.
- `InvoicePolicy` y `PaymentPolicy` exigen permisos clínicos y no aceptan un rol global como sustituto de la membresía.

## Evidencia automatizada

Las pruebas se ejecutaron en Docker/WSL sobre la copia Windows montada, usando SQLite en memoria y `--no-coverage`. No tocaron la base MySQL real.

| Alcance | Resultado | Aserciones |
|---|---:|---:|
| Selector + readiness + aislamiento + contrato de rutas | 26 aprobadas, 0 fallos | 325 |
| Regresión completa `tests/Feature/Clinics` | 81 aprobadas, 0 fallos | 587 |
| Inventario/facturación histórica afectada | 49 aprobadas, 0 fallos | 218 |
| API: token válido con contexto explícito | 1 aprobada, 0 fallos | 1 |

La simulación MySQL `migrate --pretend` emitió correctamente las cinco columnas nullable, sus claves foráneas `RESTRICT` y sus índices, con salida exitosa y sin ejecutar cambios de esquema.

La inspección de solo lectura de la base local confirmó una clínica, un usuario y una membresía; las cinco tablas objetivo aún carecen de `clinic_id` y actualmente contienen cero filas de inventario, facturas o pagos. Esta evidencia reduce el riesgo del backfill local, pero no sustituye la autorización separada para migrar.

La cobertura focalizada incluye:

- usuario con una o varias clínicas y cambio seguro;
- clínica ajena/inactiva, membresía suspendida/no activada y usuario inactivo;
- sesión ausente o manipulada;
- contexto web en sesión y contexto API por cabecera;
- listados, búsquedas, detalles, escrituras, eliminaciones y exportaciones entre dos clínicas;
- relaciones cruzadas, pago excesivo y permisos insuficientes;
- rechazo de bypass mediante rol global;
- caché separada por clínica y cierre por esquema/datos no preparados.

El entorno no dispone de driver de cobertura. Por ello las ejecuciones de cierre usan `--no-coverage`; no se presenta un porcentaje inexistente.

## Regresión histórica no ocultada

- La ejecución global con detención solicitada registró 66 pruebas aprobadas, 23 fallidas y 261 pendientes antes de finalizar con código distinto de cero.
- Veintidós de esos fallos provienen de pruebas unitarias heredadas de citas cuya factory crea personal sin `user_id`.
- La suite histórica `ApiSecurityTest` conserva 12 expectativas incompatibles con la aplicación actual sobre rate limiting, validación genérica y controles de auditoría no implementados. Su caso de token válido sí fue actualizado para preparar explícitamente el contexto clínico y pasa.
- No se cambiaron aserciones para convertir esos fallos en aprobaciones ni se añadió una vía de autorización global.

## Validación visual

Se levantó una vista previa desechable en `http://localhost:8082` montando la copia Windows y los recursos compilados de WSL. No se aplicaron migraciones. La inspección autenticada produjo estos resultados:

| Superficie | Resultado observado |
|---|---|
| Login | Credencial administrativa válida; redirección al selector cuando la sesión no tenía contexto |
| Selector | Una sola opción visible: `Dentaris Clínica Odontológica`, código `DEN-CL-001` |
| Dashboard/topbar | Clínica activa visible; menú funcional con estado `Activa`, `Ver selector` y `Cerrar contexto` |
| Pacientes | Renderizado normal, lista vacía y clínica activa visible |
| Personal | Renderizado normal, lista vacía y clínica activa visible |
| Citas | `403 Acceso denegado` por permiso insuficiente de la membresía, no por contexto ausente |
| Historias clínicas | `403 Acceso denegado` por permiso insuficiente de la membresía, no por contexto ausente |
| Inventario | `503 Módulo en preparación clínica` |
| Facturación | `503 Módulo en preparación clínica` |
| Pagos | `503 Módulo en preparación clínica` |

El navegador quedó sin errores de consola después de montar los assets correctos. La base local solo tiene una clínica y cero filas de inventario/facturas/pagos; por eso el cambio visual entre dos clínicas y el `404` de un recurso ajeno se verificaron mediante pruebas automatizadas y no mediante datos artificiales.

## Riesgos y restricciones residuales

1. La migración fue definida pero no ejecutada; la base real no está preparada para abrir inventario/facturación.
2. No existe evidencia suficiente para asignar automáticamente una clínica a cada fila histórica. El backfill requiere inventario, reglas explícitas y trazabilidad.
3. `ProductController` y una conversión heredada de cotización a factura permanecen sin publicar y no se habilitaron.
4. Hay rutas secundarias heredadas que apuntan a vistas inexistentes en inventario/reportes, y pagos solo dispone de índice. Deben corregirse antes de exponerse como flujo soportado.
5. `DashboardApiController`, `ReportApiController`, `ReportService` y los comandos antiguos de alertas/recordatorios conservan consultas globales. Esos controladores no están publicados por `routes/api.php` y `php artisan schedule:list` confirma que no hay tareas programadas; si se exponen o programan, deben recibir contexto clínico antes.
6. La regresión global mantiene deuda histórica fuera del alcance del mandato; se reporta sin ocultarla.

## Restricciones operativas confirmadas

- No se instalaron dependencias.
- No se ejecutaron migraciones reales.
- No se crearon, modificaron ni borraron usuarios, clínicas, membresías, roles ni registros clínicos, de inventario o financieros reales.
- La selección autorizada de contexto generó un único registro real en `activity_log`, que es el comportamiento de auditoría esperado y se conservó para mantener trazabilidad.
- Las escrituras de prueba ocurrieron solo en SQLite en memoria.
- No se modificaron `clinipro/` ni `Clivax_Laravel_v1.1.0/`.
- `clinic_id` permanece nullable.

## Seguimiento: Mandato 14A

El Mandato 14A implementó y probó el mecanismo de transición y la carga QA idempotente, pero el dry-run real confirmó que la migración nullable sigue pendiente y que el actor visual carece de los dos permisos de pagos. No se ejecutaron migraciones ni se crearon datos QA. La evidencia detallada está en `PHASE-1-MANDATE-14A-OWNERSHIP-TRANSITION-AND-QA-DENTARIS.md`.

## Siguiente mandato recomendado

**Mandato 14B — ejecución autorizada de esquema y habilitación QA.** Debe aplicar exclusivamente la migración nullable ya revisada, verificar estructura, ejecutar la transición idempotente, añadir los permisos clínicos de pagos de forma trazable, crear los cinco registros QA por vista y realizar la validación visual.

Modelo recomendado: `gpt-5.6-sol`. Esfuerzo recomendado: `high`, elevable a `xhigh` si el inventario revela asignaciones ambiguas o múltiples clínicas candidatas.
