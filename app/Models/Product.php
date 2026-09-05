<?php

namespace App\Models;

use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'product_code', 'name', 'description', 'category', 'subcategory', 'unit_of_measure',
        'cost_price', 'selling_price', 'minimum_stock', 'maximum_stock', 'barcode', 'brand',
        'model', 'expiry_date', 'storage_conditions', 'usage_instructions', 'requires_prescription',
        'is_controlled', 'is_active', 'primary_supplier_id', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'cost_price' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'expiry_date' => 'date',
            'requires_prescription' => 'boolean',
            'is_controlled' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function primarySupplier()
    {
        return $this->belongsTo(Supplier::class, 'primary_supplier_id');
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeForClinic(Builder $query, ClinicContext $context): Builder
    {
        return $query->where($query->qualifyColumn('clinic_id'), $context->clinicId);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function purchaseItems()
    {
        return $this->hasMany(PurchaseItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByBrand($query, $brand)
    {
        return $query->where('brand', $brand);
    }

    public function scopeLowStock($query)
    {
        return $query->whereHas('inventory', function($q) {
            $q->whereRaw('available_stock <= minimum_stock');
        });
    }

    public function scopeOutOfStock($query)
    {
        return $query->whereHas('inventory', function($q) {
            $q->where('available_stock', 0);
        });
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('expiry_date', '<=', now()->addDays($days))
                    ->where('expiry_date', '>', now());
    }

    public function scopeControlled($query)
    {
        return $query->where('is_controlled', true);
    }

    public function scopeRequiresPrescription($query)
    {
        return $query->where('requires_prescription', true);
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->is_active;
    }

    public function isControlled()
    {
        return $this->is_controlled;
    }

    public function requiresPrescription()
    {
        return $this->requires_prescription;
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date <= now()->addDays($days) && 
               $this->expiry_date > now();
    }

    public function getCurrentStock()
    {
        return $this->inventory ? $this->inventory->available_stock : 0;
    }

    public function isLowStock()
    {
        return $this->getCurrentStock() <= $this->minimum_stock;
    }

    public function isOutOfStock()
    {
        return $this->getCurrentStock() <= 0;
    }

    public function getStockStatus()
    {
        if ($this->isOutOfStock()) return 'out_of_stock';
        if ($this->isLowStock()) return 'low_stock';
        return 'in_stock';
    }

    public function getStockStatusDisplayAttribute()
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'Agotado',
            'low_stock' => 'Bajo stock',
            'in_stock' => 'En stock',
            default => 'Desconocido'
        };
    }

    public function getStockStatusColorAttribute()
    {
        return match($this->getStockStatus()) {
            'out_of_stock' => 'danger',
            'low_stock' => 'warning',
            'in_stock' => 'success',
            default => 'secondary'
        };
    }

    public function getFormattedCostPriceAttribute()
    {
        return $this->cost_price ? '$' . number_format($this->cost_price, 2) : 'N/A';
    }

    public function getFormattedSellingPriceAttribute()
    {
        return $this->selling_price ? '$' . number_format($this->selling_price, 2) : 'N/A';
    }

    public function getProfitMargin()
    {
        if (!$this->cost_price || !$this->selling_price) return 0;
        return (($this->selling_price - $this->cost_price) / $this->cost_price) * 100;
    }

    public function getFormattedProfitMarginAttribute()
    {
        return number_format($this->getProfitMargin(), 2) . '%';
    }

    public function getFormattedExpiryDateAttribute()
    {
        return $this->expiry_date ? $this->expiry_date->format('d/m/Y') : 'Sin vencimiento';
    }

    public function getDaysToExpiry()
    {
        if (!$this->expiry_date) return null;
        return now()->diffInDays($this->expiry_date, false);
    }

    public function getExpiryStatus()
    {
        if (!$this->expiry_date) return 'no_expiry';
        
        $days = $this->getDaysToExpiry();
        
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

    public function updateStock($quantity, $type = 'add')
    {
        if (!$this->inventory) {
            $this->inventory()->create([
                'current_stock' => 0,
                'available_stock' => 0,
                'reserved_stock' => 0
            ]);
            $this->load('inventory');
        }

        $currentStock = $this->inventory->current_stock;
        $newStock = $type === 'add' ? $currentStock + $quantity : $currentStock - $quantity;

        $this->inventory->update([
            'current_stock' => max(0, $newStock),
            'available_stock' => max(0, $newStock - $this->inventory->reserved_stock)
        ]);

        $this->inventory->refresh();
        return $this->inventory;
    }

    public function reserveStock($quantity)
    {
        if (!$this->inventory) return false;

        if ($this->inventory->available_stock >= $quantity) {
            $this->inventory->update([
                'reserved_stock' => $this->inventory->reserved_stock + $quantity,
                'available_stock' => $this->inventory->available_stock - $quantity
            ]);
            return true;
        }

        return false;
    }

    public function releaseReservedStock($quantity)
    {
        if (!$this->inventory) return false;

        $newReservedStock = max(0, $this->inventory->reserved_stock - $quantity);
        $this->inventory->update([
            'reserved_stock' => $newReservedStock,
            'available_stock' => $this->inventory->current_stock - $newReservedStock
        ]);

        return true;
    }

    public static function getCategories()
    {
        return [
            'materiales' => 'Materiales',
            'equipos' => 'Equipos',
            'medicamentos' => 'Medicamentos',
            'instrumentos' => 'Instrumentos',
            'consumibles' => 'Consumibles',
            'otros' => 'Otros'
        ];
    }

    public static function getUnitsOfMeasure()
    {
        return [
            'piezas' => 'Piezas',
            'kg' => 'Kilogramos',
            'g' => 'Gramos',
            'litros' => 'Litros',
            'ml' => 'Mililitros',
            'metros' => 'Metros',
            'cajas' => 'Cajas',
            'paquetes' => 'Paquetes'
        ];
    }
}
