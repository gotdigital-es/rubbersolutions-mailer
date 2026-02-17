<?php

if (!function_exists('send_email')) {
    /**
     * Helper para enviar email rápidamente
     */
    function send_email($mailable, $queue = true)
    {
        return app('mailer.service')->send($mailable, queue: $queue);
    }
}

if (!function_exists('send_to_team')) {
    /**
     * Helper para enviar a un equipo específico
     */
    function send_to_team($mailable, $team = 'critical', $queue = true)
    {
        return app('mailer.service')->sendToTeam($mailable, team: $team, queue: $queue);
    }
}
