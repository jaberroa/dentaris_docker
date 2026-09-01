<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalProcedure extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_treatment_plan_id',
        'dental_odontogram_id',
        'dental_periodontogram_id',
        'procedure_name',
        'procedure_code',
        'procedure_type',
        'tooth_number',
        'surface',
        'periodontal_zone',
        'status',
        'priority',
        'estimated_sessions',
        'completed_sessions',
        'estimated_time_minutes',
        'actual_time_minutes',
        'estimated_cost',
        'actual_cost',
        'scheduled_date',
        'started_date',
        'completed_date',
        'responsible_staff_id',
        'notes',
    ];

    protected $casts = [
        'estimated_time_minutes' => 'integer',
        'actual_time_minutes' => 'integer',
        'estimated_cost' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'scheduled_date' => 'date',
        'started_date' => 'date',
        'completed_date' => 'date',
    ];

    public const STATUSES = [
        'pending' => 'Pendiente',
        'in_progress' => 'En curso',
        'completed' => 'Completado',
        'cancelled' => 'Cancelado',
    ];

    public function treatmentPlan()
    {
        return $this->belongsTo(DentalTreatmentPlan::class);
    }

    public function odontogram()
    {
        return $this->belongsTo(DentalOdontogram::class);
    }

    public function periodontogram()
    {
        return $this->belongsTo(DentalPeriodontogram::class);
    }

    public function responsible()
    {
        return $this->belongsTo(Staff::class, 'responsible_staff_id');
    }

    public function statusLabel(): string
    {
        return self::STATUSES[$this->status] ?? 'Desconocido';
    }

    public function statusBadgeColor(): string
    {
        return match ($this->status) {
            'pending' => 'secondary',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function markAsCompleted(): void
    {
        $this->update([
            'status' => 'completed',
            'completed_date' => now(),
            'completed_sessions' => $this->estimated_sessions,
        ]);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }
}
