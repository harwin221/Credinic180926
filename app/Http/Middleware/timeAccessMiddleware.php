<?php

namespace App\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class timeAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
//        if ($request->get('isAdmin'))
//            return $next($request);
//
//        $currentTime = now()->hour;
//        if (($currentTime >= 21 && $currentTime <= 23) || ($currentTime >= 0 && $currentTime <= 6)) {
//           return response()->json([
//                'message' => 'El sistema se encuentra bloqueado desde las 9pm y se reactiva a partir de las 6am.'
//            ], 403);
//        }
//
        return $next($request);
    }
}
