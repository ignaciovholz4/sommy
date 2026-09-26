<?php

namespace App\Services\CreativeStudio;

use App\Services\Ai\Concerns\ExtraeJsonDeRespuesta;
use App\Services\Ai\OpenAiClient;

/**
 * Interpreta un brief de campaña (lista pegada de piezas de contenido, tipo
 * calendario editorial) y lo traduce a items estructurados que el Estudio
 * puede generar uno por uno. NUNCA inventa productos: cada item solo puede
 * apuntar a un id real de $catalogo, o quedar marcado como pieza de marca
 * sin producto. Un humano revisa la interpretación antes de generar nada
 * (no se dispara ninguna llamada de imagen desde acá).
 */
class CampaignBriefService
{
    use ExtraeJsonDeRespuesta;

    public function __construct(protected OpenAiClient $client)
    {
    }

    /**
     * @param string $brief texto pegado por el usuario (una línea o bloque por pieza)
     * @param array<int, array{id:int,nombre:string,esCombo?:bool}> $catalogo productos y combos reales disponibles
     * @return array<int, array{numero:int,titulo:string,modo:string,producto_id:?int,menciona_tambien:string,instrucciones:string,formato:string,con_precio:bool,tipo:string,slides:array}>
     */
    public function interpretar(string $brief, array $catalogo): array
    {
        $system = "Sos un planificador de contenido del Estudio de Publicaciones de Sommy (fabrica argentina de colchones).\n"
            . "Te paso el catalogo REAL disponible y un brief de campana (una lista de piezas de contenido, puede tener numeros, emojis, titulos y descripciones).\n\n"
            . "Para cada pieza del brief devolves un item con:\n"
            . "- numero: el numero de orden que tenga en el brief (o el orden en que aparece si no tiene numero).\n"
            . "- titulo: el titulo corto de la pieza tal como esta en el brief.\n"
            . "- modo: 'producto' si es sobre UN producto real puntual, 'combo' si es claramente un combo (cama completa, colchon+base), 'marca' si es una pieza institucional/de firma sin producto puntual (ej: logo, frase de marca, cierre de campana), 'comparativo' si menciona VARIOS productos reales a la vez para comparar.\n"
            . "- producto_id: el id EXACTO del catalogo que mejor corresponda (el producto principal/ancla si es comparativo). null si modo es 'marca'. NUNCA inventes un id que no este en el catalogo — si no encontras ninguna coincidencia razonable, usa null y modo 'marca'.\n"
            . "- menciona_tambien: si modo es 'comparativo', los nombres reales del catalogo que tambien aparecen, separados por coma. Si no aplica, string vacio.\n"
            . "- instrucciones: 1-2 frases en espanol describiendo que mostrar en la imagen y de que hablar en el texto, basadas en la descripcion original de esa pieza del brief. No inventes datos (precios, medidas) que no esten ya en el catalogo o en el brief. Si modo es 'comparativo', aclara que en la IMAGEN se muestra SOLO el producto ancla real (producto_id), nunca los otros productos mencionados (esos van solo en el texto/copy).\n"
            . "- formato: 'feed' salvo que el brief pida explicitamente una historia (entonces 'story').\n"
            . "- con_precio: true SOLO si esa pieza puntual es de oferta/precio/descuento/venta directa (ej: menciona '\$PRECIO', 'OFF', 'promo', 'oferta', 'precio directo'). false para piezas de detalle/macro/textura, institucionales, educativas, de asesoramiento o de marca — la mayoria de las piezas NO llevan precio.\n"
            . "- tipo: 'carrusel' SOLO si el brief dice explicitamente que esa pieza es un carrusel/carousel o lista varios 'slides' numerados dentro de ella. Si no, 'simple'.\n"
            . "- slides: SOLO si tipo es 'carrusel' — un array con cada slide del brief, en orden, cada uno {numero (posicion 1,2,3...), headline (titular corto de ESE slide), texto (subtexto/dato de ESE slide, breve)}. Si tipo es 'simple', array vacio [].\n\n"
            . "Respondes UNICAMENTE un JSON valido con la forma {\"items\": [...]}, sin texto ni markdown alrededor.";

        $user = "CATALOGO REAL (unicos ids validos):\n" . json_encode($catalogo, JSON_UNESCAPED_UNICODE) . "\n\n"
            . "BRIEF DE CAMPANA:\n" . trim($brief);

        $response = $this->client->chat($system, [['role' => 'user', 'content' => $user]], [], config('services.publicaciones.copy_model', 'gpt-4o-mini'), 0.3);

        $json = $this->extraerJson((string) $response->text);
        $items = $json['items'] ?? [];

        if (!is_array($items) || !$items) {
            throw new \RuntimeException('No se pudo interpretar el brief (la IA no devolvio items).');
        }

        // Guarda anti-invencion: descartamos cualquier producto_id que no exista realmente en el catalogo.
        $idsValidos = array_column($catalogo, 'id');
        foreach ($items as &$item) {
            if (!empty($item['producto_id']) && !in_array((int) $item['producto_id'], $idsValidos, true)) {
                $item['producto_id'] = null;
            }
            // Sin producto_id resuelto no se puede generar fiel a un producto real: baja a "marca".
            if (in_array($item['modo'] ?? '', ['producto', 'combo', 'comparativo'], true) && empty($item['producto_id'])) {
                $item['modo'] = 'marca';
            }
            $item['con_precio'] = (bool) ($item['con_precio'] ?? false);
            $item['tipo'] = ($item['tipo'] ?? 'simple') === 'carrusel' ? 'carrusel' : 'simple';
            $item['slides'] = $item['tipo'] === 'carrusel' && is_array($item['slides'] ?? null) ? array_values($item['slides']) : [];
            if ($item['tipo'] === 'carrusel' && !$item['slides']) {
                $item['tipo'] = 'simple'; // sin slides no hay carrusel real: se degrada a pieza simple.
            }
            // Salvaguarda: aunque la IA se olvide de aclararlo, una pieza comparativa nunca puede
            // pedirle a Gemini que dibuje mas de un colchon real (solo tenemos la foto del ancla).
            if (($item['modo'] ?? '') === 'comparativo') {
                $item['instrucciones'] = trim((string) ($item['instrucciones'] ?? ''))
                    . ' En la imagen mostrar UNICAMENTE el producto ancla real, sin representar los demas productos mencionados.';
            }
        }

        return $items;
    }
}
