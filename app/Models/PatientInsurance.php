<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientInsurance extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'insurance_id', 'policy_number', 'group_number',
        'member_id', 'effective_date', 'expiry_date', 'primary_holder_name',
        'relationship', 'coverage_percentage', 'deductible_amount',
        'annual_limit', 'lifetime_limit', 'is_primary', 'is_active',
        'notes', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'effective_date' => 'date',
            'expiry_date' => 'date',
            'coverage_percentage' => 'decimal:2',
            'deductible_amount' => 'decimal:2',
            'annual_limit' => 'decimal:2',
            'lifetime_limit' => 'decimal:2',
            'is_primary' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function insurance()
    {
        return $this->belongsTo(Insurance::class);
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

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }

    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->whereBetween('expiry_date', [now(), now()->addDays($days)]);
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->is_active;
    }

    public function isPrimary()
    {
        return $this->is_primary;
    }

    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date < now();
    }

    public function isExpiringSoon($days = 30)
    {
        return $this->expiry_date && 
               $this->expiry_date > now() && 
               $this->expiry_date <= now()->addDays($days);
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->expiry_date) {
            return null;
        }

        return now()->diffInDays($this->expiry_date, false);
    }

    public function getFullPolicyInfoAttribute()
    {
        $info = "Póliza: {$this->policy_number}";
        if ($this->group_number) {
            $info .= " | Grupo: {$this->group_number}";
        }
        $info .= " | Miembro: {$this->member_id}";
        return $info;
    }
}
