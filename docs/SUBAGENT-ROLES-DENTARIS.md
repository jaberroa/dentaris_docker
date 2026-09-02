# Roles especializados para Dentaris

Fecha de revisión: 2026-09-02.

Estos roles definen responsabilidades para futuras tareas delegadas. No sustituyen la revisión del agente principal ni autorizan cambios fuera del alcance. Se activan por fase y con instrucciones concretas.

| Rol | Responsabilidad | Entregable |
|---|---|---|
| Analista funcional Clinipro | Extraer casos de uso, estados, reglas y dependencias de Clinipro | Contrato funcional y matriz de trazabilidad |
| Arquitecto Laravel | Traducir el flujo a Models, Services, Repositories, Requests, Policies y rutas | Diseño técnico y límites del módulo |
| Seguridad y autorización | Revisar autenticación, permisos, políticas, privacidad, auditoría y abuso | Dictamen de seguridad y pruebas negativas |
| Datos y persistencia | Revisar migraciones, relaciones, índices, seeders, factories y consistencia | Contrato de datos y migraciones verificadas |
| UI/UX Clivax | Adaptar layout, componentes, tablas, formularios, modales y estados | Dictamen visual y checklist responsive |
| QA funcional | Diseñar pruebas unitarias, feature, regresión y criterios de aceptación | Matriz de pruebas y reporte reproducible |
| Operaciones Git/entornos | Verificar Windows/WSL, Docker, sincronización, diff y estado Git | Evidencia de entorno y publicación |
| Documentación | Mantener planes, decisiones, inventarios, riesgos y cierres de fase | Documentación versionada y enlazada |

## Reglas de coordinación

- El Analista funcional define qué debe hacer el sistema; no aprueba código.
- El Arquitecto propone implementación; Seguridad puede bloquear un flujo inseguro.
- UI/UX no modifica reglas de negocio.
- Datos no ejecuta migraciones reales sin autorización.
- QA valida criterios; no cambia una prueba para ocultar un fallo.
- Operaciones publica solo después de la revisión del diff.
- Documentación registra decisiones, no reemplaza evidencia ejecutable.

## Secuencia recomendada por fase

Analista funcional → Arquitecto Laravel → Seguridad y Datos → Implementación → QA → UI/UX → Operaciones Git → Documentación de cierre.
