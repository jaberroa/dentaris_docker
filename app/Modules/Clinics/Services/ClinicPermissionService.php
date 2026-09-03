<?php

namespace App\Modules\Clinics\Services;

use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Contracts\Container\Container;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

/**
 * Resuelve permisos exclusivamente desde la membresía clínica activa.
 *
 * Los roles globales de User no participan en esta decisión. El contexto
 * validado identifica de forma inequívoca la membresía que puede conceder el
 * permiso durante la solicitud actual.
 */
class ClinicPermissionService
{
    public function __construct(
        private readonly ConnectionInterface $connection,
        private readonly Container $container,
    ) {
    }

    public function allows(User $user, string $permission, ?ClinicContext $context = null): bool
    {
        $context ??= $this->currentContext();

        if ($context === null
            || $permission === ''
            || (int) $user->getAuthIdentifier() !== $context->userId
            || ! $user->is_active) {
            return false;
        }

        return $this->activeRolePermissions($context)->contains(
            static fn (string $grantedPermission): bool => hash_equals($grantedPermission, $permission)
        );
    }

    public function allowsResource(
        User $user,
        Model $resource,
        string $permission,
        ?ClinicContext $context = null,
    ): bool {
        $context ??= $this->currentContext();

        if ($context === null) {
            return false;
        }

        $clinicId = $resource->getAttribute('clinic_id');

        return $clinicId !== null
            && (int) $clinicId === $context->clinicId
            && $this->allows($user, $permission, $context);
    }

    public function currentContext(): ?ClinicContext
    {
        if (! $this->container->bound('request')) {
            return null;
        }

        $request = $this->container->make('request');

        if (! $request instanceof Request) {
            return null;
        }

        $context = $request->attributes->get(ClinicContext::class)
            ?? $request->attributes->get('clinic.context');

        return $context instanceof ClinicContext ? $context : null;
    }

    /**
     * @return Collection<int, string>
     */
    private function activeRolePermissions(ClinicContext $context): Collection
    {
        if (! $this->foundationIsAvailable()) {
            return collect();
        }

        return $this->connection->table('clinic_memberships as membership')
            ->join('clinics as clinic', 'clinic.id', '=', 'membership.clinic_id')
            ->join('users as user', 'user.id', '=', 'membership.user_id')
            ->join('clinic_membership_roles as assignment', 'assignment.clinic_membership_id', '=', 'membership.id')
            ->join('roles as role', 'role.id', '=', 'assignment.role_id')
            ->where('membership.id', $context->membershipId)
            ->where('membership.user_id', $context->userId)
            ->where('membership.clinic_id', $context->clinicId)
            ->where('membership.status', 'active')
            ->whereNotNull('membership.activated_at')
            ->whereNull('membership.suspended_at')
            ->where('clinic.is_active', true)
            ->where('user.is_active', true)
            ->where('role.is_active', true)
            ->pluck('role.permissions')
            ->flatMap(static function (mixed $permissions): array {
                if (is_string($permissions)) {
                    $permissions = json_decode($permissions, true);
                }

                return is_array($permissions) ? $permissions : [];
            })
            ->filter(static fn (mixed $permission): bool => is_string($permission) && $permission !== '')
            ->values();
    }

    private function foundationIsAvailable(): bool
    {
        $schema = $this->connection->getSchemaBuilder();

        return $schema->hasTable('clinics')
            && $schema->hasTable('users')
            && $schema->hasTable('clinic_memberships')
            && $schema->hasTable('clinic_membership_roles')
            && $schema->hasTable('roles');
    }
}
