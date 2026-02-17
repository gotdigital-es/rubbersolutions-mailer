<?php

namespace App\Mail\Admin;

use App\Mail\InternalMailable;
use RubberSolutions\ShopCore\Models\Customer;
use RubberSolutions\ShopCore\Models\Order;

/**
 * Email a administración: nuevo cliente registrado (pendiente VAT)
 */
class NewCustomerRegistrationNotification extends InternalMailable
{
    public function __construct(protected Customer $customer)
    {
        parent::__construct(
            subject: "Nuevo registro B2B: {$customer->company->name}",
            markdownContent: $this->buildContent()
        );

        // Enviar a equipo comercial
        $this->to(config('mail.admin_recipients.sales'));
    }

    private function buildContent(): string
    {
        $approvalUrl = route('filament.admin.resources.customers.edit', $this->customer);

        return <<<MARKDOWN
# Nuevo Cliente B2B Registrado

**Empresa:** {$this->customer->company->name}  
**Contacto:** {$this->customer->first_name} {$this->customer->last_name}  
**Email:** {$this->customer->email}  
**Teléfono:** {$this->customer->phone}  

**VAT Number:** {$this->customer->vat_number}  
**País:** {$this->customer->company->country->name}  
**Estado:** Pendiente de verificación VAT

---

## Acción requerida

Este cliente está pendiente de que se verifique su número VAT. 
Una vez verificado automáticamente o de forma manual, será activado.

[Ver cliente en admin]({$approvalUrl})

---

**Registro en:** {$this->customer->created_at->format('d/m/Y H:i')}
MARKDOWN;
    }
}

/**
 * Email a administración: nuevo pedido recibido
 */
class NewOrderNotification extends InternalMailable
{
    public function __construct(protected Order $order)
    {
        parent::__construct(
            subject: "Nuevo pedido: #{$order->order_number} - {$order->customer->company->name}",
            markdownContent: $this->buildContent()
        );

        // Enviar a equipo de operaciones
        $this->to(config('mail.admin_recipients.operations'));
    }

    private function buildContent(): string
    {
        $orderUrl = route('filament.admin.resources.orders.edit', $this->order);
        $items    = $this->order->items->map(fn($item) => 
            "- {$item->product->name} ({$item->variant->name}): {$item->quantity} ud. @ {$item->price}€"
        )->join("\n");

        return <<<MARKDOWN
# Nuevo Pedido Recibido

**Número de pedido:** #{$this->order->order_number}  
**Cliente:** {$this->order->customer->company->name}  
**Contacto:** {$this->order->customer->first_name} {$this->order->customer->last_name}  

**Total:** {$this->order->total}€ (sin IVA: {$this->order->subtotal}€)  
**Método de pago:** {$this->order->payment_method}  
**Estado:** {$this->order->status}  

## Items del pedido

{$items}

## Dirección de envío

{$this->order->shipping_address->full_address}

---

[Ver pedido en admin]({$orderUrl})

---

**Pedido realizado:** {$this->order->created_at->format('d/m/Y H:i')}
MARKDOWN;
    }
}

/**
 * Email a administración: error en sincronización Factusol
 */
class FactusolSyncErrorNotification extends InternalMailable
{
    public function __construct(
        protected string $errorMessage,
        protected ?string $entity = null,
        protected ?int $entityId = null
    ) {
        parent::__construct(
            subject: "⚠️ Error en sincronización Factusol",
            markdownContent: $this->buildContent()
        );

        // Enviar a equipo técnico
        $this->to(config('mail.admin_recipients.technical'))
             ->bcc('logs@vitamin-swiss.eu');
    }

    private function buildContent(): string
    {
        $time = now()->format('d/m/Y H:i:s');

        return <<<MARKDOWN
# Error en Sincronización Factusol

**Timestamp:** {$time}  
**Entidad:** {$this->entity}  
**ID:** {$this->entityId}  

## Descripción del error

{$this->errorMessage}

---

## Acción recomendada

1. Revisar los logs: `/var/log/vitamin-swiss/factusol-sync.log`
2. Verificar conectividad con API Factusol
3. Reintentar sincronización manualmente en admin

---

**Sistema:** Vitamin Swiss  
**Versión:** 1.0.0
MARKDOWN;
    }
}

/**
 * Email a administración: reporte diario de pedidos
 */
class DailyOrdersSummaryReport extends InternalMailable
{
    public function __construct(protected array $statistics)
    {
        parent::__construct(
            subject: "Reporte diario de pedidos - " . now()->format('d/m/Y'),
            markdownContent: $this->buildContent()
        );

        $this->to(config('mail.admin_recipients.management'));
    }

    private function buildContent(): string
    {
        $totalOrders      = $this->statistics['total_orders'] ?? 0;
        $totalRevenue     = $this->statistics['total_revenue'] ?? 0;
        $avgOrderValue    = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;
        $pendingOrders    = $this->statistics['pending_orders'] ?? 0;
        $newCustomers     = $this->statistics['new_customers'] ?? 0;
        $date             = now()->format('d/m/Y');

        return <<<MARKDOWN
# Reporte Diario de Pedidos

**Fecha:** {$date}

## Resumen de Pedidos

| Métrica | Valor |
|---------|-------|
| Total pedidos | {$totalOrders} |
| Ingresos totales | {$totalRevenue}€ |
| Ticket promedio | {$avgOrderValue}€ |
| Pedidos pendientes | {$pendingOrders} |
| Clientes nuevos | {$newCustomers} |

---

## Acciones pendientes

- Revisar {$pendingOrders} pedidos sin procesar
- Contactar {$newCustomers} cliente(s) nuevo(s) para verificación
- Sincronizar con Factusol

---

**Generado automáticamente por el sistema**
MARKDOWN;
    }
}
