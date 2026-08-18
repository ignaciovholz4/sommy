<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\ShareController;
use App\Models\ecommerce\order_ecommerce;
use Illuminate\Support\Facades\Auth;

/**
 * Seguimiento de pedidos del comprador logueado (guard cliente).
 */
class ClientePedidosController extends Controller
{
    private function datosLayout(): array
    {
        return [
            'getCategoryLimit' => ShareController::getLimitCategory(),
            'arrayEmpresa'     => ShareController::getEmpresaImage(),
        ];
    }

    public function index()
    {
        $cliente = Auth::guard('cliente')->user();

        $pedidos = order_ecommerce::with('status')
            ->where('cliente_id', $cliente->idcliente)
            ->where('active', 1)
            ->orderByDesc('order_id')
            ->get();

        return view('ecommerce.account.pedidos', $this->datosLayout() + compact('pedidos'));
    }

    public function show($id)
    {
        $cliente = Auth::guard('cliente')->user();

        $pedido = order_ecommerce::with(['status', 'detalles.producto'])
            ->where('cliente_id', $cliente->idcliente)
            ->where('order_id', $id)
            ->firstOrFail();

        // Etapas visibles para el cliente (sin "anulado" salvo que lo esté)
        $etapas = [
            1 => ['nombre' => 'Recibido',    'icono' => 'fa-inbox'],
            2 => ['nombre' => 'Preparando',  'icono' => 'fa-box-open'],
            3 => ['nombre' => 'Pago confirmado', 'icono' => 'fa-circle-check'],
            4 => ['nombre' => 'En camino',   'icono' => 'fa-truck'],
            5 => ['nombre' => 'Entregado',   'icono' => 'fa-house-circle-check'],
        ];

        return view('ecommerce.account.pedido', $this->datosLayout() + compact('pedido', 'etapas'));
    }
}
