<?php

/**
 * Configuración del paquete rubbersolutions-mailer
 *
 * Cada storefront puede personalizar estos valores publicando este archivo
 * con: php artisan vendor:publish --tag=rubbersolutions-mailer-config
 */

return [

    /*
    |--------------------------------------------------------------------------
    | Habilitación del paquete
    |--------------------------------------------------------------------------
    */
    'enabled' => env('MAILER_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | STORE_CODE actual
    |--------------------------------------------------------------------------
    | Se usa para resolver el tema visual y datos de contacto.
    | Cada storefront lo define en su .env
    */
    'store_code' => env('STORE_CODE', 'default'),

    /*
    |--------------------------------------------------------------------------
    | Temas visuales por storefront
    |--------------------------------------------------------------------------
    | Colores, logos, datos de contacto. CustomerMailable los inyecta
    | automáticamente en la plantilla HTML.
    |
    | Cada tienda hereda de 'default' y sobreescribe lo que necesite.
    */
    'themes' => [

        'default' => [
            'primary_color'      => '#0E870E',
            'primary_color_dark' => '#096909',
            'danger_color'       => '#D40000',
            'dark_color'         => '#171717',
            'accent_color'       => '#dbc005',
            'company_name'       => 'Rubber Solutions',
            'support_email'      => env('MAIL_SUPPORT_EMAIL', 'soporte@rubbersolutions.es'),
            'support_phone'      => env('MAIL_SUPPORT_PHONE', ''),
            'logo_url'           => env('MAIL_LOGO_URL', ''),
            'website_url'        => env('APP_URL', ''),
        ],

        'VITAMIN' => [
            'primary_color'      => '#0E870E',
            'primary_color_dark' => '#096909',
            'danger_color'       => '#D40000',
            'accent_color'       => '#dbc005',
            'company_name'       => 'Vitamin Swiss',
            'support_email'      => 'soporte@vitamin-swiss.eu',
            'support_phone'      => '+41 00 000 00 00',
            'logo_url'           => 'https://vitamin-swiss.eu/images/logo-white.svg',
            'website_url'        => 'https://vitamin-swiss.eu',
        ],

        'QUALIT' => [
            'primary_color'      => '#dc2626',
            'primary_color_dark' => '#b91c1c',
            'danger_color'       => '#D40000',
            'accent_color'       => '#fbbf24',
            'company_name'       => 'Qualit Glue',
            'support_email'      => 'soporte@qualitglue.com',
            'support_phone'      => '+34 912 345 678',
            'logo_url'           => 'https://qualitglue.com/images/logo-white.svg',
            'website_url'        => 'https://qualitglue.com',
        ],

    ],

    /*
    |--------------------------------------------------------------------------
    | Destinatarios administrativos por equipo
    |--------------------------------------------------------------------------
    */
    'admin_recipients' => [
        'sales'      => explode(',', env('MAIL_ADMIN_SALES', '')),
        'operations' => explode(',', env('MAIL_ADMIN_OPERATIONS', '')),
        'technical'  => explode(',', env('MAIL_ADMIN_TECHNICAL', '')),
        'management' => explode(',', env('MAIL_ADMIN_MANAGEMENT', '')),
        'critical'   => explode(',', env('MAIL_ADMIN_CRITICAL', '')),
    ],

    /*
    |--------------------------------------------------------------------------
    | Mailables disponibles (alias → clase)
    |--------------------------------------------------------------------------
    | Usados por MailableFactory para crear emails por nombre.
    | Solo incluye clases que realmente existen en el paquete.
    */
    'preset_templates' => [
        'customer.welcome'          => \RubberSolutions\Mailer\Mail\Mailables\CustomerWelcome::class,
        'customer.account_verified' => \RubberSolutions\Mailer\Mail\Mailables\CustomerAccountVerified::class,
        'customer.password_reset'   => \RubberSolutions\Mailer\Mail\Mailables\PasswordReset::class,
        'admin.new_customer'        => \RubberSolutions\Mailer\Mail\Mailables\AdminNewCustomer::class,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    */
    'log_events' => env('MAILER_LOG_EVENTS', true),

];
