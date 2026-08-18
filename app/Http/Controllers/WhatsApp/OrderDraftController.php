<?php

namespace App\Http\Controllers\WhatsApp;

use App\Http\Controllers\Controller;
use App\Models\WaOrderDraft;
use App\Services\OrderDraftService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class OrderDraftController extends Controller
{
    /**
     * Borradores pendientes de confirmacion (badge de la bandeja).
     */
    public function pending()
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $drafts = WaOrderDraft::with(['conversation:id,phone_e164,profile_name,cliente_id', 'cliente:idcliente,nombre,paterno'])
            ->where('status', 'pendiente_confirmacion')
            ->orderByDesc('id')
            ->get();

        return response()->json(['drafts' => $drafts, 'count' => $drafts->count()]);
    }

    public function show($id)
    {
        Gate::authorize('haveaccess', 'whatsapp.index');

        $draft = WaOrderDraft::with(['conversation', 'cliente', 'aiAgent:id,nombre'])->findOrFail($id);

        // Enriquecer items con nombre de producto actual
        $items = collect($draft->items ?? [])->map(function ($item) {
            $producto = \App\Models\Articulo::find($item['producto_id'] ?? 0);
            $item['nombre'] = $item['descripcion'] ?? ($producto->nombre ?? 'Producto #' . ($item['producto_id'] ?? '?'));
            return $item;
        });

        return response()->json(['draft' => $draft, 'items' => $items]);
    }

    public function confirm(Request $request, $id, OrderDraftService $service)
    {
        Gate::authorize('haveaccess', 'whatsapp.confirm_order');

        $draft = WaOrderDraft::with('conversation')->findOrFail($id);

        // El vendedor puede ajustar items/envio antes de confirmar
        if ($request->filled('items')) {
            $draft->items = $request->items;
        }
        if ($request->filled('costo_envio')) {
            $draft->costo_envio = (float) $request->costo_envio;
        }
        $draft->recalcularTotales();
        $draft->save();

        try {
            $order = $service->confirm($draft, Auth::id());
        } catch (\RuntimeException $e) {
            return response()->json(['status' => 0, 'mensaje' => $e->getMessage()], 422);
        }

        return response()->json([
            'status' => 1,
            'order_id' => $order->order_id,
            'url' => url('orders/order/' . $order->order_id),
        ]);
    }

    public function reject(Request $request, $id, OrderDraftService $service)
    {
        Gate::authorize('haveaccess', 'whatsapp.confirm_order');

        $draft = WaOrderDraft::findOrFail($id);
        $service->reject($draft, Auth::id(), $request->input('motivo'));

        return response()->json(['status' => 1]);
    }
}
