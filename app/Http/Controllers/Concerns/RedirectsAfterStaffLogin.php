<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

trait RedirectsAfterStaffLogin
{
    private function sendRedirectResponse(Request $request, string $url)
    {
        $request->session()->save();
        $request->session()->reflash();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return (new \Illuminate\Http\Response('', 302))->header('Location', $url);
    }

    /**
     * Resuelve a dónde mandar al usuario de staff ya autenticado según su rol,
     * o lo desloguea y manda de vuelta al login si algo no está en orden.
     */
    private function redirectAfterStaffLogin(Request $request)
    {
        if (!Auth::user()->hasVerifiedEmail()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            $request->session()->flash('message', 'Debe verificar su correo electrónico antes de continuar.');
            $request->session()->flash('typealert', 'warning');
            return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/email/verify');
        }

        $user = DB::table('users')
            ->join('role_user', 'users.id', '=', 'role_user.user_id')
            ->join('roles', 'roles.id', '=', 'role_user.role_id')
            ->select('roles.full-access as access')
            ->where('users.id', '=', Auth::user()->id)
            ->get();

        $count = count($user);
        if ($count == 1) {
            $access = strtolower($user[0]->access);
            $base = $request->getSchemeAndHttpHost();

            if ($access === 'yes') {
                return $this->sendRedirectResponse($request, $base . '/dashboard');
            } else if ($access === 'no') {
                return $this->sendRedirectResponse($request, $base . '/userdashboard');
            }
        }

        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        $request->session()->flash('message', 'El usuario no tiene un Rol asignado');
        $request->session()->flash('typealert', 'danger');
        return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/login');
    }
}
