<?php

namespace App\Models;

use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Purchase extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_number', 'supplier_id', 'purchase_date', 'expected_delivery', 'actual_delivery',
        'status', 'subtotal', 'tax_rate', 'tax_amount', 'shipping_cost', 'discount_amount',
        'total_amount', 'notes', 'invoice_number', 'tracking_number', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'purchase_date' => 'date',
            'expected_delivery' => 'date',
            'actual_delivery' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
        ];
    }

    // Relaciones
    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeForClinic(Builder $query, ClinicContext $context): Builder
    {
        return $query->where($query->qualifyColumn('clinic_id'), $context->clinicId);
    }

    public function items()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeOrdered($query)
    {
        return $query->where('status', 'ordered');
    }

    public function scopeReceived($query)
    {
        return $query->where('status', 'received');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery', '<', now())
                    ->whereIn('status', ['pending', 'ordered']);
    }

    public function scopeBySupplier($query, $supplierId)
    {
        return $query->where('supplier_id', $supplierId);
    }

    public function scopeByDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('purchase_date', [$startDate, $endDate]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('purchase_date', now()->month)
                    ->whereYear('purchase_date', now()->year);
    }

    public function scopeThisYear($query)
    {
        return $query->whereYear('purchase_date', now()->year);
    }

    // Métodos de utilidad
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isOrdered()
    {
        return $this->status === 'ordered';
    }

    public function isReceived()
    {
        return $this->status === 'received';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isOverdue()
    {
        return $this->expected_delivery < now() && 
               in_array($this->status, ['pending', 'ordered']);
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'ordered' => 'Ordenado',
            'received' => 'Recibido',
            'cancelled' => 'Cancelado',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'ordered' => 'info',
            'received' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedSubtotalAttribute()
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return '$' . number_format($this->tax_amount, 2);
    }

    public function getFormattedShippingCostAttribute()
    {
        return '$' . number_format($this->shipping_cost, 2);
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return '$' . number_format($this->discount_amount, 2);
    }

    public function getFormattedTotalAmountAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }

    public function getFormattedPurchaseDateAttribute()
    {
        return $this->purchase_date->format('d/m/Y');
    }

    public function getFormattedExpectedDeliveryAttribute()
    {
        return $this->expected_delivery ? $this->expected_delivery->format('d/m/Y') : 'N/A';
    }

    public function getFormattedActualDeliveryAttribute()
    {
        return $this->actual_delivery ? $this->actual_delivery->format('d/m/Y') : 'N/A';
    }

    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) return 0;
        return now()->diffInDays($this->expected_delivery);
    }

    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    public function getTotalQuantityOrderedAttribute()
    {
        return $this->items()->sum('quantity_ordered');
    }

    public function getTotalQuantityReceivedAttribute()
    {
        return $this->items()->sum('quantity_received');
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->total_quantity_ordered == 0) return 100;
        return round(($this->total_quantity_received / $this->total_quantity_ordered) * 100, 2);
    }

    public function isFullyReceived()
    {
        return $this->total_quantity_received >= $this->total_quantity_ordered;
    }

    public function isPartiallyReceived()
    {
        return $this->total_quantity_received > 0 && !$this->isFullyReceived();
    }

    public function calculateTotals()
    {
        $subtotal = $this->items()->sum('total_cost');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $totalAmount = $subtotal + $taxAmount + $this->shipping_cost - $this->discount_amount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
        ]);

        return $this;
    }

    public function markAsOrdered()
    {
        $this->update(['status' => 'ordered']);
    }

    public function markAsReceived($actualDeliveryDate = null)
    {
        $this->update([
            'status' => 'received',
            'actual_delivery' => $actualDeliveryDate ?: now(),
        ]);

        // Actualizar inventario de productos
        foreach ($this->items as $item) {
            if ($item->quantity_received > 0) {
                $product = $item->product;
                if ($product->inventory) {
                    $product->inventory->updateStock(
                        $item->quantity_received, 
                        'add', 
                        $item->unit_cost
                    );
                }
            }
        }
    }

    public function markAsCancelled($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason ? $this->notes . "\nCancelado: " . $reason : $this->notes
        ]);
    }

    public function addItem($productId, $quantity, $unitCost, $expiryDate = null)
    {
        $product = Product::find($productId);
        
        return $this->items()->create([
            'product_id' => $productId,
            'quantity_ordered' => $quantity,
            'unit_cost' => $unitCost,
            'total_cost' => $quantity * $unitCost,
            'expiry_date' => $expiryDate,
        ]);
    }

    public function updateItemQuantities($itemQuantities)
    {
        foreach ($itemQuantities as $itemId => $quantities) {
            $item = $this->items()->find($itemId);
            if ($item) {
                $item->update([
                    'quantity_received' => $quantities['received'],
                ]);
            }
        }

        // Recalcular totales si es necesario
        $this->calculateTotals();
    }

    public function getDeliveryStatus()
    {
        if ($this->isCancelled()) return 'cancelled';
        if ($this->isReceived()) return 'received';
        if ($this->isOverdue()) return 'overdue';
        if ($this->isOrdered()) return 'ordered';
        return 'pending';
    }

    public function getDeliveryStatusDisplayAttribute()
    {
        return match($this->getDeliveryStatus()) {
            'cancelled' => 'Cancelado',
            'received' => 'Recibido',
            'overdue' => 'Atrasado',
            'ordered' => 'Ordenado',
            'pending' => 'Pendiente',
            default => 'Desconocido'
        };
    }

    public function getDeliveryStatusColorAttribute()
    {
        return match($this->getDeliveryStatus()) {
            'cancelled' => 'danger',
            'received' => 'success',
            'overdue' => 'danger',
            'ordered' => 'info',
            'pending' => 'warning',
            default => 'secondary'
        };
    }

    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pendiente',
            'ordered' => 'Ordenado',
            'received' => 'Recibido',
            'cancelled' => 'Cancelado',
        ];
    }

    public static function generatePurchaseNumber()
    {
        $lastPurchase = self::latest()->first();
        $number = $lastPurchase ? (int) substr($lastPurchase->purchase_number, -6) + 1 : 1;
        
        return 'COMP-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public static function getOverduePurchases()
    {
        return self::overdue()->with(['supplier', 'items.product'])->get();
    }

    public static function getMonthlyPurchases($year = null, $month = null)
    {
        $year = $year ?: now()->year;
        $month = $month ?: now()->month;

        return self::whereYear('purchase_date', $year)
                   ->whereMonth('purchase_date', $month)
                   ->where('status', '!=', 'cancelled')
                   ->sum('total_amount');
    }

    public static function getTotalPurchasesBySupplier($supplierId, $startDate = null, $endDate = null)
    {
        $query = self::where('supplier_id', $supplierId)
                    ->where('status', '!=', 'cancelled');

        if ($startDate) $query->where('purchase_date', '>=', $startDate);
        if ($endDate) $query->where('purchase_date', '<=', $endDate);

        return $query->sum('total_amount');
    }
}
