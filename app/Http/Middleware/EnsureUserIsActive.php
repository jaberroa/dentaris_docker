<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Verificar si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Verificar si el usuario está activo
            if (!$user->is_active) {
                Auth::logout();
                
                // Si es una petición AJAX, devolver JSON
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'error' => 'Account Deactivated',
                        'message' => 'Tu cuenta ha sido desactivada. Contacta al administrador.'
                    ], 403);
                }
                
                // Si no es AJAX, redirigir al login con mensaje
                return redirect()->route('login')
                    ->withErrors(['error' => 'Tu cuenta ha sido desactivada. Contacta al administrador.']);
            }
        }

        return $next($request);
    }
}





