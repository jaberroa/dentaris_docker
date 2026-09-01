<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Security Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains security-related configuration options for the
    | Dentaris application.
    |
    */

    'encryption' => [
        'enabled' => env('SECURITY_ENCRYPTION_ENABLED', true),
        'key' => env('SECURITY_ENCRYPTION_KEY', env('APP_KEY')),
        'cipher' => env('SECURITY_ENCRYPTION_CIPHER', 'AES-256-CBC'),
    ],

    'two_factor_auth' => [
        'enabled' => env('2FA_ENABLED', true),
        'required_for_admin' => env('2FA_REQUIRED_ADMIN', true),
        'required_for_staff' => env('2FA_REQUIRED_STAFF', false),
        'backup_codes_count' => env('2FA_BACKUP_CODES_COUNT', 10),
        'window' => env('2FA_WINDOW', 1), // Time window for TOTP
    ],

    'login_security' => [
        'max_attempts' => env('MAX_LOGIN_ATTEMPTS', 5),
        'lockout_duration' => env('LOCKOUT_DURATION', 15), // minutes
        'password_min_length' => env('PASSWORD_MIN_LENGTH', 8),
        'password_require_special' => env('PASSWORD_REQUIRE_SPECIAL', true),
        'password_require_numbers' => env('PASSWORD_REQUIRE_NUMBERS', true),
        'password_require_uppercase' => env('PASSWORD_REQUIRE_UPPERCASE', true),
        'password_require_lowercase' => env('PASSWORD_REQUIRE_LOWERCASE', true),
    ],

    'session_security' => [
        'timeout' => env('SESSION_TIMEOUT', 120), // minutes
        'regenerate_on_login' => env('SESSION_REGENERATE_LOGIN', true),
        'secure_cookies' => env('SESSION_SECURE_COOKIES', true),
        'http_only_cookies' => env('SESSION_HTTP_ONLY', true),
        'same_site' => env('SESSION_SAME_SITE', 'strict'),
    ],

    'audit_logging' => [
        'enabled' => env('AUDIT_LOGGING_ENABLED', true),
        'log_failed_logins' => env('AUDIT_LOG_FAILED_LOGINS', true),
        'log_successful_logins' => env('AUDIT_LOG_SUCCESSFUL_LOGINS', true),
        'log_password_changes' => env('AUDIT_LOG_PASSWORD_CHANGES', true),
        'log_data_access' => env('AUDIT_LOG_DATA_ACCESS', true),
        'retention_days' => env('AUDIT_LOG_RETENTION_DAYS', 365),
    ],

    'xss_protection' => [
        'enabled' => env('XSS_PROTECTION_ENABLED', true),
        'sanitize_input' => env('XSS_SANITIZE_INPUT', true),
        'strip_dangerous_tags' => env('XSS_STRIP_DANGEROUS_TAGS', true),
        'encode_special_chars' => env('XSS_ENCODE_SPECIAL_CHARS', true),
        'log_attempts' => env('XSS_LOG_ATTEMPTS', true),
    ],

    'csrf_protection' => [
        'enabled' => env('CSRF_PROTECTION_ENABLED', true),
        'token_lifetime' => env('CSRF_TOKEN_LIFETIME', 7200), // seconds
        'check_referer' => env('CSRF_CHECK_REFERER', true),
        'check_origin' => env('CSRF_CHECK_ORIGIN', true),
        'log_violations' => env('CSRF_LOG_VIOLATIONS', true),
    ],

    'rate_limiting' => [
        'enabled' => env('RATE_LIMITING_ENABLED', true),
        'login_attempts' => env('RATE_LIMIT_LOGIN_ATTEMPTS', 5),
        'api_requests' => env('RATE_LIMIT_API_REQUESTS', 60),
        'password_reset' => env('RATE_LIMIT_PASSWORD_RESET', 3),
        'window' => env('RATE_LIMIT_WINDOW', 60), // minutes
    ],

    'headers' => [
        'x_frame_options' => env('SECURITY_X_FRAME_OPTIONS', 'DENY'),
        'x_content_type_options' => env('SECURITY_X_CONTENT_TYPE_OPTIONS', 'nosniff'),
        'x_xss_protection' => env('SECURITY_X_XSS_PROTECTION', '1; mode=block'),
        'referrer_policy' => env('SECURITY_REFERRER_POLICY', 'strict-origin-when-cross-origin'),
        'content_security_policy' => env('SECURITY_CSP_ENABLED', true),
        'strict_transport_security' => env('SECURITY_HSTS_ENABLED', true),
    ],

    'data_protection' => [
        'encrypt_sensitive_fields' => env('ENCRYPT_SENSITIVE_FIELDS', true),
        'sensitive_fields' => [
            'email',
            'phone',
            'address',
            'emergency_contact_phone',
            'medical_conditions',
            'allergies',
            'medications',
            'notes',
            'payment_method',
            'card_number',
            'cvv',
            'bank_account',
            'social_security_number',
            'insurance_number',
        ],
        'anonymize_after_days' => env('ANONYMIZE_DATA_AFTER_DAYS', 2555), // 7 years
    ],

    'backup_security' => [
        'encrypt_backups' => env('ENCRYPT_BACKUPS', true),
        'backup_retention_days' => env('BACKUP_RETENTION_DAYS', 30),
        'secure_backup_storage' => env('SECURE_BACKUP_STORAGE', true),
    ],

    'monitoring' => [
        'enabled' => env('SECURITY_MONITORING_ENABLED', true),
        'alert_on_suspicious_activity' => env('ALERT_SUSPICIOUS_ACTIVITY', true),
        'alert_on_failed_logins' => env('ALERT_FAILED_LOGINS', true),
        'alert_on_admin_actions' => env('ALERT_ADMIN_ACTIONS', true),
        'notification_email' => env('SECURITY_NOTIFICATION_EMAIL'),
    ],

    'compliance' => [
        'gdpr_enabled' => env('GDPR_ENABLED', true),
        'hipaa_enabled' => env('HIPAA_ENABLED', true),
        'data_retention_days' => env('DATA_RETENTION_DAYS', 2555), // 7 years
        'right_to_be_forgotten' => env('RIGHT_TO_BE_FORGOTTEN', true),
        'data_portability' => env('DATA_PORTABILITY', true),
    ],
];





