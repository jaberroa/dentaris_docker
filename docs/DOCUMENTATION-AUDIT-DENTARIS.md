# Auditoría documental de Dentaris

**Fecha de auditoría:** 2026-09-02
**Estado:** Fase 0 — cerrada con saneamiento documental
**Fuente de verdad:** código, pruebas y configuración actuales del repositorio

## Propósito

Este documento clasifica la documentación existente para evitar que informes históricos, afirmaciones no verificadas o referencias externas se confundan con el estado real de Dentaris.

La clasificación se realizó contra la estructura actual del repositorio, sus pruebas y las fronteras aprobadas de las referencias:

- `clinipro/` aporta funcionalidad y reglas de negocio para adaptación.
- `Clivax_Laravel_v1.1.0/` aporta únicamente patrones visuales y UI/UX.
- Dentaris continúa siendo la única fuente de implementación.

## Criterios

| Clasificación | Significado | Acción |
| --- | --- | --- |
| Vigente | Orienta el trabajo actual y no contradice la evidencia disponible. | Mantener. |
| Reconciliar | Puede ser útil, pero contiene supuestos, ejemplos o estado que deben contrastarse con el código. | Mantener y actualizar en una fase documental posterior. |
| Histórico | Afirma finalización, cobertura o infraestructura sin evidencia reproducible actual. | Archivar; no usar como fuente de verdad. |
| Referencia externa | Pertenece a Clinipro o Clivax y no describe el estado de Dentaris. | Conservar en su ubicación original. |
| Sin redundancia segura | No se identificó contenido idéntico ni un archivo sin valor único. | No eliminar. |

## Resultado del inventario

| Archivo o grupo | Evidencia observada | Clasificación | Disposición |
| --- | --- | --- | --- |
| `APPOINTMENT_MODULE_COMPLETION.md` | Declara módulo completado, 70 pruebas y aptitud para producción sin verificación actual equivalente. | Histórico | Archivado en `docs/archive/`. |
| `PATIENT_MODULE_COMPLETION.md` | Declara módulo completado y cobertura total sin verificación actual equivalente. | Histórico | Archivado en `docs/archive/`. |
| `TESTING_REPORT.md` | Reporte antiguo de appointments con resultados y entorno no demostrados como actuales. | Histórico | Archivado en `docs/archive/`. |
| `module_reports/FINAL_TEST_RESULTS_APPOINTMENTS.md` | Afirma 84/84, 100%, infraestructura operativa y aptitud inmediata para integración. | Histórico | Archivado en `docs/archive/`. |
| `module_reports/README.md` | Índice basado en reportes históricos, métricas no verificables y fechas dinámicas. | Histórico | Archivado en `docs/archive/`. |
| `docs/KUBERNETES.md` | Declara Kubernetes implementado; existen manifiestos, pero no evidencia de despliegue actual. | Histórico | Archivado para futura validación operativa. |
| `docs/ISTIO.md` | Declara service mesh implementado; existen archivos de configuración, pero no evidencia de instalación activa. | Histórico | Archivado para futura validación operativa. |
| `docs/API.md`, `docs/API_APPOINTMENTS.md`, `docs/API_PATIENTS.md` | Contratos útiles, pero con ejemplos, versionado y estados que requieren contraste con rutas actuales. | Reconciliar | Mantener para actualización posterior. |
| `docs/SECURITY.md`, `docs/PERFORMANCE.md`, `docs/CI-CD.md`, `docs/TESTING.md` | Guías técnicas útiles, pero contienen afirmaciones de implementación y métricas que deben comprobarse. | Reconciliar | Mantener; no son evidencia de cumplimiento. |
| `docs/MASTER-PLAN-DENTARIS.md`, `docs/DEVELOPMENT-ROADMAP-DENTARIS.md`, `docs/PROJECT-RULES-DENTARIS.md` | Gobernanza y planificación aprobadas, sujetas a la realidad verificada del repositorio. | Vigente | Mantener como documentos de dirección. |
| Documentación de autorización, inventario, facturación y rutas | Contratos y decisiones recientes vinculados a código y pruebas actuales. | Vigente | Mantener y actualizar por fase. |
| `docs/CLINIPRO-FUNCTIONAL-INVENTORY-DENTARIS.md` | Inventario funcional de la referencia NodeJS/React. | Vigente | Mantener como referencia funcional. |
| `docs/CLIVAX-UIUX-DESIGN-SYSTEM-DENTARIS.md` | Sistema visual aprobado basado en Clivax. | Vigente | Mantener como referencia visual exclusiva. |
| README de `clinipro/` y `Clivax_Laravel_v1.1.0/` | Documentación propia de las referencias. | Referencia externa | No modificar ni eliminar. |

## Comprobaciones realizadas

- No se encontraron archivos Markdown idénticos por contenido.
- No se encontraron referencias a nombres institucionales o frameworks visuales obsoletos en la documentación ni en el código de Dentaris inspeccionado.
- No se eliminaron documentos: los candidatos históricos fueron archivados para conservar trazabilidad.
- No se modificó código funcional, migraciones, dependencias ni datos.

## Regla de uso posterior

Un documento solo puede declarar una funcionalidad como implementada, completada, segura, cubierta o apta para producción cuando incluya evidencia reproducible: rutas o símbolos actuales, pruebas ejecutables, configuración vigente y fecha de verificación.

Los documentos archivados son material histórico y no autorizan decisiones de implementación.
