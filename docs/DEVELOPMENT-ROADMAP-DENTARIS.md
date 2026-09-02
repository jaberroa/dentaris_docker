# Plan de desarrollo por fases Dentaris

Fecha de revisión: 2026-09-02.

## Regla de avance

Las fases son secuenciales. No se inicia una fase nueva mientras la anterior no tenga código, documentación, pruebas automatizadas, revisión visual cuando aplique, estado Git limpio y commit publicado. Un fallo bloquea el cierre; no se compensa avanzando a otro módulo.

## Fases

| Fase | Alcance | Salida mínima |
|---|---|---|
| 0. Gobierno y saneamiento | Reglas, referencias, inventarios, Git y seguridad documental | Documentación aprobada y repositorio verificable |
| 1. Identidad y contexto clínico | Usuarios, roles, permisos, clínicas, configuración y alcance | Flujos de acceso probados y permisos auditables |
| 2. Pacientes y expediente | Paciente, contactos, seguros, documentos e historia | CRUD, búsqueda, estados, privacidad y pruebas |
| 3. Agenda clínica | Citas, disponibilidad, estados, recordatorios y calendario | Transiciones válidas, conflictos y pruebas |
| 4. Atención odontológica | Tratamientos, servicios, odontograma, diagnósticos y recetas | Relación clínica completa y trazabilidad |
| 5. Facturación y pagos | Factura, conceptos, pagos, cuentas por cobrar, PDF y auditoría | Ciclo de vida, restricciones y conciliación |
| 6. Inventario y compras | Productos, ubicaciones, movimientos, transferencias, compras y alertas | Stock consistente, historial y pruebas de concurrencia |
| 7. Laboratorio | Proveedores, pruebas, muestras, resultados e informes | Flujo de laboratorio trazable |
| 8. Gastos, nómina y reportes | Gastos, nómina, dashboard, analítica y exportaciones | Reportes autorizados y reproducibles |
| 9. Integraciones avanzadas | Correo, pagos externos, almacenamiento privado, IA y mensajería | Integraciones configurables, seguras y observables |
| 10. Endurecimiento | Rendimiento, accesibilidad, seguridad, backup y operación | Criterios de producción documentados |

## Plantilla obligatoria de cierre de fase

- Alcance y fuera de alcance.
- Matriz de casos de uso y entidades afectadas.
- Rutas, permisos, policies, Form Requests, Services y Repositories.
- Migraciones, seeders y factories necesarios.
- Pruebas unitarias, feature, seguridad y regresión.
- Evidencia visual y estados de la interfaz.
- Riesgos pendientes y decisión de cierre.
- Archivos modificados, diff, commit, push y git status --short --branch.

## Dependencias críticas

- No ampliar módulos clínicos antes de cerrar identidad, permisos y contexto de clínica.
- No cerrar facturación antes de definir pacientes y servicios.
- No cerrar pagos antes de definir estados de factura y cuentas por cobrar.
- No cerrar inventario avanzado antes de terminar ubicaciones y movimientos.
- No habilitar IA o servicios externos antes de privacidad, configuración y auditoría.
