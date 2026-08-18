<?php

namespace App\Http\Controllers\Caja;

use App\Http\Controllers\Controller;
use App\Models\Caja;
use App\Models\Moneda;
use App\Models\Sucursal;
use Illuminate\Http\Request;

class CajaController extends Controller
{
    /**
     * Mostrar listado de cajas
     */
    public function index()
    {
        $cajas = Caja::with('moneda','sucursal')->get();
        $monedas = Moneda::all();
        $sucursales = Sucursal::all();

        return view('caja.gestor_de_cajas.index', compact('cajas','monedas','sucursales'));
    }

    /**
     * Crear nueva caja
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'       => 'required|string|max:255',
            'sucursal_id'  => 'required|exists:sucursales,id',
            'moneda_id'    => 'required|exists:monedas,id',
            'activa'       => 'boolean',
        ]);

        Caja::create([
            'nombre'      => $request->nombre,
            'sucursal_id' => $request->sucursal_id,
            'moneda_id'   => $request->moneda_id,
            'activa'      => $request->has('activa'),
        ]);

        return redirect()->route('caja.listado')->with('success','Caja creada correctamente');
    }

    /**
     * Actualizar atributos de una caja
     */
    public function update(Request $request, Caja $caja)
    {
        // Validar únicamente el nombre
        $request->validate([
            'nombre' => 'required|string|max:255',
        ]);

        // Actualizar solo el nombre
        $caja->update([
            'nombre' => $request->nombre,
        ]);

        return redirect()->route('caja.listado')
                        ->with('success','Caja actualizada correctamente');
    }

    public function list(Request $request)
    {
        $cajas = Caja::with(['sucursal','moneda']);

        return datatables()->eloquent($cajas)
            ->addColumn('action', function($caja){
                $btnEdit = '<button class="btn btn-sm btn-primary btn-edit" 
                                data-id="'.$caja->id.'" 
                                data-nombre="'.$caja->nombre.'" 
                                data-activa="'.$caja->activa.'">
                                <i class="fas fa-edit"></i>
                            </button>';

                if ($caja->activa) {
                    $btnToggle = '<button class="btn btn-sm btn-danger btn-deactivate" 
                                    data-id="'.$caja->id.'">
                                    <i class="fas fa-trash"></i>
                                </button>';
                } else {
                    $btnToggle = '<button class="btn btn-sm btn-success btn-activate" 
                                    data-id="'.$caja->id.'">
                                    <i class="fas fa-check"></i>
                                </button>';
                }

                // 🔑 Nuevo botón: historial
                $btnHistorial = '<a href="'.route('caja.historial', $caja->id).'" 
                                    class="btn btn-sm btn-info">
                                    <i class="fas fa-history"></i>
                                </a>';

                return $btnEdit . ' ' . $btnToggle . ' ' . $btnHistorial;
            })
            ->toJson();
    }


    public function desactivar(Caja $caja)
    {
        $caja->activa = 0;
        $caja->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Caja desactivada correctamente'
        ]);
    }

    public function activar(Caja $caja)
    {
        $caja->activa = 1;
        $caja->save();

        return response()->json([
            'estado' => 1,
            'mensaje' => 'Caja activada correctamente'
        ]);
    }



}