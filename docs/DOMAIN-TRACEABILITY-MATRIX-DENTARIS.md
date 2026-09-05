# Matriz de trazabilidad por dominio Dentaris

Fecha de revisión: 2026-09-05.

Esta matriz cruza artefactos presentes en el repositorio. `Pendiente` significa que la existencia del archivo no demuestra por sí sola autorización, cobertura o corrección funcional.

| Dominio | Ruta web/API | Modelos principales | Persistencia identificada | Validación dedicada | Prueba específica |
|---|---|---|---|---|---|
| Pacientes | Web + API | `Patient`, `PatientContact`, `PatientDocument`, `PatientInsurance` | `create_patients_table` y relacionadas | `PatientRequest` | Sí: paciente web/API |
| Citas | Web; API histórica no publicada | `Appointment`, `AppointmentStatus`, `AppointmentReminder` | citas, estados, recordatorios y extensiones | `AppointmentRequest` limitado por clínica | Sí: integración clínica web; suite API histórica pendiente de saneamiento |
| Historias clínicas | Web | `MedicalRecord`, `MedicalDiagnosis`, `MedicalImage`, `DentalClinicalHistory` | historias, diagnósticos, imágenes y clínica dental | `MedicalRecordRequest` con relación cita-paciente-personal | Sí: `ClinicalAppointmentsMedicalRecordsIntegrationTest` |
| Planes dentales | Web | `TreatmentPlan`, `TreatmentPlanItem`, `DentalTreatmentPlan`, `DentalProcedure`, `DentalOdontogram`, `DentalPeriodontogram` | planes, procedimientos, odontograma y periodontograma | Pendiente | Pendiente |
| Inventario | Web | `Inventory`, `InventoryLocation`, `InventoryMovement`, `Product`, `Supplier` | inventario, ubicaciones, movimientos, productos y proveedores con `clinic_id` nullable; cinco productos/inventarios QA íntegros para `DEN-CL-001` | Form Requests de ubicación, ajuste, transferencia y exportación; Policies por membresía clínica | Sí: HTTP histórico actualizado, transiciones/QA e `InventoryBillingClinicalIsolationTest` |
| Facturación/pagos | Web | `Invoice`, `InvoiceItem`, `Payment`, `PaymentPlan`, `AccountsReceivable`, `DailyCash` | facturas y pagos con `clinic_id` nullable desplegado y cinco registros QA de cada tipo; planes y caja aún pendientes | Requests de crear/actualizar/cancelar factura y crear/actualizar pago; `InvoicePolicy` y `PaymentPolicy` por membresía clínica | Sí: `BillingLifecycleTest`, transición/QA e `InventoryBillingClinicalIsolationTest` |
| Compras/cotizaciones | Web cerrada con `503` | `Purchase`, `PurchaseItem`, `Quote`, `QuoteItem` | compras y cotizaciones con `clinic_id` nullable; partidas heredan del padre | Policies clínicas añadidas; Form Requests y controladores seguros pendientes | Sí para propiedad, rollback, autorización y cierre; CRUD pendiente |
| Laboratorio | Web | `LabWork`, `LabWorkItem`, `DentalLab`, `Prosthesis` | trabajos, partidas, laboratorios y prótesis | Pendiente | Pendiente |
| Personal/usuarios | Web | `User`, `Staff`, `StaffSchedule`, `StaffCredential`, `Role` | usuarios, roles, personal y credenciales | Auth Requests solamente | Seguridad general; cobertura de dominio pendiente |
| Reportes | Web + API | `Report`, `ReportTemplate`, `Kpi` | reportes, plantillas y KPIs | Pendiente | Cobertura indirecta pendiente |
| Notificaciones | Web | `Notification`, `NotificationQueue`, `NotificationLog`, `NotificationTemplate` | notificaciones, colas, registros y plantillas | Validator inline | Pendiente |

## Brechas de trazabilidad

1. Planes dentales, CRUD de compras/cotizaciones, laboratorio, reportes generales y notificaciones aún requieren completar su contrato de validación, autorización y prueba.
2. Inventario, facturación y pagos tienen consultas y Policies limitadas por `ClinicContext`; los Mandatos 14B y 14C aplicaron esquema nullable, transiciones auditadas y datos QA idempotentes.
3. `ClinicOwnedDomainReadinessService` mantiene inventario/facturación cerrados con `503` ante propietario nulo o relación inconsistente, y mantiene procurement/cotizaciones cerrados incondicionalmente hasta reconstruir sus flujos heredados.
4. Persisten controladores históricos no publicados, consultas programadas globales y vistas faltantes; no forman parte del flujo habilitado por el Mandato 14.

## Próximo paso verificable

Ejecutar el **Mandato 14D**: reconstruir de forma clínica proveedores/compras/cotizaciones y sanear las alertas operativas globales. Mantener `clinic_id` nullable y las superficies en `503` hasta que cada flujo tenga validación, autorización, auditoría y pruebas completas.
