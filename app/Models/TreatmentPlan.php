<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TreatmentPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'staff_id',
        'plan_name',
        'description',
        'status',
        'start_date',
        'end_date',
        'total_sessions',
        'total_cost',
        'notes',
        'is_urgent',
        'requires_approval',
        'approved_at',
        'approved_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'total_cost' => 'decimal:2',
            'is_urgent' => 'boolean',
            'requires_approval' => 'boolean',
            'approved_at' => 'datetime',
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
     * Relación con items del plan
     */
    public function items()
    {
        return $this->hasMany(TreatmentPlanItem::class);
    }

    /**
     * Relación con presupuestos
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Relación con el usuario que creó el plan
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relación con el usuario que aprobó el plan
     */
    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Verificar si el plan está aprobado
     */
    public function isApproved()
    {
        return $this->approved_at !== null;
    }

    /**
     * Verificar si el plan está activo
     */
    public function isActive()
    {
        return $this->status === 'active';
    }

    /**
     * Verificar si el plan está completado
     */
    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    /**
     * Verificar si el plan puede ser modificado
     */
    public function canBeModified()
    {
        // El plan puede ser modificado si no está completado o cancelado
        return !in_array($this->status, ['completed', 'cancelled']);
    }

    /**
     * Obtener el progreso del plan en porcentaje
     */
    public function getProgressPercentageAttribute()
    {
        $totalItems = $this->items()->count();
        if ($totalItems === 0) return 0;
        
        $completedItems = $this->items()->where('status', 'completed')->count();
        return round(($completedItems / $totalItems) * 100, 2);
    }

    /**
     * Scope para planes activos
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope para planes urgentes
     */
    public function scopeUrgent($query)
    {
        return $query->where('is_urgent', true);
    }

    /**
     * Scope para planes que requieren aprobación
     */
    public function scopeRequiringApproval($query)
    {
        return $query->where('requires_approval', true);
    }

    /**
     * Obtener opciones de estado
     */
    public static function getStatusOptions()
    {
        return [
            'draft' => 'Borrador',
            'active' => 'Activo',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            'on_hold' => 'En Espera',
        ];
    }

    /**
     * Obtener opciones de prioridad
     */
    public static function getPriorityOptions()
    {
        return [
            'low' => 'Baja',
            'normal' => 'Normal',
            'high' => 'Alta',
            'urgent' => 'Urgente',
        ];
    }

    /**
     * Obtener opciones de plan de pago
     */
    public static function getPaymentPlanOptions()
    {
        return [
            'cash' => 'Efectivo',
            'installments' => 'Cuotas',
            'insurance' => 'Seguro',
            'financing' => 'Financiamiento',
        ];
    }
}
