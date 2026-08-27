<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Models\ecommerce\order_ecommerce;
use App\Models\ecommerce\order_detail_ecommerce;
use App\Models\ecommerce\payment_ecommerce;
use App\Models\Cliente;
use App\Models\Revendedor;
use App\Models\RevendedorComision;
use App\Models\ZonaEnvio;
use App\Mail\OrderConfirmationMailable;
use App\Services\MercadoPagoService;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

use App\Http\Controllers\Ecommerce\ShareController;

class EcommerceorderController extends Controller
{
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $arrayDataOrder = json_decode($request->data, true);

            $totalGlobalProduct = 0;
            $emailCustomer = "";
            $data = [];
            $dataEntrega = [];
            $infoAddictional = "";
            $adress = "";
            $localidad = null;
            $provincia = null;
            $codigoPostal = null;
            $dniCuit = null;
            $zonaEnvioId = null;
            $metodoPago = 'transferencia';

            foreach ($arrayDataOrder as $rowData) {
                if($rowData['nameSection'] === 'emailSection'){
                    $emailCustomer = $rowData['data']['email'];
                }

                if($rowData['nameSection'] === 'identificacionSection'){
                    $data[]= [
                        'name' => $rowData['data']['name'],
                        'phone' => $rowData['data']['phone'],
                        'materno' => $rowData['data']['materno'],
                    ];
                }

                if($rowData['nameSection'] === 'entregaSection'){
                    $dataEntrega[] =[
                        'numberExterior' => $rowData['data']['numberExterior'],
                        'numberInterior' => $rowData['data']['numberInterior'] ?? '',
                    ];
                    $adress = $rowData['data']['calle'];
                    $infoAddictional = $rowData['data']['infoAdicional'] ?? '';
                    $localidad = $rowData['data']['localidad'] ?? null;
                    $provincia = $rowData['data']['provincia'] ?? null;
                    $codigoPostal = $rowData['data']['codigoPostal'] ?? null;
                    $dniCuit = $rowData['data']['dniCuit'] ?? null;
                }

                if($rowData['nameSection'] === 'envioSection'){
                    $zonaEnvioId = $rowData['data']['zonaEnvioId'] ?? null;
                }

                if($rowData['nameSection'] === 'pagoSection'){
                    // Medios habilitados en la tienda (todos existen en payment_methods)
                    // MercadoPago está deshabilitado a pedido del cliente: nunca se acepta acá,
                    // aunque alguien llame a este endpoint directo sin pasar por el checkbox oculto.
                    $metodosHabilitados = ['transferencia', 'efectivo', 'tarjeta'];
                    if (app(MercadoPagoService::class)->habilitado()) {
                        $metodosHabilitados[] = 'mercadopago';
                    }
                    $metodoPago = in_array($rowData['data']['metodo'] ?? '', $metodosHabilitados)
                        ? $rowData['data']['metodo']
                        : 'transferencia';
                }

                if($rowData['nameSection'] === 'products'){
                    foreach ($rowData['data'] as $keyData) {
                        $totalGlobalProduct += $keyData['total'];
                    }
                }
            }

            // 🔒 El comprador SIEMPRE es la cuenta logueada con correo verificado
            // (ya lo exige el middleware de la ruta): se ignora cualquier email
            // que venga en el POST, para que no se pueda finalizar la compra a
            // nombre de otro correo sin verificar con solo editar el request.
            $emailCustomer = Auth::guard('cliente')->user()->email;

            // 🔒 Costo de envío SIEMPRE server-side (el carrito localStorage no es confiable)
            $costoEnvio = 0;
            if ($zonaEnvioId) {
                $zona = ZonaEnvio::where('id', $zonaEnvioId)->where('activo', true)->first();
                if (!$zona) {
                    return response()->json(['status' => 0, 'mensaje' => 'La zona de envío seleccionada no es válida']);
                }
                $costoEnvio = (float) $zona->costo;
            }

            // 🔒 Descuento por transferencia server-side desde configuración
            $config = DB::table('configuracion')->first();
            $descuentoPago = 0;
            if ($metodoPago === 'transferencia' && $config && $config->descuento_transferencia > 0) {
                $descuentoPago = round($totalGlobalProduct * ($config->descuento_transferencia / 100), 2);
            }

            $totalFinal = $totalGlobalProduct + $costoEnvio - $descuentoPago;

            DB::beginTransaction();

