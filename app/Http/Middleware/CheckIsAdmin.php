<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class CheckIsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        $user = DB::table('role_user')
        ->join('roles','roles.id','=','role_user.role_id')
        ->select('roles.full-access as access')
        ->where('user_id','=', Auth::user()->id)
        ->get();

        if ($user->isEmpty()) {
            $request->session()->flash('message', 'No tienes un rol asignado');
            $request->session()->flash('typealert', 'danger');
            return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/userdashboard');
        }

        $access = strtolower($user[0]->access);
        if ($access === 'yes') {
            return $next($request);
        } else {
            return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/userdashboard');
        }
    }

    private function sendRedirectResponse(Request $request, string $url)
    {
        $request->session()->save();
        $request->session()->reflash();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return (new Response('', 302))->header('Location', $url);
    }
}
