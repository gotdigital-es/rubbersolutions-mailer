<?php

namespace RubberSolutions\Mailer\Providers;

use Illuminate\Support\ServiceProvider;
use RubberSolutions\Mailer\Services\EmailService;

/**
 * Service Provider para rubbersolutions-mailer.
 *
 * Registra: configuración, vistas, servicio de email.
 */
class MailerServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Publicar config
        $this->publishes([
            __DIR__ . '/../../config/mailer.php' => config_path('mailer.php'),
        ], 'rubbersolutions-mailer-config');

        // Publicar vistas (para override en storefronts)
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/rubbersolutions-mailer'),
        ], 'rubbersolutions-mailer-views');

        // Registrar vistas del paquete
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'rubbersolutions-mailer'
        );
    }

    public function register(): void
    {
        // Merge config
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/mailer.php',
            'mailer'
        );

        // EmailService como singleton
        $this->app->singleton(EmailService::class);
        $this->app->alias(EmailService::class, 'mailer.service');
    }
}
