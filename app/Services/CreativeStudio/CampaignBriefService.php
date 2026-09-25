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
     * @return array<int, array{numero:int,titulo:string,modo:string,producto_id:?int,menciona_tambien:string,instrucciones:string,formato:string}>
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
            . "- instrucciones: 1-2 frases en espanol describiendo que mostrar en la imagen y de que hablar en el texto, basadas en la descripcion original de esa pieza del brief. No inventes datos (precios, medidas) que no esten ya en el catalogo o en el brief.\n"
            . "- formato: 'feed' salvo que el brief pida explicitamente una historia (entonces 'story').\n\n"
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
        }

        return $items;
    }
}
