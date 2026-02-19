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
 * Emails internos (notificaciones a equipo admin/operaciones).
 * Usa Markdown simple sin diseño gráfico.
 */
class InternalMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        protected string $mailSubject,
        protected string $markdownContent
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                config('mail.from.address'),
                config('mail.from.name')
            ),
            subject: $this->mailSubject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'rubbersolutions-mailer::emails.internal.simple',
            with: [
                'content' => $this->markdownContent,
            ],
        );
    }
}
