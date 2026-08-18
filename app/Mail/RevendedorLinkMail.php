<?php

namespace App\Mail;

use App\Models\Revendedor;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

/**
 * Le manda al revendedor su link de venta y su QR.
 * Es el único contacto automático que recibe: no tiene panel.
 */
class RevendedorLinkMail extends Mailable
{
    use Queueable, SerializesModels;

    public $revendedor;
    public $link;

    public function __construct(Revendedor $revendedor)
    {
        $this->revendedor = $revendedor;
        $this->link = $revendedor->link;
    }

    public function build()
    {
        return $this->subject('Tu link de revendedor Sommy — ' . $this->revendedor->codigo)
            ->view('emails.revendedor-link');
    }
}
