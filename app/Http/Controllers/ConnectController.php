<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Auth\ThrottlesLogins;
use App\Http\Controllers\Concerns\RedirectsAfterStaffLogin;

class ConnectController extends Controller
{
    use ThrottlesLogins, RedirectsAfterStaffLogin;

    public function username()
    {
        return 'email';
    }

    public function __construct(){

        $this->middleware('guest')->except(['getLogout']);

    }

    public function index()
    {
        try {
            $empresa = DB::table('configuracion')->first();
            $image = $empresa ? $empresa->image : 'default.png';
            $name = $empresa ? $empresa->name : 'FacturARG';
        } catch (\Exception $e) {
            $image = 'default.png';
            $name = 'FacturARG';
        }
        $logo = "imagenes/empresa/".$image;

        return view("connect.login",["logo"=>$logo, "name"=>$name]);
    }

    public function postLogin(Request $request){
        $rules = [
            'email'=>'required|email',
            'password'=>'required'
        ];

        $messages = [
            'email.required'=>'Su correo electronico es requerido',
            'email.email'=>'El formato de su correo electronico es invalido',
            'password.required'=>'Por favor escriba una contraseña'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return back()->withErrors($validator)->with('message','Se ha producido un error')->with('typealert', 'danger');
        }

        if ($this->hasTooManyLoginAttempts($request)) {
            $seconds = $this->limiter()->availableIn($this->throttleKey($request));
            $request->session()->flash('message', "Demasiados intentos de inicio de sesión. Volvé a intentar en {$seconds} segundos.");
            $request->session()->flash('typealert', 'danger');
            return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/login');
        }

        $remember = $request->boolean('remember');

        if (Auth::attempt(['email'=>$request->input('email'), 'password'=> $request->input('password')], $remember)) {

            $this->clearLoginAttempts($request);
            $request->session()->regenerate();

            if(Auth::user()->estatus == 1){

                if (Auth::user()->two_factor_confirmed_at) {
                    $userId = Auth::user()->id;
                    Auth::logout();
                    $request->session()->put('2fa.user_id', $userId);
                    $request->session()->put('2fa.remember', $remember);
                    $request->session()->put('2fa.expires', now()->addMinutes(5)->timestamp);
                    return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/login/verificar-codigo');
                }

                return $this->redirectAfterStaffLogin($request);

            } else {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->flash('message', 'El usuario no esta activo');
                $request->session()->flash('typealert', 'danger');
                return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/login');
            }
        } else {
            $this->incrementLoginAttempts($request);
            $request->session()->flash('message', 'Correo electronico o contraseña errónea');
            $request->session()->flash('typealert', 'danger');
            return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/login');
        }

    }

    public function getLogout(Request $request){
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/');
    }

    
}
