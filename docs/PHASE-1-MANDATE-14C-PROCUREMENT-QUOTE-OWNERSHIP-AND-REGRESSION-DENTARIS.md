# Fase 1 — Mandato 14C: propiedad operativa y saneamiento de regresión

Fecha de ejecución y cierre documental: 2026-09-05, zona horaria `America/Caracas`.

## Dictamen

**APROBADO CON RESTRICCIONES.** Proveedores, productos, compras y cotizaciones tienen ahora un contrato explícito de propiedad clínica nullable, claves foráneas restrictivas, scopes y Policies basadas únicamente en la membresía clínica activa. La transición real asignó exclusivamente los cinco productos QA existentes a `DEN-CL-001`, conservó todos los demás valores y resultó idempotente.

No se abrieron los módulos heredados de proveedores, compras o cotizaciones. Continúan respondiendo `503` antes de llegar a sus controladores porque estos todavía mezclan consultas globales, validación inline, vistas faltantes y, en cotizaciones, un contrato de modelo incompatible con el esquema real.

La regresión global mejoró de 207 aprobadas y 154 fallidas a 294 aprobadas y 74 fallidas. No quedan fallos en las suites de clínicas, inventario, facturación, pacientes ni en los contratos nuevos de este mandato. La Fase 1 sigue abierta por las 74 fallas heredadas y por las superficies operativas que permanecen cerradas.

## Decisión de propiedad

| Entidad | Decisión | Evidencia o regla |
|---|---|---|
| `suppliers` | Raíz con `clinic_id` | Un proveedor puede participar en productos o compras de una clínica; no se declaró catálogo global |
| `products` | Raíz con `clinic_id` | El inventario ya es clínico y cada producto debe coincidir con el propietario de todas sus existencias |
| `purchases` | Raíz con `clinic_id` | La compra hereda evidencia coherente de proveedor y productos, pero conserva dueño explícito para consulta y autorización |
| `quotes` | Raíz con `clinic_id` | Paciente y profesional ya son clínicos; ambos deben coincidir y el plan opcional debe pertenecer a esa misma relación |
| `purchase_items` | Hereda de `purchase` | No duplica `clinic_id`; valida producto y compra del mismo propietario |
| `quote_items` | Hereda de `quote` | No duplica `clinic_id`; valida la existencia de cotización y catálogo CDT |

Las restricciones únicas históricas —por ejemplo códigos globales— no se modificaron. Cambiar unicidad a alcance clínico requiere un mandato separado con análisis de colisiones y despliegue; no era necesario para asignar los datos actuales.

## Respaldo y precondiciones

Antes de modificar esquema o datos se creó un volcado completo en almacenamiento privado:

- archivo: `storage/app/private/backups/dentaris-pre-mandate-14c-20260905-043107.sql`;
- tamaño: `155129` bytes;
- SHA-256: `84CACCEFB11CB65C96EFAD052B01C5E56725325EEC8DA94C1C9FFE1CC8772F3C`;
- cierre del volcado: `2026-09-05 08:31:25`;
- estado Git: ignorado, sin publicación de contenido sensible.

Precondiciones reales inmediatamente anteriores a la migración:

| Tabla o autoridad | Resultado |
|---|---:|
| `suppliers` | 0 |
| `products` | 5, IDs `1..5`, códigos `QA14A-P33-001..005` |
| `purchases` / `purchase_items` | 0 / 0 |
| `quotes` / `quote_items` | 0 / 0 |
| Clínica objetivo | ID `33`, `DEN-CL-001`, activa |
| Membresía | ID `28`, usuario ID `37`, activa, activada y no suspendida |
| Permiso clínico necesario para tablas con filas | `manage_inventory=true` |
| Permisos no necesarios por tablas vacías | `manage_suppliers=false`, `manage_purchases=false`, `manage_quotes=false` |

La autoridad se resolvió mediante `clinic_membership_roles`; no se usó ni aceptó un rol global como sustituto.

## Migración aplicada

Se simuló y aplicó exclusivamente:

```text
database/migrations/2026_09_05_000000_add_clinic_ownership_to_procurement_and_quotes_tables.php
```

Quedó registrada en el lote `3`. Las cuatro columnas son `BIGINT UNSIGNED`, admiten nulo y referencian `clinics.id` con borrado `RESTRICT`.

| Tabla | Clave foránea | Índices clínicos verificados |
|---|---|---|
| `suppliers` | `suppliers_clinic_id_foreign` | `suppliers_clinic_active_idx (clinic_id, is_active)` |
| `products` | `products_clinic_id_foreign` | `products_clinic_category_active_idx (clinic_id, category, is_active)`; `products_clinic_supplier_idx (clinic_id, primary_supplier_id)` |
| `purchases` | `purchases_clinic_id_foreign` | `purchases_clinic_date_status_idx (clinic_id, purchase_date, status)`; `purchases_clinic_supplier_idx (clinic_id, supplier_id)` |
| `quotes` | `quotes_clinic_id_foreign` | `quotes_clinic_date_status_idx (clinic_id, quote_date, status)`; `quotes_clinic_patient_idx (clinic_id, patient_id)` |

