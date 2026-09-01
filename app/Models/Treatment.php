<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Treatment extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'treatment_code',
        'treatment_name',
        'description',
        'type',
        'status',
        'start_date',
        'end_date',
        'sessions_planned',
        'sessions_completed',
        'cost',
        'materials_used',
        'procedure_notes',
        'complications',
        'follow_up_instructions',
        'requires_follow_up',
        'next_appointment_date',
        'performed_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'next_appointment_date' => 'date',
            'cost' => 'decimal:2',
            'requires_follow_up' => 'boolean',
        ];
    }

    /**
     * Relación con el registro médico
     */
    public function medicalRecord()
    {
        return $this->belongsTo(MedicalRecord::class);
    }

    /**
     * Relación con el doctor que realizó el tratamiento
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'performed_by');
    }

    /**
     * Verificar si el tratamiento está completado
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar si el tratamiento está en progreso
     */
    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    /**
     * Verificar si el tratamiento está planificado
     */
    public function isPlanned()
    {
        return $this->status === 'planned';
    }

    /**
     * Obtener el progreso del tratamiento en porcentaje
     */
    public function getProgressPercentageAttribute()
    {
        if ($this->sessions_planned > 0) {
            return round(($this->sessions_completed / $this->sessions_planned) * 100, 2);
        }
        return 0;
    }

    /**
     * Obtener las sesiones restantes
     */
    public function getRemainingSessionsAttribute()
    {
        return max(0, $this->sessions_planned - $this->sessions_completed);
    }

    /**
     * Scope para tratamientos completados
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Scope para tratamientos en progreso
     */
    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    /**
     * Scope para tratamientos planificados
     */
    public function scopePlanned($query)
    {
        return $query->where('status', 'planned');
    }

    /**
     * Scope para tratamientos por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
