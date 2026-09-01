<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class InsuranceCoverage extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_id', 'coverage_name', 'description', 'category',
        'coverage_percentage', 'maximum_amount', 'deductible_amount',
        'annual_limit', 'lifetime_limit', 'requires_authorization',
        'authorization_days', 'exclusions', 'notes', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'coverage_percentage' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'deductible_amount' => 'decimal:2',
            'requires_authorization' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
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

    public function scopeRequiringAuthorization($query)
    {
        return $query->where('requires_authorization', true);
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->is_active;
    }

    public function requiresAuthorization()
    {
        return $this->requires_authorization;
    }

    public function calculateCoverageAmount($treatmentCost)
    {
        $coveredAmount = $treatmentCost * ($this->coverage_percentage / 100);
        
        if ($this->maximum_amount && $coveredAmount > $this->maximum_amount) {
            $coveredAmount = $this->maximum_amount;
        }

        return round($coveredAmount, 2);
    }

    public function calculatePatientResponsibility($treatmentCost)
    {
        $coveredAmount = $this->calculateCoverageAmount($treatmentCost);
        $patientResponsibility = $treatmentCost - $coveredAmount;
        
        return max(0, round($patientResponsibility, 2));
    }
}
