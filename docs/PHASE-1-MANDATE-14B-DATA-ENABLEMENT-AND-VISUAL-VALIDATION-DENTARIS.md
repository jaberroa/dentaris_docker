# Fase 1 — Mandato 14B: habilitación de datos y validación visual

Fecha de ejecución: 2026-09-04; cierre documental: 2026-09-05, zona horaria `America/Caracas`. Los registros de auditoría de MySQL usan UTC y quedaron fechados el 2026-09-05.

## Dictamen

**APROBADO CON RESTRICCIONES.** La identidad administrativa existente fue preservada, la migración nullable autorizada quedó aplicada, la propiedad clínica de inventario/facturación/pagos quedó verificada, los permisos clínicos de pagos se añadieron con auditoría y la carga QA produjo cinco registros visibles por superficie en su primera ejecución y cero nuevos en la segunda.

La Fase 1 continúa abierta por dos restricciones conocidas:

1. proveedores y compras permanecen cerrados con `503` hasta que un mandato separado defina la propiedad de `suppliers`, `products`, `purchases` y `purchase_items`;
2. la suite global mantiene 154 fallos heredados fuera de las suites focalizadas verdes.

La comprobación visual autenticada fue ejecutada manualmente por el usuario en `http://localhost:8080` y confirmada como aprobada. No se publicó ni se registró la contraseña.

## Identidad administrativa y acceso

- Se confirmó una única cuenta administrativa existente: usuario ID `37`, activa y verificada.
- La credencial autorizada ya coincidía con el hash almacenado; por ello no se actualizó la contraseña.
- La cuenta, su ID, nombre, correo, estado y hash se conservaron. No se eliminó, reemplazó ni recreó el usuario.
- La membresía ID `28` continúa activa y activada, sin suspensión, para la clínica ID `33`, código `DEN-CL-001`.
- El rol procede de la membresía clínica `clinic-admin` ID `20`; no existe asignación global que sustituya esa autoridad.
- Se retiraron del login y de las vistas de error los accesos rápidos y credenciales demostrativas publicadas.
- `UserSeeder`, `DemoDataSeeder` y `DoctorUsersSeeder` ya no sobrescriben identidades existentes ni generan contraseñas predecibles. Los comandos auxiliares tampoco imprimen contraseñas ni incorporan una clave fija.

La prueba `AdminLoginSecurityTest` demuestra que el formulario no publica cuentas, una identidad activa y verificada puede autenticarse sin ser recreada y los seeders preservan ID, nombre y hash de usuarios existentes.

## Respaldo y precondiciones

Antes de cualquier cambio de esquema o datos se generó un respaldo SQL completo en almacenamiento privado:

- archivo: `storage/app/private/backups/dentaris-pre-mandate-14b-20260904-203419.sql`;
- tamaño: `140226` bytes;
- SHA-256: `F9036B53D2C03050A19B7189BF09DA90D3E09F142F0B042099C6706177FA34A0`;
- marca final de volcado: confirmada.

El respaldo está ignorado por Git y no se inspeccionó ni publicó su contenido sensible.

Precondiciones confirmadas antes de migrar:

| Elemento | Resultado |
|---|---:|
| Usuarios | 1 |
| Clínicas | 1 |
| Membresías | 1 |
| Filas en cada tabla objetivo | 0 |
| Proveedores, productos, compras, partidas de compra y partidas de factura | 0 |
| Migraciones pendientes | solo `2026_09_03_000000_add_clinic_ownership_to_inventory_and_billing_tables.php` |

El dry-run previo `a0171c8b-6cde-44e9-98bb-391aeab8368c` confirmó que el esquema aún no estaba disponible, sin escribir datos. El archivo de migración fue idéntico en Windows y WSL, con SHA-256 `7a5fa1cc30ddb7095c89f1681644d6717bf23e3f98ec6d8d85f543a6f2c176be`.

## Migración aplicada

Se simuló y luego se aplicó exclusivamente:

```text
database/migrations/2026_09_03_000000_add_clinic_ownership_to_inventory_and_billing_tables.php
```

Quedó registrada en el lote `2`. No se ejecutó ninguna otra migración. Las cinco columnas `clinic_id` son `BIGINT UNSIGNED`, admiten nulo y tienen clave foránea a `clinics.id` con borrado `RESTRICT`.

| Tabla | Clave foránea | Índice clínico verificado |
|---|---|---|
| `inventory_locations` | `inventory_locations_clinic_id_foreign` | `inv_locations_clinic_active_idx (clinic_id, is_active)` |
| `inventory` | `inventory_clinic_id_foreign` | `inventory_clinic_product_idx (clinic_id, product_id)`; `inventory_clinic_location_idx (clinic_id, inventory_location_id)` |
| `inventory_movements` | `inventory_movements_clinic_id_foreign` | `inv_movements_clinic_inventory_idx (clinic_id, inventory_id, created_at)` |
| `invoices` | `invoices_clinic_id_foreign` | `invoices_clinic_date_status_idx (clinic_id, invoice_date, status)` |
| `payments` | `payments_clinic_id_foreign` | `payments_clinic_date_status_idx (clinic_id, payment_date, status)` |

