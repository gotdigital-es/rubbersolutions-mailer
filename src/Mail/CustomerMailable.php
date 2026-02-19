<?php

namespace RubberSolutions\Mailer\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Clase base para emails a clientes de cualquier storefront.
 *
 * Resuelve automáticamente el tema visual (colores, logo, datos de contacto)
 * a partir del STORE_CODE del storefront.
 *
 * Implementa ShouldQueue para envío asíncrono.
 * El worker de colas debe ejecutarse desde el contexto de cada storefront.
 */
abstract class CustomerMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    protected string $emailTitle = '';
    protected ?string $introMessage = null;
    protected ?array $mainAction = null;
    protected ?string $footerNote = null;

    /**
     * Envelope: remitente, asunto, reply-to.
     * Todo se resuelve desde la config del storefront.
     */
    public function envelope(): Envelope
    {
        $theme = $this->getStoreTheme();

        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $this->getSubject(),
            replyTo: [
                new Address(
                    $theme['support_email'] ?? config('mail.from.address'),
                    $theme['company_name'] ?? config('mail.from.name')
                ),
            ],
        );
    }

    /**
     * Content: usa la vista del paquete inyectando el tema de la tienda.
     */
    public function content(): Content
    {
        $theme = $this->getStoreTheme();

        return new Content(
            view: 'rubbersolutions-mailer::emails.customers.layout',
            with: [
                'title'      => $this->emailTitle,
                'intro'      => $this->introMessage,
                'content'    => $this->getContent(),
                'mainAction' => $this->mainAction,
                'footerNote' => $this->footerNote,
                'theme'      => $theme,
            ],
        );
    }

    /** Cada email implementa su contenido HTML */
    abstract protected function getContent(): string;

    /** Asunto del email (override en subclases) */
    protected function getSubject(): string
    {
        $companyName = $this->getStoreTheme()['company_name'] ?? 'Rubber Solutions';
        return $this->emailTitle ?: "Notificación de {$companyName}";
    }

    /**
     * Resolver tema visual de la tienda actual.
     * Merge de default + store-specific para heredar valores no definidos.
     */
    protected function getStoreTheme(): array
    {
        $storeCode = config('mailer.store_code', 'default');
        $themes = config('mailer.themes', []);

        return array_merge(
            $themes['default'] ?? [],
            $themes[$storeCode] ?? []
        );
    }

    // ── Fluent setters ──────────────────────────────────────

    public function withTitle(string $title): static
    {
        $this->emailTitle = $title;
        return $this;
    }

    public function withIntroduction(string $message): static
    {
        $this->introMessage = $message;
        return $this;
    }

    public function withAction(string $label, string $url, ?string $color = null): static
    {
        $this->mainAction = [
            'label' => $label,
            'url'   => $url,
            'color' => $color ?? ($this->getStoreTheme()['primary_color'] ?? '#0E870E'),
        ];
        return $this;
    }

    public function withFooterNote(string $note): static
    {
        $this->footerNote = $note;
        return $this;
    }
}
