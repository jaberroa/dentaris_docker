<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class QuoteItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_id', 'cdt_catalog_id', 'sequence_order', 'description', 'quantity',
        'unit_price', 'total_price', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'sequence_order' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
        ];
    }

    // Relaciones
    public function quote()
    {
        return $this->belongsTo(Quote::class);
    }

    public function cdtCatalog()
    {
        return $this->belongsTo(CdtCatalog::class);
    }

    // Scopes
    public function scopeByQuote($query, $quoteId)
    {
        return $query->where('quote_id', $quoteId);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }

    // Métodos de utilidad
    public function getFormattedUnitPriceAttribute()
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function calculateTotalPrice()
    {
        $totalPrice = $this->quantity * $this->unit_price;
        $this->update(['total_price' => $totalPrice]);
        return $totalPrice;
    }

    public function getSequenceDisplayAttribute()
    {
        return "Item {$this->sequence_order}";
    }

    public function getFullDescriptionAttribute()
    {
        $description = $this->description;
        
        if ($this->cdtCatalog) {
            $description = $this->cdtCatalog->name . ' - ' . $description;
        }
        
        return $description;
    }

    public static function getTotalValue($quoteId = null)
    {
        $query = self::query();
        
        if ($quoteId) {
            $query->where('quote_id', $quoteId);
        }
        
        return $query->sum('total_price');
    }

    public static function getItemsCount($quoteId = null)
    {
        $query = self::query();
        
        if ($quoteId) {
            $query->where('quote_id', $quoteId);
        }
        
        return $query->count();
    }
}
