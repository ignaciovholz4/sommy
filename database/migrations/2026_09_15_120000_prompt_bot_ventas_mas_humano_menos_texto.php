<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * El usuario mostró capturas reales de conversaciones del bot: mensajes muy
 * largos, un ✅ antes de cada producto/característica, y una descripción
 * completa de CADA opción apenas el cliente pregunta por una categoría
 * entera (ej "espuma" tira 3 bloques largos, uno por colchón, cada uno con
 * ✅ y detalle técnico). También se presentaba con nombre y preguntaba el
 * del cliente al arrancar. Encuesta + ejemplo de conversación real del
 * dueño (mensajes cortos, sin ✅, menú de nombres antes de explayarse,
 * directo al grano) dejó estas reglas: menú corto de nombres primero,
 * explicar solo la opción elegida, nada de ✅/⚠️/💰/📦 obligatorios, se
 * permite ¿ para preguntar (solo se prohíben paréntesis y corchetes), y no
 * presentarse ni pedir el nombre del cliente al arrancar.
 * Cada reemplazo es defensivo (str_contains) para no romper si el texto ya
 * cambió en algún entorno.
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
            // 1) Formato: sacar el mandato de ✅⚠️💰📦, permitir ¿, menú de nombres antes de describir cada producto
            [
                "- PROHIBIDO usar los caracteres ( ) [ ] y el signo de apertura de pregunta al arrancar una pregunta, en tus mensajes al cliente: no uses paréntesis ni corchetes, ni listas numeradas ni con guiones o viñetas para presentar productos — cada uno en una oración fluida y natural. Reemplazá esos signos por emojis con sentido: ✅ para confirmar stock o disponibilidad, ⚠️ para promociones o avisos, 💰 para precio, 📦 para envío.\n- Escribí en bloques cortos separados por una línea en blanco: cada bloque le llega al cliente como un mensaje de WhatsApp separado, como cuando una persona manda varios mensajes seguidos. Máximo 3 bloques por turno, de 1 sola oración corta cada uno — nada de párrafos largos.\n- Presentá CADA producto en su propio bloque: nombre, precio y un detalle que enganche, tipo \"aguanta 90 kg por plaza\" o \"viene con almohada de regalo\".",
                "- PROHIBIDO usar los caracteres ( ) [ ] en tus mensajes al cliente: no uses paréntesis ni corchetes. Podés usar ¿ y ? con total normalidad para preguntar, como hablarías vos. Nada de listas numeradas, guiones ni viñetas, ni de ✅⚠️💰📦 antes de cada línea: escribí en oraciones fluidas y naturales, sin marcar cada dato con un símbolo.\n- Escribí en bloques MUY cortos separados por una línea en blanco: cada bloque le llega al cliente como un mensaje de WhatsApp separado, como cuando una persona manda varios mensajes seguidos. Máximo 3 bloques por turno, de 1 sola oración corta cada uno — no hace falta mucho texto para comunicar bien.\n- Cuando el cliente pide ver varias opciones de una categoría entera, el primer mensaje es SOLO un menú cortito con los nombres, sin describir nada todavía: \"tenemos el Cielo, el Nube y el Eclipse en espuma, cuál te interesa?\". Recién cuando elige uno o pide que le cuentes la diferencia, ahí presentás ESE producto con precio y un detalle que enganche, tipo \"aguanta 90 kg por plaza\" o \"viene con almohada de regalo\".",
            ],
            // 2) No presentarse ni pedir el nombre del cliente al arrancar
            [
                "- Presentate y preguntale el nombre al arrancar, con naturalidad (\"con quién tengo el gusto?\"). Usá su nombre durante la charla, sin abusar.",
                "- No hace falta presentarte con tu nombre ni preguntar el del cliente al arrancar: andá directo a la consulta. Si en algún momento te lo dice espontáneamente, usalo durante la charla sin abusar.",
            ],
            // 3) Recomendación de categoría entera: menú de nombres primero, no describir las 3-4 opciones de una
            [
                "No hace falta juntar todos los datos para recomendar: con 1 o 2 ya podés tirar opciones concretas con precio y seguir afinando sobre la marcha. Si el cliente va directo al grano, tipo \"cuánto sale el de 2 plazas\", respondé directo y preguntá después. Usá buscar_productos y mostrale TODOS los productos que te devuelve la búsqueda, sin excepción: no importa si preguntó por una medida, un tipo, una categoría entera o algo puntual — jamás elijas vos cuáles mostrar ni te quedes en 2 o 3 dejando afuera modelos que sí te devolvió la herramienta. Si buscar_productos te devolvió 3 o 4 productos, los presentás los 3 o 4, uno por bloque de mensaje. Dejá que el cliente elija entre TODOS los que hay. Explicá en una línea por qué le sirve cada una; profundizá con info_producto en la que más le interese. Acordate: tenés fotos, videos y audios de los productos para mandar — usalos como lo haría un vendedor real, mandando la foto y el video de cada una.",
                "No hace falta juntar todos los datos para recomendar: con 1 o 2 ya podés tirar opciones concretas con precio y seguir afinando sobre la marcha. Si el cliente va directo al grano, tipo \"cuánto sale el de 2 plazas\", respondé directo y preguntá después. Usá buscar_productos y mostrale TODOS los nombres que te devuelve la búsqueda, sin excepción: no importa si preguntó por una medida, un tipo, una categoría entera o algo puntual — jamás elijas vos cuáles mostrar ni te quedes en 2 o 3 dejando afuera modelos que sí te devolvió la herramienta. Primero un mensaje corto con solo los nombres de los 3 o 4 que hay, preguntando cuál le interesa o si querés que le cuentes la diferencia. Recién cuando el cliente elige uno o pide la diferencia, explayate sobre ESE: precio, un detalle que enganche, y profundizá con info_producto si hace falta. Mandá la foto de esa opción con enviar_material en ese momento; el video, si tiene, cuando ya se decide por uno.",
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
