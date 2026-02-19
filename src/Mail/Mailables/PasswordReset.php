<?php

namespace RubberSolutions\Mailer\Mail\Mailables;

use RubberSolutions\Mailer\Mail\CustomerMailable;
use RubberSolutions\ShopCore\Models\Customer;

/**
 * Email de restablecimiento de contraseña.
 */
class PasswordReset extends CustomerMailable
{
    public function __construct(
        protected Customer $customer,
        protected string $resetUrl
    ) {
        $this->emailTitle = 'Restablecer Contraseña';
    }

    protected function getContent(): string
    {
        return <<<HTML
        <p>
            Hola <strong>{$this->customer->first_name}</strong>,
        </p>

        <p>
            Hemos recibido una solicitud para restablecer la contraseña de tu cuenta.
            Si no has sido tú, puedes ignorar este mensaje.
        </p>

        <p>
            Este enlace expira en 60 minutos.
        </p>
        HTML;
    }

    protected function getSubject(): string
    {
        $companyName = $this->getStoreTheme()['company_name'] ?? 'Rubber Solutions';
        return "Restablecer contraseña - {$companyName}";
    }

    /**
     * Override content to add the reset button.
     */
    public function content(): \Illuminate\Mail\Mailables\Content
    {
        $this->mainAction = [
            'label' => 'Restablecer Contraseña',
            'url'   => $this->resetUrl,
            'color' => $this->getStoreTheme()['primary_color'] ?? '#0E870E',
        ];

        return parent::content();
    }
}
