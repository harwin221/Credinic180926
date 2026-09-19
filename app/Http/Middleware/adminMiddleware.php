<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class adminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (in_array(userLogeado()->tipo_usuario, [1, 2]))
            return $next($request);
        else {
            \Auth::logout();
            $request->session()->invalidate();

            $request->session()->regenerateToken();
            return redirect()->route('login')->with('error','No posee los permisos para ingresar a este módulo ');
        }
    }
}
