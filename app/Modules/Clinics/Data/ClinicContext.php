<?php

namespace App\Modules\Clinics\Data;

use InvalidArgumentException;

/**
 * Contexto multiclínica validado en servidor para una solicitud clínica.
 *
 * Los identificadores almacenados aquí proceden exclusivamente de una
 * membresía y, cuando aplica, de una autorización de sede verificadas.
 */
final readonly class ClinicContext
{
    public function __construct(
        public int $userId,
        public int $clinicId,
        public int $membershipId,
        public ?int $clinicSiteId = null,
    ) {
        if ($this->userId < 1 || $this->clinicId < 1 || $this->membershipId < 1) {
            throw new InvalidArgumentException('A clinic context requires valid identifiers.');
        }

        if ($this->clinicSiteId !== null && $this->clinicSiteId < 1) {
            throw new InvalidArgumentException('A clinic site identifier must be valid when present.');
        }
    }

    public function hasClinicSite(): bool
    {
        return $this->clinicSiteId !== null;
    }
}
