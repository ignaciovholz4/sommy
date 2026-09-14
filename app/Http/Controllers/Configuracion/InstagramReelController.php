<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\configuracion\InstagramReel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;

/**
 * Carrusel de reels de Instagram en la home: se cargan solo pegando la URL
 * del reel (embed oficial de Instagram, sin descargar ni alojar video).
 */
class InstagramReelController extends Controller
{
    public function index()
    {
        return view('admin.reels.index');
    }

    public function store(Request $request)
    {
        try {
            $reelId = (int) $request->reelId;
            $esNuevo = $reelId === 0;

            $validator = Validator::make($request->all(), [
                'url' => ['required', 'string', 'regex:#instagram\.com/(reel|reels|p|tv)/[A-Za-z0-9_-]+#i'],
                'titulo' => 'nullable|string|max:120',
                'orden' => 'nullable|integer',
            ], [
                'url.required' => 'Pegá el link del reel de Instagram',
                'url.regex' => 'Ese link no parece ser de un reel/publicación de Instagram (ej: https://www.instagram.com/reel/XXXXXXX/)',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 0, 'message' => $validator->errors()->all()]);
            }

            $datos = [
                'url' => $request->url,
                'titulo' => $request->titulo ?: null,
                'orden' => $request->orden ?: 0,
            ];

            if ($esNuevo) {
                InstagramReel::create($datos);
                $message = 'Se agregó el reel con éxito';
            } else {
                $reel = InstagramReel::find($reelId);
                if (!$reel) {
                    return response()->json(['status' => 0, 'message' => ['No se encontró el reel a actualizar']]);
                }
                $reel->update($datos);
                $message = 'Se actualizó el reel con éxito';
            }

            return response()->json(['status' => 1, 'message' => $message]);
        } catch (\Throwable $th) {
            return response()->json(['status' => 0, 'message' => ['Excepción capturada: ' . $th->getMessage()]]);
        }
    }

    public function show()
    {
        $data = DB::table('instagram_reels')->where('status', 1)->orderBy('orden')->orderBy('id')->get();

        return Datatables::of($data)
            ->addColumn('action', function ($data) {
                $id = $data->id;
                $button = '<i class="fas fa-edit text-primary mr-3" onclick="edit_reel(' . $id . ');" title="Editar"></i>';
                $button .= '<i class="fas fa-trash-alt text-danger" onclick="delete_reel(' . $id . ');" title="Eliminar"></i>';

                return $button;
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function edit(Request $request)
    {
        $reel = DB::table('instagram_reels')->where('id', $request->id)->get();

        return response()->json(['id' => $request->id, 'data' => $reel]);
    }

    public function destroy(Request $request)
    {
        try {
            $reel = InstagramReel::find((int) $request->reelId);
            if ($reel) {
                $reel->status = 0;
                $reel->save();
            }

            return response()->json(['status' => 1, 'message' => 'Se eliminó el reel con éxito']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 0, 'message' => ['Excepción capturada: ' . $th->getMessage()]]);
        }
    }
}
