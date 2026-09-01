<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Supplier extends Model
{
    use HasFactory;

    protected $fillable = [
        'supplier_code', 'company_name', 'contact_name', 'email', 'phone', 'address',
        'city', 'state', 'postal_code', 'country', 'tax_id', 'notes', 'payment_terms',
        'credit_limit', 'is_active', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'credit_limit' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function products()
    {
        return $this->hasMany(Product::class, 'primary_supplier_id');
    }

    public function purchases()
    {
        return $this->hasMany(Purchase::class);
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

    public function scopeByPaymentTerms($query, $terms)
    {
        return $query->where('payment_terms', $terms);
    }

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeWithCreditLimit($query)
    {
        return $query->whereNotNull('credit_limit')->where('credit_limit', '>', 0);
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->is_active;
    }

    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->city) $address .= ', ' . $this->city;
        if ($this->state) $address .= ', ' . $this->state;
        if ($this->postal_code) $address .= ' ' . $this->postal_code;
        if ($this->country) $address .= ', ' . $this->country;
        
        return $address;
    }

    public function getPaymentTermsDisplayAttribute()
    {
        return match($this->payment_terms) {
            'net_15' => 'Neto 15 días',
            'net_30' => 'Neto 30 días',
            'net_45' => 'Neto 45 días',
            'net_60' => 'Neto 60 días',
            'cash_on_delivery' => 'Contra entrega',
            default => $this->payment_terms
        };
    }

    public function getFormattedCreditLimitAttribute()
    {
        return $this->credit_limit ? '$' . number_format($this->credit_limit, 2) : 'Sin límite';
    }

    public function getTotalPurchasesAttribute()
    {
        return $this->purchases()->sum('total_amount');
    }

    public function getFormattedTotalPurchasesAttribute()
    {
        return '$' . number_format($this->total_purchases, 2);
    }

    public function getActiveProductsCountAttribute()
    {
        return $this->products()->active()->count();
    }

    public function getLastPurchaseDateAttribute()
    {
        $lastPurchase = $this->purchases()->latest()->first();
        return $lastPurchase ? $lastPurchase->purchase_date : null;
    }

    public function getFormattedLastPurchaseDateAttribute()
    {
        return $this->last_purchase_date ? $this->last_purchase_date->format('d/m/Y') : 'Nunca';
    }

    public function hasOutstandingPayments()
    {
        // TODO: Implementar lógica de pagos pendientes
        return false;
    }

    public function getOutstandingAmount()
    {
        // TODO: Implementar cálculo de monto pendiente
        return 0;
    }

    public function getFormattedOutstandingAmountAttribute()
    {
        return '$' . number_format($this->getOutstandingAmount(), 2);
    }

    public static function getPaymentTermsOptions()
    {
        return [
            'net_15' => 'Neto 15 días',
            'net_30' => 'Neto 30 días',
            'net_45' => 'Neto 45 días',
            'net_60' => 'Neto 60 días',
            'cash_on_delivery' => 'Contra entrega',
        ];
    }

    public function updateStats()
    {
        // Actualizar estadísticas del proveedor
        $this->touch();
    }
}