El dry-run posterior `2d6a3de7-8379-42be-b5d1-89a9160140ac` confirmó el esquema completo, cero candidatos y cero errores.

## Permisos clínicos de pagos

En una transacción con bloqueo y validaciones estrictas se añadieron únicamente:

- `view_payments`;
- `manage_payments`.

El cambio se aplicó al rol clínico `clinic-admin` ID `20` vinculado a la membresía ID `28`, no a un rol global. Se preservaron todos los permisos anteriores.

| Ejecución | Permisos añadidos | Hash anterior | Hash posterior |
|---|---:|---|---|
| Primera | 2 | `0bd00aae0779526fc9155c2ab21119adee74626426a727b5a760b4bb27428dc8` | `133e3fa8e3eec617479a10c0d527473f719115aab4b29dd970b6784f7c832546` |
| Segunda | 0 | `133e3fa8e3eec617479a10c0d527473f719115aab4b29dd970b6784f7c832546` | `133e3fa8e3eec617479a10c0d527473f719115aab4b29dd970b6784f7c832546` |

La actividad `clinic_role.payment_permissions_granted`, ID `4`, registra mandato, actor, clínica, membresía, rol, permisos añadidos y hashes. El resultado final confirma ambos permisos activos y cero asignaciones globales.

## Transición de propiedad ejecutada dos veces

La transición se ejecutó dentro de transacción y con auditoría usando la clínica y membresía validadas:

| Ejecución | `run_id` | Actualizaciones |
|---|---|---:|
| Primera | `d2b66343-a09e-407a-bf4e-c4b09a2ece89` | 0 |
| Segunda | `b04212ec-385d-4a3c-ba89-a7468fd0fe01` | 0 |

El resultado cero es el esperado: las cinco tablas estaban vacías antes de la carga QA. En ambas ejecuciones los hashes de integridad y propiedad permanecieron en el SHA-256 de conjunto vacío `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855`, sin ambigüedades, relaciones cruzadas ni escrituras parciales. Las actividades de auditoría son ID `5` e ID `6`.

## Datos QA idempotentes

La carga QA se ejecutó dos veces con los marcadores estables `QA14A-*` y la etiqueta visible `QA/PRUEBA`:

| Ejecución | `run_id` | Registros creados | Resultado visible |
|---|---|---:|---|
| Primera | `b3adf8b2-3d4a-4529-b834-88b3d533c7e1` | 38 | 5 inventarios, 5 facturas y 5 pagos |
| Segunda | `7c3470a1-080c-40f1-9f83-ac4503037c09` | 0 | los mismos registros e IDs |

La primera ejecución creó cinco ubicaciones, productos, inventarios, movimientos, facturas, partidas y pagos; además creó un paciente, un perfil de personal y un procedimiento de soporte. La segunda ejecución conservó exactamente los mismos IDs:

- ubicaciones, productos, inventarios, movimientos, facturas, partidas y pagos: IDs `1..5`;
- paciente y personal de soporte: ID `8`;
- procedimiento de soporte: ID `1`.

Las actividades de auditoría son ID `7` e ID `8`. Ninguna ejecución creó o modificó usuarios, credenciales, clínicas, membresías o roles.

## Integridad posterior

El dry-run final `c15cf783-d9bd-4d60-bbe6-e94a32091b4b` verificó cinco filas totales y cinco filas de la clínica objetivo en cada tabla propietaria, con cero nulos, cero propietarios ajenos y cero relaciones inconsistentes.

| Tabla | SHA-256 de integridad | SHA-256 con propiedad |
|---|---|---|
| `inventory_locations` | `7d86e956322777bf18ebe60c06bdfba7802086a385e282b9ce2110047df02f54` | `0e1d60c358d0c97c9cbf91c41e48952cd6e6f5f1b4e3a265b5a443939ccdb18c` |
| `inventory` | `aa893367d401643225d7ded0b8dfd18b9e8921ffb432126ce8bdcbb5e46e646e` | `b4cf83135b09d3c8944f3e81859bc152714fc361b97596d14c176b9ab8f61be4` |
| `inventory_movements` | `fc936f8b53a942a54829f6833772758040ab1549958d18d61c2085aa1333f4db` | `79228adfaedf57a6f5cfc0c8377271866dda4d0553a4956f5ed6183b3236c628` |
| `invoices` | `5c1f645aac11d159f5bd22c17f6661cbb2f0032151b3b4e081758b4cfee11acd` | `83b5a7f21aac1093a3dbaa53184dc8f74afd70bfe65c3a4fb51973015f6456b8` |
| `payments` | `6f10d60b779b760aad6eaf52afa07864d7bc21f227b9089e93e81f8e71cf332c` | `a08bd30a834816e759796e0897d962dcf8ca8978851d1ba1392eeef328c66651` |

