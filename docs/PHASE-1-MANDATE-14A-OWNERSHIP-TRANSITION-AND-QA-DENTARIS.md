# Fase 1 — Mandato 14A: transición de propiedad y datos QA

Fecha de ejecución: 2026-09-04.

## Dictamen

**APROBADO CON RESTRICCIONES para publicación del mecanismo; RECHAZADO para ejecutar la transición real y cerrar el mandato.**

El inventario, los controles, los comandos idempotentes, las pruebas de aislamiento y el cierre seguro de proveedores/compras están implementados. La base MySQL real no puede recibir el backfill ni los datos QA porque las cinco columnas `clinic_id` continúan ausentes y la migración correspondiente permanece pendiente. El mandato prohíbe ejecutar migraciones, por lo que no se realizó ningún cambio de esquema ni de datos.

La Fase 1 continúa abierta.

## Estado previo verificable

- Base de datos: `dentaris`.
- Clínica objetivo: ID `33`, código `DEN-CL-001`, activa.
- Actor: usuario ID `37`, activo.
- Membresía: ID `28`, estado `active`, `activated_at` informado y `suspended_at` nulo.
- Rol clínico: `clinic-admin`; los permisos proceden de la membresía, no de roles globales.
- `main` y `origin/main` estaban en `c8fceb9` al iniciar; Windows estaba limpio.
- WSL conservaba únicamente artefactos de referencia Clivax/Clinipro no rastreados, que no se tocaron.

### Tablas objetivo

| Tabla | Filas antes | `clinic_id` en MySQL | Hash no sensible antes |
|---|---:|---:|---|
| `inventory_locations` | 0 | No | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `inventory` | 0 | No | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `inventory_movements` | 0 | No | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `invoices` | 0 | No | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |
| `payments` | 0 | No | `e3b0c44298fc1c149afbf4c8996fb92427ae41e4649b934ca495991b7852b855` |

También se verificaron cero filas en `suppliers`, `products`, `purchases`, `purchase_items` e `invoice_items`.

La migración `2026_09_03_000000_add_clinic_ownership_to_inventory_and_billing_tables.php` figura como `Pending`. No se ejecutó ni se modificó.

## Transición controlada implementada

El comando:

```text
php artisan clinics:transition-owned-domains --clinic-code=DEN-CL-001 --actor-email=<actor>
```

es una vista previa sin escritura. Solo `--execute` habilita la transacción.

Controles incorporados:

1. Resuelve clínica por código estable y exige que esté activa.
2. Exige actor activo, membresía activa, `activated_at` no nulo y `suspended_at` nulo.
3. Exige `manage_inventory` y `manage_billing` desde los roles de la membresía clínica.
4. Verifica las cinco columnas de propiedad antes de consultar o escribir `clinic_id`.
5. Bloquea ubicaciones históricas sin una relación inequívoca; una única clínica disponible no se considera evidencia suficiente.
6. Asigna inventario solo desde una ubicación ya propietaria de la clínica.
7. Asigna movimientos solo desde su inventario y exige coincidencia de producto.
8. Asigna facturas solo cuando paciente y personal ya pertenecen a la misma clínica objetivo.
9. Asigna pagos solo cuando factura, paciente y propiedad clínica coinciden.
10. Actualiza únicamente `clinic_id`, sin ejecutar actualizaciones Eloquent ni tocar `updated_at`.
11. Bloquea toda la transacción si existe una fila ambigua o una relación rota/cruzada.
12. Calcula hashes separados de integridad —sin `clinic_id`— y propiedad —con `clinic_id`—; confirma que la integridad permanezca idéntica.
13. Registra `run_id`, actor, clínica, conteos, hashes y filas actualizadas; la auditoría estructurada participa en la misma transacción.
14. Una segunda ejecución produce cero actualizaciones.

Dry-run real: `493ce560-dd92-4e01-82cc-a05f509b7504`. Resultado: cinco tablas con cero filas, cinco columnas ausentes y error controlado `missing_clinic_ownership_schema`. No hubo escritura.

## Datos QA preparados

El comando:

```text
php artisan clinics:create-owned-domain-qa --clinic-code=DEN-CL-001 --actor-email=<actor> --count=5
```

es una vista previa. Solo `--execute` crea datos y exige entre 5 y 25 registros por vista.

La ejecución idempotente preparada crea, en una única transacción:

- 5 ubicaciones, 5 productos, 5 inventarios y 5 movimientos;
- 5 facturas, 5 renglones y 5 pagos;
- un paciente, un perfil de personal y un procedimiento de soporte, solo si no existen los marcadores QA;
- marcas estables `QA14A-*` y textos `QA/PRUEBA`, sin datos personales reales.

No crea ni modifica usuarios, credenciales, clínicas, sedes, membresías o roles. No incluye operación de borrado.

### Resultado real de datos QA

| Superficie | Planeados | Creados en MySQL | Motivo |
|---|---:|---:|---|
| Inventario | 5 | 0 | Falta esquema `clinic_id` |
| Facturación | 5 | 0 | Falta esquema `clinic_id` |
| Pagos | 5 | 0 | Falta esquema y permisos del actor |
| Proveedores | 0 | 0 | No existe contrato de propiedad clínica |
| Compras | 0 | 0 | No existe contrato de propiedad clínica |

