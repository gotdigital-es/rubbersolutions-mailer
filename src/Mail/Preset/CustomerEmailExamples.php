<?php

namespace App\Mail\Customer;

use App\Mail\CustomerMailable;
use RubberSolutions\ShopCore\Models\Customer;
use RubberSolutions\ShopCore\Models\Order;

/**
 * Email de bienvenida después del registro B2B
 */
class WelcomeEmail extends CustomerMailable
{
    public function __construct(protected Customer $customer)
    {
    }

    protected function getContent(): string
    {
        return <<<'HTML'
        <p>
            Gracias por registrarte en <strong>Vitamin Swiss</strong>. 
            Somos especialistas en la distribución de suplementos de máxima calidad.
        </p>

        <p>
            Tu cuenta está pendiente de verificación. Una vez verifiquemos tu número VAT, 
            tendrás acceso completo a:
        </p>

        <ul style="line-height: 1.8; margin: 20px 0;">
            <li>Catálogo completo de productos con precios por tarifa</li>
            <li>Área técnica con fichas y certificados</li>
            <li>Quick Order para pedidos rápidos</li>
            <li>Panel de cliente con historial de pedidos</li>
            <li>Acceso a documentación y recursos B2B</li>
        </ul>

        <p>
            <strong>¿Necesitas ayuda?</strong><br>
            Nuestro equipo de soporte está disponible en 
            <a href="mailto:soporte@vitamin-swiss.eu">soporte@vitamin-swiss.eu</a>
        </p>
        HTML;
    }

    protected function getSubject(): string
    {
        return 'Bienvenida a Vitamin Swiss - Cuenta registrada';
    }

    public function build(): self
    {
        return parent::build()
            ->withTitle('¡Bienvenida!')
            ->withIntroduction("Hola {$this->customer->first_name}, hemos recibido tu solicitud de registro.")
            ->withAction('Completar perfil', route('customer.account.profile'))
            ->withFooterNote(
                'Mientras verificamos tu cuenta, puedes explorar nuestro ' .
                '<a href="' . route('home') . '">catálogo público</a>'
            );
    }
}

/**
 * Email de confirmación de pedido
 */
class OrderConfirmationEmail extends CustomerMailable
{
    public function __construct(
        protected Customer $customer,
        protected Order $order
    ) {
    }

    protected function getContent(): string
    {
        $itemsHtml = '';
        foreach ($this->order->items as $item) {
            $itemsHtml .= <<<HTML
            <tr style="border-bottom: 1px solid #e5e7eb;">
                <td style="padding: 12px; text-align: left;">
                    {$item->product->name} ({$item->variant->name})
                </td>
                <td style="padding: 12px; text-align: center;">{$item->quantity}</td>
                <td style="padding: 12px; text-align: right;">
                    {$item->price} €
                </td>
                <td style="padding: 12px; text-align: right;">
                    <strong>{$item->subtotal} €</strong>
                </td>
            </tr>
            HTML;
        }

        $subtotal = $this->order->subtotal;
        $vat      = $this->order->vat_amount;
        $total    = $this->order->total;

        return <<<HTML
        <p>Gracias por tu pedido. Aquí está el resumen:</p>

        <table style="width: 100%; border-collapse: collapse; margin: 20px 0;">
            <thead style="background-color: #f3f4f6;">
                <tr>
                    <th style="padding: 12px; text-align: left; font-weight: bold;">Producto</th>
                    <th style="padding: 12px; text-align: center; font-weight: bold;">Cantidad</th>
                    <th style="padding: 12px; text-align: right; font-weight: bold;">Precio</th>
                    <th style="padding: 12px; text-align: right; font-weight: bold;">Total</th>
                </tr>
            </thead>
            <tbody>
                {$itemsHtml}
            </tbody>
        </table>

        <div style="text-align: right; margin: 20px 0; font-size: 16px;">
            <p style="margin: 8px 0;">
                Subtotal (sin IVA): <strong>{$subtotal} €</strong>
            </p>
            <p style="margin: 8px 0;">
                IVA ({$this->order->vat_rate}%): <strong>{$vat} €</strong>
            </p>
            <p style="margin: 12px 0; font-size: 18px; color: #0E870E;">
                <strong>Total: {$total} €</strong>
            </p>
        </div>

        <p>
            <strong>Número de pedido:</strong> #{$this->order->order_number}<br>
            <strong>Fecha:</strong> {$this->order->created_at->format('d/m/Y H:i')}<br>
            <strong>Dirección de envío:</strong> {$this->order->shipping_address->full_address}
        </p>

        <p>
            Recibirás un email con el número de seguimiento cuando se envíe tu pedido.
        </p>
        HTML;
    }