El contrato automatizado ejecutó `down()` y `up()` sobre una base aislada, preservó una fila de proveedor y demostró que la FK impide eliminar una clínica referenciada. La migración real se ejecutó solo después del respaldo, prechecks, simulación SQL y cinco pruebas de transición/rollback verdes.

## Transición controlada

El comando `clinics:transition-operational-domains` es dry-run por defecto y exige `--execute` para escribir. Valida esquema, clínica, usuario, membresía activa, permisos por tabla con filas, evidencia completa, relaciones y hashes; durante la escritura bloquea las tablas implicadas y opera dentro de una transacción.

Ensayo real:

- `run_id`: `ca6cb61e-3efa-48dd-81b7-95329b8c5328`;
- candidatos: productos `[1,2,3,4,5]`;
- proveedores, compras y cotizaciones: cero candidatos;
- relaciones ambiguas, huérfanas o cruzadas: cero;
- errores: ninguno;
- escrituras: ninguna.

Ejecuciones confirmadas:

| Ejecución | `run_id` | Proveedores | Productos | Compras | Cotizaciones | Total |
|---|---|---:|---:|---:|---:|---:|
| Primera | `16943ca9-91b8-480f-963d-7cb79398fa56` | 0 | 5 | 0 | 0 | 5 |
| Segunda | `a9446e58-e528-48f1-98bb-8c255f8ec29f` | 0 | 0 | 0 | 0 | 0 |

El hash de integridad de `products`, calculado sin `clinic_id`, permaneció idéntico antes y después:

```text
dbbf70e488dc66bd09980f4d93a9ce9bfea124b9f6592bd4dea169c7729c3dee
```

El hash con propiedad cambió de `1d417c4d81e641a4e1ec7f68e0a69e0a127c704030e4356c720ea7cd38968146` a `b3ac23b8c592f590e5f97ceecde69187c494f6fe07ca3481e72085f1d16d2d11`, que es el cambio esperado. Las otras cinco tablas del conjunto de integridad permanecieron vacías con SHA-256 `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`.

Las actividades `clinic_operational_domains.transitioned` quedaron registradas con IDs `10` y `11`, actor `37`, clínica `33`, sus respectivos `run_id` y totales 5 y 0.

## Integridad posterior

- Los productos IDs `1..5` conservan sus códigos `QA14A-P33-001..005` y `updated_at = 2026-09-05 01:14:10`.
- Los cinco productos tienen `clinic_id=33`; no quedan propietarios nulos o ajenos en las cuatro raíces.
- Ubicaciones, inventarios, movimientos, facturas, partidas de factura y pagos conservan cinco filas cada uno.
- Hay cero inconsistencias inventario-producto, inventario-ubicación y movimiento-inventario/producto.
- La membresía ID `28` continúa activa, activada y no suspendida; no se modificaron usuario, contraseña, clínica, membresía ni rol.
- La disponibilidad calculada es `inventory=true`, `billing=true`, `procurement=false`, `quotes=false`.

## Aislamiento y cierre seguro

- `Product`, `Supplier`, `Purchase` y `Quote` exponen relación `clinic()` y `scopeForClinic()`; `clinic_id` no es asignable masivamente.
- Sus cuatro Policies consultan solo permisos de la membresía clínica activa. Un recurso nulo o de otra clínica se oculta con `404`; un permiso insuficiente se deniega.
- Las rutas ejecutan `clinic.context` antes de disponibilidad y autorización; las rutas estáticas `create` preceden a los parámetros de modelo.
- Inventario, reportes y dashboard filtran explícitamente productos y proveedores por la clínica activa; exportación y movimientos restringen también las relaciones cargadas. Una regresión adicional demuestra que un `primarySupplier` cruzado no se carga en el inventario de otra clínica.
- Proveedores, compras y cotizaciones permanecen detrás de `clinic.domain.ready` y responden `503` de forma deliberada. Tener esquema y Policies no basta para publicar controladores heredados inseguros.

Cotizaciones presenta además una incompatibilidad concreta: la migración admite `draft`, `sent`, `accepted`, `rejected` y `expired`, mientras el modelo/controlador histórico trabaja con `pending` y `approved` y referencia campos de aprobación que no existen en esa tabla. No se maquilló ni se abrió esa superficie.

## Saneamiento de fixtures y regresión

