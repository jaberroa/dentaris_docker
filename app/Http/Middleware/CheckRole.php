<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar si el usuario tiene alguno de los roles requeridos
        if (!$user->hasAnyRole($roles)) {
            // Si es una petición AJAX, devolver JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'No tienes el rol requerido para acceder a esta función'
                ], 403);
            }

            // Si no es AJAX, redirigir con mensaje de error
            return redirect()->back()
                ->withErrors(['error' => 'No tienes el rol requerido para acceder a esta función']);
        }

        return $next($request);
    }
}