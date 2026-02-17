<?php

namespace RubberSolutions\Mailer\Services;

use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;

/**
 * Servicio para resolver plantillas de email por tienda
 * 
 * Busca plantillas en este orden:
 * 1. resources/emails/{store_code}/template.blade.php (personalizado de tienda)
 * 2. resources/emails/shared/template.blade.php (compartido entre tiendas)
 * 3. vendor/.../resources/views/emails/template.blade.php (default del paquete)
 */
class TemplateService
{
    public function __construct(
        protected ConfigRepository $config,
        protected Filesystem $files
    ) {
    }

    /**
     * Resolver una plantilla para una tienda
     * 
     * @param string $template Nombre de la plantilla (sin extensión)
     * @param string $storeCode Código de la tienda
     * @return string Ruta completa a la plantilla
     * @throws \Exception Si no encuentra la plantilla
     */
    public function resolve(string $template, string $storeCode = 'default'): string
    {
        $storeCode = $storeCode ?? $this->config->get('mailer.store_code', 'default');

        // Rutas de búsqueda en orden de precedencia
        $paths = [
            resource_path("emails/{$storeCode}/{$template}.blade.php"),
            resource_path("emails/shared/{$template}.blade.php"),
            $this->config->get('mailer.template_paths.default') . "/{$template}.blade.php",
        ];

        foreach ($paths as $path) {
            if ($this->files->exists($path)) {
                return $path;
            }
        }

        throw new \Exception("Template '{$template}' not found for store '{$storeCode}'");
    }

    /**
     * Verificar si una plantilla existe
     */
    public function exists(string $template, string $storeCode = 'default'): bool
    {
        try {
            $this->resolve($template, $storeCode);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}