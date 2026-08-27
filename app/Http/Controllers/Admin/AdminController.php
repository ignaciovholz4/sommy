<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Articulo;
use Illuminate\Http\Request;
use App\Http\Controllers\General\FechaController;

use App\Models\Compra;
use App\Models\Venta;
use App\Models\CajaApertura;
use App\Models\ecommerce\order_ecommerce;
use App\Models\Devolucion;
use App\Models\Cliente;



use Response,Validator;

use DB;

class AdminController extends Controller
{
    public function index()
    {
        $date_now = now()->toDateString();
        $inicioMes = now()->startOfMonth()->toDateString();
        $inicioMesAnterior = now()->subMonth()->startOfMonth()->toDateString();
        $finMesAnterior = now()->subMonth()->endOfMonth()->toDateString();

        // KPIs del día
        $total_ventas = Venta::whereDate('fecha', $date_now)->where('estado', '!=', 'anulada')->count();
        $total_articulos = Articulo::where('estado', 'Activo')->count();
        $total_compras = Compra::whereDate('fecha', $date_now)->where('estado', '!=', 'anulada')->count();
        $total_cajas = CajaApertura::all()->filter(fn($caja) => $caja->estaAbierta())->count();

        $ventasHoyMonto   = Venta::whereDate('fecha', $date_now)->where('estado', '!=', 'anulada')->sum('total_con_iva');
        $comprasHoyMonto  = Compra::whereDate('fecha', $date_now)->where('estado', '!=', 'anulada')->sum('total_con_iva');

        // Ventas del mes actual vs anterior
        $ventasMes         = Venta::where('fecha', '>=', $inicioMes)->where('estado', '!=', 'anulada')->sum('total_con_iva');
        $ventasMesAnterior = Venta::whereBetween('fecha', [$inicioMesAnterior, $finMesAnterior])->where('estado', '!=', 'anulada')->sum('total_con_iva');
        $variacionMes      = $ventasMesAnterior > 0 ? round((($ventasMes - $ventasMesAnterior) / $ventasMesAnterior) * 100, 1) : null;

        // Ecommerce
        $pedidosPendientes = order_ecommerce::whereHas('status', fn($q) => $q->where('status_name', 'Pendiente'))->count();
        $pedidosMesCount   = order_ecommerce::whereNotNull('order_date')->where('order_date', '>=', $inicioMes)->count();
        $ecommerceMes      = order_ecommerce::whereNotNull('order_date')->where('order_date', '>=', $inicioMes)->where('active', 1)->sum('total_amount');
        $pedidosPorEstado  = DB::table('order_ecommerce')
            ->join('status_orders_ecommerce', 'order_ecommerce.status_order_id', '=', 'status_orders_ecommerce.status_id')
            ->selectRaw('status_orders_ecommerce.status_name as estado, COUNT(*) as total')
            ->groupBy('status_orders_ecommerce.status_name')
            ->get();

        // Devoluciones del mes
        $devolucionesMes = Devolucion::where('fecha', '>=', $inicioMes)->count();

        // Total clientes
        $totalClientes = Cliente::count();

        // Top 5 productos más vendidos del mes
        $topProductos = DB::table('detalle_ventas')
            ->join('ventas', 'detalle_ventas.venta_id', '=', 'ventas.idventa')
            ->join('productos', 'detalle_ventas.articulo_id', '=', 'productos.idarticulo')
            ->where('ventas.fecha', '>=', $inicioMes)
            ->where('ventas.estado', '!=', 'anulada')
            ->groupBy('detalle_ventas.articulo_id', 'productos.nombre')
            ->selectRaw('productos.nombre, SUM(detalle_ventas.cantidad) as total_vendido, SUM(detalle_ventas.subtotal_con_iva) as total_monto')
            ->orderByDesc('total_vendido')
            ->limit(5)
            ->get();

        // --- GRÁFICOS DINÁMICOS ---
        $ventasPorMesAnio = DB::table('ventas')
            ->selectRaw('MONTH(fecha) as mes, ROUND(SUM(total_con_iva),2) as total')
            ->whereYear('fecha', now()->year)->where('estado', '!=', 'anulada')
            ->groupByRaw('MONTH(fecha)')->get()->keyBy('mes');

        $comprasPorMesAnio = DB::table('compras')
            ->selectRaw('MONTH(fecha) as mes, ROUND(SUM(total_con_iva),2) as total')
            ->whereYear('fecha', now()->year)->where('estado', '!=', 'anulada')
            ->groupByRaw('MONTH(fecha)')->get()->keyBy('mes');

        $ecommercePorMesAnio = DB::table('order_ecommerce')
            ->selectRaw('MONTH(order_date) as mes, COUNT(*) as cantidad, ROUND(SUM(total_amount),2) as total')
            ->whereYear('order_date', now()->year)->whereNotNull('order_date')->where('active', 1)
            ->groupByRaw('MONTH(order_date)')->get()->keyBy('mes');

        $mesesLabels = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
        $graficoVentasAnio = $graficoComprasAnio = $graficoEcommerceAnio = $graficoEcommerceCount = [];
        for ($m = 1; $m <= 12; $m++) {
            $graficoVentasAnio[]     = (float) ($ventasPorMesAnio->get($m)->total ?? 0);
            $graficoComprasAnio[]    = (float) ($comprasPorMesAnio->get($m)->total ?? 0);
            $graficoEcommerceAnio[]  = (float) ($ecommercePorMesAnio->get($m)->total ?? 0);
            $graficoEcommerceCount[] = (int)   ($ecommercePorMesAnio->get($m)->cantidad ?? 0);
        }

        $ventasPorDiaMes = DB::table('ventas')
            ->selectRaw('DAY(fecha) as dia, ROUND(SUM(total_con_iva),2) as total')
            ->whereYear('fecha', now()->year)->whereMonth('fecha', now()->month)
            ->where('estado', '!=', 'anulada')
            ->groupByRaw('DAY(fecha)')->get()->keyBy('dia');

        $diasDelMes = now()->daysInMonth;
        $graficoDiasLabels = $graficoVentasDia = [];
        for ($d = 1; $d <= $diasDelMes; $d++) {
            $graficoDiasLabels[] = $d;
            $graficoVentasDia[]  = (float) ($ventasPorDiaMes->get($d)->total ?? 0);
        }

        // --- ALERTAS ---
        $sinStock = DB::table('sucursal_articulo')
            ->join('productos', 'sucursal_articulo.articulo_id', '=', 'productos.idarticulo')
            ->where('sucursal_articulo.stock', 0)->where('productos.estado', 'Activo')->count();

        $stockBajo = DB::table('sucursal_articulo')
            ->join('productos', 'sucursal_articulo.articulo_id', '=', 'productos.idarticulo')
            ->where('sucursal_articulo.stock', '>', 0)->where('sucursal_articulo.stock', '<=', 5)
            ->where('productos.estado', 'Activo')->count();

        $presupuestosPendientes = DB::table('presupuestos')->whereIn('estado', ['borrador', 'confirmado'])->count();
        $presupuestosMonto      = DB::table('presupuestos')->whereIn('estado', ['borrador', 'confirmado'])->sum('total_con_iva');

        // total_ars: monto ya convertido a pesos (para cuentas en USD, total * cotizacion).
        // Sumar "total" a secas mezclaría dólares y pesos como si fueran la misma moneda.
        $flujoCaja  = DB::table('movimientos')->whereDate('fecha', $date_now)
            ->selectRaw("tipo, ROUND(SUM(efectivo),2) as ef, ROUND(SUM(bancos),2) as bco, ROUND(SUM(tarjetas),2) as tar, ROUND(SUM(total_ars),2) as total")
            ->groupBy('tipo')->get()->keyBy('tipo');
        $ingresoHoy   = $flujoCaja->get('ingreso');
        $egresoHoy    = $flujoCaja->get('egreso');
        $saldoCajaHoy = round(($ingresoHoy->total ?? 0) - ($egresoHoy->total ?? 0), 2);

        // Pendientes globales (A cobrar / A pagar)
        $ventas = Venta::with('movimientos')->where('estado', '!=', 'anulada')->get();
        $totalVentas      = $ventas->sum('total_con_iva');
        $ingresadoVentas  = $ventas->sum(fn($v) => $v->movimientos->sum('total_ars'));
        $pendienteVentas  = $totalVentas - $ingresadoVentas;

        $compras = Compra::with('movimientos')->where('estado', '!=', 'anulada')->get();
        $totalCompras     = $compras->sum('total_con_iva');
        $pagadoCompras    = $compras->sum(fn($c) => $c->movimientos->sum('total_ars'));
        $pendienteCompras = $totalCompras - $pagadoCompras;

        return view('admin.admin.index', [
            // KPIs conteo
            "ventas"            => $total_ventas,
            "productos"         => $total_articulos,
            "entradas"          => $total_compras,
            "cajas"             => $total_cajas,
            // Montos del día
            "ventasHoyMonto"    => round($ventasHoyMonto, 2),
            "comprasHoyMonto"   => round($comprasHoyMonto, 2),
            // Mes actual vs anterior
            "ventasMes"         => round($ventasMes, 2),
            "ventasMesAnterior" => round($ventasMesAnterior, 2),
            "variacionMes"      => $variacionMes,
            // Ecommerce
            "pedidosPendientes" => $pedidosPendientes,
            "pedidosMesCount"   => $pedidosMesCount,
            "ecommerceMes"      => round($ecommerceMes, 2),
            "pedidosPorEstado"  => $pedidosPorEstado,
            // Otros
            "devolucionesMes"         => $devolucionesMes,
            "totalClientes"           => $totalClientes,
            "topProductos"            => $topProductos,
            // Gráficos dinámicos
            "mesesLabels"             => $mesesLabels,
            "graficoVentasAnio"       => $graficoVentasAnio,
            "graficoComprasAnio"      => $graficoComprasAnio,
            "graficoEcommerceAnio"    => $graficoEcommerceAnio,
            "graficoEcommerceCount"   => $graficoEcommerceCount,
            "graficoDiasLabels"       => $graficoDiasLabels,
            "graficoVentasDia"        => $graficoVentasDia,
            // Alertas
            "sinStock"                => $sinStock,
            "stockBajo"               => $stockBajo,
            "presupuestosPendientes"  => $presupuestosPendientes,
            "presupuestosMonto"       => round($presupuestosMonto, 2),
            "ingresoHoy"              => $ingresoHoy,
            "egresoHoy"               => $egresoHoy,
            "saldoCajaHoy"            => $saldoCajaHoy,
            // A cobrar / A pagar (existentes)
            "totalVentas"       => round($totalVentas, 2),
            "ingresadoVentas"   => round($ingresadoVentas, 2),
            "pendienteVentas"   => round($pendienteVentas, 2),
            "totalCompras"      => round($totalCompras, 2),
            "pagadoCompras"     => round($pagadoCompras, 2),
            "pendienteCompras"  => round($pendienteCompras, 2),
        ]);
    }



    public function get_sales()
    {
      try {
        $date = new FechaController();
        $date_now = $date->datenow();
        $date_yesterday = $date->yesterday();

        $sales_today = DB::table('ventas')
            ->whereDate('fecha', $date_now)
            ->sum('total_con_iva'); // o total_venta si mantenés ese campo

        $sales_yesterday = DB::table('ventas')
            ->whereDate('fecha', $date_yesterday)
            ->sum('total_con_iva');


        return response()->json([
          "sale_today"=> $sales_today,
          "today_date"=> $date_now,
          "sale_yesterday" => $sales_yesterday,
          "yesterday_date" => $date_yesterday
        ]);
      } catch (\Throwable $th) {
         $m = 'Excepción capturada: '.$th->getMessage(). "\n";
         return response()->json([
             'estatus'=> 0,  
             'mensaje' => (array) $m,
         ]);
      }
    }
}
