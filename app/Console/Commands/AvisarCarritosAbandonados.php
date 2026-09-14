<?php

namespace App\Console\Commands;

use App\Mail\CarritoAbandonadoMailable;
use App\Models\ecommerce\ClienteCarrito;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

/**
 * Avisa por mail a clientes logueados que dejaron productos en el carrito
 * sin comprar hace mas de 2 horas. Se manda UNA sola vez por abandono: al
 * enviarse se marca aviso_enviado_at, y solo se resetea si el cliente vuelve
 * a tocar el carrito (nueva actividad = nuevo abandono posible mas adelante).
 */
class AvisarCarritosAbandonados extends Command
{
    protected $signature = 'carrito:avisar-abandonados';

    protected $description = 'Manda un mail a clientes que dejaron el carrito abandonado hace mas de 2 horas (una sola vez por abandono)';

    public function handle(): int
    {
        $config = DB::table('configuracion')->first();

        $carritos = ClienteCarrito::with('cliente')
            ->whereNull('aviso_enviado_at')
            ->where('updated_at', '<=', now()->subHours(2))
            ->whereJsonLength('items', '>', 0)
            ->get();

        $enviados = 0;
        $omitidos = 0;

        foreach ($carritos as $carrito) {
            $cliente = $carrito->cliente;

            if (!$cliente || empty($cliente->email)) {
                $omitidos++;
                continue;
            }

            // Si ya compró después de la última vez que tocó el carrito, no es un abandono real.
            $yaCompro = DB::table('order_ecommerce')
                ->where('cliente_id', $cliente->idcliente)
                ->where('order_date', '>=', $carrito->updated_at)
                ->exists();

            if ($yaCompro) {
                $carrito->update(['aviso_enviado_at' => now()]);
                $omitidos++;
                continue;
            }

            try {
                Mail::to($cliente->email)->send(
                    new CarritoAbandonadoMailable($cliente, $carrito->items, (float) $carrito->total, $config)
                );
                $carrito->update(['aviso_enviado_at' => now()]);
                $enviados++;
            } catch (\Throwable $e) {
                Log::error('AvisarCarritosAbandonados: no se pudo enviar a ' . $cliente->email . ': ' . $e->getMessage());
                $omitidos++;
            }
        }

        $this->info("Avisos de carrito abandonado enviados: {$enviados}. Omitidos: {$omitidos}.");

        return self::SUCCESS;
    }
}
