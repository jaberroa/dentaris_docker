# Roles especializados para Dentaris

Fecha de revisión: 2026-09-02.

Estos roles definen responsabilidades para futuras tareas delegadas. No sustituyen la revisión del agente principal ni autorizan cambios fuera del alcance. Se activan por fase y con instrucciones concretas.

| Rol | Responsabilidad | Entregable |
|---|---|---|
| Coordinador de fase | Divide el objetivo aprobado, asigna mandatos, evita solapamientos, integra evidencias y bloquea el cierre si falta un criterio | Registro de coordinación, estado de tareas y dictamen de fase |
| Analista funcional Clinipro | Extraer casos de uso, estados, reglas y dependencias de Clinipro | Contrato funcional y matriz de trazabilidad |
| Arquitecto Laravel | Traducir el flujo a Models, Services, Repositories, Requests, Policies y rutas | Diseño técnico y límites del módulo |
| Seguridad y autorización | Revisar autenticación, permisos, políticas, privacidad, auditoría y abuso | Dictamen de seguridad y pruebas negativas |
| Datos y persistencia | Revisar migraciones, relaciones, índices, seeders, factories y consistencia | Contrato de datos y migraciones verificadas |
| UI/UX Clivax | Adaptar layout, componentes, tablas, formularios, modales y estados | Dictamen visual y checklist responsive |
| QA funcional | Diseñar pruebas unitarias, feature, regresión y criterios de aceptación | Matriz de pruebas y reporte reproducible |
| Operaciones Git/entornos | Verificar Windows/WSL, Docker, sincronización, diff y estado Git | Evidencia de entorno y publicación |
| Documentación | Mantener planes, decisiones, inventarios, riesgos y cierres de fase | Documentación versionada y enlazada |

## Reglas de coordinación

- El Coordinador de fase es el punto único de distribución y seguimiento; no sustituye la aprobación del agente principal.
- Cada mandato debe indicar objetivo, alcance, archivos permitidos, si es lectura o escritura, evidencia requerida, pruebas y criterio de cierre.
- Las tareas independientes pueden ejecutarse en paralelo; las tareas con dependencias esperan el resultado del rol anterior.
- Ningún subagente edita la copia Windows canónica directamente. Los cambios se entregan en una rama o worktree aislado para integración central.
- El Coordinador no permite que dos agentes tengan el mismo conjunto de archivos modificables.
- Un subagente informa bloqueos con evidencia y no los oculta cambiando pruebas o documentación.
- Solo el agente principal integra, revisa el diff final y autoriza commit y push.
- El Analista funcional define qué debe hacer el sistema; no aprueba código.
- El Arquitecto propone implementación; Seguridad puede bloquear un flujo inseguro.
- UI/UX no modifica reglas de negocio.
- Datos no ejecuta migraciones reales sin autorización.
- QA valida criterios; no cambia una prueba para ocultar un fallo.
- Operaciones publica solo después de la revisión del diff.
- Documentación registra decisiones, no reemplaza evidencia ejecutable.

## Secuencia recomendada por fase

Coordinador → Analista funcional → Arquitecto Laravel → Seguridad y Datos → Implementación → QA/UI/UX → Operaciones Git → Documentación de cierre.
