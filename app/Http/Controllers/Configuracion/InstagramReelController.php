<?php

namespace App\Http\Controllers\Configuracion;

use App\Http\Controllers\Controller;
use App\Models\configuracion\InstagramReel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;

/**
 * Carrusel de reels de Instagram en la home: se sube el video (guardado y
 * alojado acá, se reproduce en un <video> propio, sin el marco/branding de
 * Instagram) y opcionalmente se linkea al posteo original.
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
                'video' => ($esNuevo ? 'required' : 'nullable') . '|mimes:mp4,mov,webm,m4v|max:25600',
                'poster' => 'nullable|image|max:5120',
                'url' => ['nullable', 'string', 'regex:#instagram\.com/(reel|reels|p|tv)/[A-Za-z0-9_-]+#i'],
                'titulo' => 'nullable|string|max:120',
                'orden' => 'nullable|integer',
            ], [
                'video.required' => 'Subí el video del reel (mp4)',
                'video.mimes' => 'El video tiene que ser mp4, mov o webm',
                'video.max' => 'El video no puede pesar más de 25MB — comprimilo (720p) antes de subirlo, si no la página va a cargar muy lento en el celular',
                'poster.image' => 'La portada tiene que ser una imagen',
                'url.regex' => 'Ese link no parece ser de un reel/publicación de Instagram (ej: https://www.instagram.com/reel/XXXXXXX/)',
            ]);

            if ($validator->fails()) {
                return response()->json(['status' => 0, 'message' => $validator->errors()->all()]);
            }

            $destinationPath = public_path('/imagenes/reels');
            if (!File::isDirectory($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            $datos = [
                'url' => $request->url ?: null,
                'titulo' => $request->titulo ?: null,
                'orden' => $request->orden ?: 0,
            ];

            $videoFile = $request->file('video');
            $posterFile = $request->file('poster');

            if ($esNuevo) {
                $nombreVideo = $videoFile->hashName();
                $videoFile->move($destinationPath, $nombreVideo);
                $datos['video'] = $nombreVideo;

                if ($posterFile) {
                    $nombrePoster = $posterFile->hashName();
                    $posterFile->move($destinationPath, $nombrePoster);
                    $datos['poster'] = $nombrePoster;
                }

                InstagramReel::create($datos);
                $message = 'Se agregó el reel con éxito';
            } else {
                $reel = InstagramReel::find($reelId);
                if (!$reel) {
                    return response()->json(['status' => 0, 'message' => ['No se encontró el reel a actualizar']]);
                }

                if ($videoFile) {
                    if ($reel->video && File::exists($destinationPath . '/' . $reel->video)) {
                        File::delete($destinationPath . '/' . $reel->video);
                    }
                    $nombreVideo = $videoFile->hashName();
                    $videoFile->move($destinationPath, $nombreVideo);
                    $datos['video'] = $nombreVideo;
                }

                if ($posterFile) {
                    if ($reel->poster && File::exists($destinationPath . '/' . $reel->poster)) {
                        File::delete($destinationPath . '/' . $reel->poster);
                    }
                    $nombrePoster = $posterFile->hashName();
                    $posterFile->move($destinationPath, $nombrePoster);
                    $datos['poster'] = $nombrePoster;
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
                // El archivo de video se conserva (soft delete vía status) por si se restaura.
            }

            return response()->json(['status' => 1, 'message' => 'Se eliminó el reel con éxito']);
        } catch (\Throwable $th) {
            return response()->json(['status' => 0, 'message' => ['Excepción capturada: ' . $th->getMessage()]]);
        }
    }
}
