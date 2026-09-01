<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalTreatmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'staff_id',
        'plan_code',
        'plan_name',
        'patient_type',
        'work_schema',
        'status',
        'total_procedures',
        'completed_procedures',
        'progress_percentage',
        'priority',
        'is_urgent',
        'start_date',
        'end_date',
        'estimated_total_cost',
        'actual_total_cost',
        'diagnosis_summary',
        'notes',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_urgent' => 'boolean',
        'progress_percentage' => 'decimal:2',
        'estimated_total_cost' => 'decimal:2',
        'actual_total_cost' => 'decimal:2',
    ];

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater()
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function odontograms()
    {
        return $this->hasMany(DentalOdontogram::class);
    }

    public function periodontograms()
    {
        return $this->hasMany(DentalPeriodontogram::class);
    }

    public function procedures()
    {
        return $this->hasMany(DentalProcedure::class);
    }

    public function clinicalHistories()
    {
        return $this->hasMany(DentalClinicalHistory::class);
    }

    public function calculateProgress(): float
    {
        if ($this->total_procedures === 0) {
            return 0;
        }

        return round(($this->completed_procedures / max($this->total_procedures, 1)) * 100, 2);
    }

    public function getProgressColor(): string
    {
        $progress = $this->progress_percentage;

        return match (true) {
            $progress <= 25 => 'danger',
            $progress <= 75 => 'warning',
            default => 'success',
        };
    }

    public function getProgressLabel(): string
    {
        $progress = $this->progress_percentage;

        return match (true) {
            $progress === 0 => 'Plan sin iniciar',
            $progress <= 25 => 'Avance crítico',
            $progress <= 75 => 'Avance moderado',
            $progress < 100 => 'Avance alto',
            default => 'Plan completado',
        };
    }

    public function refreshProgress(): void
    {
        $this->total_procedures = $this->procedures()->count();
        $this->completed_procedures = $this->procedures()->where('status', 'completed')->count();
        $this->progress_percentage = $this->calculateProgress();
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeForPatient($query, int $patientId)
    {
        return $query->where('patient_id', $patientId);
    }
}
