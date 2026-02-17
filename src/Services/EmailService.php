<?php

namespace RubberSolutions\Mailer\Services;


use Illuminate\Mail\Mailable;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Servicio centralizado para envío de emails
 * 
 * Proporciona métodos helper para:
 * - Envío directo de Mailable
 * - Envío en cola (queue)
 * - Logging y manejo de errores
 * - Retry automático en caso de fallo
 */
class EmailService
{
    /**
     * Enviar email inmediatamente
     * 
     * @param Mailable $mailable
     * @param bool $queue Si true, encola el email en lugar de enviarlo inmediatamente
     * @return bool
     */
    public function send(Mailable $mailable, bool $queue = true): bool
    {
        try {
            if ($queue) {
                Mail::queue($mailable);
            } else {
                Mail::send($mailable);
            }

            Log::info('Email enviado exitosamente', [
                'class'      => class_basename($mailable),
                'to'         => $mailable->getContainer()['mailer']?->getTo() ?? 'desconocido',
                'queued'     => $queue,
                'timestamp'  => now()->toIso8601String(),
            ]);

            return true;
        } catch (Exception $e) {
            Log::error('Error al enviar email', [
                'class'     => class_basename($mailable),
                'error'     => $e->getMessage(),
                'trace'     => $e->getTraceAsString(),
                'timestamp' => now()->toIso8601String(),
            ]);

            return false;
        }
    }

    /**
     * Enviar email a múltiples destinatarios
     * 
     * @param Mailable $mailable
     * @param array $recipients Lista de emails
     * @param bool $queue
     * @return int Número de emails enviados exitosamente
     */
    public function sendToMultiple(Mailable $mailable, array $recipients, bool $queue = true): int
    {
        $sent = 0;

        foreach ($recipients as $email) {
            try {
                $mail = clone $mailable;
                $mail->to($email);
                
                if ($this->send($mail, $queue)) {
                    $sent++;
                }
            } catch (Exception $e) {
                Log::error("Error al enviar a {$email}: " . $e->getMessage());
            }
        }

        return $sent;
    }

    /**
     * Enviar email con reintentos automáticos
     * 
     * @param Mailable $mailable
     * @param int $maxAttempts Número de intentos máximos
     * @param int $delaySeconds Segundos de espera entre intentos
     * @param bool $queue
     * @return bool
     */
    public function sendWithRetry(
        Mailable $mailable,
        int $maxAttempts = 3,
        int $delaySeconds = 5,
        bool $queue = true
    ): bool {
        $attempt = 1;

        while ($attempt <= $maxAttempts) {
            try {
                if ($this->send($mailable, $queue)) {
                    return true;
                }
            } catch (Exception $e) {
                Log::warning("Intento {$attempt}/{$maxAttempts} fallido: {$e->getMessage()}");
            }

            if ($attempt < $maxAttempts) {
                sleep($delaySeconds);
            }

            $attempt++;
        }

        Log::error("Email no pudo ser enviado después de {$maxAttempts} intentos");
        return false;
    }

    /**
     * Enviar email en background con job
     * 
     * @param Mailable $mailable
     * @param string $queue Nombre de la queue
     * @param int $delay Segundos de espera antes de procesar
     */
    public function sendLater(Mailable $mailable, string $queue = 'default', int $delay = 0): void
    {
        if ($delay > 0) {
            Mail::queue($mailable)->delay(now()->addSeconds($delay));
        } else {
            Mail::queue($mailable);
        }

        Log::info('Email encolado', [
            'class'   => class_basename($mailable),
            'queue'   => $queue,
            'delay'   => $delay,
        ]);
    }

    /**
     * Validar que una dirección de email sea válida y enroutable
     * 
     * @param string $email
     * @return bool
     */
    public function isValidEmail(string $email): bool
    {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Obtener lista de destinatarios admin por equipo
     * 
     * @param string $team (sales|operations|technical|management|critical)
     * @return array
     */
    public function getAdminRecipients(string $team = 'critical'): array
    {
        return config("mail.admin_recipients.{$team}") ?? [];
    }

    /**
     * Enviar email a equipo específico
     * 
     * @param Mailable $mailable
     * @param string $team
     * @param bool $queue
     * @return bool
     */
    public function sendToTeam(Mailable $mailable, string $team = 'critical', bool $queue = true): bool
    {
        $recipients = $this->getAdminRecipients($team);

        if (empty($recipients)) {
            Log::warning("No hay destinatarios configurados para el equipo: {$team}");
            return false;
        }

        $sent = 0;
        foreach ($recipients as $email) {
            $mail = clone $mailable;
            $mail->to($email);
            if ($this->send($mail, $queue)) {
                $sent++;
            }
        }

        return $sent > 0;
    }
}
