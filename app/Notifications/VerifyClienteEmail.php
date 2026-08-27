<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\URL;

/**
 * Verificación de correo para la cuenta de comprador (guard "cliente").
 * No puede usar la VerifyEmail de stock: esa apunta a la ruta
 * "verification.verify", que ya está tomada por el panel de admin (guard web).
 */
class VerifyClienteEmail extends VerifyEmail
{
    protected function verificationUrl($notifiable)
    {
        return URL::temporarySignedRoute(
            'cliente.verification.verify',
            Carbon::now()->addMinutes(60),
            [
                'id' => $notifiable->getKey(),
                'hash' => sha1($notifiable->getEmailForVerification()),
            ]
        );
    }

    public function toMail($notifiable)
    {
        $url = $this->verificationUrl($notifiable);

        return (new MailMessage())
            ->subject('Confirmá tu correo — Sommy')
            ->greeting('¡Hola' . ($notifiable->nombre ? ' ' . $notifiable->nombre : '') . '!')
            ->line('Antes de poder finalizar tu compra necesitamos que confirmes tu correo.')
            ->action('Confirmar mi correo', $url)
            ->line('El enlace vence en 60 minutos. Si no creaste esta cuenta, ignorá este mensaje.');
    }
}
