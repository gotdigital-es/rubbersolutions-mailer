<?php

/**
 * config/mailer.php - Configuración del paquete rubbersolutions-mailer
 * 
 * Este archivo contiene la configuración para el sistema de emails.
 * Cada storefront puede personalizar estos valores en su .env o .env.{environment}
 */

return [
    /**
     * Habilitación del paquete
     */
    'enabled' => env('MAILER_ENABLED', true),

    /**
     * Storefront/Tienda actual
     * Se usa para resolver plantillas y configuración específica
     * 
     * Ejemplo: 'vitamin', 'qualit', etc.
     */
    'store_code' => env('STORE_CODE', 'default'),

    /**
     * Logging de eventos de email
     */
    'log_events' => env('MAILER_LOG_EVENTS', true),

    /**
     * Directorios de búsqueda de plantillas (en orden de precedencia)
     * 
     * 1. resources/emails/{store_code}/  - Plantillas personalizadas por tienda
     * 2. resources/emails/shared/         - Plantillas compartidas entre tiendas
     * 3. vendor/rubbersolutions/rubbersolutions-mailer/resources/views/  - Plantillas default
     */
    'template_paths' => [
        'store'   => resource_path('emails/{store_code}'),
        'shared'  => resource_path('emails/shared'),
        'default' => __DIR__ . '/../resources/views/emails',
    ],

    /**
     * Configuración de temas visuales por storefront
     * 
     * Puedes sobrescribir esto en cada storefront en
     * config/mailer-stores.php o desde el .env
     */
    'themes' => [
        'default' => [
            'primary_color'   => '#0E870E',
            'secondary_color' => '#171717',
            'accent_color'    => '#dbc005',
            'danger_color'    => '#D40000',
            'company_name'    => 'Rubber Solutions',
            'support_email'   => env('MAIL_SUPPORT_EMAIL', 'soporte@example.com'),
            'support_phone'   => env('MAIL_SUPPORT_PHONE', '+34 000 000 000'),
            'logo_url'        => env('MAIL_LOGO_URL', '/images/logo-white.svg'),
        ],

        // Tema para Vitamin Swiss (B2B suizo)
        'vitamin' => [
            'primary_color'   => '#0E870E',
            'secondary_color' => '#171717',
            'accent_color'    => '#dbc005',
            'company_name'    => 'Vitamin Swiss',
            'support_email'   => 'soporte@vitamin-swiss.eu',
            'support_phone'   => '+41 00 000 00 00',
            'logo_url'        => '/images/logo-vitamin-white.svg',
        ],

        // Tema para Qualit Glue (B2C industrial)
        'qualit' => [
            'primary_color'   => '#dc2626',
            'secondary_color' => '#171717',
            'accent_color'    => '#fbbf24',
            'company_name'    => 'Qualit Glue',
            'support_email'   => 'soporte@qualitglue.com',
            'support_phone'   => '+34 912 345 678',
            'logo_url'        => '/images/logo-qualit-white.svg',
        ],
    ],

    /**
     * Destinatarios administrativos por equipo
     * 
     * Utilizado para enviar notificaciones internas
     */
    'admin_recipients' => [
        'sales'       => explode(',', env('MAIL_ADMIN_SALES', 'sales@example.com')),
        'operations'  => explode(',', env('MAIL_ADMIN_OPERATIONS', 'ops@example.com')),
        'technical'   => explode(',', env('MAIL_ADMIN_TECHNICAL', 'tech@example.com')),
        'management'  => explode(',', env('MAIL_ADMIN_MANAGEMENT', 'mgmt@example.com')),
        'critical'    => explode(',', env('MAIL_ADMIN_CRITICAL', 'admin@example.com')),
    ],

    /**
     * Queue por defecto para envío asincrónico
     * 
     * 'default' | 'critical' | 'sync' (síncrono, no recomendado en producción)
     */
    'queue' => env('MAIL_QUEUE', 'default'),

    /**
     * Número de reintentos automáticos en caso de fallo
     */
    'retry_attempts' => env('MAIL_RETRY_ATTEMPTS', 3),

    /**
     * Segundos de espera entre reintentos
     */
    'retry_delay_seconds' => env('MAIL_RETRY_DELAY_SECONDS', 5),

    /**
     * Plantillas predefinidas que ofrece el paquete
     * 
     * Cada tienda puede sobrescribir o agregar más
     * 
     * Formato: 'alias' => 'MailableClass'
     */
    'preset_templates' => [
        // B2B - Emails para clientes
        'customer.welcome'           => \RubberSolutions\Mailer\Mail\Preset\CustomerWelcome::class,
        'customer.account_verified'  => \RubberSolutions\Mailer\Mail\Preset\CustomerAccountVerified::class,
        'customer.account_rejected'  => \RubberSolutions\Mailer\Mail\Preset\CustomerAccountRejected::class,
        'order.confirmation'         => \RubberSolutions\Mailer\Mail\Preset\OrderConfirmation::class,
        'order.shipment'             => \RubberSolutions\Mailer\Mail\Preset\ShipmentNotification::class,
        'order.delivered'            => \RubberSolutions\Mailer\Mail\Preset\OrderDelivered::class,
        'account.report'             => \RubberSolutions\Mailer\Mail\Preset\AccountReport::class,
        'password.reset'             => \RubberSolutions\Mailer\Mail\Preset\PasswordReset::class,

        // Admin - Emails internos
        'admin.new_customer'         => \RubberSolutions\Mailer\Mail\Preset\AdminNewCustomer::class,
        'admin.new_order'            => \RubberSolutions\Mailer\Mail\Preset\AdminNewOrder::class,
        'admin.sync_error'           => \RubberSolutions\Mailer\Mail\Preset\AdminSyncError::class,
        'admin.daily_report'         => \RubberSolutions\Mailer\Mail\Preset\AdminDailyReport::class,
    ],

    /**
     * Vistas base a cargar automáticamente
     */
    'views' => [
        'customer_layout'   => 'rubbersolutions-mailer::emails.customers.layout',
        'internal_layout'   => 'rubbersolutions-mailer::emails.internal.layout',
        'welcome_customer'  => 'rubbersolutions-mailer::emails.customers.welcome',
    ],

    /**
     * Cache de templates (optimización)
     * 
     * true: cachear templates compilados
     * false: recompilar cada vez (desarrollo)
     */
    'cache_templates' => env('MAILER_CACHE_TEMPLATES', env('APP_ENV') === 'production'),

    /**
     * Rate limiting (importante para SES)
     * 
     * Máximo de emails por segundo
     * SES Sandbox: 1
     * SES Producción: depende de tu límite (empezar con 5)
     */
    'rate_limit_per_second' => env('MAIL_RATE_LIMIT', 5),

    /**
     * Validación de emails
     */
    'validation' => [
        'validate_email' => true,
        'validate_on_send' => true,
    ],

    /**
     * Tracking y logging de deliverables
     * 
     * Útil para integración con EventBridge / SNS de AWS
     */
    'tracking' => [
        'enabled' => env('MAILER_TRACKING_ENABLED', false),
        'store_events' => env('MAILER_STORE_EVENTS', true),
        'configuration_set' => env('AWS_SES_CONFIGURATION_SET', null),
    ],

    /**
     * Certificados SSL para conexión SMTP (si aplica)
     */
    'ssl' => [
        'verify_peer' => env('MAIL_SSL_VERIFY_PEER', true),
        'verify_peer_name' => env('MAIL_SSL_VERIFY_PEER_NAME', true),
    ],
];
