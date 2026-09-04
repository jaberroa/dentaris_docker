<?php

namespace App\Http\Controllers;

use App\Http\Requests\Clinics\SelectClinicRequest;
use App\Models\User;
use App\Modules\Clinics\Services\ClinicSelectionService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ClinicContextController extends Controller
{
    public function index(Request $request, ClinicSelectionService $selection): View
    {
        /** @var User $user */
        $user = $request->user();

        return view('clinics.select', [
            'clinics' => $selection->availableFor($user),
        ]);
    }

    public function store(
        SelectClinicRequest $request,
        ClinicSelectionService $selection,
    ): RedirectResponse {
        /** @var User $user */
        $user = $request->user();
        $context = $selection->resolveFor($user, $request->validated('clinic_id'));

        if ($context === null) {
            throw ValidationException::withMessages([
                'clinic_id' => 'La clínica seleccionada no está disponible para tu cuenta.',
            ]);
        }

        $request->session()->regenerate();
        $request->session()->put('clinic_id', $context->clinicId);
        $request->session()->forget('clinic_site_id');

        Log::info('clinic_context.selected', [
            'user_id' => $context->userId,
            'clinic_id' => $context->clinicId,
            'membership_id' => $context->membershipId,
        ]);
        activity('security')
            ->causedBy($user)
            ->withProperties([
                'clinic_id' => $context->clinicId,
                'membership_id' => $context->membershipId,
            ])
            ->log('clinic_context.selected');

        return redirect()->route('dashboard')
            ->with('success', 'Contexto clínico actualizado correctamente.');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $userId = $request->user()?->getAuthIdentifier();
        $clinicId = $request->session()->get('clinic_id');

        $request->session()->forget(['clinic_id', 'clinic_site_id']);
        $request->session()->regenerate();

        Log::info('clinic_context.cleared', [
            'user_id' => $userId,
            'clinic_id' => $clinicId,
        ]);
        activity('security')
            ->causedBy($request->user())
            ->withProperties(['clinic_id' => $clinicId])
            ->log('clinic_context.cleared');

        return redirect()->route('clinics.select')
            ->with('status', 'Selecciona la clínica con la que deseas trabajar.');
    }
}
