<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Services\ClinicPermissionService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPermission
{
    public function __construct(
        private readonly ClinicPermissionService $permissions,
    ) {
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string $permission): Response
    {
        $user = $request->user();

        // Se conserva el contrato histórico para solicitudes web anónimas.
        if (! $user instanceof User) {
            return redirect()->route('login');
        }

        $context = $request->attributes->get(ClinicContext::class)
            ?? $request->attributes->get('clinic.context');

        // El contexto debe haber sido validado previamente por clinic.context.
        // Nunca se usan roles globales como alternativa de autorización clínica.
        if (! $context instanceof ClinicContext
            || ! $this->permissions->allows($user, $permission, $context)) {
            return $this->reject($request);
        }

        return $next($request);
    }

    private function reject(Request $request): Response
    {
        // Se mantienen las respuestas públicas del middleware anterior para no
        // romper clientes existentes; solo cambia la fuente de autorización.
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'No tienes permisos para realizar esta acción',
            ], 403);
        }

        return response()->view('errors.403', [
            'message' => 'No tienes permisos para acceder a esta página. Contacta al administrador si crees que esto es un error.',
        ], 403);
    }
}
