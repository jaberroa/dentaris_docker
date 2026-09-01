<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogUserActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo registrar actividad si el usuario está autenticado
        if (Auth::check()) {
            $user = Auth::user();
            
            // Obtener información de la petición
            $method = $request->method();
            $url = $request->fullUrl();
            $ip = $request->ip();
            $userAgent = $request->userAgent();
            
            // Filtrar rutas que no necesitan ser registradas
            $excludedRoutes = [
                'dashboard.appointments',
                'dashboard.revenue',
                'search.patients',
                'search.products'
            ];
            
            $routeName = $request->route()?->getName();
            
            if (!in_array($routeName, $excludedRoutes)) {
                // Registrar actividad
                Log::info('User Activity', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'method' => $method,
                    'url' => $url,
                    'route_name' => $routeName,
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'timestamp' => now()
                ]);
            }
        }

        return $response;
    }
}





