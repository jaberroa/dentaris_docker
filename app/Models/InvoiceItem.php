<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InvoiceItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_id', 'cdt_catalog_id', 'sequence_order', 'item_name', 'description',
        'quantity', 'unit_price', 'total_price', 'tax_rate', 'tax_amount', 'notes', 'is_taxable'
    ];

    protected function casts(): array
    {
        return [
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'is_taxable' => 'boolean',
        ];
    }

    // Relaciones
    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function cdtCatalog()
    {
        return $this->belongsTo(CdtCatalog::class);
    }

    // Scopes
    public function scopeByInvoice($query, $invoiceId)
    {
        return $query->where('invoice_id', $invoiceId);
    }

    public function scopeTaxable($query)
    {
        return $query->where('is_taxable', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }

    // Métodos de utilidad
    public function isTaxable()
    {
        return $this->is_taxable;
    }

    public function calculateTotalPrice()
    {
        $totalPrice = $this->quantity * $this->unit_price;
        $taxAmount = $this->is_taxable ? $totalPrice * ($this->tax_rate / 100) : 0;
        
        return $totalPrice + $taxAmount;
    }

    public function updateTotalPrice()
    {
        $this->total_price = $this->calculateTotalPrice();
        $this->tax_amount = $this->is_taxable ? $this->quantity * $this->unit_price * ($this->tax_rate / 100) : 0;
        $this->save();
    }

    public function getFormattedUnitPriceAttribute()
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return '$' . number_format($this->tax_amount, 2);
    }
}
