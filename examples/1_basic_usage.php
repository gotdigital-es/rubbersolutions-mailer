<?php

/**
 * Ejemplo básico: Enviar email de bienvenida
 */

use RubberSolutions\Mailer\Mail\Preset\OrderConfirmation;
use Illuminate\Support\Facades\Mail;

// En tu OrderController
public function store(Request $request)
{
    $order = Order::create($request->validated());
    
    // Enviar email de confirmación en cola
    Mail::queue(new OrderConfirmation(
        $order->customer,
        $order
    ));
    
    return response()->json(['status' => 'Order created']);
}


