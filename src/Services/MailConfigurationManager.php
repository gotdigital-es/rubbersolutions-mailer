<?php

namespace RubberSolutions\Mailer\Services;

use Illuminate\Config\Repository as ConfigRepository;

/**
 * Gestor de configuración de emails por tienda
 * 
 * Maneja:
 * - Temas visuales (colores, logos, etc.)
 * - Configuración específica de tienda
 * - Destinatarios admin por equipo
 */
class MailConfigurationManager
{
    public function __construct(
        protected ConfigRepository $config,
        protected TemplateService $templateService
    ) {
    }

    /**
     * Obtener tema completo de una tienda
     * 
     * @param string $storeCode Código de la tienda
     * @return array Configuración de tema
     */
    public function getTheme(string $storeCode = 'default'): array
    {
        $storeCode = $storeCode ?? $this->config->get('mailer.store_code', 'default');
        
        $themes = $this->config->get('mailer.themes', []);
        
        return $themes[$storeCode] ?? $themes['default'] ?? [];
    }

    /**
     * Obtener un valor específico del tema
     * 
     * @param string $key Clave del tema (ej: 'primary_color')
     * @param string $storeCode Código de la tienda
     * @param mixed $default Valor por defecto si no existe
     * @return mixed
     */
    public function getThemeValue(string $key, string $storeCode = 'default', $default = null)
    {
        $theme = $this->getTheme($storeCode);
        return $theme[$key] ?? $default;
    }

    /**
     * Establecer tema de una tienda
     * 
     * @param string $storeCode Código de la tienda
     * @param array $theme Configuración de tema
     */
    public function setTheme(string $storeCode, array $theme): void
    {
        $themes = $this->config->get('mailer.themes', []);
        $themes[$storeCode] = $theme;
        $this->config->set('mailer.themes', $themes);
    }

    /**
     * Obtener destinatarios admin de un equipo
     * 
     * @param string $team Nombre del equipo (sales, operations, technical, etc.)
     * @return array Lista de emails
     */
    public function getAdminRecipients(string $team = 'critical'): array
    {
        return $this->config->get("mailer.admin_recipients.{$team}") ?? [];
    }

    /**
     * Obtener configuración completa del mailer
     */
    public function getConfig(): array
    {
        return $this->config->get('mailer', []);
    }

    /**
     * Obtener tienda actual
     */
    public function getCurrentStore(): string
    {
        return $this->config->get('mailer.store_code', 'default');
    }
}