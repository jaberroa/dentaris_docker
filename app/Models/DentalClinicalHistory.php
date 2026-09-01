<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalClinicalHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_treatment_plan_id',
        'dental_procedure_id',
        'staff_id',
        'action_type',
        'title',
        'old_value',
        'new_value',
        'notes',
        'action_at',
    ];

    protected $casts = [
        'old_value' => 'array',
        'new_value' => 'array',
        'action_at' => 'datetime',
    ];

    public function treatmentPlan()
    {
        return $this->belongsTo(DentalTreatmentPlan::class, 'dental_treatment_plan_id');
    }

    public function procedure()
    {
        return $this->belongsTo(DentalProcedure::class, 'dental_procedure_id');
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function getActionLabel(): string
    {
        return match ($this->action_type) {
            'created' => 'Creación de plan',
            'updated' => 'Actualización de plan',
            'status_changed' => 'Cambio de estado',
            'completed' => 'Procedimiento completado',
            'cancelled' => 'Procedimiento cancelado',
            'note_added' => 'Nota agregada',
            default => 'Actividad',
        };
    }

    public function scopeLatest($query)
    {
        return $query->orderByDesc('action_at');
    }
}
