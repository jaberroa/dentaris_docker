# Inventario verificable de arquitectura Dentaris

Fecha de revisión: 2026-09-01.

## Alcance y método

Inventario estático de la copia Windows del repositorio. No se instalaron dependencias, no se ejecutaron migraciones y no se ejecutó la aplicación. Las cantidades son conteos de archivos presentes en las rutas indicadas.

## Evidencia estructural

| Componente | Ruta | Conteo/estado |
|---|---|---:|
| Modelos | `app/Models/` | 56 archivos |
| Controladores | `app/Http/Controllers/` | 37 archivos |
| Servicios | `app/Services/` | 9 archivos |
| Repositories | `app/Repositories/` | No existe |
| Módulos | `app/Modules/` | No existe |
| Form Requests | `app/Http/Requests/` | 4 archivos |
| Policies | `app/Policies/` | No existe |
| Migraciones | `database/migrations/` | 69 archivos |
| Seeders | `database/seeders/` | 25 archivos |
| Factories | `database/factories/` | 18 archivos |
| Vistas Blade | `resources/views/` | 66 archivos |
| Pruebas | `tests/` | 16 archivos |

## Superficies identificadas

- Web: `routes/web.php`, con autenticación, dashboard, pacientes, citas, historias clínicas, inventario, facturación, reportes, notificaciones, personal, planes, laboratorio, cotizaciones, proveedores, tratamientos, pagos y compras.
- API: `routes/api.php`, protegida por `auth:sanctum`, con endpoints de pacientes y controladores API adicionales presentes en `app/Http/Controllers/Api/`.
- Seguridad: middleware de autenticación, roles, permisos, protección CSRF/XSS, cabeceras, actividad y auditoría; existe `TwoFactorAuthController` y configuración de 2FA.
- Referencias: `clinipro/` contiene backend NodeJS y frontend ReactJS; `Clivax_Laravel_v1.1.0/` contiene referencias Laravel visuales. No forman parte de la aplicación Dentaris.

## Siguiente inventario detallado

La siguiente revisión debe mapear cada ruta a controlador, permiso, validación, servicio, modelo, prueba y vista; identificar consultas directas y duplicidades; y contrastar almacenamiento sensible, auditoría, 2FA y reportes contra evidencia ejecutable cuando el entorno WSL esté disponible.
