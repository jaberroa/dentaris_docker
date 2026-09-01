<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class CdtCatalog extends Model
{
    use HasFactory;

    protected $table = 'cdt_catalog';

    protected $fillable = [
        'cdt_code',
        'category',
        'subcategory',
        'procedure_name',
        'description',
        'clinical_notes',
        'base_price',
        'estimated_duration',
        'difficulty_level',
        'required_materials',
        'contraindications',
        'requires_anesthesia',
        'is_surgical',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
            'required_materials' => 'array',
            'contraindications' => 'array',
            'requires_anesthesia' => 'boolean',
            'is_surgical' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación con items de planes de tratamiento
     */
    public function treatmentPlanItems()
    {
        return $this->hasMany(TreatmentPlanItem::class);
    }

    /**
     * Relación con items de presupuestos
     */
    public function quoteItems()
    {
        return $this->hasMany(QuoteItem::class);
    }

    /**
     * Scope para procedimientos activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para procedimientos por categoría
     */
    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    /**
     * Scope para procedimientos quirúrgicos
     */
    public function scopeSurgical($query)
    {
        return $query->where('is_surgical', true);
    }

    /**
     * Scope para procedimientos que requieren anestesia
     */
    public function scopeRequiringAnesthesia($query)
    {
        return $query->where('requires_anesthesia', true);
    }

    /**
     * Scope para buscar por código o nombre
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('cdt_code', 'like', "%{$search}%")
              ->orWhere('procedure_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }
}
