# Inventario verificable de dominios funcionales Dentaris

Fecha de revisión: 2026-09-01.

Este inventario describe la superficie existente sin afirmar que esté lista para producción. La implementación funcional nueva queda fuera de esta fase.

| Dominio | Controlador principal | Vistas Blade | Pruebas identificadas | Estado documental |
|---|---|---:|---|---|
| Pacientes | `PatientController` | 6 | `PatientTest`, `PatientApiTest` | Existe superficie web y API; falta mapa completo de permisos y servicios |
| Citas | `AppointmentController` | 7 | `AppointmentTest`, `AppointmentApiTest` | Existe web/API; revisar validación inline y duplicidad de rutas |
| Historias clínicas | `MedicalRecordController` | 3 | No específica | Requiere contrato de datos sensibles, auditoría y almacenamiento |
| Planes dentales | `DentalTreatmentPlanController`, `TreatmentPlanController` | 9 combinadas | No específica | Hay dos superficies relacionadas; definir límites y evitar solapamiento |
| Inventario | `InventoryController`, `ProductController`, `SupplierController` | 3 combinadas | No específica | Validar separación producto/inventario/proveedor y permisos |
| Facturación y pagos | `BillingController`, `PaymentController`, `PurchaseController`, `QuoteController` | 4 combinadas | No específica | Requiere trazabilidad financiera y pruebas de mutaciones |
| Laboratorio | `LabController`, `LabWorkController` | 1 | No específica | Revisar alcance entre laboratorio y trabajos de laboratorio |
| Personal y usuarios | `StaffController`, `UserController`, `ProfileController` | 7 combinadas | Seguridad general | Validar roles, permisos y datos personales |
| Reportes | `ReportController` y `ReportApiController` | 1 | Seguridad general | Alinear web/API y comprobar consultas complejas |
| Notificaciones | `NotificationController` | 1 | No específica | Validar colas, canales y permisos |
| Seguridad y autenticación | `AuthController`, `Auth/*`, `TwoFactorAuthController` | `auth/*` | 6 pruebas de seguridad | Consolidar contrato de autenticación, 2FA y auditoría |

## Cobertura observable

- Hay 28 controladores principales y un controlador de tratamiento respaldado (`TreatmentPlanController.php.backup`) que requiere decisión documental antes de eliminar o recuperar.
- Hay 66 vistas Blade distribuidas por dominio, más layouts y componentes globales.
- Las pruebas se concentran en pacientes, citas, autenticación y seguridad; la mayoría de dominios administrativos no tiene una prueba específica identificable por nombre.
- La siguiente revisión debe cruzar cada fila con rutas concretas, modelos, migraciones, Form Requests, permisos, servicios y consultas.

## Criterios de aceptación

1. Cada dominio tiene un responsable de entrada HTTP y un límite de datos documentado.
2. Cada operación de escritura tiene validación, autorización, auditoría y prueba identificables.
3. Las vistas sensibles no exponen documentos clínicos desde almacenamiento público.
4. Los controladores respaldados o duplicados tienen decisión explícita y no se eliminan durante el inventario.
