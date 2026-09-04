<?php

namespace App\Models;

use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryMovement extends Model
{
    use HasFactory;

    protected $fillable = [
        'inventory_id',
        'product_id',
        'user_id',
        'type',
        'quantity',
        'stock_before',
        'stock_after',
        'source_location',
        'destination_location',
        'reason',
        'reference_type',
        'reference_id',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'stock_before' => 'integer',
            'stock_after' => 'integer',
            'reference_id' => 'integer',
            'metadata' => 'array',
        ];
    }

    public function inventory()
    {
        return $this->belongsTo(Inventory::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeForClinic(Builder $query, ClinicContext $context): Builder
    {
        return $query->where($query->qualifyColumn('clinic_id'), $context->clinicId);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reference()
    {
        return $this->morphTo();
    }
}
