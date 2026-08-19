<?php

namespace App\Http\Controllers\Finanzas;

use App\Http\Controllers\Controller;
use App\Models\ReposicionAjuste;
use App\Services\Reposicion\SugerenciaReposicionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ReposicionController extends Controller
{
    public function generarAhora(SugerenciaReposicionService $service)
    {
        Gate::authorize('haveaccess', 'compras.reposicion.manage');

        $resultado = $service->generar();

        $mensaje = count($resultado['pedidos']) . ' pedido(s) de compra borrador generados a partir de ' . $resultado['analizados'] . ' artículo(s) por debajo del mínimo.';
        if (!empty($resultado['sin_proveedor'])) {
            $mensaje .= ' ' . count($resultado['sin_proveedor']) . ' artículo(s) necesitan reposición pero no tienen proveedor asignado y quedaron sin pedido.';
        }

        return redirect()->route('pedidos-compra.index')->with('success', $mensaje);
    }

    public function ajustes()
    {
        Gate::authorize('haveaccess', 'compras.reposicion.index');

        $ajustes = ReposicionAjuste::actual();

        return view('finanzas.reposicion.ajustes', compact('ajustes'));
    }

    public function guardarAjustes(Request $request)
    {
        Gate::authorize('haveaccess', 'compras.reposicion.manage');

        $data = $request->validate([
            'dias_cobertura_objetivo' => 'required|integer|min:1|max:365',
            'ventana_analisis_dias' => 'required|integer|min:7|max:365',
            'stock_minimo_default' => 'required|integer|min:0',
            'activo' => 'nullable|boolean',
        ]);
        $data['activo'] = $request->boolean('activo');

        ReposicionAjuste::actual()->update($data);

        return back()->with('success', 'Ajustes de reposición guardados.');
    }
}
