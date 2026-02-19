<?php

namespace RubberSolutions\Mailer\Mail\Mailables;

use RubberSolutions\Mailer\Mail\CustomerMailable;
use RubberSolutions\ShopCore\Models\Customer;

/**
 * Email de cuenta verificada / activada.
 * Se envía cuando el VAT del cliente ha sido validado y la cuenta está activa.
 */
class CustomerAccountVerified extends CustomerMailable
{
    public function __construct(protected Customer $customer)
    {
        $this->emailTitle = 'Cuenta Activada';
        $this->introMessage = "Hola {$this->customer->first_name}, tu cuenta ha sido verificada correctamente.";
    }

    protected function getContent(): string
    {
        $companyName = $this->getStoreTheme()['company_name'] ?? 'nuestra tienda';

        return <<<HTML
        <p>
            Tu número VAT ha sido verificado y tu cuenta en <strong>{$companyName}</strong>
            está ahora completamente activa.
        </p>

        <p>Ya puedes acceder a:</p>
        <ul style="line-height: 1.8; margin: 16px 0;">
            <li>Catálogo completo con precios personalizados</li>
            <li>Realizar pedidos online</li>
            <li>Área técnica con fichas y certificados</li>
            <li>Historial de pedidos y facturas</li>
        </ul>
        HTML;
    }

    protected function getSubject(): string
    {
        $companyName = $this->getStoreTheme()['company_name'] ?? 'Rubber Solutions';
        return "Tu cuenta en {$companyName} ha sido activada";
    }
}
