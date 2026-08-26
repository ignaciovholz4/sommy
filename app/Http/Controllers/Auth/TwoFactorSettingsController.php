<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorSettingsController extends Controller
{
    public function show(Request $request)
    {
        $user = Auth::user();

        return view('auth.two-factor.settings', [
            'enabled' => (bool) $user->two_factor_confirmed_at,
            'pendingSecret' => $request->session()->get('2fa.pending_secret'),
            'qrDataUri' => $request->session()->has('2fa.pending_secret')
                ? $this->qrDataUri($request->session()->get('2fa.pending_secret'), $user->email)
                : null,
            'recoveryCodes' => $request->session()->get('recoveryCodes'),
        ]);
    }

    public function enable(Request $request)
    {
        $google2fa = new Google2FA();
        $secret = $google2fa->generateSecretKey();

        $request->session()->put('2fa.pending_secret', $secret);

        return redirect()->route('two-factor.settings');
    }

    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'Ingresá el código de tu app de autenticación.',
        ]);

        $secret = $request->session()->get('2fa.pending_secret');

        if (!$secret) {
            return back()->withErrors(['code' => 'No hay una activación de 2FA en curso. Empezá de nuevo.']);
        }

        $google2fa = new Google2FA();

        if (!$google2fa->verifyKey($secret, $request->input('code'))) {
            return back()->withErrors(['code' => 'El código ingresado no es válido.']);
        }

        $recoveryCodes = collect(range(1, 8))
            ->map(fn () => Str::upper(Str::random(10)))
            ->all();

        $user = Auth::user();
        $user->two_factor_secret = $secret;
        $user->two_factor_recovery_codes = $recoveryCodes;
        $user->two_factor_confirmed_at = now();
        $user->save();

        $request->session()->forget('2fa.pending_secret');

        return redirect()->route('two-factor.settings')
            ->with('status', '¡Listo! La verificación en dos pasos está activada.')
            ->with('recoveryCodes', $recoveryCodes);
    }

    public function disable(Request $request)
    {
        $request->validate([
            'password' => 'required|string',
        ], [
            'password.required' => 'Ingresá tu contraseña actual para desactivar la verificación en dos pasos.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->input('password'), $user->password)) {
            return back()->withErrors(['password' => 'La contraseña no es correcta.']);
        }

        $user->two_factor_secret = null;
        $user->two_factor_recovery_codes = null;
        $user->two_factor_confirmed_at = null;
        $user->save();

        $request->session()->forget('2fa.pending_secret');

        return redirect()->route('two-factor.settings')->with('status', 'La verificación en dos pasos fue desactivada.');
    }

    private function qrDataUri(string $secret, string $email): string
    {
        $google2fa = new Google2FA();
        $url = $google2fa->getQRCodeUrl(config('app.name', 'Sommy'), $email, $secret);

        $qr = new \Endroid\QrCode\QrCode(
            data: $url,
            errorCorrectionLevel: \Endroid\QrCode\ErrorCorrectionLevel::Medium,
            size: 260,
            margin: 10,
        );

        $png = (new \Endroid\QrCode\Writer\PngWriter())->write($qr)->getString();

        return 'data:image/png;base64,' . base64_encode($png);
    }
}
