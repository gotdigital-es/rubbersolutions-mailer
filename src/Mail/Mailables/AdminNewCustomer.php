<?php

namespace RubberSolutions\Mailer\Mail\Mailables;

use RubberSolutions\Mailer\Mail\InternalMailable;
use RubberSolutions\ShopCore\Models\Customer;

/**
 * Notificación interna: nuevo cliente registrado (pendiente verificación VAT).
 */
class AdminNewCustomer extends InternalMailable
{
    public function __construct(protected Customer $customer)
    {
        $storeName = config('mailer.themes.' . config('mailer.store_code', 'default') . '.company_name', 'Rubber Solutions');

        parent::__construct(
            mailSubject: "[{$storeName}] Nuevo registro B2B: {$customer->first_name} {$customer->last_name}",
            markdownContent: $this->buildContent()
        );
    }

    private function buildContent(): string
    {
        return <<<MARKDOWN
# Nuevo Cliente B2B Registrado

**Contacto:** {$this->customer->first_name} {$this->customer->last_name}
**Email:** {$this->customer->email}
**VAT Number:** {$this->customer->vat_number}
**Estado:** Pendiente de verificación VAT

---

**Registro:** {$this->customer->created_at?->format('d/m/Y H:i') ?? 'ahora'}
MARKDOWN;
    }
}
