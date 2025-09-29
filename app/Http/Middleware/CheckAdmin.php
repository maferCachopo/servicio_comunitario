<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response{
       
        // Revisa si el usuario está logueado Y si su rol es 'admin'
        if (Auth::check() && Auth::user()->role == 'admin') {
            // Si es admin, déjalo continuar con su petición
            return $next($request);
        }

        // Si no es admin, redirígelo a su 'home' con un mensaje de error.
        return redirect('/home')->with('error', 'No tienes permiso para acceder a esta sección.');
    }
}
