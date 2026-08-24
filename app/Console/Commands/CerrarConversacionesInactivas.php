<?php

namespace App\Console\Commands;

use App\Models\WaConversation;
use Illuminate\Console\Command;

/**
 * Higiene del tablero de atención: lo resuelto migra solo a "Cerradas" para
 * que las columnas activas muestren únicamente lo que necesita acción.
 *  - esperando_cliente sin respuesta por 24 h -> cerrada (el cliente no siguió)
 *  - en_atencion sin actividad por 48 h -> cerrada (quedó colgada)
 * Al cerrar vuelven a modo bot: si el cliente escribe de nuevo, arranca de cero.
 */
class CerrarConversacionesInactivas extends Command
{
    protected $signature = 'whatsapp:cerrar-inactivas';
    protected $description = 'Cierra conversaciones inactivas y las devuelve al bot';

    public function handle(): int
    {
        $esperando = WaConversation::where('status', 'esperando_cliente')
            ->where('last_message_at', '<', now()->subHours(24))
            ->update(['status' => 'cerrada', 'mode' => 'bot']);

        $colgadas = WaConversation::where('status', 'en_atencion')
            ->where('last_message_at', '<', now()->subHours(48))
            ->update(['status' => 'cerrada', 'mode' => 'bot']);

        $this->info("Cerradas: {$esperando} esperando_cliente, {$colgadas} en_atencion");

        return self::SUCCESS;
    }
}
