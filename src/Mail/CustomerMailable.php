<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Clase base para emails a clientes B2B de Vitamin Swiss
 * 
 * Proporciona diseño consistente industrial suizo con:
 * - Header con logo y diseño corporativo
 * - Footer con información de contacto
 * - Estilos inline para compatibilidad con clientes de email
 */
abstract class CustomerMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Título del email (usado en subject y header visual)
     */
    protected string $emailTitle = '';

    /**
     * Mensaje introductorio (opcional)
     */
    protected ?string $introMessage = null;

    /**
     * Acción principal (botón CTA)
     */
    protected ?array $mainAction = null;

    /**
     * Nota al pie importante (ej: aviso legal)
     */
    protected ?string $footerNote = null;

    /**
     * Obtener el envelope (destinatario, asunto, reply-to)
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $this->getSubject(),
            replyTo: [
                new Address('soporte@vitamin-swiss.eu', 'Soporte Vitamin Swiss'),
            ],
        );
    }

    /**
     * Obtener el contenido del email
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.customers.layout',
            with: [
                'title'        => $this->emailTitle,
                'intro'        => $this->introMessage,
                'content'      => $this->getContent(),
                'mainAction'   => $this->mainAction,
                'footerNote'   => $this->footerNote,
            ],
        );
    }

    /**
     * Método abstracto que cada email debe implementar
     * Retorna el HTML del contenido principal
     */
    abstract protected function getContent(): string;

    /**
     * Obtener el asunto del email (personalizable en subclases)
     */
    protected function getSubject(): string
    {
        return $this->emailTitle ?: 'Notificación de Vitamin Swiss';
    }

    /**
     * Establecer título del email
     */
    public function withTitle(string $title): self
    {
        $this->emailTitle = $title;
        return $this;
    }

    /**
     * Establecer mensaje introductorio
     */
    public function withIntroduction(string $message): self
    {
        $this->introMessage = $message;
        return $this;
    }

    /**
     * Establecer acción principal (botón)
     */
    public function withAction(string $label, string $url, string $color = '#0E870E'): self
    {
        $this->mainAction = [
            'label' => $label,
            'url'   => $url,
            'color' => $color,
        ];
        return $this;
    }

    /**
     * Establecer nota al pie
     */
    public function withFooterNote(string $note): self
    {
        $this->footerNote = $note;
        return $this;
    }

    /**
     * Obtener propiedades de diseño para usar en la vista
     */
    protected function getDesignProperties(): array
    {
        return [
            'colors' => [
                'primary'   => '#0E870E',    // Verde corporativo
                'danger'    => '#D40000',    // Rojo
                'dark'      => '#171717',    // Negro
                'accent'    => '#dbc005',    // Amarillo mostaza
            ],
            'fonts' => [
                'family'  => "'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif",
                'heading' => '24px',
                'body'    => '16px',
                'small'   => '14px',
            ],
        ];
    }
}
