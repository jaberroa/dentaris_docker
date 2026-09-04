<?php

namespace App\Modules\Clinics\Services;

use Illuminate\Database\ConnectionInterface;

class ClinicOwnedDomainReadinessService
{
    /** @var array<string, list<string>> */
    private const DOMAIN_TABLES = [
        'inventory' => ['inventory_locations', 'inventory', 'inventory_movements'],
        'billing' => ['invoices', 'payments'],
    ];

    public function __construct(
        private readonly ConnectionInterface $connection,
    ) {}

    public function isReady(string $domain): bool
    {
        $tables = self::DOMAIN_TABLES[$domain] ?? [];

        if ($tables === []) {
            return false;
        }

        $schema = $this->connection->getSchemaBuilder();

        foreach ($tables as $table) {
            if (! $schema->hasTable($table) || ! $schema->hasColumn($table, 'clinic_id')) {
                return false;
            }

            if ($this->connection->table($table)->whereNull('clinic_id')->exists()) {
                return false;
            }
        }

        return $this->relationsAreConsistent($domain);
    }

    private function relationsAreConsistent(string $domain): bool
    {
        if ($domain === 'inventory') {
            $locationMismatch = $this->connection->table('inventory as stock')
                ->leftJoin('inventory_locations as location', 'location.id', '=', 'stock.inventory_location_id')
                ->whereNotNull('stock.inventory_location_id')
                ->where(function ($query): void {
                    $query->whereNull('location.id')
                        ->orWhereNull('location.clinic_id')
                        ->orWhereColumn('location.clinic_id', '<>', 'stock.clinic_id');
                })
                ->exists();
            $movementMismatch = $this->connection->table('inventory_movements as movement')
                ->leftJoin('inventory as stock', 'stock.id', '=', 'movement.inventory_id')
                ->where(function ($query): void {
                    $query->whereNull('stock.id')
                        ->orWhereNull('stock.clinic_id')
                        ->orWhereColumn('stock.clinic_id', '<>', 'movement.clinic_id')
                        ->orWhereColumn('stock.product_id', '<>', 'movement.product_id');
                })
                ->exists();

            return ! $locationMismatch && ! $movementMismatch;
        }

        if ($domain === 'billing') {
            $invoiceMismatch = $this->connection->table('invoices as invoice')
                ->leftJoin('patients as patient', 'patient.id', '=', 'invoice.patient_id')
                ->leftJoin('staff as professional', 'professional.id', '=', 'invoice.staff_id')
                ->where(function ($query): void {
                    $query->whereNull('patient.id')
                        ->orWhereNull('patient.clinic_id')
                        ->orWhereColumn('patient.clinic_id', '<>', 'invoice.clinic_id')
                        ->orWhereNull('professional.id')
                        ->orWhereNull('professional.clinic_id')
                        ->orWhereColumn('professional.clinic_id', '<>', 'invoice.clinic_id');
                })
                ->exists();
            $paymentMismatch = $this->connection->table('payments as payment')
                ->leftJoin('invoices as invoice', 'invoice.id', '=', 'payment.invoice_id')
                ->leftJoin('patients as patient', 'patient.id', '=', 'payment.patient_id')
                ->where(function ($query): void {
                    $query->whereNull('invoice.id')
                        ->orWhereNull('invoice.clinic_id')
                        ->orWhereColumn('invoice.clinic_id', '<>', 'payment.clinic_id')
                        ->orWhereNull('patient.id')
                        ->orWhereNull('patient.clinic_id')
                        ->orWhereColumn('patient.clinic_id', '<>', 'payment.clinic_id')
                        ->orWhereColumn('invoice.patient_id', '<>', 'payment.patient_id');
                })
                ->exists();

            return ! $invoiceMismatch && ! $paymentMismatch;
        }

        return false;
    }
}
