<?php

namespace App\Models;

use App\Modules\Clinics\Data\ClinicContext;
use App\Modules\Clinics\Models\Clinic;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryLocation extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'type', 'is_active', 'notes'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }

    public function clinic()
    {
        return $this->belongsTo(Clinic::class);
    }

    public function scopeForClinic(Builder $query, ClinicContext $context): Builder
    {
        return $query->where($query->qualifyColumn('clinic_id'), $context->clinicId);
    }
}
