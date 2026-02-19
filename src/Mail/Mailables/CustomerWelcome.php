<?php

namespace RubberSolutions\Mailer\Mail\Mailables;

use RubberSolutions\Mailer\Mail\CustomerMailable;
use RubberSolutions\ShopCore\Models\Customer;

/**
 * Email de bienvenida tras registro B2B.
 * Informa al cliente que su cuenta está pendiente de verificación VAT.
 */
class CustomerWelcome extends CustomerMailable
{
    public function __construct(protected Customer $customer)
    {
        $this->emailTitle = '¡Bienvenido!';
        $this->introMessage = "Hola {$this->customer->first_name}, hemos recibido tu solicitud de registro.";
    }

    protected function getContent(): string
    {
        $companyName = $this->getStoreTheme()['company_name'] ?? 'nuestra tienda';

        return <<<HTML
        <p>
            Gracias por registrarte en <strong>{$companyName}</strong>.
        </p>

        <p>
            Tu cuenta está pendiente de verificación. Una vez verifiquemos tu número VAT,
            tendrás acceso completo a nuestro catálogo con precios personalizados.
        </p>

        <p>
            <strong>¿Necesitas ayuda?</strong><br>
            Nuestro equipo de soporte está disponible en
            <a href="mailto:{$this->getStoreTheme()['support_email']}">{$this->getStoreTheme()['support_email']}</a>
        </p>
        HTML;
    }

    protected function getSubject(): string
    {
        $companyName = $this->getStoreTheme()['company_name'] ?? 'Rubber Solutions';
        return "Bienvenido a {$companyName} - Cuenta registrada";
    }
}