            // Cliente
            $getEmail = Cliente::where('email', trim($emailCustomer))->first();
            if($getEmail){
                $getEmail->nombre = $data[0]['name'];
                $getEmail->direccion = $adress;
                $getEmail->localidad = $localidad;
                $getEmail->provincia = $provincia;
                $getEmail->codigo_postal = $codigoPostal;
                $getEmail->dni_cuit = $dniCuit;
                $getEmail->telefono = $data[0]['phone'];
                $getEmail->email = $emailCustomer;
                $getEmail->estatus = "Activo";
                $getEmail->number_exterior = $dataEntrega[0]['numberExterior'];
                $getEmail->number_interior = $dataEntrega[0]['numberInterior'];
                $getEmail->materno = $data[0]['materno'];
                $getEmail->save();
                $clienteId = $getEmail->idcliente;
                $dataCustomer = $getEmail;
            }else{
                $cliente = new Cliente;
                $cliente->nombre = $data[0]['name'];
                $cliente->direccion = $adress;
                $cliente->localidad = $localidad;
                $cliente->provincia = $provincia;
                $cliente->codigo_postal = $codigoPostal;
                $cliente->dni_cuit = $dniCuit;
                $cliente->telefono = $data[0]['phone'];
                $cliente->email = $emailCustomer;
                $cliente->estatus = "Activo";
                $cliente->number_exterior = $dataEntrega[0]['numberExterior'];
                $cliente->number_interior = $dataEntrega[0]['numberInterior'];
                $cliente->materno = $data[0]['materno'];
                $cliente->save();
                $clienteId = $cliente->idcliente;
                $dataCustomer = $cliente;
            }

            // 🤝 Atribución a revendedor: la cookie la deja /r/{codigo} y dura 30 días
            $revendedor = null;
            $codigoRef = $request->cookie(RevendedorPublicController::COOKIE);
            if ($codigoRef) {
                $revendedor = Revendedor::where('codigo', $codigoRef)->where('estado', 'activo')->first();
            }

            // Orden
            $order = new order_ecommerce();
            $order->status_order_id = 1;
            $order->cliente_id = $clienteId;
            $order->revendedor_id = $revendedor->id ?? null;
            $order->subtotal_amount = $totalGlobalProduct;
            $order->zona_envio_id = $zonaEnvioId;
            $order->costo_envio = $costoEnvio;
            $order->descuento_pago = $descuentoPago;
            $order->total_amount = $totalFinal;
            $order->additional_info = $infoAddictional;
            $order->direccion_localidad = $localidad;
            $order->direccion_provincia = $provincia;
            $order->direccion_cp = $codigoPostal;
            $order->order_date = Carbon::now();
            $order->save();

            \App\Models\Notificacion::avisar('pedido',
                'Pedido web nuevo #' . $order->order_id . ' por $' . number_format($totalFinal, 0, ',', '.'),
                trim(($localidad ?: '') . ' ' . ($provincia ? '· ' . $provincia : '')) ?: null,
                url('orders/order/' . $order->order_id), 'exito');

            // Detalles
            // 🔒 sin_stock se recalcula acá, no se confía en lo que mande el navegador:
            // permite que el cliente pida igual aunque no haya stock (queda "a consultar"),
            // pero la marca de qué tiene/no tiene stock sale del stock real en este momento.
            foreach ($arrayDataOrder as $products) {
                if($products['nameSection'] === 'products'){
                    foreach ($products['data'] as $rowProduct) {
                        $combinacionId = ($rowProduct['tipoProductoId'] === 2 && isset($rowProduct['rowProdVariant']))
                            ? $rowProduct['rowProdVariant']['idcombinacion']
                            : null;

                        if ($combinacionId) {
                            $stockDisponible = (float) DB::table('sucursal_combinacion')
                                ->where('combinacion_id', $combinacionId)
                                ->where('activo', 1)
                                ->sum('stock');
                        } else {
                            $stockDisponible = (float) DB::table('sucursal_articulo')
                                ->where('articulo_id', $rowProduct['productId'])
                                ->where('activo', 1)
                                ->sum('stock');
                        }

                        $orderDetail = new order_detail_ecommerce();
                        $orderDetail->order_ecommerce_id = $order->order_id;
                        $orderDetail->product_id = $rowProduct['productId'];
                        $orderDetail->quantity = $rowProduct['cant'];
                        $orderDetail->price = $rowProduct['display_price']; // precio efectivo
                        $orderDetail->total = $rowProduct['total'];
                        $orderDetail->sin_stock = ((float) $rowProduct['cant']) > $stockDisponible;
                        if($combinacionId){
                            $orderDetail->producto_variacion_variante_id = $combinacionId;
                        }
                        $orderDetail->active = 1;
                        $orderDetail->save();
                    }
                }
            }

            // Pago
            $metodoPagoRow = DB::table('payment_methods')->where('method_name', $metodoPago)->first();

