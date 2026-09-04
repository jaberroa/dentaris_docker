# Matriz de trazabilidad por dominio Dentaris

Fecha de revisión: 2026-09-04.

Esta matriz cruza artefactos presentes en el repositorio. `Pendiente` significa que la existencia del archivo no demuestra por sí sola autorización, cobertura o corrección funcional.

| Dominio | Ruta web/API | Modelos principales | Persistencia identificada | Validación dedicada | Prueba específica |
|---|---|---|---|---|---|
| Pacientes | Web + API | `Patient`, `PatientContact`, `PatientDocument`, `PatientInsurance` | `create_patients_table` y relacionadas | `PatientRequest` | Sí: paciente web/API |
| Citas | Web; API histórica no publicada | `Appointment`, `AppointmentStatus`, `AppointmentReminder` | citas, estados, recordatorios y extensiones | `AppointmentRequest` limitado por clínica | Sí: integración clínica web; suite API histórica pendiente de saneamiento |
| Historias clínicas | Web | `MedicalRecord`, `MedicalDiagnosis`, `MedicalImage`, `DentalClinicalHistory` | historias, diagnósticos, imágenes y clínica dental | `MedicalRecordRequest` con relación cita-paciente-personal | Sí: `ClinicalAppointmentsMedicalRecordsIntegrationTest` |
| Planes dentales | Web | `TreatmentPlan`, `TreatmentPlanItem`, `DentalTreatmentPlan`, `DentalProcedure`, `DentalOdontogram`, `DentalPeriodontogram` | planes, procedimientos, odontograma y periodontograma | Pendiente | Pendiente |
| Inventario | Web | `Inventory`, `InventoryLocation`, `InventoryMovement`, `Product`, `Supplier` | inventario, ubicaciones, movimientos, productos y proveedores; propiedad `clinic_id` definida como nullable y pendiente de despliegue/backfill | Form Requests de ubicación, ajuste, transferencia y exportación; `InventoryPolicy` y `InventoryMovementPolicy` por membresía clínica | Sí: HTTP histórico actualizado y `InventoryBillingClinicalIsolationTest` |
| Facturación/pagos | Web | `Invoice`, `InvoiceItem`, `Payment`, `PaymentPlan`, `AccountsReceivable`, `DailyCash` | facturas y pagos con propiedad `clinic_id` nullable definida y pendiente de despliegue/backfill; planes y caja aún pendientes | Requests de crear/actualizar/cancelar factura y crear/actualizar pago; `InvoicePolicy` y `PaymentPolicy` por membresía clínica | Sí: `BillingLifecycleTest` e `InventoryBillingClinicalIsolationTest` |
| Compras/cotizaciones | Web | `Purchase`, `PurchaseItem`, `Quote`, `QuoteItem` | compras, partidas, cotizaciones y partidas | Pendiente | Pendiente |
| Laboratorio | Web | `LabWork`, `LabWorkItem`, `DentalLab`, `Prosthesis` | trabajos, partidas, laboratorios y prótesis | Pendiente | Pendiente |
| Personal/usuarios | Web | `User`, `Staff`, `StaffSchedule`, `StaffCredential`, `Role` | usuarios, roles, personal y credenciales | Auth Requests solamente | Seguridad general; cobertura de dominio pendiente |
| Reportes | Web + API | `Report`, `ReportTemplate`, `Kpi` | reportes, plantillas y KPIs | Pendiente | Cobertura indirecta pendiente |
| Notificaciones | Web | `Notification`, `NotificationQueue`, `NotificationLog`, `NotificationTemplate` | notificaciones, colas, registros y plantillas | Validator inline | Pendiente |

## Brechas de trazabilidad

1. Planes dentales, compras/cotizaciones, laboratorio, reportes generales y notificaciones aún requieren completar su contrato de validación, autorización y prueba.
2. Inventario, facturación y pagos ya tienen consultas y Policies limitadas por `ClinicContext`, pero no deben habilitarse sobre datos reales hasta ejecutar una migración y un backfill autorizados.
3. `ClinicOwnedDomainReadinessService` mantiene esos dominios cerrados con `503` si falta el esquema, existe un propietario nulo o una relación clínica inconsistente.
4. Persisten controladores históricos no publicados y vistas faltantes en rutas secundarias de inventario, reportes y pagos; no forman parte del flujo habilitado por el Mandato 14.

## Próximo paso verificable

Ejecutar el **Mandato 14A**: inspección previa, migración nullable autorizada, inventario de registros sin propietario, propuesta y ejecución trazable de backfill, validación referencial y comprobación posterior de apertura segura. No convertir `clinic_id` en obligatorio ni asignar propietarios por inferencia dudosa.
