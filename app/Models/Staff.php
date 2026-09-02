<?php

namespace App\Models;

use App\Modules\Clinics\Models\Clinic;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Staff extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employee_id',
        'specialty',
        'license_number',
        'license_expiry',
        'university',
        'graduation_year',
        'bio',
        'profile_photo',
        'consultation_fee',
        'experience_years',
        'languages',
        'certifications',
        'is_available',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'license_expiry' => 'date',
            'consultation_fee' => 'decimal:2',
            'languages' => 'array',
            'certifications' => 'array',
            'is_available' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Clínica propietaria del perfil profesional.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Limita consultas de forma explícita a una clínica; no es un global scope
     * mientras las rutas heredadas continúen en transición.
     */
    public function scopeForClinic(Builder $query, ClinicContext $context): Builder
    {
        return $query->where('clinic_id', $context->clinicId);
    }

    /**
     * Relación con horarios
     */
    public function schedules()
    {
        return $this->hasMany(StaffSchedule::class);
    }

    /**
     * Relación con credenciales
     */
    public function credentials()
    {
        return $this->hasMany(StaffCredential::class);
    }

    /**
     * Relación con citas
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Relación con historiales médicos
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Obtener el nombre completo del staff
     */
    public function getFullNameAttribute()
    {
        return $this->user->name ?? 'Sin nombre';
    }

    /**
     * Verificar si la cédula está vencida
     */
    public function isLicenseExpired()
    {
        return $this->license_expiry && $this->license_expiry->isPast();
    }

    /**
     * Obtener horarios activos para un día específico
     */
    public function getScheduleForDay($dayOfWeek)
    {
        return $this->schedules()
            ->where('day_of_week', $dayOfWeek)
            ->where('is_available', true)
            ->first();
    }

    /**
     * Scope para staff activo
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para staff disponible
     */
    public function scopeAvailable($query)
    {
        return $query->where('is_available', true);
    }

    /**
     * Scope para doctores
     */
    public function scopeDoctors($query)
    {
        return $query->whereHas('user.roles', function ($q) {
            $q->where('name', 'doctor');
        });
    }
}
