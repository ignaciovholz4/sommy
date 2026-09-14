<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CarritoAbandonadoMailable extends Mailable
{
    use Queueable, SerializesModels;

    public $cliente;
    public $items;
    public $total;
    public $config;

    public function __construct($cliente, array $items, float $total, $config)
    {
        $this->cliente = $cliente;
        $this->items = $items;
        $this->total = $total;
        $this->config = $config;
    }

    public function build()
    {
        return $this->subject('¿Te olvidaste algo? Tu carrito te espera — ' . ($this->config->name ?? 'Sommy'))
            ->view('emails.carrito_abandonado');
    }
}
