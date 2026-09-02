# Referencia UI/UX Clivax para Dentaris

Fecha de revisión: 2026-09-02.

## Alcance

Clivax_Laravel_v1.1.0/ es la referencia visual oficial de Dentaris. Se puede estudiar su composición de páginas, componentes, espaciado, colores, tipografía, navegación y estados visuales. No se debe copiar su lógica Laravel, arquitectura, dependencias, datos de ejemplo, secretos ni archivos de uploads/.

## Evidencia revisada

- Layouts Starterkit: Clivax_Laravel_v1.1.0/Starterkit/resources/views/layouts/.
- Componentes UI Admin: Clivax_Laravel_v1.1.0/Admin/resources/views/.
- Formularios: vistas form-*.
- Tablas y controles: vistas ui-tables.blade.php y datatable-*.blade.php.
- Modales, alertas y notificaciones: vistas ui-modals.blade.php, ui-alerts.blade.php, ui-toaster.blade.php y ui-sweet-alert.blade.php.
- Navegación: topbar.blade.php, sidebar.blade.php, right-sidebar.blade.php y master.blade.php.

## Sistema visual a adaptar

| Elemento | Regla para Dentaris |
|---|---|
| Layout | Mantener layout Blade propio de Dentaris y adaptar sus regiones al patrón Clivax |
| Página | Encabezado, descripción, breadcrumb, contenido en tarjetas y pie coherente |
| Tabla | table, table-hover, table-responsive, alineación vertical, estados vacíos y paginación |
| Controles | Selectores form-select, filtros colapsables, búsqueda, ordenamiento y exportación |
| Acciones | Botones agrupados, iconos Font Awesome, colores semánticos y tooltips |
| Formularios | Labels claros, validación visible, ayudas contextuales y estados de carga/error |
| Modal | Título, contexto, advertencia, acción primaria, cancelación y foco accesible |
| Feedback | Alertas/toasts consistentes para éxito, advertencia, error y confirmación |
| Responsive | Revisar tablas, formularios y acciones en escritorio y viewport reducido |

## Criterio de aceptación visual por módulo

Una vista se considera alineada cuando comparte con las vistas aprobadas de Pacientes, Citas, Personal e Inventario: estructura del layout, jerarquía de títulos, clases de controles, card headers, tabla, estados vacíos, acciones, colores, mensajes y comportamiento responsive. La similitud visual no debe ocultar permisos ni acciones no autorizadas.

## Proceso de revisión

1. Identificar una vista Clivax equivalente.
2. Documentar el patrón elegido y su adaptación a datos reales de Dentaris.
3. Implementar solo Blade/CSS/JS local necesario.
4. Probar estados vacío, cargando, validación, error, éxito y permisos.
5. Revisar visualmente en WSL/Docker.
6. Registrar evidencia antes de cerrar la fase.
