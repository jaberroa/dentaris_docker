<?php

namespace App\Modules\Clinics\Services;

use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Database\ConnectionInterface;

/**
 * Resuelve contexto clínico sin depender de roles globales ni de modelos
 * heredados. La solicitud solo aporta candidatos; la base de datos concede
 * el contexto al comprobar clínica, membresía y sede activas.
 */
class ClinicContextResolver
{
    public function __construct(
        private readonly ConnectionInterface $connection,
    ) {
    }

    public function resolve(mixed $userId, mixed $clinicId, mixed $clinicSiteId = null): ?ClinicContext
    {
        $userId = $this->positiveInteger($userId);
        $clinicId = $this->positiveInteger($clinicId);

        if ($userId === null || $clinicId === null || !$this->foundationIsAvailable()) {
            return null;
        }

        $membership = $this->connection->table('clinic_memberships as membership')
            ->join('clinics as clinic', 'clinic.id', '=', 'membership.clinic_id')
            ->where('membership.user_id', $userId)
            ->where('membership.clinic_id', $clinicId)
            ->where('membership.status', 'active')
            ->where('clinic.is_active', true)
            ->first([
                'membership.id as membership_id',
                'membership.clinic_id',
            ]);

        if ($membership === null) {
            return null;
        }

        $siteId = $this->positiveInteger($clinicSiteId);

        if ($clinicSiteId !== null && $siteId === null) {
            return null;
        }

        if ($siteId !== null && !$this->siteIsAuthorized((int) $membership->membership_id, $clinicId, $siteId)) {
            return null;
        }

        return new ClinicContext(
            userId: $userId,
            clinicId: $clinicId,
            membershipId: (int) $membership->membership_id,
            clinicSiteId: $siteId,
        );
    }

    private function foundationIsAvailable(): bool
    {
        $schema = $this->connection->getSchemaBuilder();

        return $schema->hasTable('clinics')
            && $schema->hasTable('clinic_memberships')
            && $schema->hasTable('clinic_sites')
            && $schema->hasTable('clinic_membership_sites');
    }

    private function siteIsAuthorized(int $membershipId, int $clinicId, int $clinicSiteId): bool
    {
        return $this->connection->table('clinic_membership_sites as site_access')
            ->join('clinic_sites as site', function ($join) {
                $join->on('site.id', '=', 'site_access.clinic_site_id')
                    ->on('site.clinic_id', '=', 'site_access.clinic_id');
            })
            ->join('clinic_memberships as membership', 'membership.id', '=', 'site_access.clinic_membership_id')
            ->where('site_access.clinic_membership_id', $membershipId)
            ->where('site.id', $clinicSiteId)
            ->where('site.clinic_id', $clinicId)
            ->where('site.is_active', true)
            ->where('membership.status', 'active')
            ->exists();
    }

    private function positiveInteger(mixed $value): ?int
    {
        if (is_int($value)) {
            return $value > 0 ? $value : null;
        }

        if (!is_string($value) || preg_match('/^[1-9][0-9]*$/D', $value) !== 1) {
            return null;
        }

        $integer = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

        return $integer === false ? null : $integer;
    }
}
