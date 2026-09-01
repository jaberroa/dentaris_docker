<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Performance Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains performance-related configuration options for the
    | Dentaris application.
    |
    */

    'cache' => [
        'default_ttl' => env('CACHE_DEFAULT_TTL', 60), // minutes
        'dashboard_ttl' => env('CACHE_DASHBOARD_TTL', 30), // minutes
        'reports_ttl' => env('CACHE_REPORTS_TTL', 120), // minutes
        'statistics_ttl' => env('CACHE_STATISTICS_TTL', 60), // minutes
    ],

    'database' => [
        'query_logging' => env('DB_QUERY_LOGGING', false),
        'slow_query_threshold' => env('DB_SLOW_QUERY_THRESHOLD', 100), // milliseconds
        'connection_pooling' => env('DB_CONNECTION_POOLING', true),
        'index_optimization' => env('DB_INDEX_OPTIMIZATION', true),
    ],

    'api' => [
        'response_compression' => env('API_RESPONSE_COMPRESSION', true),
        'rate_limiting' => [
            'enabled' => env('API_RATE_LIMITING', true),
            'requests_per_minute' => env('API_RATE_LIMIT', 60),
        ],
        'caching' => [
            'enabled' => env('API_CACHING', true),
            'ttl' => env('API_CACHE_TTL', 30), // minutes
        ],
    ],

    'monitoring' => [
        'enabled' => env('PERFORMANCE_MONITORING', true),
        'slow_query_logging' => env('SLOW_QUERY_LOGGING', true),
        'memory_monitoring' => env('MEMORY_MONITORING', true),
        'cache_monitoring' => env('CACHE_MONITORING', true),
    ],

    'optimization' => [
        'eager_loading' => env('EAGER_LOADING', true),
        'query_optimization' => env('QUERY_OPTIMIZATION', true),
        'response_optimization' => env('RESPONSE_OPTIMIZATION', true),
        'asset_optimization' => env('ASSET_OPTIMIZATION', true),
    ],

    'indexes' => [
        'auto_create' => env('AUTO_CREATE_INDEXES', true),
        'common_indexes' => [
            'patients' => ['status', 'gender', 'created_at', 'email'],
            'appointments' => ['appointment_date', 'patient_id', 'staff_id', 'appointment_status_id'],
            'invoices' => ['invoice_date', 'status', 'patient_id', 'due_date'],
            'payments' => ['payment_date', 'status', 'patient_id', 'payment_method'],
            'products' => ['category', 'product_code'],
            'inventory' => ['current_stock', 'product_id'],
        ],
    ],

    'memory' => [
        'limit' => env('MEMORY_LIMIT', '256M'),
        'monitoring' => env('MEMORY_MONITORING', true),
        'gc_collect_cycles' => env('GC_COLLECT_CYCLES', true),
    ],

    'logging' => [
        'performance_log' => env('PERFORMANCE_LOG', 'performance.log'),
        'slow_queries_log' => env('SLOW_QUERIES_LOG', 'slow-queries.log'),
        'cache_log' => env('CACHE_LOG', 'cache.log'),
    ],
];





