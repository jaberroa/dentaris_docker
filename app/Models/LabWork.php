<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabWork extends Model
{
    use HasFactory;

    protected $fillable = [
        'work_number', 'patient_id', 'staff_id', 'dental_lab_id', 'appointment_id',
        'work_date', 'expected_delivery', 'actual_delivery', 'status', 'work_description',
        'specifications', 'notes', 'cost', 'paid_amount', 'is_urgent', 'requires_pickup',
        'tracking_number', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'work_date' => 'date',
            'expected_delivery' => 'date',
            'actual_delivery' => 'date',
            'cost' => 'decimal:2',
            'paid_amount' => 'decimal:2',
            'is_urgent' => 'boolean',
            'requires_pickup' => 'boolean',
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

    public function dentalLab()
    {
        return $this->belongsTo(DentalLab::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function items()
    {
        return $this->hasMany(LabWorkItem::class);
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

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeDelivered($query)
    {
        return $query->where('status', 'delivered');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    public function scopeOverdue($query)
    {
        return $query->where('expected_delivery', '<', now())
                    ->whereIn('status', ['pending', 'sent', 'in_progress']);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByDentalLab($query, $labId)
    {
        return $query->where('dental_lab_id', $labId);
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeRequiresPickup($query)
    {
        return $query->where('requires_pickup', true);
    }

    // Métodos de utilidad
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isSent()
    {
        return $this->status === 'sent';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isDelivered()
    {
        return $this->status === 'delivered';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isUrgent()
    {
        return $this->is_urgent;
    }

    public function isOverdue()
    {
        return $this->expected_delivery < now() && 
               in_array($this->status, ['pending', 'sent', 'in_progress']);
    }

    public function requiresPickup()
    {
        return $this->requires_pickup;
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'sent' => 'Enviado',
            'in_progress' => 'En progreso',
            'completed' => 'Completado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'sent' => 'info',
            'in_progress' => 'primary',
            'completed' => 'success',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedWorkDateAttribute()
    {
        return $this->work_date->format('d/m/Y');
    }

    public function getFormattedExpectedDeliveryAttribute()
    {
        return $this->expected_delivery ? $this->expected_delivery->format('d/m/Y') : 'Por definir';
    }

    public function getFormattedActualDeliveryAttribute()
    {
        return $this->actual_delivery ? $this->actual_delivery->format('d/m/Y') : 'Pendiente';
    }

    public function getFormattedCostAttribute()
    {
        return $this->cost ? '$' . number_format($this->cost, 2) : 'Por cotizar';
    }

    public function getFormattedPaidAmountAttribute()
    {
        return '$' . number_format($this->paid_amount, 2);
    }

    public function getBalanceDue()
    {
        return $this->cost - $this->paid_amount;
    }

    public function getFormattedBalanceDueAttribute()
    {
        return '$' . number_format($this->getBalanceDue(), 2);
    }

    public function getPaymentStatus()
    {
        if ($this->cost == 0) return 'not_quoted';
        if ($this->paid_amount >= $this->cost) return 'paid';
        if ($this->paid_amount > 0) return 'partial';
        return 'unpaid';
    }

    public function getPaymentStatusDisplayAttribute()
    {
        return match($this->getPaymentStatus()) {
            'not_quoted' => 'Sin cotizar',
            'paid' => 'Pagado',
            'partial' => 'Pago parcial',
            'unpaid' => 'Sin pagar',
            default => 'Desconocido'
        };
    }

    public function getPaymentStatusColorAttribute()
    {
        return match($this->getPaymentStatus()) {
            'not_quoted' => 'info',
            'paid' => 'success',
            'partial' => 'warning',
            'unpaid' => 'danger',
            default => 'secondary'
        };
    }

    public function getDaysOverdue()
    {
        if (!$this->isOverdue()) return 0;
        return now()->diffInDays($this->expected_delivery);
    }

    public function getDaysToDelivery()
    {
        if (!$this->expected_delivery) return null;
        return now()->diffInDays($this->expected_delivery, false);
    }

    public function getActualCompletionDays()
    {
        if (!$this->actual_delivery || !$this->work_date) return null;
        return $this->work_date->diffInDays($this->actual_delivery);
    }

    public function getFormattedActualCompletionDaysAttribute()
    {
        $days = $this->getActualCompletionDays();
        return $days ? $days . ' días' : 'N/A';
    }

    public function getTotalItemsAttribute()
    {
        return $this->items()->count();
    }

    public function getCompletedItemsAttribute()
    {
        return $this->items()->where('status', 'completed')->count();
    }

    public function getCompletionPercentageAttribute()
    {
        if ($this->total_items == 0) return 100;
        return round(($this->completed_items / $this->total_items) * 100, 2);
    }

    public function isFullyCompleted()
    {
        return $this->total_items > 0 && $this->completed_items >= $this->total_items;
    }

    public function calculateTotalCost()
    {
        $totalCost = $this->items()->sum('total_cost');
        $this->update(['cost' => $totalCost]);
        return $totalCost;
    }

    public function markAsSent($trackingNumber = null)
    {
        $this->update([
            'status' => 'sent',
            'tracking_number' => $trackingNumber
        ]);
    }

    public function markAsInProgress()
    {
        $this->update(['status' => 'in_progress']);
    }

    public function markAsCompleted()
    {
        $this->update(['status' => 'completed']);
    }

    public function markAsDelivered($actualDeliveryDate = null)
    {
        $this->update([
            'status' => 'delivered',
            'actual_delivery' => $actualDeliveryDate ?: now()
        ]);
    }

    public function markAsCancelled($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason ? $this->notes . "\nCancelado: " . $reason : $this->notes
        ]);
    }

    public function addPayment($amount)
    {
        $newPaidAmount = $this->paid_amount + $amount;
        $this->update(['paid_amount' => $newPaidAmount]);
        return $this;
    }

    public function addItem($prosthesisId, $quantity, $unitCost = null, $specifications = null)
    {
        $prosthesis = Prosthesis::find($prosthesisId);
        $cost = $unitCost ?: $prosthesis->cost;
        
        return $this->items()->create([
            'prosthesis_id' => $prosthesisId,
            'quantity' => $quantity,
            'unit_cost' => $cost,
            'total_cost' => $quantity * $cost,
            'specifications' => $specifications,
        ]);
    }

    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pendiente',
            'sent' => 'Enviado',
            'in_progress' => 'En progreso',
            'completed' => 'Completado',
            'delivered' => 'Entregado',
            'cancelled' => 'Cancelado',
        ];
    }

    public static function generateWorkNumber()
    {
        $lastWork = self::latest()->first();
        $number = $lastWork ? (int) substr($lastWork->work_number, -6) + 1 : 1;
        
        return 'LAB-' . str_pad($number, 6, '0', STR_PAD_LEFT);
    }

    public static function getOverdueWorks()
    {
        return self::overdue()->with(['patient', 'dentalLab', 'staff.user'])->get();
    }

    public static function getUrgentWorks()
    {
        return self::urgent()->with(['patient', 'dentalLab', 'staff.user'])->get();
    }

    public static function getMonthlyWorks($year = null, $month = null)
    {
        $year = $year ?: now()->year;
        $month = $month ?: now()->month;

        return self::whereYear('work_date', $year)
                   ->whereMonth('work_date', $month)
                   ->where('status', '!=', 'cancelled')
                   ->count();
    }

    public static function getAverageCompletionTime($labId = null)
    {
        $query = self::where('status', 'delivered')
                    ->whereNotNull('actual_delivery')
                    ->whereNotNull('work_date');

        if ($labId) $query->where('dental_lab_id', $labId);

        $works = $query->get();

        if ($works->isEmpty()) return null;

        $totalDays = 0;
        foreach ($works as $work) {
            $totalDays += $work->getActualCompletionDays();
        }

        return round($totalDays / $works->count(), 1);
    }

    public static function getTotalValue($startDate = null, $endDate = null)
    {
        $query = self::where('status', '!=', 'cancelled');

        if ($startDate) $query->where('work_date', '>=', $startDate);
        if ($endDate) $query->where('work_date', '<=', $endDate);

        return $query->sum('cost');
    }
}