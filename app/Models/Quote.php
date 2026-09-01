<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Quote extends Model
{
    use HasFactory;

    protected $fillable = [
        'quote_number', 'patient_id', 'staff_id', 'appointment_id', 'treatment_plan_id',
        'quote_date', 'valid_until', 'status', 'subtotal', 'tax_rate', 'tax_amount',
        'discount_percentage', 'discount_amount', 'total_amount', 'notes', 'terms_conditions',
        'is_approved', 'approved_date', 'approved_by', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'quote_date' => 'date',
            'valid_until' => 'date',
            'subtotal' => 'decimal:2',
            'tax_rate' => 'decimal:2',
            'tax_amount' => 'decimal:2',
            'discount_percentage' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'total_amount' => 'decimal:2',
            'is_approved' => 'boolean',
            'approved_date' => 'date',
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
        return $this->hasMany(QuoteItem::class);
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeExpired($query)
    {
        return $query->where('valid_until', '<', now())
                    ->where('status', 'pending');
    }

    public function scopeValid($query)
    {
        return $query->where('valid_until', '>=', now())
                    ->where('status', 'pending');
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Métodos de utilidad
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isExpired()
    {
        return $this->valid_until < now() && $this->status === 'pending';
    }

    public function isValid()
    {
        return $this->valid_until >= now() && $this->status === 'pending';
    }

    public function isApprovedByPatient()
    {
        return $this->is_approved;
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        if ($this->isExpired()) return 'danger';
        
        return match($this->status) {
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedQuoteDateAttribute()
    {
        return $this->quote_date->format('d/m/Y');
    }

    public function getFormattedValidUntilAttribute()
    {
        return $this->valid_until->format('d/m/Y');
    }

    public function getFormattedApprovedDateAttribute()
    {
        return $this->approved_date ? $this->approved_date->format('d/m/Y') : 'N/A';
    }

    public function getFormattedSubtotalAttribute()
    {
        return '$' . number_format($this->subtotal, 2);
    }

    public function getFormattedTaxAmountAttribute()
    {
        return '$' . number_format($this->tax_amount, 2);
    }

    public function getFormattedDiscountAmountAttribute()
    {
        return '$' . number_format($this->discount_amount, 2);
    }

    public function getFormattedTotalAmountAttribute()
    {
        return '$' . number_format($this->total_amount, 2);
    }

    public function getDaysToExpiry()
    {
        return now()->diffInDays($this->valid_until, false);
    }

    public function getDaysToExpiryDisplayAttribute()
    {
        $days = $this->getDaysToExpiry();
        
        if ($days < 0) return 'Expirada';
        if ($days == 0) return 'Expira hoy';
        if ($days == 1) return 'Expira mañana';
        return "Expira en {$days} días";
    }

    public function getExpiryStatus()
    {
        if ($this->isExpired()) return 'expired';
        if ($this->getDaysToExpiry() <= 3) return 'expiring_soon';
        return 'valid';
    }

    public function getExpiryStatusColorAttribute()
    {
        return match($this->getExpiryStatus()) {
            'expired' => 'danger',
            'expiring_soon' => 'warning',
            'valid' => 'success',
            default => 'secondary'
        };
    }

    public function getItemsCountAttribute()
    {
        return $this->items()->count();
    }

    public function calculateTotals()
    {
        $subtotal = $this->items()->sum('total_price');
        $taxAmount = $subtotal * ($this->tax_rate / 100);
        $discountAmount = $subtotal * ($this->discount_percentage / 100);
        $totalAmount = $subtotal + $taxAmount - $discountAmount;

        $this->update([
            'subtotal' => $subtotal,
            'tax_amount' => $taxAmount,
            'discount_amount' => $discountAmount,
            'total_amount' => $totalAmount,
        ]);

        return $this;
    }

    public function approve($userId = null)
    {
        $this->update([
            'status' => 'approved',
            'is_approved' => true,
            'approved_date' => now(),
            'approved_by' => $userId
        ]);
        return $this;
    }

    public function reject($reason = null)
    {
        $this->update([
            'status' => 'rejected',
            'notes' => $reason ? $this->notes . "\nRechazada: " . $reason : $this->notes
        ]);
        return $this;
    }

    public function markAsApprovedByPatient()
    {
        $this->update(['is_approved' => true]);
        return $this;
    }

    public function addItem($cdtCatalogId, $quantity, $unitPrice, $description = null)
    {
        return $this->items()->create([
            'cdt_catalog_id' => $cdtCatalogId,
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total_price' => $quantity * $unitPrice,
            'description' => $description,
        ]);
    }

    public function updateDiscount($percentage)
    {
        $this->update(['discount_percentage' => $percentage]);
        $this->calculateTotals();
        return $this;
    }

    public function updateTaxRate($rate)
    {
        $this->update(['tax_rate' => $rate]);
        $this->calculateTotals();
        return $this;
    }

    public function getPaymentTerms()
    {
        return $this->treatmentPlan ? $this->treatmentPlan->payment_plan : null;
    }

    public function getEstimatedDuration()
    {
        return $this->treatmentPlan ? $this->treatmentPlan->estimated_duration : null;
    }

    public function getWarrantyInfo()
    {
        return $this->treatmentPlan ? $this->treatmentPlan->warranty_period : null;
    }

    public function canBeModified()
    {
        return $this->isPending() && !$this->isExpired();
    }

    public function canBeApproved()
    {
        return $this->isPending() && !$this->isExpired();
    }

    public function getValidityPeriod()
    {
        return $this->quote_date->diffInDays($this->valid_until);
    }

    public function getFormattedValidityPeriodAttribute()
    {
        return $this->getValidityPeriod() . ' días';
    }

    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pendiente',
            'approved' => 'Aprobada',
            'rejected' => 'Rechazada',
        ];
    }

    public static function generateQuoteNumber()
    {
        $lastQuote = self::latest()->first();
        $number = $lastQuote ? (int) substr($lastQuote->quote_number, -6) + 1 : 1;
        
        return 'COT-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public static function getExpiredQuotes()
    {
        return self::expired()->with(['patient', 'staff.user'])->get();
    }

    public static function getExpiringSoonQuotes($days = 3)
    {
        return self::where('valid_until', '<=', now()->addDays($days))
                    ->where('valid_until', '>', now())
                    ->where('status', 'pending')
                    ->with(['patient', 'staff.user'])
                    ->get();
    }

    public static function getMonthlyQuotes($year = null, $month = null)
    {
        $year = $year ?: now()->year;
        $month = $month ?: now()->month;

        return self::whereYear('quote_date', $year)
                   ->whereMonth('quote_date', $month)
                   ->count();
    }

    public static function getApprovalRate($startDate = null, $endDate = null)
    {
        $query = self::whereNotNull('approved_date');

        if ($startDate) $query->where('approved_date', '>=', $startDate);
        if ($endDate) $query->where('approved_date', '<=', $endDate);

        $approved = $query->count();
        $total = self::whereNotNull('quote_date');

        if ($startDate) $total->where('quote_date', '>=', $startDate);
        if ($endDate) $total->where('quote_date', '<=', $endDate);

        $total = $total->count();

        return $total > 0 ? round(($approved / $total) * 100, 2) : 0;
    }

    public static function getTotalValue($startDate = null, $endDate = null)
    {
        $query = self::where('status', 'approved');

        if ($startDate) $query->where('quote_date', '>=', $startDate);
        if ($endDate) $query->where('quote_date', '<=', $endDate);

        return $query->sum('total_amount');
    }
}
