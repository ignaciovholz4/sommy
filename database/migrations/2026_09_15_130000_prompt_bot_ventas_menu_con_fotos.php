<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Corrección sobre la migration anterior (2026_09_15_120000): el usuario
 * había elegido en la encuesta "foto + nombre, sin texto largo" para el
 * menú de opciones de una categoría entera, pero el texto que quedó decía
 * "SOLO los nombres" sin mandar fotos, dejando la foto para recién cuando
 * el cliente ya elige una — no era lo pedido. Ahora cada opción del menú
 * corto va con su foto principal (enviar_material) y el nombre debajo, sin
 * descripción, y el video se manda cuando el cliente ya eligió una puntual.
 * Reemplazos defensivos (str_contains) por si el texto ya cambió.
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

        $reemplazos = [
            // FORMATO DE LOS MENSAJES: agregar foto a cada opción del menú
            [
                "- Cuando el cliente pregunta por una categoría entera, el primer mensaje es SOLO un menú cortito con los nombres, sin describir nada todavía: \"tenemos el Cielo, el Nube y el Eclipse en espuma, cuál te interesa?\". Recién cuando elige uno o pide que le cuentes la diferencia, ahí presentás ESE producto con precio y un detalle que enganche, tipo \"aguanta 90 kg por plaza\" o \"viene con almohada de regalo\".",
                "- Cuando el cliente pregunta por una categoría entera, mostrale cada opción con su foto principal (enviar_material, foto_material_id) y el nombre debajo, sin describir nada todavía. Cerrá ese turno preguntando cuál le interesa o si querés que le cuentes la diferencia entre ellos. Recién cuando elige uno o pide la diferencia, ahí presentás ESE producto con precio y un detalle que enganche, tipo \"aguanta 90 kg por plaza\" o \"viene con almohada de regalo\", y mandás también el video si tiene.",
            ],
            // CÓMO ASESORAR: mismo ajuste
            [
                "Primero un mensaje corto con solo los nombres de los 3 o 4 que hay, preguntando cuál le interesa o si querés que le cuentes la diferencia. Recién cuando el cliente elige uno o pide la diferencia, explayate sobre ESE: precio, un detalle que enganche, y profundizá con info_producto si hace falta. Mandá la foto de esa opción con enviar_material en ese momento; el video, si tiene, cuando ya se decide por uno.",
                "Mostrale cada una con su foto principal (enviar_material, foto_material_id) y el nombre debajo, sin descripción todavía, preguntando cuál le interesa o si querés que le cuentes la diferencia. Recién cuando el cliente elige uno o pide la diferencia, explayate sobre ESE: precio, un detalle que enganche, y profundizá con info_producto si hace falta. Mandá también el video de esa opción, si tiene, en ese momento.",
            ],
        ];

        foreach ($reemplazos as [$old, $new]) {
            if (str_contains($prompt, $old)) {
                $prompt = str_replace($old, $new, $prompt);
            }
        }

        DB::table('ai_agents')->where('id', $agent->id)->update(['system_prompt' => $prompt]);
    }

    public function down(): void
    {
        // No se revierte contenido de prompt: no hay forma segura de volver
        // al texto original sin perder ediciones manuales posteriores.
    }
};
