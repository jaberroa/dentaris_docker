# Inventario verificable de dominios funcionales Dentaris

Fecha de revisión: 2026-09-05.

Este inventario describe la superficie existente sin afirmar que esté lista para producción. Los estados reflejan evidencia acumulada hasta el Mandato 14B de la Fase 1.

| Dominio | Controlador principal | Vistas Blade | Pruebas identificadas | Estado documental |
|---|---|---:|---|---|
| Pacientes | `PatientController` | 6 | `PatientTest`, `PatientApiTest` | Existe superficie web y API; falta mapa completo de permisos y servicios |
| Citas | `AppointmentController` | 7 | `AppointmentTest`, `AppointmentApiTest`, `ClinicalAppointmentsMedicalRecordsIntegrationTest` | Web integrada a `ClinicContext` con Form Request y aislamiento; el controlador API histórico no tiene rutas publicadas y sus pruebas requieren saneamiento |
| Historias clínicas | `MedicalRecordController` | 3 | `ClinicalAppointmentsMedicalRecordsIntegrationTest` | Web integrada a `ClinicContext`, Form Request, relaciones cruzadas validadas y actividad auditable; exportación PDF aún no implementada |
| Planes dentales | `DentalTreatmentPlanController`, `TreatmentPlanController` | 9 combinadas | No específica | Hay dos superficies relacionadas; definir límites y evitar solapamiento |
| Inventario | `InventoryController`, `InventoryLocationController`; `ProductController` y `SupplierController` heredados | 6 identificadas, incluida una parcial | `InventoryBillingClinicalIsolationTest`, `ClinicOwnedDomainTransitionTest` y 7 suites HTTP/servicio históricas | Contexto, permisos, consultas, movimientos, exportación y caché aislados; migración y transición 14B verificadas, con 5 registros QA visibles |
| Facturación y pagos | `BillingController`, `PaymentController`; `PurchaseController` y `QuoteController` heredados | 6 identificadas | `BillingLifecycleTest`, `InventoryBillingClinicalIsolationTest`, `ClinicOwnedDomainQaFixtureTest` | Facturas/pagos aislados; permisos clínicos habilitados y 5 facturas + 5 pagos QA idempotentes |
| Laboratorio | `LabController`, `LabWorkController` | 1 | No específica | Revisar alcance entre laboratorio y trabajos de laboratorio |
| Personal y usuarios | `StaffController`, `UserController`, `ProfileController` | 7 combinadas | Seguridad general | Validar roles, permisos y datos personales |
| Reportes | `ReportController` y `ReportApiController` | 1 | Seguridad general | Alinear web/API y comprobar consultas complejas |
| Notificaciones | `NotificationController` | 1 | No específica | Validar colas, canales y permisos |
| Seguridad y autenticación | `AuthController`, `Auth/*`, `TwoFactorAuthController` | `auth/*` | 6 pruebas de seguridad | Consolidar contrato de autenticación, 2FA y auditoría |

## Cobertura observable

- Hay 28 controladores principales y un controlador de tratamiento respaldado (`TreatmentPlanController.php.backup`) que requiere decisión documental antes de eliminar o recuperar.
- Hay 66 vistas Blade distribuidas por dominio, más layouts y componentes globales.
- Las pruebas clínicas cubren pacientes, personal, citas, historias, selector, inventario, facturación y pagos. Otros dominios administrativos aún no tienen una prueba específica identificable por nombre.
- La siguiente revisión debe cruzar cada fila con rutas concretas, modelos, migraciones, Form Requests, permisos, servicios y consultas.
- El selector clínico web solo expone membresías activas y activadas de usuarios y clínicas activos; el valor persistido en sesión se revalida en cada solicitud.
- Inventario, facturación y pagos tienen `clinic_id` nullable desplegado y datos QA íntegros para `DEN-CL-001`; continúan fallando cerrados si aparece una fila nula o inconsistente.
- Proveedores y compras ahora fallan cerrados mediante el dominio `procurement`; no se consideran globales ni se abren hasta definir propiedad clínica para proveedores, productos y compras.

## Criterios de aceptación

1. Cada dominio tiene un responsable de entrada HTTP y un límite de datos documentado.
2. Cada operación de escritura tiene validación, autorización, auditoría y prueba identificables.
3. Las vistas sensibles no exponen documentos clínicos desde almacenamiento público.
4. Los controladores respaldados o duplicados tienen decisión explícita y no se eliminan durante el inventario.

## Restricciones verificadas hasta el Mandato 14B

- `ProductController` y la conversión de cotización a factura permanecen como superficies heredadas no publicadas; no se habilitaron ni se usaron como vía alternativa.
- Algunas rutas secundarias de inventario/reportes y las acciones visuales de pago no tienen todas sus vistas Blade. Deben auditarse antes de exponerlas en navegación.
- La migración `2026_09_03_000000_add_clinic_ownership_to_inventory_and_billing_tables.php` fue aplicada de forma autorizada y conserva columnas nullable; proveedores/compras requieren un contrato separado.

## Evidencia del Mandato 14A

- El dry-run real encontró cero filas en las cinco tablas objetivo y las cinco columnas `clinic_id` ausentes.
- Los comandos `clinics:transition-owned-domains` y `clinics:create-owned-domain-qa` son dry-run por defecto, exigen actor/membresía clínica y solo escriben con `--execute`.
- La transición conserva hashes de integridad, bloquea ambigüedad y es idempotente; la carga QA exige un mínimo de cinco registros por vista y usa marcadores `QA14A-*`.
- En el Mandato 14A no se crearon datos QA porque el esquema todavía no estaba disponible; proveedores/compras tampoco tenían contrato de propiedad clínica.

## Seguimiento del Mandato 14B

- Se respaldó MySQL, se aplicó exclusivamente la migración nullable y se verificaron cinco claves foráneas `RESTRICT` y seis índices clínicos.
- La transición se ejecutó dos veces con cero actualizaciones, porque las tablas estaban vacías; los hashes y la auditoría confirmaron idempotencia.
- El rol clínico recibió `view_payments` y `manage_payments` de forma trazable, sin crear una autoridad global.
- La primera carga creó cinco registros `QA/PRUEBA` por vista de inventario, facturación y pagos; la segunda creó cero y conservó los IDs.
- La evidencia detallada está en `PHASE-1-MANDATE-14B-DATA-ENABLEMENT-AND-VISUAL-VALIDATION-DENTARIS.md`.
