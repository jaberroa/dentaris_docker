# Inventario funcional de Clinipro para Dentaris

Fecha de revisión: 2026-09-02.

## Propósito

Este documento identifica la lógica funcional que Dentaris debe evaluar y adaptar desde clinipro/. Clinipro no es una fuente para copiar arquitectura, código, dependencias, secretos ni datos de ejemplo. La adaptación se hará al stack Laravel/Blade de Dentaris, con sus propias reglas de seguridad, persistencia y auditoría.

## Evidencia revisada

- Backend Node/Mongoose: clinipro/Backend - NodeJS/src/models/ contiene 32 modelos.
- Controladores: clinipro/Backend - NodeJS/src/controllers/ contiene 35 controladores.
- Rutas: clinipro/Backend - NodeJS/src/routes/ contiene 37 archivos; src/routes/index.ts monta los módulos.
- Frontend: clinipro/Frontend - ReactJS/src/pages/dashboard/ contiene 44 páginas o pantallas.
- Semillas: clinipro/Backend - NodeJS/src/seeds/ contiene roles, permisos, clínica, usuarios, datos y odontograma.
- Servicios frontend identificados: citas, gastos, laboratorio, odontograma, pagos, rendimiento, servicios y usuarios.

## Dominios funcionales identificados

| Dominio | Evidencia Clinipro | Resultado esperado en Dentaris | Prioridad |
|---|---|---|---|
| Identidad y acceso | authRoutes, userRoutes, roleRoutes, permissionRoutes, userClinicRoutes | Usuarios, roles, permisos, sesión, alcance por clínica y auditoría | Crítica |
| Clínica y sedes | clinicRoutes, departmentRoutes, settingsRoutes | Configuración de clínica, sedes/departamentos, moneda, horarios y preferencias | Alta |
| Pacientes | patientRoutes, modelo Patient, página patients/Patients.tsx | Expediente base, contactos, seguros, búsqueda, estados y exportaciones | Crítica |
| Agenda | appointmentRoutes, appointments/Appointments.tsx, calendar/Calendar.tsx | Citas, estados, disponibilidad, recordatorios y vistas calendario | Crítica |
| Historia clínica | medicalRecordRoutes, prescriptionRoutes, odontogramRoutes | Historia, diagnóstico, tratamiento, receta y odontograma relacionados al paciente | Crítica |
| Tratamientos y servicios | serviceRoutes, modelo Service, planes en frontend | Catálogo de servicios, precios, duración, requisitos y seguimiento | Alta |
| Facturación | invoiceRoutes, páginas billing/ e invoices/ | Facturas, conceptos, estados, saldo, edición controlada, PDF y auditoría | Crítica |
| Pagos | paymentRoutes, página payments/, utils/stripe.ts | Pagos, conciliación, estados, comprobantes y futura pasarela | Crítica |
| Inventario | inventoryRoutes, página inventory/, migración explícita en Clinipro | Productos, stock, ubicaciones, movimientos, transferencias, alertas y exportación | Alta |
| Laboratorio | labVendorRoutes, test*Routes, testReportRoutes, sampleTypeRoutes, testMethodologyRoutes, turnaroundTimeRoutes | Proveedores, muestras, pruebas, resultados, reportes y tiempos de respuesta | Alta |
| Gastos y nómina | expenseRoutes, payrollRoutes, páginas correspondientes | Gastos, recibos, nómina, periodos, deducciones y estados | Media |
| Comunicación | receptionistRoutes, recordatorios y servicios de mensajería | Flujos operativos de recepción, avisos y recordatorios | Media |
| Analítica | dashboardRoutes, analyticsRoutes, performanceRoutes | Indicadores, rendimiento y reportes con permisos | Media |
| IA clínica | xrayAnalysisRoutes, aiTestAnalysisRoutes, aiTestComparisonRoutes | Solo después de definir privacidad, consentimiento, coste, proveedor y trazabilidad | Posterior |

## Reglas funcionales que deben extraerse antes de programar

Para cada dominio se debe registrar: actores, precondiciones, estados, transiciones permitidas, validaciones, efectos sobre otras entidades, permisos, eventos auditables, errores y pruebas. Una pantalla o endpoint de Clinipro no se considera requisito completo hasta que su comportamiento esté descrito en esa forma.

## Orden funcional aprobado

1. Identidad, clínica y permisos.
2. Paciente, expediente e historia clínica.
3. Agenda y recordatorios.
4. Servicios, tratamientos y odontograma.
5. Facturación, pagos y cuentas por cobrar.
6. Inventario, laboratorio y compras.
7. Gastos, nómina, reportes y analítica.
8. Integraciones, IA y funciones avanzadas.

La prioridad podrá cambiar solo con una decisión documentada que explique dependencias y riesgo.

## Hallazgos y límites

- Clinipro usa Node/Express/Mongoose; Dentaris usa Laravel/Eloquent/Blade. La equivalencia funcional no implica equivalencia técnica.
- Clinipro tiene conceptos multi-clínica (clinic_id) que deben resolverse explícitamente en Dentaris antes de ampliar el alcance.
- Hay funciones de Stripe, S3, IA y análisis de imágenes que no deben habilitarse por copiar una ruta: requieren configuración, privacidad, permisos, almacenamiento privado y pruebas.
- Los archivos de referencia se mantienen fuera del runtime de Dentaris.