## Aislamiento y cierres seguros

- Inventario, ubicaciones, movimientos, facturas y pagos parten de `ClinicContext` validado y no aceptan `clinic_id` del cliente como autoridad.
- Listados, búsquedas, detalle, exportación y escrituras están limitados por clínica.
- Las relaciones cruzadas se rechazan; un recurso de otra clínica se oculta con `404` y un permiso insuficiente responde `403`.
- La ausencia de esquema, propietario o coherencia referencial responde `503` antes de consultar globalmente.
- Proveedores y compras continúan detrás de `clinic.domain.ready:procurement` y responden `503` de forma controlada. No se declararon catálogos globales ni se inventó una propiedad clínica.

## Pruebas finales

Las pruebas se ejecutaron en una copia aislada que cargó sus propias clases, con SQLite en memoria y sin modificar MySQL real.

| Alcance | Resultado |
|---|---:|
| Clínicas + acceso + inventario + facturación | 117 aprobadas, 0 fallos, 806 aserciones |
| Regresión global | 207 aprobadas, 154 fallidas, 1189 aserciones |

El registro global privado está en `storage/app/private/mandate-14b/global-tests-final.txt`, tiene `229442` bytes y SHA-256 `7E91D2A51933EC5AD7CA29204D47BC0789F7271FE4E7143A02101E5E5432427C`.

Distribución de los 154 fallos heredados:

| Suite | Fallos | Causa representativa |
|---|---:|---|
| `AppointmentTest` unitario | 22 | factory histórica de personal sin `user_id` obligatorio |
| `AppointmentApiTest` | 18 | misma precondición histórica |
| `AppointmentTest` feature | 21 | misma precondición histórica |
| `PatientApiTest` | 19 | expectativas antiguas sin contexto clínico |
| `PatientTest` | 24 | expectativas antiguas sin contexto clínico |
| `ApiSecurityTest` | 12 | contrato histórico de rate limiting y seguridad no implementado |
| `PenetrationTest` | 6 | estados HTTP históricos incompatibles |
| `SecurityMiddlewareTest` | 13 | contrato histórico de middleware |
| `SimpleSecurityTest` | 1 | conteo histórico de auditoría |
| `TwoFactorAuthTest` | 18 | rutas/flujo 2FA ausentes y contexto clínico no preparado |

No se alteraron aserciones ni controles para ocultar esa deuda. Las 117 pruebas que cubren el mandato permanecen verdes.

## Validación visual

Estado: **APROBADA mediante validación manual del usuario** en `http://localhost:8080`.

| Superficie | Resultado confirmado |
|---|---|
| Login real | Cuenta administrativa existente autenticó correctamente; la contraseña no fue compartida |
| Selector | Clínica `DEN-CL-001` seleccionable y contexto activo |
| Inventario | 5 insumos `QA/PRUEBA 14A Insumo 001..005` visibles |
| Ubicaciones | 5 ubicaciones QA visibles |
| Movimientos | 5 movimientos QA visibles |
| Facturación | 5 facturas `QA14A-I33-001..005` visibles |
| Pagos | 5 filas, IDs `1..5`, accesibles sin `403` ni `503` |
| Cierre `403` | Respuesta controlada, sin traza ni error `500` |
| Cierre `404` | Recurso inexistente oculto de forma controlada |
| Cierre `503` | Proveedores y compras muestran `Módulo en preparación clínica` |
| Presentación | Recursos visuales cargados y páginas sin pérdida de estilos |

La primera vista previa desechable en el puerto `8083` no se aceptó como evidencia visual porque el checkout Windows no contiene los artefactos compilados de `public/build`; las solicitudes de CSS devolvían HTML. Se descartó ese resultado y la validación se repitió en el runtime oficial del puerto `8080`, cuyo CSS respondió correctamente.

## Restricciones y siguiente mandato

1. No convertir `clinic_id` en obligatorio hasta completar una política explícita de datos y despliegue.
2. No abrir proveedores o compras hasta definir propiedad, migración, backfill, permisos, auditoría y pruebas.
3. No declarar cerrada la Fase 1 mientras permanezcan los 154 fallos globales o flujos clínicos principales sin saneamiento.

Siguiente trabajo recomendado: **Mandato 14C — contrato de propiedad clínica de proveedores/compras y saneamiento priorizado de la regresión global**, empezando por las factories de citas y las suites de pacientes que aún omiten contexto.

Modelo recomendado: `gpt-5.6-sol`; esfuerzo `xhigh`, por combinar decisión de arquitectura de datos, migración segura, aislamiento multiclínica y reparación de deuda transversal.
