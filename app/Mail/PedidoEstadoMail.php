<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Aviso automático al cliente cuando su pedido cambia de estado.
 */
class PedidoEstadoMail extends Mailable
{
    use Queueable, SerializesModels;

    public $pedido;
    public $cliente;
    public $titulo;
    public $mensaje;

    public function __construct($pedido, $cliente, string $titulo, string $mensaje)
    {
        $this->pedido  = $pedido;
        $this->cliente = $cliente;
        $this->titulo  = $titulo;
        $this->mensaje = $mensaje;
    }

    public function build()
    {
        return $this->subject($this->titulo . ' — Pedido #' . $this->pedido->order_id)
            ->view('emails.pedido-estado');
    }
}
