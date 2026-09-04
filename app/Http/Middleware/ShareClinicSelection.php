<?php

namespace App\Http\Middleware;

use App\Models\User;
use App\Modules\Clinics\Services\ClinicSelectionService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class ShareClinicSelection
{
    private const CLINIC_SESSION_KEY = 'clinic_id';

    private const CLINIC_SITE_SESSION_KEY = 'clinic_site_id';

    public function __construct(
        private readonly ClinicSelectionService $selection,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $options = collect();
        $activeClinic = null;
        $user = $request->user();

        if ($user instanceof User) {
            $options = $this->selection->availableFor($user);
            $candidate = $request->hasSession()
                ? $request->session()->get(self::CLINIC_SESSION_KEY)
                : null;

            if ($candidate !== null) {
                $context = $this->selection->resolveFor($user, $candidate);

                if ($context === null) {
                    $request->session()->forget([
                        self::CLINIC_SESSION_KEY,
                        self::CLINIC_SITE_SESSION_KEY,
                    ]);
                } else {
                    $activeClinic = $options->firstWhere('id', $context->clinicId);
                }
            }
        }

        View::share('clinicSelectionOptions', $options);
        View::share('activeClinicSelection', $activeClinic);

        return $next($request);
    }
}