- `StaffFactory` crea la identidad obligatoria y ofrece `forClinic()`.
- `PatientFactory` deja de depender de un usuario hardcodeado y ofrece `forClinic()`.
- `AppointmentFactory` crea paciente y profesional de la misma clínica mediante `forClinic()`.
- Los estados genéricos de cita usan nombres de prueba únicos sin colisionar con los estados canónicos.
- Las suites históricas de pacientes y citas recibieron únicamente contexto/membresía y fixtures coherentes; sus aserciones funcionales no se debilitaron ni se cambiaron para ocultar fallos.

Durante la primera corrida global apareció una regresión propia: las clausuras de eager loading en `InventoryExportRepository` estaban tipadas como `Builder`, aunque Laravel entrega una relación `BelongsTo`. Se corrigió el contrato también en `InventoryMovementRepository`; las 14 pruebas relacionadas pasaron después con 85 aserciones y la corrida global final dejó esas suites verdes.

## Pruebas finales

Todas las pruebas se ejecutaron en contenedores efímeros, con las dependencias ya instaladas reutilizadas en modo solo lectura y SQLite en memoria. No se instalaron paquetes ni se escribieron datos de prueba en MySQL real.

| Alcance | Resultado |
|---|---:|
| Contratos nuevos de propiedad/transición | 5 aprobadas, 0 fallos, 55 aserciones |
| Matriz focalizada final de 14C | 47 aprobadas, 0 fallos, 450 aserciones |
| Clínicas + inventario + facturación + pacientes | 169 aprobadas, 0 fallos, 1105 aserciones |
| Regresión global anterior (14B) | 207 aprobadas, 154 fallidas, 1189 aserciones |
| Regresión global final (14C) | 294 aprobadas, 74 fallidas, 1618 aserciones |

La mejora recuperó 80 pruebas heredadas: 21 unitarias de citas, 16 web de citas, 19 API de pacientes y 24 web de pacientes. Las siete pruebas adicionales corresponden a los contratos 14C nuevos o extendidos.

Distribución exacta de las 74 fallas residuales:

| Suite | Fallas | Causa representativa |
|---|---:|---|
| `Tests\\Unit\\AppointmentTest` | 1 | El cast `decimal:2` devuelve string correctamente, pero la prueba histórica exige float |
| `ApiSecurityTest` | 12 | Rate limiting, estados HTTP y auditoría de seguridad históricos no implementados |
| `AppointmentApiTest` | 18 | API de citas deliberadamente no publicada; respuestas `404/405` y dos errores derivados |
| `AppointmentTest` feature | 5 | Expectativas antiguas de contenido y redirección no alineadas con el flujo web actual |
| `PenetrationTest` | 6 | Contratos históricos de estados y logging de ataques |
| `SecurityMiddlewareTest` | 13 | Cabeceras, rate limiting y auditoría histórica |
| `SimpleSecurityTest` | 1 | Conteo histórico de auditoría no aislado |
| `TwoFactorAuthTest` | 18 | Rutas/flujo 2FA ausentes o incompatibles con el contexto actual |

El informe JUnit final privado está en `storage/app/private/mandate-14c-global-junit-final.xml`, tiene `300200` bytes y SHA-256 `E392F7E8D5E577899A7C80AE15EAAB54026632F2A37F1638749168C49A44F255`. Los nueve archivos PHP nuevos pasan el verificador de estilo; todo archivo PHP modificado pasa validación de sintaxis.

## Validación visual

No se modificó ningún Blade, CSS, JavaScript ni activo visual. La revisión en `http://localhost:8080` confirmó que el login real de Dentaris renderiza correctamente, no muestra accesos demostrativos y no produce errores de consola.

No había una sesión autenticada disponible para repetir de forma autónoma las vistas internas y no se inspeccionaron ni reutilizaron credenciales. Por tanto, la aprobación visual autenticada del Mandato 14B se conserva como antecedente de las vistas sin cambios, pero no se presenta como una nueva comprobación manual 14C. Los flujos internos afectados sí quedaron cubiertos por pruebas HTTP autenticadas; proveedores, compras y cotizaciones verifican respuesta controlada `503`.

## Restricciones y siguiente mandato

1. Mantener `clinic_id` nullable hasta definir una política explícita de obligatoriedad, colisiones y despliegue multiclínica.
2. No abrir proveedores, compras o cotizaciones hasta reconstruir sus controladores con Services, Repositories, Form Requests, relaciones clínicas, auditoría y vistas completas.
3. Revisar las alertas programadas y reportes API heredados antes de uso multiclínica; hoy contienen consultas globales y el modelo de notificaciones aún no tiene propietario clínico.
4. No declarar cerrada la Fase 1 mientras permanezcan las 74 fallas globales.

Siguiente trabajo recomendado: **Mandato 14D — reconstrucción segura de proveedores/compras/cotizaciones y saneamiento de alertas operativas**, manteniendo las superficies en `503` hasta que cada flujo tenga contrato clínico completo.
