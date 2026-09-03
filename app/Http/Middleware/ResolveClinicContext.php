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
    private const CLINIC_SESSION_KEY = 'clinic_id';

    private const CLINIC_SITE_SESSION_KEY = 'clinic_site_id';

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
                clinicId: $this->candidate($request, 'X-Clinic-Id', self::CLINIC_SESSION_KEY),
                clinicSiteId: $this->candidate($request, 'X-Clinic-Site-Id', self::CLINIC_SITE_SESSION_KEY),
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

    /**
     * Las cabeceras conservan prioridad para API y clientes existentes. En
     * solicitudes web, la sesión permite transportar un contexto previamente
     * seleccionado sin convertir ese valor en una concesión de acceso.
     */
    private function candidate(Request $request, string $header, string $sessionKey): mixed
    {
        if ($request->headers->has($header)) {
            return $request->header($header);
        }

        if ($request->hasSession()) {
            return $request->session()->get($sessionKey);
        }

        return null;
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
