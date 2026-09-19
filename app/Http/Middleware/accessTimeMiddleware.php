<?php

namespace App\Http\Middleware;

use App\Models\configModel;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class accessTimeMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $horaActual = now()->format('H:i:00');

        if (Auth::check() && Auth::id() !== 1) {//admin puede navegar sin restricciones
            $user = Auth::user();
            $inicio = $user->hora_inicio;
            $fin = $user->hora_fin;

            if (is_null($inicio) || is_null($fin)) {
                $config = configModel::where('sistema', 'admin')->first();
                $inicio = $config->hora_inicio_activacion;
                $fin = $config->hora_fin_activacion;
            }

            if ($horaActual >= $inicio && $horaActual <= $fin) {
                return $next($request);
            } else {
                return redirect()->route('restringido');
            }
        }
        return $next($request);
    }
}
