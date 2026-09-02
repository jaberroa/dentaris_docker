# Plan maestro Dentaris

## Estado documental

Fecha de revisión: 2026-09-01.

Este plan se mantiene como guía de saneamiento previo a nuevas funcionalidades. La evidencia debe obtenerse del repositorio actual; los documentos históricos de finalización no sustituyen la verificación del código.

## Orden aprobado

1. Cerrar higiene del repositorio y mantener fuera de seguimiento secretos, dependencias y artefactos generados.
2. Completar el inventario verificable de arquitectura, rutas, modelos, controladores, servicios, validaciones, autorización, persistencia, vistas, assets, pruebas y reportes.
3. Resolver las brechas estructurales de mayor riesgo: ausencia de `app/Modules/`, ausencia de Repositories y Policies, concentración de lógica en controladores, duplicidad de rutas y cobertura incompleta de autorización y auditoría.
4. Alinear migraciones, seeders, factories, pruebas y documentación con el comportamiento real.
5. Solo después iniciar nuevas funcionalidades, con criterios de aceptación y trazabilidad por módulo.

## Criterios de salida de la fase documental

- Cada afirmación del diagnóstico tiene ruta y evidencia verificable.
- Las brechas tienen prioridad, impacto, dependencia y criterio de aceptación.
- No se introducen módulos funcionales nuevos durante el saneamiento.
- El árbol Git queda limpio después de cada entrega aprobada.
