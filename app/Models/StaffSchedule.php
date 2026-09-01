<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffSchedule extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'day_of_week',
        'start_time',
        'end_time',
        'break_duration',
        'break_start',
        'appointment_duration',
        'is_available',
        'effective_from',
        'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'break_start' => 'datetime:H:i',
            'effective_from' => 'date',
            'effective_until' => 'date',
            'is_available' => 'boolean',
        ];
    }

    /**
     * Relación con el staff
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Obtener la duración total de trabajo en minutos
     */
    public function getTotalWorkMinutesAttribute()
    {
        $start = \Carbon\Carbon::parse($this->start_time);
        $end = \Carbon\Carbon::parse($this->end_time);
        $totalMinutes = $end->diffInMinutes($start);
        
        return $totalMinutes - $this->break_duration;
    }

    /**
     * Obtener la duración total de trabajo formateada
     */
    public function getTotalWorkHoursAttribute()
    {
        $hours = floor($this->total_work_minutes / 60);
        $minutes = $this->total_work_minutes % 60;
        
        return sprintf('%d:%02d', $hours, $minutes);
    }

    /**
     * Verificar si el horario está activo en una fecha específica
     */
    public function isActiveOnDate($date)
    {
        $date = \Carbon\Carbon::parse($date);
        
        if ($this->effective_from && $date->lt($this->effective_from)) {
            return false;
        }
        
        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }
        
        return true;
    }
}
