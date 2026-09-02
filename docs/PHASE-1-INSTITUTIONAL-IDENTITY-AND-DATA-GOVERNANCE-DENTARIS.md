# Fase 1 — Identidad institucional y gobierno de datos

**Estado:** Aprobado para diseño e implementación controlada
**Fecha:** 2026-09-02
**Alcance:** Dentaris; contrato para la transición multiclínica.
**Límites de referencia:** Clinipro aporta únicamente flujos funcionales; Clivax_Laravel_v1.1.0 aporta únicamente patrones visuales.

## 1. Identidades institucionales iniciales

Para conservar el historial actual sin atribuciones implícitas, Dentaris adopta las siguientes identidades operativas iniciales:

| Entidad | Nombre operativo | Código estable | Uso |
|---|---|---|---|
| Clínica histórica | Dentaris Clínica Odontológica | `DEN-CL-001` | Propietaria inicial de los datos históricos aprobados. |
| Sede histórica | Sede Principal Dentaris | `DEN-SD-001` | Sede inicial de los eventos que requieran ubicación física. |

Estos nombres son identificadores operativos internos; no sustituyen denominación legal, identificación fiscal ni datos regulatorios. Si tales datos se aprueban posteriormente, se actualizarán mediante una decisión auditada, sin cambiar los códigos estables.

## 2. Gobierno y responsabilidad

| Responsabilidad | Rol responsable | Regla |
|---|---|---|
| Aprobación funcional de asignación histórica | Responsable Clínico | Confirma que pacientes, expedientes y atención histórica pertenecen a la clínica histórica. |
| Aprobación financiera | Responsable Administrativo-Financiero | Confirma atribución de facturas, pagos, caja, compras y cuentas por cobrar. |
| Ejecución técnica y conciliación | Responsable de Migración de Datos | Ejecuta únicamente procesos aprobados, idempotentes y trazables. |
| Validación de seguridad | Responsable de Seguridad | Verifica aislamiento, membresías, auditoría y ausencia de exposición cruzada. |
| Aceptación final | Coordinación Técnica Principal | Requiere evidencias de las aprobaciones anteriores, QA y reversa ensayada. |

Un usuario existente sin rol o membresía clínica verificable quedará **bloqueado para acceso clínico** hasta recibir una asignación administrativa explícita y auditada. No se crearán membresías, roles ni accesos por defecto.

## 3. Propiedad y clasificación de datos

| Clasificación | Regla |
|---|---|
| Plataforma global | Identidades `users`, catálogo de permisos, definiciones reutilizables de roles y catálogo CDT. No concede acceso clínico por sí misma. |
| Clínica obligatoria | Pacientes, expedientes, documentos, imágenes, planes, citas, personal, presupuestos, facturas, pagos, cuentas por cobrar, proveedores, productos, compras, laboratorios, notificaciones, reportes, auditoría y configuraciones operativas. Cada registro nuevo debe llevar `clinic_id` derivado en servidor. |
| Sede obligatoria | Citas, horarios, atención presencial, caja presencial, existencias, ubicaciones, movimientos de inventario, recepción y trabajos de laboratorio. Cada registro aplicable debe llevar `clinic_site_id`. |
| Catálogos por clínica | Aseguradoras, proveedores, productos, laboratorios, plantillas, estados operativos de cita, servicios, listas de precios, inventario y configuraciones de notificación. |
| Catálogos globales de solo lectura | Permisos, roles plantilla y CDT. Cualquier precio, disponibilidad o configuración comercial se resuelve por clínica. |

Los pacientes, expedientes, imágenes y documentos no se comparten entre clínicas. La portabilidad o el expediente transversal requerirán un contrato posterior de consentimiento, trazabilidad y autorización.

## 4. Reglas de identidad, acceso y contexto

- `User` es una identidad global; el acceso clínico exige una membresía activa de la clínica seleccionada.
- Roles y permisos clínicos se asignan a la membresía, no a la identidad global.
- `platform.super_admin` administra la plataforma, pero no consulta ni modifica datos clínicos sin membresía activa en la clínica seleccionada.
- Toda solicitud clínica debe resolver en servidor: usuario activo, clínica activa, sede autorizada cuando corresponda, membresía activa, permisos efectivos y Policy/Gate aplicable.
- Ningún valor `clinic_id` o `clinic_site_id` enviado por URL, formulario, cookie o cabecera constituye autoridad. El servidor lo valida y lo deriva del contexto.
- El cambio de clínica es explícito, regenera el contexto de sesión, invalida cachés de autorización y queda auditado.
- Cualquier recurso de otra clínica debe responder como inexistente o rechazado sin revelar metadatos.

## 5. Numeración y unicidad

Los siguientes identificadores serán únicos **dentro de la clínica**, no globalmente: código de paciente, empleado, cita, plan, presupuesto, factura, pago, compra, trabajo de laboratorio, proveedor, producto, laboratorio, prótesis, aseguradora, plantilla, notificación, KPI y reporte.

La numeración se generará de forma transaccional y con protección de concurrencia; queda prohibido derivarla de `count() + 1` o de máximos no bloqueados. Los códigos fiscales o regulatorios que exijan unicidad externa conservarán su regla legal explícita y no se modificarán por esta decisión.

## 6. Transición histórica y reversa

1. Antes de cualquier migración se obtiene una línea base reproducible: conteos, huellas, relaciones huérfanas, documentos privados, saldos financieros e inventario.
2. La clínica y sede históricas solo reciben datos después de la aprobación documentada de los responsables funcional y financiero.
3. La transición es aditiva: las columnas y pivots existentes se conservan durante la coexistencia controlada; no se borran datos ni archivos.
4. Los lotes deben ser reanudables e idempotentes, con identificador de ejecución, procesados, omitidos, excepciones y responsable.
5. Ninguna excepción se asigna automáticamente. Las excepciones abiertas bloquean el endurecimiento de restricciones.
6. La reversa usa restauración validada o compensación hacia adelante. No se elimina contexto nuevo ni se fusionan datos de clínicas después de escrituras multiclínica.

## 7. Ventana de corte y aceptación

La primera ejecución se realizará únicamente después de un ensayo exitoso de restauración en un entorno controlado y contará con:

- Ventana de mantenimiento aprobada de hasta dos horas, con suspensión temporal de escrituras clínicas.
- Respaldo cifrado verificado antes del corte y procedimiento de restauración ensayado.
- Responsable de Migración de Datos como ejecutor; Responsable de Seguridad y QA como validadores independientes.
- Aceptación final de Coordinación Técnica Principal solo si se cumplen: cero registros clínicos sin clínica, cero relaciones cruzadas, conciliación de conteos y huellas, pruebas de aislamiento A/B verdes y auditoría contextual verificable.

## 8. Prohibiciones

- No crear membresías, roles ni clínicas automáticamente durante una solicitud.
- No aceptar acceso clínico por rol global, `admin` o `platform.super_admin` sin membresía válida.
- No migrar, borrar, anonimizar ni reasignar datos existentes sin un mandato posterior y evidencias de conciliación.
- No guardar documentos clínicos en rutas públicas ni devolver rutas internas en APIs.
- No trasladar código, secretos, dependencias, cargas ni datos de Clinipro o Clivax.
