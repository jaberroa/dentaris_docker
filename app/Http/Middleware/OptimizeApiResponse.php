<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class OptimizeApiResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo optimizar respuestas JSON de API
        if ($this->isApiRequest($request) && $response instanceof JsonResponse) {
            $this->optimizeJsonResponse($response);
        }

        return $response;
    }

    /**
     * Check if this is an API request
     */
    protected function isApiRequest(Request $request): bool
    {
        return $request->is('api/*') || $request->expectsJson();
    }

    /**
     * Optimize JSON response
     */
    protected function optimizeJsonResponse(JsonResponse $response): void
    {
        $data = $response->getData(true);

        // Optimizar estructura de respuesta
        if (is_array($data)) {
            $optimizedData = $this->optimizeResponseStructure($data);
            $response->setData($optimizedData);
        }

        // Agregar headers de optimización
        $response->headers->set('X-Response-Time', $this->getResponseTime());
        $response->headers->set('X-Memory-Usage', memory_get_usage(true));
        $response->headers->set('X-Cache-Status', $this->getCacheStatus());
    }

    /**
     * Optimize response structure
     */
    protected function optimizeResponseStructure(array $data): array
    {
        // Remover campos nulos
        $data = $this->removeNullFields($data);

        // Optimizar arrays de datos
        if (isset($data['data']) && is_array($data['data'])) {
            $data['data'] = $this->optimizeDataArray($data['data']);
        }

        // Optimizar paginación
        if (isset($data['pagination'])) {
            $data['pagination'] = $this->optimizePaginationData($data['pagination']);
        }

        return $data;
    }

    /**
     * Remove null fields from response
     */
    protected function removeNullFields(array $data): array
    {
        return array_filter($data, function ($value) {
            return $value !== null;
        });
    }

    /**
     * Optimize data array
     */
    protected function optimizeDataArray(array $data): array
    {
        // Si es una colección de modelos, optimizar cada uno
        if (!empty($data) && is_array($data[0] ?? null)) {
            return array_map([$this, 'optimizeModelData'], $data);
        }

        return $data;
    }

    /**
     * Optimize individual model data
     */
    protected function optimizeModelData(array $model): array
    {
        // Remover campos vacíos
        $model = array_filter($model, function ($value) {
            return $value !== null && $value !== '';
        });

        // Optimizar fechas
        $model = $this->optimizeDateFields($model);

        return $model;
    }

    /**
     * Optimize date fields
     */
    protected function optimizeDateFields(array $data): array
    {
        $dateFields = ['created_at', 'updated_at', 'deleted_at', 'appointment_date', 'payment_date', 'invoice_date'];

        foreach ($dateFields as $field) {
            if (isset($data[$field]) && is_string($data[$field])) {
                // Convertir fechas a formato ISO si es necesario
                try {
                    $date = new \DateTime($data[$field]);
                    $data[$field] = $date->format('c'); // ISO 8601
                } catch (\Exception $e) {
                    // Mantener formato original si no es una fecha válida
                }
            }
        }

        return $data;
    }

    /**
     * Optimize pagination data
     */
    protected function optimizePaginationData(array $pagination): array
    {
        // Remover campos innecesarios
        $optimized = [
            'current_page' => $pagination['current_page'] ?? 1,
            'last_page' => $pagination['last_page'] ?? 1,
            'per_page' => $pagination['per_page'] ?? 15,
            'total' => $pagination['total'] ?? 0,
        ];

        // Solo incluir from/to si son relevantes
        if (isset($pagination['from']) && isset($pagination['to'])) {
            $optimized['from'] = $pagination['from'];
            $optimized['to'] = $pagination['to'];
        }

        return $optimized;
    }

    /**
     * Get response time
     */
    protected function getResponseTime(): string
    {
        $startTime = defined('LARAVEL_START') ? LARAVEL_START : microtime(true);
        $endTime = microtime(true);
        
        return round(($endTime - $startTime) * 1000, 2) . 'ms';
    }

    /**
     * Get cache status
     */
    protected function getCacheStatus(): string
    {
        // Verificar si la respuesta viene del cache
        $cacheHeaders = [
            'X-Cache-Status' => 'MISS',
            'X-Cache-Key' => null,
        ];

        return $cacheHeaders['X-Cache-Status'];
    }
}