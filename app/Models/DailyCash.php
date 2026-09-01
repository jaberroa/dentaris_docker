<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DailyCash extends Model
{
    use HasFactory;

    protected $fillable = [
        'cash_date', 'opening_balance', 'cash_sales', 'card_sales', 'transfer_sales',
        'total_sales', 'cash_expenses', 'cash_withdrawals', 'expected_balance',
        'actual_balance', 'difference', 'notes', 'status', 'opened_at', 'closed_at',
        'opened_by', 'closed_by'
    ];

    protected function casts(): array
    {
        return [
            'cash_date' => 'date',
            'opening_balance' => 'decimal:2',
            'cash_sales' => 'decimal:2',
            'card_sales' => 'decimal:2',
            'transfer_sales' => 'decimal:2',
            'total_sales' => 'decimal:2',
            'cash_expenses' => 'decimal:2',
            'cash_withdrawals' => 'decimal:2',
            'expected_balance' => 'decimal:2',
            'actual_balance' => 'decimal:2',
            'difference' => 'decimal:2',
            'opened_at' => 'datetime',
            'closed_at' => 'datetime',
        ];
    }

    // Relaciones
    public function opener()
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function closer()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    // Scopes
    public function scopeOpen($query)
    {
        return $query->where('status', 'open');
    }

    public function scopeClosed($query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeByDate($query, $date)
    {
        return $query->where('cash_date', $date);
    }

    public function scopeByUser($query, $userId)
    {
        return $query->where('opened_by', $userId);
    }

    public function scopeWithDifference($query)
    {
        return $query->where('difference', '!=', 0);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('cash_date', now()->month)
                    ->whereYear('cash_date', now()->year);
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('cash_date', [
            now()->startOfWeek(),
            now()->endOfWeek()
        ]);
    }

    // Métodos de utilidad
    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function open($userId)
    {
        $this->update([
            'status' => 'open',
            'opened_at' => now(),
            'opened_by' => $userId,
            'closed_by' => null,
            'closed_at' => null
        ]);
    }

    public function close($userId, $actualBalance = null)
    {
        if ($actualBalance !== null) {
            $this->actual_balance = $actualBalance;
            $this->difference = $this->actual_balance - $this->expected_balance;
        }

        $this->update([
            'status' => 'closed',
            'closed_at' => now(),
            'closed_by' => $userId,
            'actual_balance' => $this->actual_balance,
            'difference' => $this->difference
        ]);
    }

    public function calculateExpectedBalance()
    {
        $expectedBalance = $this->opening_balance + $this->cash_sales - $this->cash_expenses - $this->cash_withdrawals;
        
        $this->update([
            'expected_balance' => $expectedBalance,
            'difference' => $this->actual_balance - $expectedBalance
        ]);

        return $expectedBalance;
    }

    public function addCashSale($amount)
    {
        $this->cash_sales += $amount;
        $this->total_sales += $amount;
        $this->save();
        $this->calculateExpectedBalance();
    }

    public function addCardSale($amount)
    {
        $this->card_sales += $amount;
        $this->total_sales += $amount;
        $this->save();
    }

    public function addTransferSale($amount)
    {
        $this->transfer_sales += $amount;
        $this->total_sales += $amount;
        $this->save();
    }

    public function addCashExpense($amount)
    {
        $this->cash_expenses += $amount;
        $this->save();
        $this->calculateExpectedBalance();
    }

    public function addCashWithdrawal($amount)
    {
        $this->cash_withdrawals += $amount;
        $this->save();
        $this->calculateExpectedBalance();
    }

    public function getFormattedOpeningBalanceAttribute()
    {
        return '$' . number_format($this->opening_balance, 2);
    }

    public function getFormattedTotalSalesAttribute()
    {
        return '$' . number_format($this->total_sales, 2);
    }

    public function getFormattedExpectedBalanceAttribute()
    {
        return '$' . number_format($this->expected_balance, 2);
    }

    public function getFormattedActualBalanceAttribute()
    {
        return '$' . number_format($this->actual_balance, 2);
    }

    public function getFormattedDifferenceAttribute()
    {
        return '$' . number_format($this->difference, 2);
    }

    public function getDifferenceColorAttribute()
    {
        if ($this->difference > 0) return 'success'; // Sobrante
        if ($this->difference < 0) return 'danger';  // Faltante
        return 'primary'; // Exacto
    }

    public function getDifferenceTextAttribute()
    {
        if ($this->difference > 0) return 'Sobrante';
        if ($this->difference < 0) return 'Faltante';
        return 'Exacto';
    }

    public static function getCurrentDayCash()
    {
        return static::where('cash_date', today())->first();
    }

    public static function openNewDay($userId, $openingBalance = 0)
    {
        return static::create([
            'cash_date' => today(),
            'opening_balance' => $openingBalance,
            'status' => 'open',
            'opened_at' => now(),
            'opened_by' => $userId
        ]);
    }
}