    protected function getSubject(): string
    {
        return "Pedido confirmado #{$this->order->order_number}";
    }

    public function build(): self
    {
        return parent::build()
            ->withTitle('Pedido Confirmado')
            ->withIntroduction("Tu pedido ha sido recibido correctamente.")
            ->withAction('Ver mi pedido', route('customer.orders.show', $this->order));
    }
}

/**
 * Email de envío / tracking
 */
class ShipmentNotificationEmail extends CustomerMailable
{
    public function __construct(
        protected Customer $customer,
        protected Order $order,
        protected string $trackingNumber,
        protected string $carrier
    ) {
    }

    protected function getContent(): string
    {
        return <<<HTML
        <p>
            Tu pedido <strong>#{$this->order->order_number}</strong> ha sido despachado 
            y está en tránsito hacia ti.
        </p>

        <div style="background-color: #f3f4f6; padding: 16px; border-radius: 8px; margin: 20px 0;">
            <p style="margin: 8px 0;"><strong>Transportista:</strong> {$this->carrier}</p>
            <p style="margin: 8px 0;">
                <strong>Número de seguimiento:</strong> 
                <code style="background: white; padding: 4px 8px;">{$this->trackingNumber}</code>
            </p>
            <p style="margin: 8px 0;">
                <strong>Fecha de envío:</strong> 
                {$this->order->shipped_at->format('d/m/Y H:i')}
            </p>
        </div>

        <p>
            Puedes seguir tu envío en la web del transportista usando el número de seguimiento anterior.
        </p>

        <p>
            Si tienes problemas con el tracking o no recibes tu pedido en los plazos esperados, 
            contacta con nuestro equipo de soporte.
        </p>
        HTML;
    }

    protected function getSubject(): string
    {
        return "Tu pedido #{$this->order->order_number} está en camino";
    }

    public function build(): self
    {
        return parent::build()
            ->withTitle('Pedido en Tránsito')
            ->withIntroduction("Actualizamos el estado de tu pedido.")
            ->withAction('Rastrear pedido', route('customer.orders.show', $this->order));
    }
}

/**
 * Email de reporte de cuenta (límite crédito, estado cuenta, etc.)
 */
class AccountReportEmail extends CustomerMailable
{
    public function __construct(
        protected Customer $customer,
        protected array $reportData
    ) {
    }

    protected function getContent(): string
    {
        $credit = $this->reportData['available_credit'] ?? 0;
        $limit  = $this->reportData['credit_limit'] ?? 0;
        $used   = $limit - $credit;
        $used_percent = $limit > 0 ? round(($used / $limit) * 100) : 0;

        return <<<HTML
        <p>
            Este es un resumen del estado de tu cuenta en Vitamin Swiss.
        </p>

        <h3 style="color: #171717; font-size: 18px; margin: 16px 0 8px 0;">
            Límite de Crédito
        </h3>

        <div style="background-color: #f3f4f6; padding: 16px; border-radius: 8px; margin: 12px 0;">
            <p style="margin: 8px 0;">
                <strong>Límite disponible:</strong> {$credit} €
            </p>
            <p style="margin: 8px 0;">
                <strong>Límite total:</strong> {$limit} €
            </p>
            <p style="margin: 8px 0;">
                <strong>Utilizado:</strong> {$used} € ({$used_percent}%)
            </p>
        </div>

        <p>
            Si necesitas aumentar tu límite de crédito o tienes preguntas sobre tu cuenta, 
            no dudes en contactar con nuestro departamento comercial.
        </p>
        HTML;
    }

    protected function getSubject(): string
    {
        return 'Resumen de cuenta - Vitamin Swiss';
    }

    public function build(): self
    {
        return parent::build()
            ->withTitle('Estado de tu Cuenta')
            ->withIntroduction("Aquí está el resumen de tu cuenta a " . now()->format('d/m/Y'))
            ->withAction('Ir a mi cuenta', route('customer.account.dashboard'));
    }
}
