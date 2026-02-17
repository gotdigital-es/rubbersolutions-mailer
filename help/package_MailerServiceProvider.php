<?php

namespace RubberSolutions\Mailer\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
use RubberSolutions\Mailer\Services\EmailService;
use RubberSolutions\Mailer\Services\TemplateService;
use RubberSolutions\Mailer\Services\MailConfigurationManager;
use RubberSolutions\Mailer\Factories\MailableFactory;

/**
 * Service Provider para el paquete rubbersolutions-mailer
 * 
 * Registra:
 * - Servicios centralizados de email
 * - Event listeners (si corresponde)
 * - Comandos de artisan
 * - Configuración y vistas
 */
class MailerServiceProvider extends ServiceProvider
{
    /**
     * Boot del paquete
     */
    public function boot(): void
    {
        // Publicar configuración
        $this->publishes([
            __DIR__ . '/../../config/mailer.php' => config_path('mailer.php'),
        ], 'rubbersolutions-mailer-config');

        // Publicar vistas (plantillas base)
        $this->publishes([
            __DIR__ . '/../../resources/views' => resource_path('views/vendor/rubbersolutions-mailer'),
        ], 'rubbersolutions-mailer-views');

        // Registrar comandos de artisan
        if ($this->app->runningInConsole()) {
            $this->commands([
                \RubberSolutions\Mailer\Commands\PublishTemplatesCommand::class,
                \RubberSolutions\Mailer\Commands\TestEmailCommand::class,
                \RubberSolutions\Mailer\Commands\ClearEmailCacheCommand::class,
            ]);
        }

        // Cargar rutas (si las hay)
        if (file_exists(__DIR__ . '/../../routes/web.php')) {
            $this->loadRoutesFrom(__DIR__ . '/../../routes/web.php');
        }

        // Registrar vistas del paquete
        $this->loadViewsFrom(
            __DIR__ . '/../../resources/views',
            'rubbersolutions-mailer'
        );

        // Cargar migraciones
        $this->loadMigrationsFrom(__DIR__ . '/../../database/migrations');
    }

    /**
     * Register del paquete
     */
    public function register(): void
    {
        // Mergear configuración
        $this->mergeConfigFrom(
            __DIR__ . '/../../config/mailer.php',
            'mailer'
        );

        // Registrar servicios singleton
        $this->registerServices();

        // Registrar eventos (listeners)
        $this->registerEventListeners();

        // Registrar factories
        $this->registerFactories();
    }

    /**
     * Registrar servicios centralizados
     */
    protected function registerServices(): void
    {
        // Servicio de templates (resuelve plantillas por tienda)
        $this->app->singleton(TemplateService::class, function ($app) {
            return new TemplateService(
                $app['config'],
                $app['files']
            );
        });

        // Gestor de configuración por tienda
        $this->app->singleton(MailConfigurationManager::class, function ($app) {
            return new MailConfigurationManager(
                $app['config'],
                $app[TemplateService::class]
            );
        });

        // Servicio principal de emails
        $this->app->singleton(EmailService::class, function ($app) {
            return new EmailService(
                $app['mailer'],
                $app[TemplateService::class],
                $app[MailConfigurationManager::class]
            );
        });

        // Alias para acceso más fácil
        $this->app->alias(EmailService::class, 'mailer.service');
    }

    /**
     * Registrar event listeners (si es necesario)
     */
    protected function registerEventListeners(): void
    {
        // Los listeners específicos se registran en cada storefront
        // via sus propios providers
        
        // Pero podemos registrar listener global para logging
        if (config('mailer.log_events')) {
            Event::listen(
                '*',
                \RubberSolutions\Mailer\Listeners\LogEmailEvent::class
            );
        }
    }

    /**
     * Registrar factories de emails
     */
    protected function registerFactories(): void
    {
        $this->app->singleton(MailableFactory::class, function ($app) {
            return new MailableFactory(
                $app[TemplateService::class]
            );
        });
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            EmailService::class,
            TemplateService::class,
            MailConfigurationManager::class,
            MailableFactory::class,
            'mailer.service',
        ];
    }
}
