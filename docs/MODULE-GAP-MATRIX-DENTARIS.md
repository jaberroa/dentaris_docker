# Matriz de brechas Dentaris

Fecha de revisión: 2026-09-01.

| Área | Evidencia actual | Brecha | Prioridad | Criterio de aceptación |
|---|---|---|---|---|
| Arquitectura modular | `app/Modules/` no existe | La aplicación vive principalmente en `app/` global | Alta | Cada módulo priorizado tiene límites, rutas, servicios y pruebas identificables |
| Repositories | No existe `app/Repositories/` | Consultas complejas pueden permanecer en controladores | Alta | Las consultas complejas están encapsuladas y cubiertas por pruebas |
| Services | Existen 9 servicios | Cobertura desigual frente a los 37 controladores | Alta | Cada caso de negocio crítico tiene un servicio explícito |
| Validación | Existen 4 Form Requests | Muchas operaciones pueden depender de validación inline | Alta | Las entradas mutables usan Form Requests versionables |
| Autorización | Existen middleware y gates por nombre en rutas | No se observan Policies en `app/Policies/` | Alta | Operaciones y recursos sensibles tienen autorización verificable y consistente |
| Rutas | `routes/web.php` contiene bloques repetidos | Riesgo de duplicidad y comportamiento ambiguo | Alta | Existe una sola definición por operación y un inventario de rutas aprobado |
| Persistencia | 69 migraciones, 25 seeders y 18 factories | Requiere alineación verificable entre esquema y datos de prueba | Media | Relaciones, seeders y factories se validan contra modelos y migraciones |
| API | Hay controladores API para pacientes, inventario, citas, reportes y dashboard | Cobertura y autorización requieren matriz propia | Media | Cada endpoint tiene contrato, permiso y prueba |
| Seguridad | Hay 2FA, middleware y auditoría | Debe verificarse exposición, flujo y cobertura real | Alta | 2FA, auditoría, rate limiting y datos sensibles tienen pruebas y almacenamiento privado |
| Vistas | 66 archivos Blade y layouts globales | No hay separación modular ni inventario UI completo | Media | Cada módulo tiene vistas, layout, componentes y estado documentados |
| Pruebas | 16 archivos de prueba | Cobertura y vigencia deben contrastarse con rutas y reglas actuales | Alta | Pruebas críticas reproducibles y vinculadas a criterios de aceptación |
| Referencias | `clinipro/` y `Clivax_Laravel_v1.1.0/` están separadas | Riesgo de mezclar lógica funcional y referencia visual | Alta | Clinipro se usa solo para funcionalidad y Clivax solo para UI/UX |
