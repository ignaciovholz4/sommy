<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\ShareController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Páginas legales de la tienda: términos, devoluciones y botón de arrepentimiento
 * (obligatorio por normativa de defensa del consumidor en Argentina).
 */
class LegalController extends Controller
{
    private function datosLayout(): array
    {
        return [
            'getCategoryLimit' => ShareController::getLimitCategory(),
            'arrayEmpresa'     => ShareController::getEmpresaImage(),
        ];
    }

    public function terminos()
    {
        return view('ecommerce.legal.terminos', $this->datosLayout());
    }

    public function devoluciones()
    {
        return view('ecommerce.legal.devoluciones', $this->datosLayout());
    }

    public function arrepentimiento()
    {
        return view('ecommerce.legal.arrepentimiento', $this->datosLayout());
    }

    public function arrepentimientoStore(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:120',
            'email'    => 'required|email|max:150',
            'telefono' => 'nullable|string|max:40',
            'pedido'   => 'nullable|string|max:40',
            'motivo'   => 'nullable|string|max:2000',
        ], [
            'nombre.required' => 'Ingresá tu nombre.',
            'email.required'  => 'Ingresá tu correo.',
            'email.email'     => 'El correo no tiene un formato válido.',
        ]);

        DB::table('arrepentimientos')->insert([
            'nombre'   => $request->nombre,
            'email'    => trim($request->email),
            'telefono' => $request->telefono,
            'pedido'   => $request->pedido,
            'motivo'   => $request->motivo,
        ]);

        return back()->with('arrepentimiento_ok', true);
    }
}
