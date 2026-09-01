<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AppointmentReminder extends Model
{
    use HasFactory;

    protected $fillable = [
        'appointment_id',
        'type',
        'minutes_before',
        'message',
        'sent_at',
        'status',
        'error_message',
        'retry_count',
        'next_retry_at',
    ];

    protected function casts(): array
    {
        return [
            'sent_at' => 'datetime',
            'next_retry_at' => 'datetime',
        ];
    }

    /**
     * Relación con la cita
     */
    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    /**
     * Verificar si el recordatorio está pendiente
     */
    public function isPending()
    {
        return $this->status === 'pending';
    }

    /**
     * Verificar si el recordatorio fue enviado
     */
    public function isSent()
    {
        return $this->status === 'sent';
    }

    /**
     * Verificar si el recordatorio falló
     */
    public function isFailed()
    {
        return $this->status === 'failed';
    }

    /**
     * Verificar si el recordatorio está cancelado
     */
    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    /**
     * Obtener la fecha y hora programada para el recordatorio
     */
    public function getScheduledDateTimeAttribute()
    {
        return $this->appointment->start_date_time->subMinutes($this->minutes_before);
    }

    /**
     * Scope para recordatorios pendientes
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope para recordatorios enviados
     */
    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    /**
     * Scope para recordatorios fallidos
     */
    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    /**
     * Scope para recordatorios por tipo
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }
}
