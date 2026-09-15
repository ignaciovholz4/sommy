<?php

namespace App\Services\Ai\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Resuelve el material_id de foto/video de un producto para mandar con
 * enviar_material: primero busca en la base de conocimiento (articulo_
 * conocimiento, el de mayor prioridad cargada), y si no tiene nada cargado
 * ahí, cae a la foto principal real del catálogo de la tienda (producto_
 * imagenes) — así funciona sin que el dueño cargue nada a mano. Para video
 * no hay fallback: solo existe si se cargó en Conocimiento.
 */
trait ResuelveMaterialProducto
{
    private function materialId(int $productoId, string $tipo): ?string
    {
        $conocimientoId = DB::table('articulo_conocimiento')
            ->where('articulo_id', $productoId)
            ->where('tipo', $tipo)->where('activo', 1)
            ->whereNotNull('archivo')
            ->orderByDesc('prioridad')->orderBy('id')
            ->value('id');

        if ($conocimientoId) {
            return (string) $conocimientoId;
        }

        if ($tipo !== 'imagen') {
            return null;
        }

        $imagenId = DB::table('producto_imagenes')
            ->where('producto_id', $productoId)
            ->orderByDesc('principal')->orderBy('orden')
            ->value('id');

        return $imagenId ? 'img:' . $imagenId : null;
    }
}
