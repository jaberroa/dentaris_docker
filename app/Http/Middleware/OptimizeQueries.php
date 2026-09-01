<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class OptimizeQueries
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Habilitar query logging en desarrollo
        if (app()->environment('local')) {
            DB::enableQueryLog();
        }

        $response = $next($request);

        // Log queries lentas en desarrollo
        if (app()->environment('local')) {
            $queries = DB::getQueryLog();
            $slowQueries = array_filter($queries, function ($query) {
                return $query['time'] > 100; // Queries que toman más de 100ms
            });

            if (!empty($slowQueries)) {
                \Log::warning('Slow queries detected:', $slowQueries);
            }
        }

        return $response;
    }
}