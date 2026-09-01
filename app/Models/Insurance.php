<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Insurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'insurance_code', 'company_name', 'contact_name', 'email', 'phone',
        'address', 'city', 'state', 'postal_code', 'country', 'tax_id',
        'description', 'type', 'coverage_percentage', 'deductible_amount',
        'coverage_details', 'exclusions', 'requires_authorization',
        'authorization_days', 'notes', 'is_active', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'coverage_percentage' => 'decimal:2',
            'deductible_amount' => 'decimal:2',
            'requires_authorization' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function coverages()
    {
        return $this->hasMany(InsuranceCoverage::class);
    }

    public function patientInsurances()
    {
        return $this->hasMany(PatientInsurance::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('company_name', 'like', "%{$search}%")
              ->orWhere('insurance_code', 'like', "%{$search}%")
              ->orWhere('contact_name', 'like', "%{$search}%");
        });
    }

    // Métodos de utilidad
    public function getFullAddressAttribute()
    {
        $parts = array_filter([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ]);
        return implode(', ', $parts);
    }

    public function isActive()
    {
        return $this->is_active;
    }

    public function requiresAuthorization()
    {
        return $this->requires_authorization;
    }
}
