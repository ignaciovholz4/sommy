<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ConnectController extends Controller
{
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

    private function sendRedirectResponse(Request $request, string $url)
    {
        $request->session()->save();
        $request->session()->reflash();

        while (ob_get_level() > 0) {
            ob_end_clean();
        }

        return (new Response('', 302))->header('Location', $url);
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

        if (Auth::attempt(['email'=>$request->input('email'), 'password'=> $request->input('password')], true)) {

            if(Auth::user()->estatus == 1){
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
                    $access = $user[0]->access;

                    $base = $request->getSchemeAndHttpHost();
                    $access = strtolower($access);
                    if ($access === 'yes') {
                        return $this->sendRedirectResponse($request, $base . '/dashboard');
                    } else if ($access === 'no') {
                        return $this->sendRedirectResponse($request, $base . '/userdashboard');
                    }

                } else {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    $request->session()->flash('message', 'El usuario no tiene un Rol asignado');
                    $request->session()->flash('typealert', 'danger');
                    return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/login');
                }

            } else {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                $request->session()->flash('message', 'El usuario no esta activo');
                $request->session()->flash('typealert', 'danger');
                return $this->sendRedirectResponse($request, $request->getSchemeAndHttpHost() . '/login');
            }
        } else {
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
