<?php

namespace App\Modules\Clinics\Services;

use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Support\Collection;

class ClinicSelectionService
{
    public function __construct(
        private readonly ClinicContextResolver $resolver,
    ) {}

    /**
     * @return Collection<int, Clinic>
     */
    public function availableFor(User $user): Collection
    {
        if (! $user->is_active) {
            return collect();
        }

        return Clinic::query()
            ->select('clinics.*')
            ->join('clinic_memberships as membership', 'membership.clinic_id', '=', 'clinics.id')
            ->where('membership.user_id', $user->getAuthIdentifier())
            ->where('membership.status', 'active')
            ->whereNotNull('membership.activated_at')
            ->whereNull('membership.suspended_at')
            ->where('clinics.is_active', true)
            ->distinct()
            ->orderBy('clinics.name')
            ->get();
    }

    public function resolveFor(User $user, mixed $clinicId): ?ClinicContext
    {
        if (! $user->is_active) {
            return null;
        }

        return $this->resolver->resolve(
            $user->getAuthIdentifier(),
            $clinicId,
        );
    }
}
