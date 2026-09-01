<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        // Verificar si el usuario está autenticado
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Verificar si el usuario tiene el permiso requerido
        if (!$user->hasPermission($permission)) {
            // Si es una petición AJAX, devolver JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Unauthorized',
                    'message' => 'No tienes permisos para realizar esta acción'
                ], 403);
            }

            // Si no es AJAX, mostrar página de error 403
            return response()->view('errors.403', [
                'message' => 'No tienes permisos para acceder a esta página. Contacta al administrador si crees que esto es un error.'
            ], 403);
        }

        return $next($request);
    }
}
