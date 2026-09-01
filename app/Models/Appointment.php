<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Carbon\Carbon;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_code',
        'patient_id',
        'staff_id',
        'appointment_status_id',
        'appointment_date',
        'start_time',
        'end_time',
        'duration',
        'type',
        'reason',
        'notes',
        'treatment_plan',
        'estimated_cost',
        'is_urgent',
        'is_follow_up',
        'is_recurring',
        'reminder_sent',
        'parent_appointment_id',
        'confirmed_at',
        'cancelled_at',
        'cancellation_reason',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'estimated_cost' => 'decimal:2',
            'is_urgent' => 'boolean',
            'is_follow_up' => 'boolean',
            'is_recurring' => 'boolean',
            'reminder_sent' => 'boolean',
            'confirmed_at' => 'datetime',
            'cancelled_at' => 'datetime',
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
     * Relación con el staff/doctor
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Relación con el estado de la cita
     */
    public function status()
    {
        return $this->belongsTo(AppointmentStatus::class, 'appointment_status_id');
    }

    /**
     * Relación con recordatorios
     */
    public function reminders()
    {
        return $this->hasMany(AppointmentReminder::class);
    }

    /**
     * Relación con el usuario que creó la cita
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con cita padre (para seguimientos)
     */
    public function parentAppointment()
    {
        return $this->belongsTo(Appointment::class, 'parent_appointment_id');
    }

    /**
     * Relación con citas hijas (seguimientos)
     */
    public function followUpAppointments()
    {
        return $this->hasMany(Appointment::class, 'parent_appointment_id');
    }

    /**
     * Obtener fecha y hora de inicio combinadas
     */
    public function getStartDateTimeAttribute()
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->start_time->format('H:i:s'));
    }

    /**
     * Obtener fecha y hora de fin combinadas
     */
    public function getEndDateTimeAttribute()
    {
        return Carbon::parse($this->appointment_date->format('Y-m-d') . ' ' . $this->end_time->format('H:i:s'));
    }

    /**
     * Verificar si la cita está confirmada
     */
    public function isConfirmed()
    {
        return $this->confirmed_at !== null;
    }

    /**
     * Verificar si la cita está cancelada
     */
    public function isCancelled()
    {
        return $this->cancelled_at !== null;
    }

    /**
     * Verificar si la cita es hoy
     */
    public function isToday()
    {
        return $this->appointment_date->isToday();
    }

    /**
     * Verificar si la cita es en el futuro
     */
    public function isFuture()
    {
        return $this->start_date_time->isFuture();
    }

    /**
     * Verificar si la cita es en el pasado
     */
    public function isPast()
    {
        return $this->start_date_time->isPast();
    }

    /**
     * Scope para citas de hoy
     */
    public function scopeToday($query)
    {
        return $query->whereDate('appointment_date', today());
    }

    /**
     * Scope para citas futuras
     */
    public function scopeFuture($query)
    {
        return $query->where('appointment_date', '>=', today())
                    ->orWhere(function ($q) {
                        $q->whereDate('appointment_date', today())
                          ->where('start_time', '>', now()->format('H:i:s'));
                    });
    }

    /**
     * Scope para citas por doctor
     */
    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    /**
     * Scope para citas por paciente
     */
    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    /**
     * Scope para citas por estado
     */
    public function scopeByStatus($query, $statusName)
    {
        return $query->whereHas('status', function ($q) use ($statusName) {
            $q->where('name', $statusName);
        });
    }
}
