<?php

namespace App\Services\Ai\ReportesTools;

use App\Models\Cliente;
use App\Models\Compra;
use App\Models\Nota;
use App\Models\PedidoCompra;
use App\Models\Proveedor;
use App\Models\Venta;

/**
 * Notas recordatorias cargadas por el equipo: generales o pegadas a un
 * cliente/proveedor/venta/compra/pedido de compra puntual.
 */
class NotasQueryTool
{
    /** tipo => [modelo, columna PK, callback de etiqueta] — mismo mapeo que NotaController. */
    private static function tipos(): array
    {
        return [
            'cliente'       => [Cliente::class, 'idcliente', fn ($c) => trim(collect([$c->nombre, $c->paterno, $c->materno])->filter()->implode(' '))],
            'proveedor'     => [Proveedor::class, 'idproveedor', fn ($p) => $p->nombre],
            'venta'         => [Venta::class, 'idventa', fn ($v) => 'Venta #' . ($v->num_folio ?: $v->idventa)],
            'compra'        => [Compra::class, 'idcompra', fn ($c) => 'Compra #' . ($c->num_folio ?: $c->idcompra)],
            'pedido_compra' => [PedidoCompra::class, 'id', fn ($p) => 'Pedido #' . ($p->num_folio ?? $p->id)],
        ];
    }

    public static function definition(): array
    {
        return [
            'name' => 'consultar_notas',
            'description' => 'Busca notas recordatorias cargadas por el equipo (generales, o pegadas a un cliente, proveedor, venta, compra o pedido de compra puntual). Usala cuando pregunten si hay alguna nota/observación sobre algo, o para ver las notas pendientes.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'busqueda' => ['type' => 'string', 'description' => 'Texto a buscar dentro del contenido de las notas (opcional)'],
                    'tipo' => ['type' => 'string', 'enum' => ['cliente', 'proveedor', 'venta', 'compra', 'pedido_compra'], 'description' => 'Filtrar solo notas pegadas a este tipo de registro (opcional)'],
                    'solo_pendientes' => ['type' => 'boolean', 'description' => 'Si es true, trae solo notas no completadas todavía'],
                ],
            ],
        ];
    }

    public function execute(array $args): array
    {
        $query = Nota::query()->orderByDesc('id');

        if (!empty($args['busqueda'])) {
            $query->where('contenido', 'like', '%' . $args['busqueda'] . '%');
        }
        if (!empty($args['tipo'])) {
            $query->where('notable_type', $args['tipo']);
        }
        if (!empty($args['solo_pendientes'])) {
            $query->where('completada', false);
        }

        $notas = $query->limit(30)->get();

        if ($notas->isEmpty()) {
            return ['resultado' => 'No se encontraron notas con ese criterio.'];
        }

        $tipos = self::tipos();

        return [
            'notas' => $notas->map(function (Nota $n) use ($tipos) {
                $etiqueta = null;
                if ($n->notable_type && $n->notable_id && isset($tipos[$n->notable_type])) {
                    [$modelo, $pk, $cb] = $tipos[$n->notable_type];
                    $registro = $modelo::where($pk, $n->notable_id)->first();
                    $etiqueta = $registro ? $cb($registro) : null;
                }

                return [
                    'contenido' => $n->contenido,
                    'pegada_a' => $etiqueta,
                    'completada' => (bool) $n->completada,
                    'fecha_recordatorio' => optional($n->fecha_recordatorio)->format('Y-m-d'),
                    'autor' => optional($n->usuario)->name,
                    'fecha_creacion' => $n->created_at->format('d/m/Y H:i'),
                ];
            })->all(),
        ];
    }
}
