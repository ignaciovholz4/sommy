<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderConfirmationMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $order;
    public $detalles;
    public $cliente;
    public $config;
    public $metodoPago;

    public function __construct($order, $detalles, $cliente, $config, string $metodoPago)
    {
        $this->order = $order;
        $this->detalles = $detalles;
        $this->cliente = $cliente;
        $this->config = $config;
        $this->metodoPago = $metodoPago;
    }

    public function build()
    {
        return $this->subject('Confirmación de pedido #' . $this->order->order_id . ' — ' . ($this->config->name ?? ''))
            ->view('emails.order_confirmation');
    }
}
