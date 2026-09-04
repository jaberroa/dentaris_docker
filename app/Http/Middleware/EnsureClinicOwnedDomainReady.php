<?php

namespace App\Http\Middleware;

use App\Modules\Clinics\Services\ClinicOwnedDomainReadinessService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClinicOwnedDomainReady
{
    public function __construct(
        private readonly ClinicOwnedDomainReadinessService $readiness,
    ) {}

    /**
     * Fail closed until the nullable ownership contract exists and all legacy
     * rows in the requested domain have been assigned by an authorized backfill.
     */
    public function handle(Request $request, Closure $next, string $domain): Response
    {
        if (! $this->readiness->isReady($domain)) {
            return $this->reject($request);
        }

        return $next($request);
    }

    private function reject(Request $request): Response
    {
        $message = 'El módulo todavía no tiene preparada su propiedad clínica.';

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'code' => 'CLINIC_DOMAIN_NOT_READY',
            ], 503);
        }

        return response()->view('errors.503', ['message' => $message], 503);
    }
}
