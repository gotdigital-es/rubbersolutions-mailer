<?php

namespace RubberSolutions\Mailer\Services;

use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio centralizado para envío de emails.
 *
 * Uso simple:
 *   app(EmailService::class)->send(new CustomerWelcome($customer), $customer->email);
 */
class EmailService
{
    /**
     * Enviar un email (se encola automáticamente si el Mailable implementa ShouldQueue).
     */
    public function send(Mailable $mailable, string|array $to): bool
    {
        try {
            Mail::to($to)->send($mailable);

            Log::channel('mail')->info('Email enviado', [
                'class' => class_basename($mailable),
                'to'    => $to,
            ]);

            return true;
        } catch (Exception $e) {
            Log::channel('mail')->error('Error al enviar email', [
                'class' => class_basename($mailable),
                'to'    => $to,
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Enviar email de forma síncrona (ignora ShouldQueue).
     */
    public function sendNow(Mailable $mailable, string|array $to): bool
    {
        try {
            Mail::to($to)->sendNow($mailable);
            return true;
        } catch (Exception $e) {
            Log::channel('mail')->error('Error al enviar email (sync)', [
                'class' => class_basename($mailable),
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Enviar a un equipo admin.
     */
    public function sendToTeam(Mailable $mailable, string $team = 'critical'): bool
    {
        $recipients = config("mailer.admin_recipients.{$team}", []);

        if (empty($recipients)) {
            Log::channel('mail')->warning("No hay destinatarios para equipo: {$team}");
            return false;
        }

        return $this->send($mailable, $recipients);
    }
}
