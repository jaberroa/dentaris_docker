<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'invoice_number', 'patient_id', 'staff_id', 'appointment_id', 'treatment_plan_id',
        'invoice_date', 'due_date', 'status', 'subtotal', 'tax_rate', 'tax_amount',
        'discount_amount', 'total_amount', 'paid_amount', 'balance_due', 'notes',
        'payment_terms', 'is_recurring', 'recurring_frequency', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'invoice_date' => 'date',
            'due_date' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'balance_due' => 'decimal:2',
            'is_recurring' => 'boolean',
        ];
    }

    // Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function treatmentPlan()
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function items()
    {
        return $this->hasMany(InvoiceItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    public function accountsReceivable()
    {
        return $this->hasOne(AccountsReceivable::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeOverdue($query)
    {
        return $query->where('due_date', '<', now())
                    ->where('status', '!=', 'paid');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    // Métodos de utilidad
    public function isPaid()
    {
        return $this->status === 'paid';
    }

    public function isOverdue()
    {
        return $this->due_date < now() && $this->status !== 'paid';
    }

    public function isDraft()
    {
        return $this->status === 'draft';
    }

    public function isSent()
    {
        return $this->status === 'sent';
    }

    public function getPaymentPercentageAttribute()
    {
        if ($this->total_amount == 0) return 100;
        return round(($this->paid_amount / $this->total_amount) * 100, 2);
    }

    public function getDaysOverdueAttribute()
    {
        if (!$this->isOverdue()) return 0;
        return now()->diffInDays($this->due_date);
    }

    public function calculateTotals()
    {
        $subtotal = $this->items()->sum('total_price');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $totalAmount = $subtotal + $taxAmount - $this->discount_amount;
        $balanceDue = $totalAmount - $this->paid_amount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'total_amount' => $totalAmount,
            'balance_due' => $balanceDue,
        ]);

        return $this;
    }

    public function markAsPaid()
    {
        $this->update([
            'status' => 'paid',
            'paid_amount' => $this->total_amount,
            'balance_due' => 0,
        ]);
    }

    public function markAsOverdue()
    {
        if ($this->isOverdue()) {
            $this->update(['status' => 'overdue']);
        }
    }

    public function addPayment($amount, $paymentMethod = 'cash')
    {
        $newPaidAmount = $this->paid_amount + $amount;
        $newBalanceDue = $this->total_amount - $newPaidAmount;

        $this->update([
            'paid_amount' => $newPaidAmount,
            'balance_due' => max(0, $newBalanceDue),
            'status' => $newBalanceDue <= 0 ? 'paid' : 'sent'
        ]);

        return $this;
    }
}
