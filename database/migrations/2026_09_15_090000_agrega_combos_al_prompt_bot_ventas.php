<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El agente de venta "Vendedor Sommy 24/7" no sabía nada de los combos reales
 * armados desde el panel (almacen/combos: producto ancla + relacionado con
 * descuento + regalo gratis) — buscar_productos y cotizar ya devuelven esa
 * info (ver App\Services\Ai\Tools\BuscarProductos::comboInfo y
 * Cotizar::aplicarCombos), pero el prompt nunca le decía que la usara, así
 * que ante "¿tienen combo?" el bot no lo ofrecía. Se agrega al final del
 * prompt (append defensivo: no corre dos veces si ya está).
 */
return new class extends Migration
{
    public function up(): void
    {
        $agent = DB::table('ai_agents')->where('nombre', 'Vendedor Sommy 24/7')->first();
        if (!$agent || !$agent->system_prompt) {
            return;
        }

        $marcador = 'COMBOS Y REGALOS ARMADOS';
        if (str_contains($agent->system_prompt, $marcador)) {
            return;
        }

        $bloque = "\n\nCOMBOS Y REGALOS ARMADOS (herramienta buscar_productos, campo combo):\nAlgunos productos tienen un combo real armado por el dueño: al comprarlo, otro producto se suma con descuento y/o se lleva un regalo gratis. Cuando buscar_productos te devuelva el campo combo en un producto, ofrecelo proactivamente al presentarlo y también cuando el cliente pregunte por promos, ofertas o combos, aunque no lo hayas mostrado todavia. Contaselo simple: que se suma tal producto con tanto por ciento off, y que se lleva tal cosa de regalo. Usa EXACTAMENTE los nombres, cantidades y precios que vienen en combo.relacionados y combo.regalos, nunca los calcules ni los redondees vos. Para cotizarlo, cotiza el producto ancla junto con el relacionado y/o el regalo que el cliente confirme, cada uno con su propio producto_id: el sistema aplica el descuento y el precio en cero del regalo solo, no hace falta que vos hagas la cuenta. Si el producto no tiene combo en buscar_productos, no inventes ninguno.";

        DB::table('ai_agents')->where('id', $agent->id)->update([
            'system_prompt' => $agent->system_prompt . $bloque,
        ]);
    }

    public function down(): void
    {
        // No se revierte contenido de prompt: no hay forma segura de volver
        // al texto original sin perder ediciones manuales posteriores.
    }
};
