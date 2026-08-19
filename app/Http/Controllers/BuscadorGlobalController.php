<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Buscador global del header: por DNI / CUIT / CUIL, nombre, teléfono o email
 * encuentra clientes, proveedores y revendedores, y lleva a su ficha
 * financiera completa (compras, ventas, pedidos, cuenta corriente, envíos).
 */
class BuscadorGlobalController extends Controller
{
    public function buscar(Request $request)
    {
        $q = trim($request->query('q', ''));
        if (mb_strlen($q) < 3) {
            return response()->json(['resultados' => []]);
        }
        $like = '%' . $q . '%';

        $clientes = DB::table('clientes')
            ->where(function ($w) use ($like) {
                $w->where('dni_cuit', 'like', $like)
                  ->orWhere('nombre', 'like', $like)
                  ->orWhere('paterno', 'like', $like)
                  ->orWhere('telefono', 'like', $like)
                  ->orWhere('email', 'like', $like);
            })
            ->limit(5)->get()
            ->map(fn ($c) => [
                'tipo'   => 'Cliente',
                'nombre' => trim($c->nombre . ' ' . ($c->paterno ?? '')),
                'doc'    => $c->dni_cuit,
                'extra'  => $c->telefono ?: $c->email,
                'url'    => url('clientes/' . $c->idcliente . '/ficha'),
            ]);

        $proveedores = DB::table('proveedores')
            ->where(function ($w) use ($like) {
                $w->where('cuit', 'like', $like)
                  ->orWhere('nombre', 'like', $like)
                  ->orWhere('telefono', 'like', $like)
                  ->orWhere('email', 'like', $like);
            })
            ->limit(5)->get()
            ->map(fn ($p) => [
                'tipo'   => 'Proveedor',
                'nombre' => $p->nombre,
                'doc'    => $p->cuit,
                'extra'  => $p->telefono ?: $p->email,
                'url'    => url('proveedores/' . $p->idproveedor . '/ficha'),
            ]);

        $revendedores = DB::table('revendedores')
            ->where(function ($w) use ($like) {
                $w->where('dni_cuit', 'like', $like)
                  ->orWhere('nombre', 'like', $like)
                  ->orWhere('telefono', 'like', $like)
                  ->orWhere('email', 'like', $like);
            })
            ->limit(5)->get()
            ->map(fn ($r) => [
                'tipo'   => 'Revendedor',
                'nombre' => $r->nombre,
                'doc'    => $r->dni_cuit,
                'extra'  => 'Comisión ' . rtrim(rtrim(number_format($r->comision_porcentaje, 2, '.', ''), '0'), '.') . '%',
                'url'    => url('revendedores-panel/' . $r->id),
            ]);

        return response()->json([
            'resultados' => $clientes->concat($proveedores)->concat($revendedores)->values(),
        ]);
    }
}
