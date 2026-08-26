<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Concerns\RedirectsAfterStaffLogin;
use App\Http\Controllers\Controller;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorChallengeController extends Controller
{
    use RedirectsAfterStaffLogin;

    public function __construct()
    {
        $this->middleware('guest');
    }

    public function show(Request $request)
    {
        $user = $this->pendingUser($request);

        if (!$user) {
            return redirect('/login');
        }

        return view('auth.two-factor.challenge', ['email' => $user->email]);
    }

    public function verify(Request $request)
    {
        $user = $this->pendingUser($request);

        if (!$user) {
            return redirect('/login');
        }

        $request->validate([
            'code' => 'required|string',
        ], [
            'code.required' => 'Ingresá el código de tu app de autenticación o un código de recuperación.',
        ]);

        $limiterKey = '2fa-attempt:' . $user->id . '|' . $request->ip();

        if (RateLimiter::tooManyAttempts($limiterKey, 5)) {
            $seconds = RateLimiter::availableIn($limiterKey);
            return back()->withErrors(['code' => "Demasiados intentos. Volvé a intentar en {$seconds} segundos."]);
        }

        $code = trim($request->input('code'));

        if ($this->verifyTotp($user, $code) || $this->consumeRecoveryCode($user, $code)) {
            RateLimiter::clear($limiterKey);

            $remember = (bool) $request->session()->get('2fa.remember', false);
            $request->session()->forget(['2fa.user_id', '2fa.remember', '2fa.expires']);

            Auth::loginUsingId($user->id, $remember);
            $request->session()->regenerate();

            return $this->redirectAfterStaffLogin($request);
        }

        RateLimiter::hit($limiterKey, 60);

        return back()->withErrors(['code' => 'El código ingresado no es válido.']);
    }

    public function cancel(Request $request)
    {
        $request->session()->forget(['2fa.user_id', '2fa.remember', '2fa.expires']);

        return redirect('/login');
    }

    private function pendingUser(Request $request): ?User
    {
        $userId = $request->session()->get('2fa.user_id');
        $expires = $request->session()->get('2fa.expires');

        if (!$userId || !$expires || now()->timestamp > $expires) {
            $request->session()->forget(['2fa.user_id', '2fa.remember', '2fa.expires']);
            return null;
        }

        return User::find($userId);
    }

    private function verifyTotp(User $user, string $code): bool
    {
        if (!$user->two_factor_secret) {
            return false;
        }

        return (new Google2FA())->verifyKey($user->two_factor_secret, $code);
    }

    private function consumeRecoveryCode(User $user, string $code): bool
    {
        $codes = $user->two_factor_recovery_codes ?? [];
        $match = collect($codes)->first(fn ($stored) => hash_equals($stored, strtoupper($code)));

        if (!$match) {
            return false;
        }

        $user->two_factor_recovery_codes = array_values(array_diff($codes, [$match]));
        $user->save();

        return true;
    }
}
