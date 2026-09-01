<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PurchaseItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_id', 'product_id', 'quantity_ordered', 'quantity_received', 'unit_cost',
        'total_cost', 'expiry_date', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'quantity_ordered' => 'integer',
            'quantity_received' => 'integer',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
            'expiry_date' => 'date',
        ];
    }

    // Relaciones
    public function purchase()
    {
        return $this->belongsTo(Purchase::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    // Scopes
    public function scopeReceived($query)
    {
        return $query->where('quantity_received', '>', 0);
    }

    public function scopePending($query)
    {
        return $query->where('quantity_received', 0);
    }

    public function scopePartiallyReceived($query)
    {
        return $query->where('quantity_received', '>', 0)
                    ->whereRaw('quantity_received < quantity_ordered');
    }

    public function scopeFullyReceived($query)
    {
        return $query->whereRaw('quantity_received >= quantity_ordered');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    // Métodos de utilidad
    public function isReceived()
    {
        return $this->quantity_received > 0;
    }

    public function isPending()
    {
        return $this->quantity_received == 0;
    }

    public function isPartiallyReceived()
    {
        return $this->quantity_received > 0 && $this->quantity_received < $this->quantity_ordered;
    }

    public function isFullyReceived()
    {
        return $this->quantity_received >= $this->quantity_ordered;
    }

    public function getRemainingQuantity()
    {
        return max(0, $this->quantity_ordered - $this->quantity_received);
    }

    public function getCompletionPercentage()
    {
        if ($this->quantity_ordered == 0) return 100;
        return round(($this->quantity_received / $this->quantity_ordered) * 100, 2);
    }

    public function getFormattedUnitCostAttribute()
    {
        return '$' . number_format($this->unit_cost, 2);
    }

    public function getFormattedTotalCostAttribute()
    {
        return '$' . number_format($this->total_cost, 2);
    }

    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date ? $this->expiry_date->format('d/m/Y') : 'Sin vencimiento';
    }

    public function getExpiryStatus()
    {
        if (!$this->expiry_date) return 'no_expiry';
        
        $days = now()->diffInDays($this->expiry_date, false);
        
        if ($days < 0) return 'expired';
        if ($days <= 30) return 'expiring_soon';
        return 'valid';
    }

    public function getExpiryStatusDisplayAttribute()
    {
        return match($this->getExpiryStatus()) {
            'expired' => 'Vencido',
            'expiring_soon' => 'Por vencer',
            'valid' => 'Válido',
            'no_expiry' => 'Sin vencimiento',
            default => 'Desconocido'
        };
    }

    public function getExpiryStatusColorAttribute()
    {
        return match($this->getExpiryStatus()) {
            'expired' => 'danger',
            'expiring_soon' => 'warning',
            'valid' => 'success',
            'no_expiry' => 'info',
            default => 'secondary'
        };
    }

    public function getDaysToExpiry()
    {
        if (!$this->expiry_date) return null;
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getReceiptStatus()
    {
        if ($this->isFullyReceived()) return 'fully_received';
        if ($this->isPartiallyReceived()) return 'partially_received';
        return 'pending';
    }

    public function getReceiptStatusDisplayAttribute()
    {
        return match($this->getReceiptStatus()) {
            'fully_received' => 'Completamente recibido',
            'partially_received' => 'Parcialmente recibido',
            'pending' => 'Pendiente',
            default => 'Desconocido'
        };
    }

    public function getReceiptStatusColorAttribute()
    {
        return match($this->getReceiptStatus()) {
            'fully_received' => 'success',
            'partially_received' => 'warning',
            'pending' => 'danger',
            default => 'secondary'
        };
    }

    public function updateReceivedQuantity($quantity)
    {
        $this->update(['quantity_received' => $quantity]);
        
        // Actualizar inventario si hay cantidad recibida
        if ($quantity > 0 && $this->product && $this->product->inventory) {
            $this->product->inventory->updateStock($quantity, 'add', $this->unit_cost);
        }
        
        return $this;
    }

    public function calculateTotalCost()
    {
        $totalCost = $this->quantity_ordered * $this->unit_cost;
        $this->update(['total_cost' => $totalCost]);
        return $totalCost;
    }

    public function getReceivedValue()
    {
        return $this->quantity_received * $this->unit_cost;
    }

    public function getFormattedReceivedValueAttribute()
    {
        return '$' . number_format($this->getReceivedValue(), 2);
    }

    public function getPendingValue()
    {
        return $this->getRemainingQuantity() * $this->unit_cost;
    }

    public function getFormattedPendingValueAttribute()
    {
        return '$' . number_format($this->getPendingValue(), 2);
    }

    public static function getReceiptStatusOptions()
    {
        return [
            'pending' => 'Pendiente',
            'partially_received' => 'Parcialmente recibido',
            'fully_received' => 'Completamente recibido',
        ];
    }

    public static function getTotalValueReceived()
    {
        return self::sum(\DB::raw('quantity_received * unit_cost'));
    }

    public static function getTotalValuePending()
    {
        return self::sum(\DB::raw('(quantity_ordered - quantity_received) * unit_cost'));
    }

    public static function getExpiringItems($days = 30)
    {
        return self::with(['product', 'purchase.supplier'])
            ->where('expiry_date', '<=', now()->addDays($days))
            ->where('expiry_date', '>', now())
            ->where('quantity_received', '>', 0)
            ->get();
    }

    public static function getExpiredItems()
    {
        return self::with(['product', 'purchase.supplier'])
            ->where('expiry_date', '<', now())
            ->where('quantity_received', '>', 0)
            ->get();
    }
}
