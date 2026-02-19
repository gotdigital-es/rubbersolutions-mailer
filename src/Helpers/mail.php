<?php

use RubberSolutions\Mailer\Services\EmailService;

if (!function_exists('send_email')) {
    /**
     * Helper para enviar un email rápidamente.
     *
     * Uso: send_email(new CustomerWelcome($customer), $customer->email);
     */
    function send_email(\Illuminate\Mail\Mailable $mailable, string|array $to): bool
    {
        return app(EmailService::class)->send($mailable, $to);
    }
}
