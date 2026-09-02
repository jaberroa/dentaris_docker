<?php

namespace App\Http\Middleware;

use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicContextResolver;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class ResolveClinicContext
{
    public function __construct(
        private readonly ClinicContextResolver $resolver,
    ) {
    }

    /**
     * Resuelve un contexto multiclínica tras la autenticación.
     *
     * Las cabeceras expresan la clínica o sede solicitada, pero no conceden
     * acceso: el resolver las contrasta con registros activos en servidor.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            return $this->reject($request, 401, 'AUTHENTICATION_REQUIRED');
        }

        try {
            $context = $this->resolver->resolve(
                userId: $user->getAuthIdentifier(),
                clinicId: $request->header('X-Clinic-Id'),
                clinicSiteId: $request->header('X-Clinic-Site-Id'),
            );
        } catch (Throwable) {
            // No exponer detalles de infraestructura ni de pertenencia clínica.
            return $this->reject($request, 403, 'CLINIC_CONTEXT_UNAVAILABLE');
        }

        if ($context === null) {
            return $this->reject($request, 403, 'CLINIC_CONTEXT_UNAVAILABLE');
        }

        $request->attributes->set(ClinicContext::class, $context);
        $request->attributes->set('clinic.context', $context);

        return $next($request);
    }

    private function reject(Request $request, int $status, string $code): Response
    {
        if ($request->expectsJson()) {
            return response()->json([
                'message' => 'El contexto clínico no está disponible.',
                'code' => $code,
            ], $status);
        }

        return response('El contexto clínico no está disponible.', $status);
    }
}
