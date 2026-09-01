<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PaymentPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payment_plan_id', 'invoice_id', 'installment_number', 'amount',
        'due_date', 'paid_date', 'status', 'interest_amount', 'late_fee', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'due_date' => 'date',
            'paid_date' => 'date',
            'interest_amount' => 'decimal:2',
            'late_fee' => 'decimal:2',
        ];
    }

    // Relaciones
    public function paymentPlan()
    {
        return $this->belongsTo(PaymentPlan::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopeDueToday($query)
    {
        return $query->where('due_date', today());
    }

    public function scopeDueThisWeek($query)
    {
        return $query->whereBetween('due_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    // Métodos de utilidad
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isOverdue()
    {
        return $this->status === 'overdue';
    }

    public function isDueToday()
    {
        return $this->due_date->isToday();
    }

    public function isOverdueDate()
    {
        return $this->due_date < today() && $this->status !== 'paid';
    }

    public function getDaysOverdueAttribute()
    {
        if ($this->isOverdueDate()) {
            return today()->diffInDays($this->due_date);
        }
        return 0;
    }

    public function getTotalAmountAttribute()
    {
        return $this->amount + $this->interest_amount + $this->late_fee;
    }

    public function markAsPaid($paidDate = null)
    {
        $this->update([
            'status' => 'paid',
            'paid_date' => $paidDate ?? now(),
            'interest_amount' => 0,
            'late_fee' => 0
        ]);
    }

    public function markAsOverdue()
    {
        if ($this->isOverdueDate() && $this->status === 'pending') {
            $this->update(['status' => 'overdue']);
        }
    }
}