            $payment = new payment_ecommerce();
            $payment->order_id = $order->order_id;
            $payment->payment_method_id = $metodoPagoRow->payment_method_id ?? null;
            $payment->total = $totalFinal;
            $payment->status_payment = 'Pendiente';
            $payment->save();

            // 💰 Comisión del revendedor: se calcula sobre los productos (sin envío)
            if ($revendedor) {
                RevendedorComision::create([
                    'revendedor_id' => $revendedor->id,
                    'order_id'      => $order->order_id,
                    'monto_venta'   => $totalGlobalProduct,
                    'porcentaje'    => $revendedor->comision_porcentaje,
                    'comision'      => round($totalGlobalProduct * ($revendedor->comision_porcentaje / 100), 2),
                    'estado'        => 'pendiente',
                ]);
            }

            // Query detalle con join a combinaciones
            $getDatailOrder = DB::table('order_detail_ecommerce as ode')
                ->join('productos as pr', 'ode.product_id', '=', 'pr.idarticulo')
                ->leftJoin('producto_combinaciones as pc', 'ode.producto_variacion_variante_id', '=', 'pc.idcombinacion')
                ->select(
                    'ode.*',
                    'pr.nombre as producto_nombre',
                    'pc.combinacion as variante_nombre'
                )
                ->where('ode.order_ecommerce_id', $order->order_id)
                ->get();

            DB::commit();

            // 📩 Mail de confirmación (post-commit; si falla el SMTP el pedido NO se rompe)
            try {
                if ($config && !empty($dataCustomer->email)) {
                    Mail::to($dataCustomer->email)->send(
                        new OrderConfirmationMailable($order, $getDatailOrder, $dataCustomer, $config, $metodoPago)
                    );
                }
            } catch (\Throwable $mailError) {
                Log::warning('No se pudo enviar el mail de confirmación del pedido #' . $order->order_id . ': ' . $mailError->getMessage());
            }

            // 💳 Rama MercadoPago: crear preferencia y devolver el link de pago
            $mpInitPoint = null;
            if ($metodoPago === 'mercadopago') {
                $mpInitPoint = app(MercadoPagoService::class)->crearPreferencia($order);
                if (!$mpInitPoint) {
                    Log::warning('No se pudo crear la preferencia MP para el pedido #' . $order->order_id . ' — el pedido queda registrado como pendiente');
                }
            }

            return response()->json([
                "status" => 1,
                "dataOrder" => $order,
                "dataOrderDetail" => $getDatailOrder,
                "dataCustomer" => $dataCustomer,
                "total" => $totalFinal,
                "mp_init_point" => $mpInitPoint,
                "message" => "Se registró con éxito el pedido",
            ]);
        } catch (\Throwable $th) {
            DB::rollback();
            return response()->json([
                'status'=> 0,
                'mensaje' => 'Excepción capturada: '.$th->getMessage(),
            ]);
        }
    }

    public function validateEmaiLIfExist(Request $request)
    {
        try {
           $emailCustomer =  $request->email;
           $exist = false;
           $message = "";
            $getEmail = Cliente::where('email', trim($emailCustomer))->first();
            if($getEmail != null){
                $exist = true;
                $message = "Ya existe un cliente con el mismo email. Verifica tus datos. En caso actualiza tus datos";
            }
            if($getEmail == null){
                $message = "No existe un cliente con el mismo correo";
            }
            return response()->json([
                "status" => 1,
                "dataCustomer" => $getEmail,
                "exist" => $exist,
                "message" => $message,
            ]);
        } catch (\Throwable $th) {
            $m = 'Excepción capturada: '.$th->getMessage(). "\n";
            return response()->json([
                'status'=> 0,
                'message' => (array) $m,
            ]);
        }
    }


    /**
     * Display the specified resource.
     */
    public function show()
    {

        $arrayEmpresa = ShareController::getEmpresaImage();
        $getCategoryLimit = ShareController::getLimitCategory();

        $zonasEnvio = ZonaEnvio::activas()->get();

        $config = DB::table('configuracion')->first();
        $configPago = [
            'cuit' => $config->cuit ?? '',
            'razon_social' => $config->razon_social ?? '',
            'cbu' => $config->cbu ?? '',
            'alias_cbu' => $config->alias_cbu ?? '',
            'descuento_transferencia' => $config->descuento_transferencia ?? 0,
            'whatsapp' => $config->whatsapp ?? '',
            'mp_habilitado' => app(MercadoPagoService::class)->habilitado(),
        ];

        return view('ecommerce.order.index', compact('getCategoryLimit', 'arrayEmpresa', 'zonasEnvio', 'configPago'));
    }

}
