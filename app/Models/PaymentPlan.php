<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_name', 'description', 'type', 'installments', 'frequency',
        'interest_rate', 'down_payment_percentage', 'minimum_amount',
        'maximum_amount', 'grace_period_days', 'requires_credit_check',
        'is_active', 'terms_conditions', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'interest_rate' => 'decimal:2',
            'down_payment_percentage' => 'decimal:2',
            'minimum_amount' => 'decimal:2',
            'maximum_amount' => 'decimal:2',
            'requires_credit_check' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items()
    {
        return $this->hasMany(PaymentPlanItem::class);
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
            $q->where('plan_name', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%");
        });
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->is_active;
    }

    public function requiresCreditCheck()
    {
        return $this->requires_credit_check;
    }

    public function calculateInstallmentAmount($totalAmount)
    {
        $downPayment = $totalAmount * ($this->down_payment_percentage / 100);
        $financedAmount = $totalAmount - $downPayment;
        
        if ($this->installments <= 1) {
            return $financedAmount;
        }

        if ($this->interest_rate > 0) {
            // Cálculo con interés compuesto
            $monthlyRate = $this->interest_rate / 100 / 12;
            $installmentAmount = $financedAmount * ($monthlyRate * pow(1 + $monthlyRate, $this->installments)) / 
                               (pow(1 + $monthlyRate, $this->installments) - 1);
        } else {
            // Sin interés
            $installmentAmount = $financedAmount / $this->installments;
        }

        return round($installmentAmount, 2);
    }
}