El actor visual tiene `view_inventory`, `manage_inventory`, `view_billing` y `manage_billing`; no tiene `view_payments` ni `manage_payments`. La carga QA falla cerrada mientras falten esos permisos.

## Proveedores y compras

`suppliers`, `products` y `purchases` no tienen `clinic_id`. Además, `purchase_items` solo hereda relaciones de compra/producto. No existe evidencia para declararlos catálogos globales ni para asignar una clínica automáticamente.

Sus rutas ahora exigen, en este orden:

1. autenticación y selección clínica;
2. `clinic.context`;
3. `clinic.domain.ready:procurement`;
4. permisos `view_inventory` o `manage_inventory` desde la membresía.

El dominio `procurement` responde `503` de forma controlada mientras no exista un contrato separado de propiedad para proveedores, productos y compras. Así no quedan accesibles mediante los Gates globales heredados.

## Evidencia de pruebas

Las pruebas se ejecutaron en Docker montando la copia Windows, con SQLite en memoria, sin tocar MySQL real.

| Alcance | Resultado |
|---|---:|
| Transición y carga QA nuevas | 6 aprobadas, 53 aserciones |
| Suite focalizada de propiedad/rutas/aislamiento | 27 aprobadas, 349 aserciones |
| Clínicas + regresión histórica de inventario/facturación | 113 aprobadas, 778 aserciones |
| Regresión global | 202 aprobadas, 154 fallidas |

La prueba de rollback demuestra que una ubicación ambigua impide todas las actualizaciones candidatas y no genera auditoría de éxito. Los fallos globales siguen siendo deuda heredada ya clasificada: factories de personal sin `user_id`, suites antiguas sin contexto/permisos clínicos, expectativas de seguridad/API no implementadas y rutas 2FA ausentes. Las suites focalizadas afectadas permanecen verdes; no se modificaron pruebas para esconder fallos.

Persisten advertencias de PHPUnit por metadatos `@test` en comentarios y no existe driver de cobertura.

## Validación visual

No se abrió inventario, facturación ni pagos con datos artificiales porque MySQL sigue sin las columnas requeridas. El resultado visual válido continúa siendo el `503 Módulo en preparación clínica` del Mandato 14.

Después de una autorización separada para ejecutar la migración pendiente y ajustar los dos permisos clínicos de pagos, deben ejecutarse la transición y la carga QA, sincronizar WSL y verificar manualmente:

1. Inventario: cinco filas `QA/PRUEBA 14A Insumo 001..005`.
2. Movimientos: cinco movimientos `QA/PRUEBA Mandato 14A 001..005`.
3. Ubicaciones: cinco ubicaciones `QA/PRUEBA 14A`.
4. Facturación: cinco facturas `QA14A-I33-001..005`.
5. Pagos: cinco pagos `QA14A-Y33-001..005`.
6. Búsqueda, detalle y exportación sin filas de otra clínica.
7. Proveedores y compras: `503` controlado hasta un contrato independiente.

## Archivos del mandato

- `app/Modules/Clinics/Services/ClinicOwnedDomainTransitionService.php`
- `app/Modules/Clinics/Services/ClinicOwnedDomainQaFixtureService.php`
- `app/Console/Commands/TransitionClinicOwnedDomains.php`
- `app/Console/Commands/CreateClinicOwnedDomainQaFixtures.php`
- `app/Modules/Clinics/Services/ClinicOwnedDomainReadinessService.php`
- `routes/web.php`
- `tests/Feature/Clinics/ClinicOwnedDomainTransitionTest.php`
- `tests/Feature/Clinics/ClinicOwnedDomainQaFixtureTest.php`
- `tests/Feature/Clinics/ClinicOwnedDomainReadinessTest.php`
- `tests/Feature/Clinics/ClinicalRouteContractTest.php`

## Riesgos y bloqueos

1. La migración de propiedad está pendiente y el mandato actual prohíbe ejecutarla.
2. El actor visual no tiene los dos permisos clínicos de pagos.
3. Proveedores, productos y compras necesitan una decisión de propiedad y un mandato de esquema separado.
4. La regresión global mantiene 154 fallos heredados; no son atribuibles a las suites focalizadas verdes, pero impiden declarar la Fase 1 cerrada.
5. Los datos QA autorizados no fueron creados porque hacerlo sin esquema produciría relaciones inválidas.

## Siguiente mandato recomendado

**Mandato 14B — ejecución autorizada de la migración nullable y habilitación QA.** Debe respaldar la base, aplicar exclusivamente la migración ya revisada, verificar esquema/FK/índices, conceder de forma trazable `view_payments` y `manage_payments` al rol clínico autorizado, ejecutar dry-run, transición, segunda ejecución idempotente, carga QA, segunda carga idempotente y validación visual.

Proveedores/compras deben permanecer cerrados hasta un mandato posterior que decida si son catálogos globales o entidades propiedad de clínica.

Modelo recomendado: `gpt-5.6-sol`; esfuerzo `high`, elevable a `xhigh` si aparecen filas históricas o más de una clínica candidata.
