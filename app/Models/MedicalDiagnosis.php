<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalDiagnosis extends Model
{
    use HasFactory;

    protected $fillable = [
        'medical_record_id',
        'diagnosis_code',
        'diagnosis_name',
        'description',
        'type',
        'status',
        'diagnosis_date',
        'resolved_date',
        'treatment_notes',
        'follow_up_notes',
        'is_confirmed',
        'diagnosed_by',
    ];

    protected function casts(): array
    {
        return [
            'diagnosis_date' => 'date',
            'resolved_date' => 'date',
            'is_confirmed' => 'boolean',
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
     * Relación con el doctor que diagnosticó
     */
    public function doctor()
    {
        return $this->belongsTo(User::class, 'diagnosed_by');
    }

    /**
     * Verificar si el diagnóstico está resuelto
     */
    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    /**
     * Verificar si el diagnóstico está activo
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Scope para diagnósticos activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para diagnósticos resueltos
     */
    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    /**
     * Scope para diagnósticos confirmados
     */
    public function scopeConfirmed($query)
    {
        return $query->where('is_confirmed', true);
    }
}
