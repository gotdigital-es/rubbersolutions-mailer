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
 * Clase para emails internos (a equipo, notificaciones administrativas)
 * 
 * Usa Markdown simple sin diseño gráfico
 */
class InternalMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Asunto del email
     */
    protected string $mailSubject;

    /**
     * Contenido en Markdown
     */
    protected string $markdownContent;

    /**
     * Destinatarios adicionales (CC, BCC)
     */
    protected array $cc = [];
    protected array $bcc = [];

    public function __construct(string $subject, string $markdownContent)
    {
        $this->mailSubject       = $subject;
        $this->markdownContent   = $markdownContent;
    }

    /**
     * Obtener el envelope
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $this->mailSubject,
            cc: $this->cc,
            bcc: $this->bcc,
            replyTo: [
                new Address('noreply@vitamin-swiss.eu', 'Vitamin Swiss'),
            ],
        );
    }

    /**
     * Obtener contenido Markdown
     */
    public function content(): Content
    {
        return new Content(
            markdown: 'emails.internal.simple',
            with: [
                'content' => $this->markdownContent,
            ],
        );
    }

    /**
     * Agregar destinatarios CC
     */
    public function cc(string|array $addresses): self
    {
        if (is_string($addresses)) {
            $this->cc[] = $addresses;
        } else {
            $this->cc = array_merge($this->cc, $addresses);
        }
        return $this;
    }

    /**
     * Agregar destinatarios BCC
     */
    public function bcc(string|array $addresses): self
    {
        if (is_string($addresses)) {
            $this->bcc[] = $addresses;
        } else {
            $this->bcc = array_merge($this->bcc, $addresses);
        }
        return $this;
    }
}
