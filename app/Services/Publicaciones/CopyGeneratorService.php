<?php

namespace App\Services\Publicaciones;

use App\Services\Ai\Concerns\ExtraeJsonDeRespuesta;
use App\Services\Ai\OpenAiClient;
use Illuminate\Support\Facades\DB;

/**
 * Genera los textos de una publicacion (titulo/descripcion MercadoLibre,
 * caption IG/FB y mensaje WhatsApp) con IA, usando la voz de marca Sommy
 * y las ultimas publicaciones guardadas como ejemplos de tono.
 */
class CopyGeneratorService
{
    use ExtraeJsonDeRespuesta;

    protected const BRAND_VOICE = <<<'TXT'
Marca: Sommy, fabrica argentina de colchones. Firma: "Liviano como una pluma."
Voz: calida, cercana, serena; habla de descanso y bienestar, nunca agresiva ni "grito de oferta".
Trato de "vos" (espanol rioplatense). Emojis con moderacion (2-4 por caption, tematica descanso/hogar).
Argumentos centrales: somos fabricantes (directo de fabrica, sin intermediarios), envio a domicilio,
garantia real, noches de prueba. Prohibido: mayusculas sostenidas tipo "OFERTON", urgencia falsa,
neon/agresividad, inventar caracteristicas o precios que no esten en la ficha.
TXT;

    public function __construct(protected OpenAiClient $client)
    {
    }

    /**
     * @param array $producto ficha ya mapeada (nombre, precioFinal, descuento, tipo, firmeza, plazas, altura, pillow, tela, garantia, noches, descripcion)
     * @return array{titulo_ml: string, desc_ml: string, caption: string, texto_wa: string}
     */
    public function generar(array $producto, bool $conPrecio, string $instrucciones = ''): array
    {
        // Voz de marca entrenada por el usuario (Estudio › Entrenar IA); si no hay, la default
        $vozMarca = DB::table('publicaciones_ajustes')->value('voz_marca') ?: self::BRAND_VOICE;

        $system = "Sos el redactor publicitario de Sommy.\n\n" . $vozMarca . "\n\n"
            . "Respondes UNICAMENTE un objeto JSON valido, sin markdown ni texto extra, con estas claves:\n"
            . "- titulo_ml: titulo para MercadoLibre, maximo 60 caracteres, con palabras clave de busqueda (colchon, plazas, tipo).\n"
            . "- desc_ml: descripcion completa para MercadoLibre en texto plano (parrafos + lista de caracteristicas con •), sin emojis.\n"
            . "- caption: caption para Instagram/Facebook con emojis moderados y 5-8 hashtags al final (#colchones #descanso #sommy y afines).\n"
            . "- texto_wa: mensaje corto para difusion por WhatsApp (3-5 lineas, directo, con precio si corresponde).";

        $ejemplos = $this->ejemplosPrevios();

        $user = "FICHA DEL PRODUCTO (datos reales del ERP, no inventar nada fuera de esto):\n"
            . json_encode($producto, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) . "\n\n"
            . ($conPrecio
                ? "Mostrar el precio final (\$" . number_format($producto['precioFinal'], 0, ',', '.') . ($producto['descuento'] > 0 ? ", con " . round($producto['descuento']) . "% OFF sobre \$" . number_format($producto['precio'], 0, ',', '.') : '') . ") en caption y texto_wa."
                : "NO mencionar precios en ningun texto.");

        if ($ejemplos) {
            $user .= "\n\nEJEMPLOS DE PUBLICACIONES ANTERIORES DE LA MARCA (imitar tono y estructura, no copiar literal):\n" . $ejemplos;
        }

        // Ficha interna del producto (base de conocimiento: instrucciones, características, FAQs)
        $ficha = DB::table('articulo_conocimiento')
            ->where('articulo_id', $producto['id'])
            ->where('activo', 1)
            ->whereIn('tipo', ['instrucciones', 'caracteristicas', 'faq', 'nota'])
            ->get(['titulo', 'contenido']);
        if ($ficha->isNotEmpty()) {
            $user .= "\n\nFICHA INTERNA DEL PRODUCTO (fuente de verdad, no inventar más allá de esto):\n"
                . $ficha->map(fn ($f) => "- {$f->titulo}: " . mb_substr((string) $f->contenido, 0, 800))->implode("\n");
        }

        // Recursos de contexto activos de la biblioteca (datos del negocio, direcciones, promos)
        $contextos = DB::table('publicaciones_recursos')
            ->where('tipo', 'contexto')->where('activo', 1)
            ->get(['titulo', 'contenido']);
        if ($contextos->isNotEmpty()) {
            $user .= "\n\nINFORMACIÓN DE LA MARCA (usala cuando sume; no inventar nada que no esté acá):\n"
                . $contextos->map(fn ($c) => "- {$c->titulo}: {$c->contenido}")->implode("\n");
        }

        if (trim($instrucciones) !== '') {
            $user .= "\n\nINSTRUCCIONES EXTRA DEL USUARIO PARA ESTA PUBLICACION: " . trim($instrucciones);
        }

        $response = $this->client->chat($system, [['role' => 'user', 'content' => $user]], [], config('services.publicaciones.copy_model', 'gpt-4o-mini'), 0.8);

        $json = $this->extraerJson((string) $response->text);

        return [
            'titulo_ml' => mb_substr(trim($json['titulo_ml'] ?? $producto['nombre']), 0, 60),
            'desc_ml'   => trim($json['desc_ml'] ?? ''),
            'caption'   => trim($json['caption'] ?? ''),
            'texto_wa'  => trim($json['texto_wa'] ?? ''),
        ];
    }

    /** Ultimas publicaciones guardadas, como referencia de tono para la IA. */
    protected function ejemplosPrevios(int $max = 3): string
    {
        $previas = DB::table('publicaciones')
            ->whereNotNull('caption')
            ->orderByDesc('id')
            ->limit($max)
            ->get(['titulo_ml', 'caption']);

        return $previas->map(fn ($p) => "---\nTitulo: {$p->titulo_ml}\nCaption:\n{$p->caption}")->implode("\n");
    }
}
