<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class MedicalRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'appointment_id',
        'staff_id',
        'record_type',
        'chief_complaint',
        'present_illness',
        'medical_history',
        'dental_history',
        'family_history',
        'social_history',
        'clinical_examination',
        'vital_signs',
        'oral_examination',
        'diagnostic_impression',
        'treatment_plan',
        'recommendations',
        'notes',
        'is_confidential',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'is_confidential' => 'boolean',
        ];
    }

    /**
     * Relación con el paciente
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    /**
     * Relación con la cita
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Relación con el staff/doctor
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Relación con diagnósticos
     */
    public function diagnoses()
    {
        return $this->hasMany(MedicalDiagnosis::class);
    }

    /**
     * Relación con imágenes
     */
    public function images()
    {
        return $this->hasMany(MedicalImage::class);
    }

    /**
     * Relación con tratamientos
     */
    public function treatments()
    {
        return $this->hasMany(Treatment::class);
    }

    /**
     * Relación con el usuario que creó el registro
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope para registros confidenciales
     */
    public function scopeConfidential($query)
    {
        return $query->where('is_confidential', true);
    }

    /**
     * Scope para registros por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('record_type', $type);
    }

    /**
     * Scope para registros por doctor
     */
    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }
}
