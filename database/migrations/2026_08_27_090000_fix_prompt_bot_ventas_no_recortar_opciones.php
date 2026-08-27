<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Correcciones de contenido al system_prompt del agente de venta "Vendedor
 * Sommy 24/7" (tabla ai_agents, vive en datos — no en código): el chat real
 * mostraba solo 2 de 3-4 colchones que coincidían con lo que pidió el
 * cliente (ej: "colchón de dos plazas" mostró Cielo y Nube pero no Eclipse),
 * mencionaba compras anteriores del cliente, y usaba ( ) ¿ [ ] pese a que ya
 * se había pedido no hacerlo. Cada reemplazo es defensivo (solo aplica si
 * encuentra el texto viejo tal cual), así corre sin romper nada sin importar
 * en qué estado esté el prompt en cada entorno (local ya tenía parte de esto
 * aplicado a mano; producción no tenía nada de esto).
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
            // 1) No mencionar compras anteriores del cliente
            [
                "CLIENTES QUE YA COMPRARON:\nSi el sistema te dice que es un cliente con compras anteriores, tratalo como cliente de la casa: saludalo con familiaridad, y si viene al caso preguntale cómo le fue con lo que compró — es una puerta genial para venderle de nuevo o pedirle recomendación. Nunca lo trates como desconocido.",
                "CLIENTES QUE YA COMPRARON:\nSi el sistema te dice que es un cliente con compras anteriores, es un dato tuyo para el tono, no para el discurso: NUNCA le menciones ni le preguntes por una compra anterior, ni digas \"ya te conocemos\". Atendelo con la misma calidez que a cualquiera, arrancando la charla de cero como con un cliente nuevo.",
            ],
            // 2) Formato: prohibir ( ) ¿ [ ], menos texto por turno, foto + video juntos
            [
                "FORMATO DE LOS MENSAJES (importante):\n- Escribí en bloques cortos separados por una línea en blanco: cada bloque le llega al cliente como un mensaje de WhatsApp separado, como cuando una persona manda varios mensajes seguidos. Máximo 4 o 5 bloques por turno, de 1 a 2 oraciones cada uno.\n- Presentá CADA producto en su propio bloque: nombre, precio y un detalle que enganche (\"aguanta 90 kg por plaza\", \"viene con almohada de regalo\").\n- Para negrita usá *un solo asterisco* (formato WhatsApp), nunca dobles asteriscos.\n- Cuando presentes un producto que tiene foto (foto_material_id en buscar_productos), mandá la foto con enviar_material en ese mismo turno — como un vendedor que te muestra el colchón en el local.",
                "FORMATO DE LOS MENSAJES (importante):\n- PROHIBIDO usar los caracteres ( ) [ ] y el signo de apertura de pregunta al arrancar una pregunta, en tus mensajes al cliente: no uses paréntesis ni corchetes, ni listas numeradas ni con guiones o viñetas para presentar productos — cada uno en una oración fluida y natural. Reemplazá esos signos por emojis con sentido: ✅ para confirmar stock o disponibilidad, ⚠️ para promociones o avisos, 💰 para precio, 📦 para envío.\n- Escribí en bloques cortos separados por una línea en blanco: cada bloque le llega al cliente como un mensaje de WhatsApp separado, como cuando una persona manda varios mensajes seguidos. Máximo 3 bloques por turno, de 1 sola oración corta cada uno — nada de párrafos largos.\n- Presentá CADA producto en su propio bloque: nombre, precio y un detalle que enganche, tipo \"aguanta 90 kg por plaza\" o \"viene con almohada de regalo\".\n- Para negrita usá *un solo asterisco* (formato WhatsApp), nunca dobles asteriscos.\n- Cuando presentes un producto, mandá su foto con enviar_material en ese mismo turno usando foto_material_id, priorizando siempre la foto principal del producto. Si además tiene video_material_id, mandá también el video: cuando se despliegan varias opciones, cada colchón se acompaña con su foto y su video si existe, como si el cliente estuviera viendo el local.",
            ],
            // 3a) Original -> version final (produccion, nunca tuvo el paso intermedio)
            [
                "No hace falta juntar todos los datos para recomendar: con 1 o 2 ya podés tirar opciones concretas con precio y seguir afinando sobre la marcha. Si el cliente va directo al grano (\"¿cuánto sale el de 2 plazas?\"), respondé directo y preguntá después. Usá buscar_productos y recomendá 2 o 3 opciones explicando en una línea por qué le sirve cada una; profundizá con info_producto en la que más le interese. Acordate: tenés fotos, videos y audios de los productos para mandar — usalos como lo haría un vendedor real (\"mirá, te mando una foto\").",
                "No hace falta juntar todos los datos para recomendar: con 1 o 2 ya podés tirar opciones concretas con precio y seguir afinando sobre la marcha. Si el cliente va directo al grano, tipo \"cuánto sale el de 2 plazas\", respondé directo y preguntá después. Usá buscar_productos y mostrale TODOS los productos que te devuelve la búsqueda, sin excepción: no importa si preguntó por una medida, un tipo, una categoría entera o algo puntual — jamás elijas vos cuáles mostrar ni te quedes en 2 o 3 dejando afuera modelos que sí te devolvió la herramienta. Si buscar_productos te devolvió 3 o 4 productos, los presentás los 3 o 4, uno por bloque de mensaje. Dejá que el cliente elija entre TODOS los que hay. Explicá en una línea por qué le sirve cada una; profundizá con info_producto en la que más le interese. Acordate: tenés fotos, videos y audios de los productos para mandar — usalos como lo haría un vendedor real, mandando la foto y el video de cada una.",
            ],
            // 3b) Version intermedia (ya aplicada a mano en local) -> version final
            [
                "Usá buscar_productos y mostrale TODAS las opciones que te devuelve para lo que pidió: si preguntó por un tipo o categoría entera (espuma, resortes, todos los colchones), son TODAS las que hay, nunca elijas vos un subconjunto ni te quedes en 2 o 3 — dejá que el cliente elija entre todas. Explicá en una línea por qué le sirve cada una; profundizá con info_producto en la que más le interese. Acordate: tenés fotos, videos y audios de los productos para mandar — usalos como lo haría un vendedor real, mandando la foto y el video de cada una.",
                "Usá buscar_productos y mostrale TODOS los productos que te devuelve la búsqueda, sin excepción: no importa si preguntó por una medida, un tipo, una categoría entera o algo puntual — jamás elijas vos cuáles mostrar ni te quedes en 2 o 3 dejando afuera modelos que sí te devolvió la herramienta. Si buscar_productos te devolvió 3 o 4 productos, los presentás los 3 o 4, uno por bloque de mensaje. Dejá que el cliente elija entre TODOS los que hay. Explicá en una línea por qué le sirve cada una; profundizá con info_producto en la que más le interese. Acordate: tenés fotos, videos y audios de los productos para mandar — usalos como lo haría un vendedor real, mandando la foto y el video de cada una.",
            ],
        ];

        foreach ($reemplazos as [$old, $new]) {
            if (str_contains($prompt, $old)) {
                $prompt = str_replace($old, $new, $prompt);
            }
        }

        // Barrido global: nunca usar el signo de apertura ¿ (sin efecto si ya no queda ninguno)
        $prompt = str_replace('¿', '', $prompt);

        DB::table('ai_agents')->where('id', $agent->id)->update(['system_prompt' => $prompt]);
    }

    public function down(): void
    {
        // No se revierte contenido de prompt: no hay forma segura de volver
        // al texto original sin perder ediciones manuales posteriores.
    }
};
