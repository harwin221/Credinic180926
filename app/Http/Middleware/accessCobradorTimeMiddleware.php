<?php

namespace App\Http\Middleware;

use App\Models\configModel;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class accessCobradorTimeMiddleware
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
            $config = configModel::where('sistema', 'cobradores')->first();

            if (!Carbon::now()->isSunday() && $horaActual >= $config->hora_inicio_activacion && $horaActual <= $config->hora_fin_activacion) {
                return $next($request);
            } else {
                return redirect()->route('cobrador.restringido');
            }
        }
        return $next($request);
    }
}
