<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Permisos del Sistema
    |--------------------------------------------------------------------------
    |
    | Aquí se definen todos los permisos disponibles en el sistema.
    | Cada permiso debe tener una clave única y una descripción clara.
    |
    */

    'permissions' => [
        // Gestión de Usuarios y Roles
        'view_users' => 'Ver usuarios',
        'manage_users' => 'Gestionar usuarios',
        'view_roles' => 'Ver roles',
        'manage_roles' => 'Gestionar roles',
        'view_permissions' => 'Ver permisos',
        'manage_permissions' => 'Gestionar permisos',

        // Gestión de Pacientes
        'view_patients' => 'Ver pacientes',
        'manage_patients' => 'Gestionar pacientes',
        'export_patients' => 'Exportar pacientes',

        // Gestión de Citas
        'view_appointments' => 'Ver citas',
        'manage_appointments' => 'Gestionar citas',
        'cancel_appointments' => 'Cancelar citas',
        'reschedule_appointments' => 'Reprogramar citas',

        // Gestión de Historias Clínicas
        'view_medical_records' => 'Ver historias clínicas',
        'manage_medical_records' => 'Gestionar historias clínicas',

        // Gestión de Staff
        'view_staff' => 'Ver personal',
        'manage_staff' => 'Gestionar personal',

        // Gestión de Inventario
        'view_inventory' => 'Ver inventario',
        'manage_inventory' => 'Gestionar inventario',
        'adjust_inventory' => 'Ajustar inventario',
        'export_inventory' => 'Exportar inventario',

        // Gestión de Productos
        'view_products' => 'Ver productos',
        'manage_products' => 'Gestionar productos',

        // Gestión de Proveedores
        'view_suppliers' => 'Ver proveedores',
        'manage_suppliers' => 'Gestionar proveedores',

        // Gestión de Compras
        'view_purchases' => 'Ver compras',
        'manage_purchases' => 'Gestionar compras',
        'approve_purchases' => 'Aprobar compras',

        // Gestión de Trabajos de Laboratorio
        'view_lab_works' => 'Ver trabajos de laboratorio',
        'manage_lab_works' => 'Gestionar trabajos de laboratorio',

        // Gestión de Planes de Tratamiento
        'view_treatment_plans' => 'Ver planes de tratamiento',
        'manage_treatment_plans' => 'Gestionar planes de tratamiento',

        // Gestión de Cotizaciones
        'view_quotes' => 'Ver cotizaciones',
        'manage_quotes' => 'Gestionar cotizaciones',
        'approve_quotes' => 'Aprobar cotizaciones',

        // Gestión de Facturación
        'view_billing' => 'Ver facturación',
        'manage_billing' => 'Gestionar facturación',
        'process_payments' => 'Procesar pagos',
        'manage_daily_cash' => 'Gestionar caja diaria',

        // Reportes
        'view_reports' => 'Ver reportes',
        'export_reports' => 'Exportar reportes',

        // Notificaciones
        'view_notifications' => 'Ver notificaciones',
        'send_notifications' => 'Enviar notificaciones',

        // Configuración
        'view_settings' => 'Ver configuración',
        'manage_settings' => 'Gestionar configuración',

        // Dashboard
        'view_dashboard' => 'Ver dashboard',
    ],

    /*
    |--------------------------------------------------------------------------
    | Roles del Sistema
    |--------------------------------------------------------------------------
    |
    | Aquí se definen todos los roles disponibles en el sistema
    | y los permisos que tiene cada uno.
    |
    */

    'roles' => [
        'super_admin' => [
            'name' => 'Super Administrador',
            'description' => 'Acceso completo al sistema',
            'permissions' => '*', // Todos los permisos
        ],

        'admin' => [
            'name' => 'Administrador',
            'description' => 'Administrador del sistema',
            'permissions' => [
                'view_users', 'manage_users',
                'view_roles', 'manage_roles',
                'view_patients', 'manage_patients',
                'view_appointments', 'manage_appointments',
                'view_staff', 'manage_staff',
                'view_inventory', 'manage_inventory',
                'view_products', 'manage_products',
                'view_suppliers', 'manage_suppliers',
                'view_purchases', 'manage_purchases',
                'view_lab_works', 'manage_lab_works',
                'view_treatment_plans', 'manage_treatment_plans',
                'view_quotes', 'manage_quotes',
                'view_billing', 'manage_billing',
                'view_reports', 'export_reports',
                'view_notifications', 'send_notifications',
                'view_settings', 'manage_settings',
                'view_dashboard',
            ],
        ],

        'dentist' => [
            'name' => 'Odontólogo',
            'description' => 'Odontólogo del consultorio',
            'permissions' => [
                'view_patients', 'manage_patients',
                'view_appointments', 'manage_appointments',
                'view_treatment_plans', 'manage_treatment_plans',
                'view_quotes', 'manage_quotes',
                'view_lab_works', 'manage_lab_works',
                'view_billing',
                'view_reports',
                'view_dashboard',
            ],
        ],

        'assistant' => [
            'name' => 'Asistente Dental',
            'description' => 'Asistente dental',
            'permissions' => [
                'view_patients',
                'view_appointments', 'manage_appointments',
                'view_inventory',
                'view_lab_works', 'manage_lab_works',
                'view_dashboard',
            ],
        ],

        'receptionist' => [
            'name' => 'Recepcionista',
            'description' => 'Recepcionista del consultorio',
            'permissions' => [
                'view_patients', 'manage_patients',
                'view_appointments', 'manage_appointments',
                'view_billing',
                'view_reports',
                'view_dashboard',
            ],
        ],

        'accountant' => [
            'name' => 'Contador',
            'description' => 'Contador del consultorio',
            'permissions' => [
                'view_patients',
                'view_billing', 'manage_billing',
                'process_payments', 'manage_daily_cash',
                'view_reports', 'export_reports',
                'view_dashboard',
            ],
        ],

        'inventory_manager' => [
            'name' => 'Gestor de Inventario',
            'description' => 'Gestor de inventario y compras',
            'permissions' => [
                'view_inventory', 'manage_inventory', 'adjust_inventory',
                'view_products', 'manage_products',
                'view_suppliers', 'manage_suppliers',
                'view_purchases', 'manage_purchases',
                'view_reports',
                'view_dashboard',
            ],
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Configuración de Permisos
    |--------------------------------------------------------------------------
    */

    'settings' => [
        'cache_permissions' => true,
        'cache_ttl' => 60, // minutos
        'super_admin_role' => 'super_admin',
        'default_role' => 'receptionist',
    ],
];





