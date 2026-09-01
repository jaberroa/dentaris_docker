<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CompressResponse
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Solo comprimir respuestas JSON y HTML
        if ($this->shouldCompress($request, $response)) {
            $this->compressResponse($response);
        }

        return $response;
    }

    /**
     * Determine if the response should be compressed
     */
    protected function shouldCompress(Request $request, Response $response): bool
    {
        // Verificar si el cliente acepta compresión
        if (!$request->header('Accept-Encoding') || 
            !str_contains($request->header('Accept-Encoding'), 'gzip')) {
            return false;
        }

        // Solo comprimir respuestas JSON y HTML
        $contentType = $response->headers->get('Content-Type', '');
        if (!str_contains($contentType, 'application/json') && 
            !str_contains($contentType, 'text/html')) {
            return false;
        }

        // Solo comprimir respuestas mayores a 1KB
        if (strlen($response->getContent()) < 1024) {
            return false;
        }

        return true;
    }

    /**
     * Compress the response content
     */
    protected function compressResponse(Response $response): void
    {
        $content = $response->getContent();
        $compressed = gzencode($content, 6); // Nivel de compresión 6 (balanceado)

        if ($compressed !== false) {
            $response->setContent($compressed);
            $response->headers->set('Content-Encoding', 'gzip');
            $response->headers->set('Content-Length', strlen($compressed));
            $response->headers->set('Vary', 'Accept-Encoding');
        }
    }
}