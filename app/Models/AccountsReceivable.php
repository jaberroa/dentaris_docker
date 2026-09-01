<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AccountsReceivable extends Model
{
    use HasFactory;

    protected $table = 'accounts_receivable';

    protected $fillable = [
        'patient_id', 'invoice_id', 'original_amount', 'paid_amount', 'balance_due',
        'due_date', 'status', 'days_overdue', 'interest_rate', 'interest_amount',
        'notes', 'last_payment_date', 'last_payment_amount', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'original_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'due_date' => 'date',
            'interest_rate' => 'decimal:2',
            'interest_amount' => 'decimal:2',
            'last_payment_date' => 'date',
            'last_payment_amount' => 'decimal:2',
        ];
    }

    // Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeCurrent($query)
    {
        return $query->where('status', 'current');
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeWrittenOff($query)
    {
        return $query->where('status', 'written_off');
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeOverdueAccounts($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'paid')
                    ->where('balance_due', '>', 0);
    }

    public function scopeWithInterest($query)
    {
        return $query->where('interest_rate', '>', 0);
    }

    // Métodos de utilidad
    public function isCurrent()
    {
        return $this->status === 'current';
    }

    public function isOverdue()
    {
        return $this->status === 'overdue';
    }

    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isWrittenOff()
    {
        return $this->status === 'written_off';
    }

    public function markAsOverdue()
    {
        if ($this->due_date < now() && $this->balance_due > 0) {
            $this->update([
                'status' => 'overdue',
                'days_overdue' => now()->diffInDays($this->due_date)
            ]);
        }
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_amount' => $this->original_amount,
            'balance_due' => 0,
            'days_overdue' => 0
        ]);
    }

    public function markAsWrittenOff($reason = null)
    {
        $this->update([
            'status' => 'written_off',
            'notes' => $reason ? $this->notes . "\nCancelado: " . $reason : $this->notes
        ]);
    }

    public function addPayment($amount, $paymentDate = null)
    {
        $paymentDate = $paymentDate ?? now();
        $newPaidAmount = $this->paid_amount + $amount;
        $newBalanceDue = $this->original_amount - $newPaidAmount;

        $this->update([
            'paid_amount' => $newPaidAmount,
            'balance_due' => max(0, $newBalanceDue),
            'last_payment_date' => $paymentDate,
            'last_payment_amount' => $amount,
            'status' => $newBalanceDue <= 0 ? 'paid' : ($this->isOverdue() ? 'overdue' : 'current')
        ]);

        return $this;
    }

    public function calculateInterest()
    {
        if ($this->interest_rate <= 0 || $this->isPaid()) {
            return 0;
        }

        $daysOverdue = $this->isOverdue() ? $this->days_overdue : 0;
        $dailyRate = $this->interest_rate / 365;
        $interestAmount = $this->balance_due * $dailyRate * $daysOverdue;

        $this->update(['interest_amount' => $interestAmount]);

        return $interestAmount;
    }

    public function getFormattedOriginalAmountAttribute()
    {
        return '$' . number_format($this->original_amount, 2);
    }

    public function getFormattedPaidAmountAttribute()
    {
        return '$' . number_format($this->paid_amount, 2);
    }

    public function getFormattedBalanceDueAttribute()
    {
        return '$' . number_format($this->balance_due, 2);
    }

    public function getFormattedInterestAmountAttribute()
    {
        return '$' . number_format($this->interest_amount, 2);
    }

    public function getFormattedDueDateAttribute()
    {
        return $this->due_date->format('d/m/Y');
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'current' => 'Al día',
            'overdue' => 'Vencido',
            'paid' => 'Pagado',
            'written_off' => 'Cancelado',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'current' => 'success',
            'overdue' => 'danger',
            'paid' => 'primary',
            'written_off' => 'secondary',
            default => 'light'
        };
    }

    public function getPaymentPercentageAttribute()
    {
        if ($this->original_amount == 0) return 100;
        return round(($this->paid_amount / $this->original_amount) * 100, 2);
    }

    public function getTotalDueAttribute()
    {
        return $this->balance_due + $this->interest_amount;
    }

    public function getFormattedTotalDueAttribute()
    {
        return '$' . number_format($this->total_due, 2);
    }

    public function updateDaysOverdue()
    {
        if ($this->isOverdue()) {
            $this->update(['days_overdue' => now()->diffInDays($this->due_date)]);
        }
    }

    public static function updateOverdueAccounts()
    {
        $overdueAccounts = static::overdueAccounts()->get();
        
        foreach ($overdueAccounts as $account) {
            $account->updateDaysOverdue();
            $account->calculateInterest();
        }

        return $overdueAccounts->count();
    }
}
