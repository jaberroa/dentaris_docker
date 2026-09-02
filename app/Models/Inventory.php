<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'product_id', 'current_stock', 'reserved_stock', 'available_stock', 'average_cost',
        'last_restocked', 'last_used', 'location', 'notes', 'low_stock_alert',
        'out_of_stock_alert', 'expiry_alert'
    ];

    protected function casts(): array
    {
        return [
            'average_cost' => 'decimal:2',
            'last_restocked' => 'date',
            'last_used' => 'date',
            'low_stock_alert' => 'boolean',
            'out_of_stock_alert' => 'boolean',
            'expiry_alert' => 'boolean',
        ];
    }

    // Relaciones
    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function movements()
    {
        return $this->hasMany(InventoryMovement::class);
    }

    // Scopes
    public function scopeLowStock($query)
    {
        return $query->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('available_stock', 0);
    }

    public function scopeWithAlerts($query)
    {
        return $query->where(function($q) {
            $q->where('low_stock_alert', true)
              ->orWhere('out_of_stock_alert', true)
              ->orWhere('expiry_alert', true);
        });
    }

    public function scopeByLocation($query, $location)
    {
        return $query->where('location', 'like', "%{$location}%");
    }

    public function scopeRecentlyRestocked($query, $days = 30)
    {
        return $query->where('last_restocked', '>=', now()->subDays($days));
    }

    public function scopeRecentlyUsed($query, $days = 30)
    {
        return $query->where('last_used', '>=', now()->subDays($days));
    }

    // Métodos de utilidad
    public function getStockValue()
    {
        return $this->current_stock * $this->average_cost;
    }

    public function getFormattedStockValueAttribute()
    {
        return '$' . number_format($this->getStockValue(), 2);
    }

    public function getFormattedAverageCostAttribute()
    {
        return '$' . number_format($this->average_cost, 2);
    }

    public function getFormattedLastRestockedAttribute()
    {
        return $this->last_restocked ? $this->last_restocked->format('d/m/Y') : 'Nunca';
    }

    public function getFormattedLastUsedAttribute()
    {
        return $this->last_used ? $this->last_used->format('d/m/Y') : 'Nunca';
    }

    public function getStockLevel()
    {
        if (!$this->product) return 'unknown';
        
        $minimumStock = $this->product->minimum_stock;
        
        if ($this->available_stock <= 0) return 'out_of_stock';
        if ($this->available_stock <= $minimumStock) return 'low_stock';
        if ($this->available_stock <= $minimumStock * 2) return 'normal';
        return 'high_stock';
    }

    public function getStockLevelDisplayAttribute()
    {
        return match($this->getStockLevel()) {
            'out_of_stock' => 'Agotado',
            'low_stock' => 'Bajo stock',
            'normal' => 'Normal',
            'high_stock' => 'Alto stock',
            default => 'Desconocido'
        };
    }

    public function getStockLevelColorAttribute()
    {
        return match($this->getStockLevel()) {
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'normal' => 'success',
            'high_stock' => 'info',
            default => 'secondary'
        };
    }

    public function needsRestocking()
    {
        return $this->available_stock <= $this->product->minimum_stock;
    }

    public function isOutOfStock()
    {
        return $this->available_stock <= 0;
    }

    public function hasExpiryAlerts()
    {
        if (!$this->product || !$this->product->expiry_date) return false;
        
        return $this->product->isExpiringSoon(30) || $this->product->isExpired();
    }

    public function updateStock($quantity, $type = 'add', $updateCost = null)
    {
        $oldStock = $this->current_stock;
        $newStock = $type === 'add' ? $this->current_stock + $quantity : $this->current_stock - $quantity;
        
        // Actualizar costo promedio si se proporciona
        if ($updateCost && $type === 'add' && $quantity > 0) {
            $totalValue = ($this->current_stock * $this->average_cost) + ($quantity * $updateCost);
            $this->average_cost = $totalValue / $newStock;
        }

        $this->update([
            'current_stock' => max(0, $newStock),
            'available_stock' => max(0, $newStock - $this->reserved_stock),
            'last_restocked' => $type === 'add' ? now() : $this->last_restocked,
            'last_used' => $type === 'subtract' ? now() : $this->last_used,
        ]);

        // Actualizar alertas
        $this->updateAlerts();

        return $this;
    }

    public function reserveStock($quantity)
    {
        if ($this->available_stock >= $quantity) {
            $this->update([
                'reserved_stock' => $this->reserved_stock + $quantity,
                'available_stock' => $this->available_stock - $quantity
            ]);
            return true;
        }
        return false;
    }

    public function releaseReservedStock($quantity)
    {
        $newReservedStock = max(0, $this->reserved_stock - $quantity);
        $this->update([
            'reserved_stock' => $newReservedStock,
            'available_stock' => $this->current_stock - $newReservedStock
        ]);
        return true;
    }

    public function consumeStock($quantity)
    {
        if ($this->available_stock >= $quantity) {
            $this->update([
                'current_stock' => $this->current_stock - $quantity,
                'available_stock' => $this->available_stock - $quantity,
                'last_used' => now()
            ]);
            
            $this->updateAlerts();
            return true;
        }
        return false;
    }

    public function updateAlerts()
    {
        $lowStockAlert = $this->available_stock <= $this->product->minimum_stock;
        $outOfStockAlert = $this->available_stock <= 0;
        $expiryAlert = $this->hasExpiryAlerts();

        $this->update([
            'low_stock_alert' => $lowStockAlert,
            'out_of_stock_alert' => $outOfStockAlert,
            'expiry_alert' => $expiryAlert
        ]);
    }

    public function getRecommendedOrderQuantity()
    {
        if (!$this->product) return 0;
        
        $minimumStock = $this->product->minimum_stock;
        $maximumStock = $this->product->maximum_stock;
        
        if ($this->available_stock > $minimumStock) return 0;
        
        $orderQuantity = ($maximumStock ?: $minimumStock * 2) - $this->available_stock;
        
        return max($minimumStock, $orderQuantity);
    }

    public function getDaysSinceLastRestocked()
    {
        return $this->last_restocked ? now()->diffInDays($this->last_restocked) : null;
    }

    public function getDaysSinceLastUsed()
    {
        return $this->last_used ? now()->diffInDays($this->last_used) : null;
    }

    public function getTurnoverRate()
    {
        // Tasa de rotación basada en el último uso
        $daysSinceLastUsed = $this->getDaysSinceLastUsed();
        
        if (!$daysSinceLastUsed || $daysSinceLastUsed === 0) return 0;
        
        return $this->current_stock / $daysSinceLastUsed;
    }

    public function getFormattedTurnoverRateAttribute()
    {
        return number_format($this->getTurnoverRate(), 2) . ' unidades/día';
    }

    public static function updateAllAlerts()
    {
        $inventories = self::with('product')->get();
        
        foreach ($inventories as $inventory) {
            $inventory->updateAlerts();
        }
        
        return $inventories->count();
    }

    public static function getLowStockProducts()
    {
        return self::with('product')
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->whereRaw('available_stock <= (SELECT minimum_stock FROM products WHERE products.id = inventory.product_id)')
            ->get();
    }

    public static function getOutOfStockProducts()
    {
        return self::with('product')
            ->whereHas('product', function($query) {
                $query->where('is_active', true);
            })
            ->where('available_stock', 0)
            ->get();
    }
}
