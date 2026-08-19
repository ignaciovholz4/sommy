<?php

namespace App\Services\Compras;

use App\Models\Articulo;
use App\Models\Proveedor;
use App\Models\TipoComprobante;
use App\Services\Ai\Concerns\ExtraeJsonDeRespuesta;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Lee una factura/remito de proveedor (foto o PDF) con Gemini vision y
 * devuelve los datos extraidos ya cruzados contra proveedores/articulos
 * existentes. Nunca "adivina" un match de baja confianza: si no encuentra
 * uno claro devuelve null y la pantalla de Compras pide confirmar a mano.
 */
class ComprobanteOcrService
{
    use ExtraeJsonDeRespuesta;

    /** Umbral de similitud (0-100, de similar_text) para dar un match por nombre como valido. */
    protected const UMBRAL_SIMILITUD = 60;

    public function disponible(): bool
    {
        return (bool) config('services.gemini.api_key');
    }

    /**
     * @return array{proveedor_nombre: ?string, proveedor_cuit: ?string, fecha: ?string, num_folio: ?string,
     *               tipo_comprobante_sugerido: ?string, items: array, confianza_global: ?float}
     */
    public function extraer(string $rutaAbsoluta, string $mime): array
    {
        if (!is_file($rutaAbsoluta)) {
            throw new \RuntimeException('No se encontro el archivo del comprobante.');
        }

        $prompt = <<<'TXT'
Sos un asistente que lee facturas y remitos de proveedores para un ERP de un distribuidor de colchones.
Analiza la imagen/documento adjunto y devolve UNICAMENTE un objeto JSON valido (sin markdown, sin texto extra) con esta forma exacta:
{
  "proveedor_nombre": "razon social del proveedor tal como figura en el comprobante, o null si no se lee",
  "proveedor_cuit": "CUIT del proveedor en formato NN-NNNNNNNN-N, o null si no figura",
  "fecha": "fecha del comprobante en formato YYYY-MM-DD, o null",
  "num_folio": "numero de comprobante (ej 0001-00012345), o null",
  "tipo_comprobante_sugerido": "uno de: Factura A, Factura B, Factura C, Remito, o null si no se distingue",
  "items": [
    {"descripcion": "texto tal como figura en la linea", "codigo": "codigo/SKU si figura, o null", "cantidad": numero, "precio_unitario": numero}
  ],
  "confianza_global": numero entre 0 y 1 que indique que tan seguro estas de la lectura completa
}
No inventes datos que no puedas leer: usa null en vez de adivinar. Los precios son sin simbolo de moneda, solo el numero.
TXT;

        $model = config('services.gemini.text_model', 'gemini-2.5-flash');

        $response = Http::withHeaders(['x-goog-api-key' => config('services.gemini.api_key')])
            ->timeout(120)
            ->post("https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent", [
                'contents' => [[
                    'parts' => [
                        ['text' => $prompt],
                        ['inline_data' => [
                            'mime_type' => $mime,
                            'data' => base64_encode(file_get_contents($rutaAbsoluta)),
                        ]],
                    ],
                ]],
            ]);

        if ($response->failed()) {
            throw new \RuntimeException('Gemini error: ' . ($response->json('error.message') ?? $response->body()));
        }

        $texto = collect($response->json('candidates.0.content.parts', []))
            ->pluck('text')->filter()->implode('');

        if ($texto === '') {
            throw new \RuntimeException('Gemini no devolvio texto (posible bloqueo de contenido). Proba con otra foto.');
        }

        $json = $this->extraerJson($texto);

        return [
            'proveedor_nombre' => $json['proveedor_nombre'] ?? null,
            'proveedor_cuit' => $json['proveedor_cuit'] ?? null,
            'fecha' => $json['fecha'] ?? null,
            'num_folio' => $json['num_folio'] ?? null,
            'tipo_comprobante_sugerido' => $json['tipo_comprobante_sugerido'] ?? null,
            'items' => is_array($json['items'] ?? null) ? $json['items'] : [],
            'confianza_global' => isset($json['confianza_global']) ? (float) $json['confianza_global'] : null,
        ];
    }

