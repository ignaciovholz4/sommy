<?php

namespace App\Http\Controllers\Ecommerce;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Ecommerce\ShareController;
use App\Models\ClienteCuenta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Foundation\Auth\ThrottlesLogins;

/**
 * Autenticación de compradores de la tienda (guard "cliente", tabla clientes).
 */
class ClienteAuthController extends Controller
{
    use ThrottlesLogins;

    public function username()
    {
        return 'email';
    }

    private function datosLayout(): array
    {
        return [
            'getCategoryLimit' => ShareController::getLimitCategory(),
            'arrayEmpresa'     => ShareController::getEmpresaImage(),
        ];
    }

    public function showLogin(Request $request)
    {
        if (Auth::guard('cliente')->check()) {
            return redirect($request->query('next', '/'));
        }
        return view('ecommerce.account.login', $this->datosLayout() + ['next' => $request->query('next', '/')]);
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required|string',
        ], [
            'email.required'    => 'Ingresá tu correo.',
            'email.email'       => 'El correo no tiene un formato válido.',
            'password.required' => 'Ingresá tu contraseña.',
        ]);

        if ($this->hasTooManyLoginAttempts($request)) {
            $seconds = $this->limiter()->availableIn($this->throttleKey($request));
            return back()->withInput($request->only('email', 'next'))
                ->withErrors(['email' => "Demasiados intentos. Volvé a intentar en {$seconds} segundos."]);
        }

        // Solo clientes con cuenta creada (password definido)
        $cliente = ClienteCuenta::where('email', trim($request->email))
            ->whereNotNull('password')
            ->first();

        if (!$cliente || !Hash::check($request->password, $cliente->password)) {
            $this->incrementLoginAttempts($request);
            return back()->withInput($request->only('email', 'next'))
                ->withErrors(['email' => 'Correo o contraseña incorrectos. Si es tu primera compra, registrate.']);
        }

        $this->clearLoginAttempts($request);
        Auth::guard('cliente')->login($cliente, true);
        $request->session()->regenerate();

        return redirect($request->input('next') ?: '/');
    }

    public function showRegister(Request $request)
    {
        if (Auth::guard('cliente')->check()) {
            return redirect($request->query('next', '/'));
        }
        return view('ecommerce.account.register', $this->datosLayout() + ['next' => $request->query('next', '/')]);
    }

    public function register(Request $request)
    {
        $request->validate([
            'nombre'   => 'required|string|max:100',
            'email'    => 'required|email|max:150',
            'telefono' => 'nullable|string|max:30',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'nombre.required'    => 'Ingresá tu nombre.',
            'email.required'     => 'Ingresá tu correo.',
            'email.email'        => 'El correo no tiene un formato válido.',
            'password.required'  => 'Elegí una contraseña.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        $email = trim($request->email);

        // Si ya existe una cuenta con password para ese correo, que inicie sesión
        $existente = ClienteCuenta::where('email', $email)->whereNotNull('password')->first();
        if ($existente) {
            return back()->withInput($request->only('nombre', 'email', 'telefono', 'next'))
                ->withErrors(['email' => 'Ya existe una cuenta con ese correo. Iniciá sesión.']);
        }

        // Si el cliente ya compró antes (sin cuenta), le agregamos la clave a su ficha
        $cliente = ClienteCuenta::where('email', $email)->first();
        if ($cliente) {
            $cliente->password = Hash::make($request->password);
            if ($request->filled('telefono')) {
                $cliente->telefono = $request->telefono;
            }
            $cliente->save();
        } else {
            $cliente = ClienteCuenta::create([
                'nombre'   => $request->nombre,
                'email'    => $email,
                'telefono' => $request->telefono ?? '',
                'password' => Hash::make($request->password),
                'estatus'  => 1,
            ]);
        }

        Auth::guard('cliente')->login($cliente, true);
        $request->session()->regenerate();

        return redirect($request->input('next') ?: '/');
    }

    public function logout(Request $request)
    {
        Auth::guard('cliente')->logout();
        return redirect('/');
    }
}
