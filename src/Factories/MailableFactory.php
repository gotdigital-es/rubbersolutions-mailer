<?php

namespace RubberSolutions\Mailer\Factories;

use RubberSolutions\Mailer\Services\TemplateService;

/**
 * Factory para crear instancias de Mailable dinámicamente
 * 
 * Permite crear emails basándose en alias predefinidos en configuración
 * 
 * Uso:
 * $factory = app(MailableFactory::class);
 * $email = $factory->make('customer.welcome', ['customer' => $customer]);
 */
class MailableFactory
{
    public function __construct(protected TemplateService $templateService)
    {
    }

    /**
     * Crear un mailable a partir de un alias
     * 
     * @param string $alias Nombre del alias (ej: 'customer.welcome')
     * @param array $data Datos a pasar al constructor (como array clave => valor)
     * @return mixed Instancia del Mailable
     * @throws \Exception Si el alias no existe
     */
    public function make(string $alias, array $data = [])
    {
        $presets = config('mailer.preset_templates', []);
        
        if (!isset($presets[$alias])) {
            throw new \Exception("Preset template '{$alias}' not found in mailer.preset_templates");
        }

        $class = $presets[$alias];
        
        // Verificar que la clase existe
        if (!class_exists($class)) {
            throw new \Exception("Class '{$class}' does not exist for preset '{$alias}'");
        }

        // Crear instancia con los datos proporcionados
        return new $class(...array_values($data));
    }

    /**
     * Verificar si un alias existe
     */
    public function exists(string $alias): bool
    {
        $presets = config('mailer.preset_templates', []);
        return isset($presets[$alias]);
    }

    /**
     * Obtener todos los aliases disponibles
     */
    public function getAvailableAliases(): array
    {
        return array_keys(config('mailer.preset_templates', []));
    }
}