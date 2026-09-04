# Matriz de trazabilidad por dominio Dentaris

Fecha de revisión: 2026-09-01.

Esta matriz cruza artefactos presentes en el repositorio. `Pendiente` significa que la existencia del archivo no demuestra por sí sola autorización, cobertura o corrección funcional.

| Dominio | Ruta web/API | Modelos principales | Persistencia identificada | Validación dedicada | Prueba específica |
|---|---|---|---|---|---|
| Pacientes | Web + API | `Patient`, `PatientContact`, `PatientDocument`, `PatientInsurance` | `create_patients_table` y relacionadas | `PatientRequest` | Sí: paciente web/API |
| Citas | Web; API histórica no publicada | `Appointment`, `AppointmentStatus`, `AppointmentReminder` | citas, estados, recordatorios y extensiones | `AppointmentRequest` limitado por clínica | Sí: integración clínica web; suite API histórica pendiente de saneamiento |
| Historias clínicas | Web | `MedicalRecord`, `MedicalDiagnosis`, `MedicalImage`, `DentalClinicalHistory` | historias, diagnósticos, imágenes y clínica dental | `MedicalRecordRequest` con relación cita-paciente-personal | Sí: `ClinicalAppointmentsMedicalRecordsIntegrationTest` |
| Planes dentales | Web | `TreatmentPlan`, `TreatmentPlanItem`, `DentalTreatmentPlan`, `DentalProcedure`, `DentalOdontogram`, `DentalPeriodontogram` | planes, procedimientos, odontograma y periodontograma | Pendiente | Pendiente |
| Inventario | Web | `Inventory`, `Product`, `Supplier` | inventario, productos y proveedores | Pendiente | Pendiente |
| Facturación/pagos | Web | `Invoice`, `InvoiceItem`, `Payment`, `PaymentPlan`, `AccountsReceivable`, `DailyCash` | facturas, pagos, planes y caja | Pendiente | Pendiente |
| Compras/cotizaciones | Web | `Purchase`, `PurchaseItem`, `Quote`, `QuoteItem` | compras, partidas, cotizaciones y partidas | Pendiente | Pendiente |
| Laboratorio | Web | `LabWork`, `LabWorkItem`, `DentalLab`, `Prosthesis` | trabajos, partidas, laboratorios y prótesis | Pendiente | Pendiente |
| Personal/usuarios | Web | `User`, `Staff`, `StaffSchedule`, `StaffCredential`, `Role` | usuarios, roles, personal y credenciales | Auth Requests solamente | Seguridad general; cobertura de dominio pendiente |
| Reportes | Web + API | `Report`, `ReportTemplate`, `Kpi` | reportes, plantillas y KPIs | Pendiente | Cobertura indirecta pendiente |
| Notificaciones | Web | `Notification`, `NotificationQueue`, `NotificationLog`, `NotificationTemplate` | notificaciones, colas, registros y plantillas | Validator inline | Pendiente |

## Brechas de trazabilidad

1. La mayoría de dominios no tiene un Form Request dedicado; citas e historias clínicas ya constituyen excepciones verificadas.
2. La mayoría de dominios no tiene una prueba identificable por nombre; citas, historias, pacientes y personal ya tienen cobertura clínica focalizada.
3. No hay Repositories ni Policies que permitan cerrar el vínculo entre consulta compleja, autorización y modelo.
4. La relación entre rutas duplicadas de `web.php`, controladores y permisos debe resolverse antes de extraer módulos.

## Próximo paso verificable

Construir un inventario de rutas con método, URI, nombre, controlador, middleware y permiso, y contrastarlo con pruebas existentes. No se debe mover código a módulos ni crear nuevas funcionalidades hasta aprobar esa matriz.