    /** Match exacto por CUIT (normalizado a solo digitos) o, si no, fuzzy por nombre. Null si no hay certeza. */
    public function matchProveedor(?string $nombre, ?string $cuit): ?Proveedor
    {
        if ($cuit) {
            $cuitDigits = preg_replace('/\D/', '', $cuit);
            if ($cuitDigits !== '') {
                $porCuit = Proveedor::whereRaw("REPLACE(REPLACE(cuit, '-', ''), ' ', '') = ?", [$cuitDigits])->first();
                if ($porCuit) {
                    return $porCuit;
                }
            }
        }

        if (!$nombre) {
            return null;
        }

        $id = $this->mejorIdPorNombre($nombre, Proveedor::pluck('nombre', 'idproveedor'));

        return $id ? Proveedor::find($id) : null;
    }

    public function matchTipoComprobante(?string $sugerido): ?TipoComprobante
    {
        if (!$sugerido) {
            return null;
        }

        $s = Str::lower($sugerido);
        $codigo = match (true) {
            str_contains($s, 'factura a') => 'FA',
            str_contains($s, 'factura b') => 'FB',
            str_contains($s, 'factura c') => 'FC',
            str_contains($s, 'remito') => null, // no hay tipo "remito" operativo en tipos_comprobantes
            default => null,
        };

        return $codigo ? TipoComprobante::where('codigo', $codigo)->first() : null;
    }

    /** Match exacto por codigo/codigo_proveedor (priorizando el catalogo del proveedor ya matcheado), si no fuzzy por nombre. */
    public function matchArticulo(?string $descripcion, ?string $codigo, ?int $proveedorId): ?Articulo
    {
        if ($codigo) {
            $query = Articulo::query();
            if ($proveedorId) {
                $porCodigoProveedor = (clone $query)->where('proveedor_id', $proveedorId)
                    ->where('codigo_proveedor', $codigo)->first();
                if ($porCodigoProveedor) {
                    return $porCodigoProveedor;
                }
            }

            $porCodigo = $query->where('codigo', $codigo)->first();
            if ($porCodigo) {
                return $porCodigo;
            }
        }

        if (!$descripcion) {
            return null;
        }

        if ($proveedorId) {
            $id = $this->mejorIdPorNombre($descripcion, Articulo::where('proveedor_id', $proveedorId)->pluck('nombre', 'idarticulo'));
            if ($id) {
                return Articulo::find($id);
            }
        }

        // sin match dentro del proveedor (o sin proveedor matcheado): probar contra todo el catalogo
        $id = $this->mejorIdPorNombre($descripcion, Articulo::pluck('nombre', 'idarticulo'));

        return $id ? Articulo::find($id) : null;
    }

    /**
     * Busca, entre id => nombre, el nombre mas parecido a $texto por porcentaje
     * de similitud de caracteres. Devuelve el id solo si supera el umbral.
     *
     * @param \Illuminate\Support\Collection<int, string> $candidatos
     */
    protected function mejorIdPorNombre(string $texto, $candidatos): ?int
    {
        $texto = Str::lower(trim($texto));
        if ($texto === '' || $candidatos->isEmpty()) {
            return null;
        }

        $mejorId = null;
        $mejorPorcentaje = 0.0;

        foreach ($candidatos as $id => $nombre) {
            similar_text($texto, Str::lower((string) $nombre), $porcentaje);
            if ($porcentaje > $mejorPorcentaje) {
                $mejorPorcentaje = $porcentaje;
                $mejorId = (int) $id;
            }
        }

        return $mejorPorcentaje >= self::UMBRAL_SIMILITUD ? $mejorId : null;
    }
}
