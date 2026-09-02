# Protocolo del coordinador de fases de Dentaris

**Estado:** aprobado para activarse al comenzar la Fase 1  
**Ámbito:** Dentaris únicamente  
**Copia canónica:** `C:\Users\LENOVO\Projects\dentaris_docker`

## Mandato

El coordinador dirige una fase aprobada de forma verificable. Convierte el objetivo de la fase en mandatos acotados para subagentes, coordina tareas independientes en paralelo, consolida sus resultados y bloquea el avance cuando no se cumplen los criterios de aceptación.

El coordinador no inventa alcance, no modifica reglas aprobadas y no considera un informe como evidencia si no puede vincularlo con código, pruebas, configuración o una revisión visual reproducible.

## Flujo obligatorio

1. Leer el plan, las reglas y la documentación vigente de la fase.
2. Crear un registro de tareas con responsable, dependencia, archivos permitidos y estado.
3. Emitir un mandato independiente a cada rol necesario.
4. Ejecutar en paralelo solo tareas sin dependencia ni archivos compartidos.
5. Recibir por tarea: resumen, archivos afectados, evidencia, pruebas, riesgos y bloqueo si aplica.
6. Resolver contradicciones antes de integrar cambios.
7. Entregar el conjunto de cambios al agente principal para revisión central.
8. Verificar criterios de aceptación, diff, pruebas y documentación.
9. Declarar la fase `CERRADA` o `BLOQUEADA`; nunca avanzar silenciosamente.

## Contrato de mandato

Cada prompt enviado a un subagente debe contener:

```text
Fase: <identificador y nombre>
Rol: <rol responsable>
Objetivo: <resultado concreto>
Alcance: <incluido y excluido>
Archivos permitidos: <rutas exactas o lectura completa>
Modo: <solo lectura | propuesta | escritura aislada>
Referencias autorizadas: Clinipro funcional; Clivax solo UI/UX
Restricciones: sin dependencias nuevas, sin secretos, sin cambios fuera del alcance
Evidencia requerida: <archivos, símbolos, pruebas o capturas>
Criterio de cierre: <condición verificable>
Entrega: resumen, riesgos, pruebas, diff y bloqueo si existe
```

## Autoridad y aislamiento

- El coordinador puede crear o llamar subagentes para la fase activa.
- Los subagentes trabajan en lectura o en ramas/worktrees aislados.
- La copia Windows canónica no se edita simultáneamente por varios agentes.
- El agente principal conserva la integración final, la revisión de cambios y la publicación.
- WSL solo consume `origin/main` para Docker, pruebas y revisión visual.
- Commit y push se realizan únicamente después de mostrar archivos modificados, diff, hash y estado Git.

## Puerta de fase

Una fase solo puede cerrarse cuando:

- todos los mandatos necesarios tienen resultado;
- no existen bloqueos abiertos de seguridad, datos o pruebas;
- las pruebas y revisiones exigidas son reproducibles;
- la documentación refleja el estado real;
- el diff no contiene cambios fuera del alcance;
- el agente principal aprueba la integración y publicación.

El siguiente coordinador no inicia trabajo dependiente hasta recibir el estado `CERRADA` de la fase anterior.
