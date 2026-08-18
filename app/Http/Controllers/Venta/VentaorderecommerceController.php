<?php

namespace App\Http\Controllers\Venta;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Auth;
use Yajra\Datatables\Datatables;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Response;
use Illuminate\Support\Facades\Validator;

class VentaorderecommerceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('ventas.venta_order_ecommerce.index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show()
    {
        $data = DB::table('order_ecommerce as oe')
        ->join('status_orders_ecommerce as soe', 'oe.status_order_id', '=', 'soe.status_id')
        ->join('payment_ecommerce as pe', 'oe.order_id', '=', 'pe.order_id')
        ->join('clientes as c', 'c.idcliente', '=', 'oe.cliente_id')
        ->select('oe.order_id', 'soe.status_name', 'c.nombre','c.telefono','oe.total_amount')
        ->where(
            [
                //['oe.status_order_id', '=', 2],
                ['oe.active', '=', true],
                ['pe.status_payment', '=', 'Completado']
            ]
        )->get();

        return DataTables::of($data)
        ->addColumn('action', function($data){
            $id = $data->order_id;
            $button = '
            <div class="text-center">
            <button type="button" class="btn btn-info btn-sm mr-2" onclick="show_detail('.$id.');" ><i class="fa fa-eye" aria-hidden="true"></i></button>
            </div>
            ';
            return $button;
        })
        ->addColumn('status_payment', function($data){
            $statusPayment = '
            <div class="text-center">
            <h5><span class="badge bg-success">'.$data->status_name.'</span></h5>
            </div>
            ';
            return $statusPayment;
        })
        ->addColumn('total', function($data){
            $statusPayment = '
            <div class="text-center">
            <h5><span class="badge bg-primary">'.$data->total_amount.'</span></h5>
            </div>
            ';
            return $statusPayment;
        })
        ->rawColumns(['action', 'status_payment', 'total'])
        ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $where = array('order_id'=>$id);
            $data = DB::table('order_ecommerce as oe')
            ->join('clientes as c', 'oe.cliente_id', '=', 'c.idcliente')
            ->select('oe.*','c.nombre')
            ->where($where)->first();

            $whereDetail = array('order_ecommerce_id'=>$id);
            $dataDetail = DB::table('order_detail_ecommerce as ode')
            ->join('productos as p', 'ode.product_id', '=', 'p.idarticulo')
            ->select('ode.*','p.nombre')
            ->where($whereDetail)->get();
            
            return response()->json([
                'status' => 1,
                'order' => $data,
                'order_detail' => $dataDetail
            ]);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
