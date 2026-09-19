<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles authenticating users for the application and
    | redirecting them to your home screen. The controller uses a trait
    | to conveniently provide its functionality to your applications.
    |
    */

    use AuthenticatesUsers;

    /**
     * Where to redirect users after login.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    public function authenticate(Request $request)
    {
        $credentials = [
            'username' => $request->get('username'),
            'password' => $request->get('password'),
            'estado' => 1 // Solo usuarios activos
        ];

        if (Auth::attempt($credentials)) {
            $user = Auth::user();
            
            // Verificar que el tipo de usuario sea válido (1=Admin, 2=Agente, 4=Cobrador)
            if (!in_array($user->tipo_usuario, [1, 2, 4])) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return redirect()->route('login')->with('error', 'No posee los permisos para ingresar al sistema');
            }

            $request->session()->regenerate();
            
            // Si es sistema de agentes (sistema == 2)
            if ($request->sistema == 2) {
                if ($user->tipo_usuario == 4) {
                    return redirect()->route('agentes.homeAgentes');
                } else {
                    Auth::logout();
                    $request->session()->invalidate();
                    $request->session()->regenerateToken();
                    return redirect()->route('login')->with('error', 'No posee los permisos para ingresar a este módulo');
                }
            }
            
            return redirect()->intended('/');
        }

        return back()->withErrors([
            'datosIncorrectos' => 'Los datos ingresados son incorrectos o su cuenta se encuentra inactiva',
        ]);
    }

    protected function credentials(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $credentials['estado'] = 1;
        return $credentials;
    }

    public function username()
    {
        return 'username';
    }
}
