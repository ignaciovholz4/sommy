<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Models\Revendedor;
use App\Mail\RevendedorLinkMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RevendedorPublicController extends Controller
{
    /** Días que dura la atribución de una visita referida */
    public const DIAS_COOKIE = 30;
    public const COOKIE = 'sommy_ref';

    /** Landing pública: "Vendé Sommy y ganá comisión" */
    public function landing()
    {
        $arrayEmpresa = ShareController::getEmpresaImage();
        $getCategoryLimit = ShareController::getLimitCategory();

        $config = DB::table('configuracion')->first();
        $comisionBase = 10;

        return view('ecommerce.revendedores.index', compact(
            'arrayEmpresa', 'getCategoryLimit', 'config', 'comisionBase'
        ));
    }

    /** Alta del revendedor. No hay panel: se registra, recibe su link y listo. */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:150',
            'email'    => 'required|email|max:150|unique:revendedores,email',
            'telefono' => 'required|string|max:40',
            'dni_cuit' => 'nullable|string|max:30',
            'localidad'=> 'nullable|string|max:120',
            'provincia'=> 'nullable|string|max:120',
            'instagram'=> 'nullable|string|max:120',
            'como_vende' => 'nullable|string|max:1000',
            'cbu'      => 'nullable|string|max:40',
            'alias_cbu'=> 'nullable|string|max:60',
            'titular_cuenta' => 'nullable|string|max:150',
        ], [
            'email.unique' => 'Ya hay un revendedor registrado con ese email. Pedí tu link desde "Recuperar mi link".',
        ]);

        $revendedor = Revendedor::create([
            'codigo'   => Revendedor::generarCodigo($request->nombre),
            'nombre'   => $request->nombre,
            'email'    => strtolower(trim($request->email)),
            'telefono' => $request->telefono,
            'dni_cuit' => $request->dni_cuit,
            'localidad'=> $request->localidad,
            'provincia'=> $request->provincia,
            'instagram'=> ltrim((string) $request->instagram, '@'),
            'como_vende' => $request->como_vende,
            'cbu'      => $request->cbu,
            'alias_cbu'=> $request->alias_cbu,
            'titular_cuenta' => $request->titular_cuenta ?: $request->nombre,
            'comision_porcentaje' => 10,
            'estado'   => 'activo',
        ]);

        $this->enviarLink($revendedor);

        return redirect()->route('revendedores.link', $revendedor->codigo);
    }

    /** Pantalla única del revendedor: su link + su QR */
    public function link(string $codigo)
    {
        $revendedor = Revendedor::where('codigo', $codigo)->firstOrFail();

        $arrayEmpresa = ShareController::getEmpresaImage();
        $getCategoryLimit = ShareController::getLimitCategory();

        return view('ecommerce.revendedores.link', compact('revendedor', 'arrayEmpresa', 'getCategoryLimit'));
    }

    /** Reenvío del link por email para quien lo perdió */
    public function recuperar(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $revendedor = Revendedor::where('email', strtolower(trim($request->email)))->first();

        if (!$revendedor) {
            return back()->with('error_recuperar', 'No encontramos ningún revendedor con ese email.');
        }

        $this->enviarLink($revendedor);

        return redirect()->route('revendedores.link', $revendedor->codigo);
    }

    /**
     * Link de referido: guarda la cookie de atribución y manda a la tienda.
     * Acepta /r/CODIGO y /r/CODIGO?p=15 para linkear un producto puntual.
     */
    public function ref(Request $request, string $codigo)
    {
        $revendedor = Revendedor::where('codigo', $codigo)->where('estado', 'activo')->first();

        $destino = url('/');
        if ($request->filled('p')) {
            $destino = url('/Ecommerceproduct/' . $request->p);
        } elseif ($request->filled('destino')) {
            // Solo rutas internas: nunca redirigimos a un dominio externo
            $ruta = ltrim(parse_url($request->destino, PHP_URL_PATH) ?? '', '/');
            $destino = url('/' . $ruta);
        }

        if (!$revendedor) {
            return redirect($destino);
        }

        // Contador de visitas (no bloquea la redirección si falla)
        try {
            $revendedor->increment('visitas');
            $revendedor->forceFill(['ultima_visita' => now()])->save();
        } catch (\Throwable $e) {
            Log::warning('No se pudo registrar la visita del revendedor ' . $codigo . ': ' . $e->getMessage());
        }

        return redirect($destino)->withCookie(
            Cookie::make(self::COOKIE, $revendedor->codigo, 60 * 24 * self::DIAS_COOKIE)
        );
    }

    /** QR en PNG del link del revendedor */
    public function qr(string $codigo)
    {
        $revendedor = Revendedor::where('codigo', $codigo)->firstOrFail();

        $png = self::generarQrPng($revendedor->link);

        return response($png, 200, [
            'Content-Type' => 'image/png',
            'Content-Disposition' => 'attachment; filename="QR-Sommy-' . $revendedor->codigo . '.png"',
        ]);
    }

    /** Devuelve el PNG del QR como string binario */
    public static function generarQrPng(string $contenido, int $size = 600): string
    {
        $qr = new \Endroid\QrCode\QrCode(
            data: $contenido,
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Medium,
            size: $size,
            margin: 16,
            foregroundColor: new \Endroid\QrCode\Color\Color(27, 43, 90),   // azul noche Sommy
            backgroundColor: new \Endroid\QrCode\Color\Color(255, 255, 255),
        );

        return (new \Endroid\QrCode\Writer\PngWriter())->write($qr)->getString();
    }

    /** data:image/png;base64 listo para embeber en una vista */
    public static function qrDataUri(string $contenido, int $size = 600): string
    {
        return 'data:image/png;base64,' . base64_encode(self::generarQrPng($contenido, $size));
    }

    private function enviarLink(Revendedor $revendedor): void
    {
        try {
            Mail::to($revendedor->email)->send(new RevendedorLinkMail($revendedor));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar el link al revendedor ' . $revendedor->codigo . ': ' . $e->getMessage());
        }
    }
}
