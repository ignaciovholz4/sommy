<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * La migration 2026_09_15_130000 quiso agregar la foto a cada opción del
 * menú corto en el bloque "FORMATO DE LOS MENSAJES", pero el old_string no
 * coincidía con el texto real ("pide ver varias opciones de una categoría
 * entera" vs lo que se buscaba, "pregunta por una categoría entera"), así
 * que ese reemplazo no aplicó nada — verificado con tinker tras correrla.
 * Repite el mismo cambio con el texto exacto.
 */
return new class extends Migration
{
    public function up(): void
    {
        $agent = DB::table('ai_agents')->where('nombre', 'Vendedor Sommy 24/7')->first();
        if (!$agent || !$agent->system_prompt) {
            return;
        }

        $prompt = $agent->system_prompt;

        $old = "- Cuando el cliente pide ver varias opciones de una categoría entera, el primer mensaje es SOLO un menú cortito con los nombres, sin describir nada todavía: \"tenemos el Cielo, el Nube y el Eclipse en espuma, cuál te interesa?\". Recién cuando elige uno o pide que le cuentes la diferencia, ahí presentás ESE producto con precio y un detalle que enganche, tipo \"aguanta 90 kg por plaza\" o \"viene con almohada de regalo\".";
        $new = "- Cuando el cliente pide ver varias opciones de una categoría entera, mostrale cada opción con su foto principal (enviar_material, foto_material_id) y el nombre debajo, sin describir nada todavía. Cerrá ese turno preguntando cuál le interesa o si querés que le cuentes la diferencia entre ellos. Recién cuando elige uno o pide la diferencia, ahí presentás ESE producto con precio y un detalle que enganche, tipo \"aguanta 90 kg por plaza\" o \"viene con almohada de regalo\", y mandás también el video si tiene.";

        if (str_contains($prompt, $old)) {
            $prompt = str_replace($old, $new, $prompt);
            DB::table('ai_agents')->where('id', $agent->id)->update(['system_prompt' => $prompt]);
        }
    }

    public function down(): void
    {
        // No se revierte contenido de prompt: no hay forma segura de volver
        // al texto original sin perder ediciones manuales posteriores.
    }
};
