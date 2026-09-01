<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Dentaris - Sistema de Gestión Dental

Sistema integral de gestión para consultorios dentales desarrollado con Laravel, que incluye gestión de pacientes, citas, historias clínicas, inventario y facturación.

## Características Principales

- **Gestión de Pacientes**: CRUD completo con información médica detallada
- **Sistema de Citas**: Programación y seguimiento de citas médicas
- **Historias Clínicas**: Registro detallado del historial médico y dental
- **Inventario**: Control de productos y servicios
- **Facturación**: Sistema de facturación y pagos
- **Reportes**: Exportación a Excel y PDF
- **API REST**: Endpoints para integración con otros sistemas

## Módulo de Gestión de Pacientes

### Funcionalidades
- CRUD completo de pacientes
- Búsqueda y filtros avanzados
- Exportación a Excel y PDF
- Historial médico integrado
- Contactos de emergencia
- Información médica detallada

### API Endpoints
- `GET /api/patients` - Listar pacientes
- `GET /api/patients/{id}` - Obtener paciente específico
- `POST /api/patients` - Crear paciente
- `PUT /api/patients/{id}` - Actualizar paciente
- `DELETE /api/patients/{id}` - Eliminar paciente

### Validaciones
- Email único por paciente
- Fecha de nacimiento válida
- Campos obligatorios: nombre, apellido, fecha nacimiento, género
- Validación de tipos de sangre permitidos
- Validación de estados civiles permitidos

### Pruebas
```bash
# Ejecutar todas las pruebas del módulo
php artisan test --filter=Patient

# Ejecutar pruebas unitarias
php artisan test tests/Unit/PatientTest.php

# Ejecutar pruebas de integración
php artisan test tests/Feature/PatientTest.php

# Ejecutar pruebas de API
php artisan test tests/Feature/PatientApiTest.php
```

## Tecnologías Utilizadas

- **Backend**: Laravel 10
- **Frontend**: Blade Templates, Bootstrap 5
- **Base de Datos**: MySQL/PostgreSQL
- **Testing**: PHPUnit
- **Contenedores**: Docker
- **Exportación**: Laravel Excel, DomPDF

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).
