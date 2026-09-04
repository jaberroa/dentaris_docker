<?php

namespace App\Services;

use App\Models\CdtCatalog;
use App\Models\Invoice;
use App\Models\User;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class InvoiceLifecycleService
{
    public function updateDraft(Invoice $invoice, array $data, User $actor, ClinicContext $context): Invoice
    {
        return DB::transaction(function () use ($invoice, $data, $actor, $context) {
            $invoice = Invoice::query()->forClinic($context)->lockForUpdate()->findOrFail($invoice->id);

            if (! $invoice->isEditable($context)) {
                throw new InvalidArgumentException('Solo se puede editar una factura en borrador sin pagos registrados.');
            }

            $invoice->update([
                'invoice_date' => $data['invoice_date'],
                'due_date' => $data['due_date'] ?? null,
                'tax_rate' => $data['tax_rate'] ?? 0,
                'discount_amount' => $data['discount_amount'] ?? 0,
                'notes' => $data['notes'] ?? null,
            ]);

            $invoice->items()->delete();
            foreach ($data['items'] as $index => $item) {
                $catalogItem = CdtCatalog::query()->findOrFail($item['cdt_catalog_id']);
                $quantity = (int) $item['quantity'];
                $unitPrice = (float) $item['unit_price'];

                $invoice->items()->create([
                    'cdt_catalog_id' => $catalogItem->id,
                    'sequence_order' => $index + 1,
                    'item_name' => $catalogItem->procedure_name,
                    'description' => $catalogItem->description,
                    'quantity' => $quantity,
                    'unit_price' => $unitPrice,
                    'total_price' => $quantity * $unitPrice,
                ]);
            }

            $invoice->calculateTotals();
            activity('billing')
                ->causedBy($actor)
                ->performedOn($invoice)
                ->withProperties(['clinic_id' => $context->clinicId, 'items_count' => count($data['items'])])
                ->log('invoice.updated');

            return $invoice->fresh(['items.cdtCatalog']);
        });
    }

    public function send(Invoice $invoice, User $actor, ClinicContext $context): Invoice
    {
        return DB::transaction(function () use ($invoice, $actor, $context) {
            $invoice = Invoice::query()->forClinic($context)->lockForUpdate()->findOrFail($invoice->id);

            if (! in_array($invoice->status, ['draft', 'sent'], true)) {
                throw new InvalidArgumentException('Solo se pueden marcar como enviadas las facturas en borrador.');
            }

            $invoice->update(['status' => 'sent']);
            activity('billing')->causedBy($actor)->performedOn($invoice)->withProperties(['clinic_id' => $context->clinicId])->log('invoice.sent');

            return $invoice;
        });
    }

    public function cancel(Invoice $invoice, User $actor, string $reason, ClinicContext $context): Invoice
    {
        return DB::transaction(function () use ($invoice, $actor, $reason, $context) {
            $invoice = Invoice::query()->forClinic($context)->lockForUpdate()->findOrFail($invoice->id);

            if ($invoice->payments()->forClinic($context)->exists()) {
                throw new InvalidArgumentException('No se puede anular una factura con pagos registrados.');
            }

            if ($invoice->status === 'cancelled') {
                throw new InvalidArgumentException('La factura ya está anulada.');
            }

            $invoice->update(['status' => 'cancelled']);
            activity('billing')
                ->causedBy($actor)
                ->performedOn($invoice)
                ->withProperties(['clinic_id' => $context->clinicId, 'reason' => $reason])
                ->log('invoice.cancelled');

            return $invoice;
        });
    }
}
