<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class TreatmentPlanItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'treatment_plan_id', 'cdt_catalog_id', 'sequence_order', 'tooth_number', 'surface',
        'description', 'quantity', 'unit_price', 'total_price', 'status', 'notes',
        'completed_date', 'completed_by'
    ];

    protected function casts(): array
    {
        return [
            'sequence_order' => 'integer',
            'quantity' => 'integer',
            'unit_price' => 'decimal:2',
            'total_price' => 'decimal:2',
            'completed_date' => 'date',
        ];
    }

    // Relaciones
    public function treatmentPlan()
    {
        return $this->belongsTo(TreatmentPlan::class);
    }

    public function cdtCatalog()
    {
        return $this->belongsTo(CdtCatalog::class);
    }

    public function completedBy()
    {
        return $this->belongsTo(User::class, 'completed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeByTreatmentPlan($query, $treatmentPlanId)
    {
        return $query->where('treatment_plan_id', $treatmentPlanId);
    }

    public function scopeByTooth($query, $toothNumber)
    {
        return $query->where('tooth_number', $toothNumber);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sequence_order');
    }

    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    // Métodos de utilidad
    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function getStatusDisplayAttribute()
    {
        return match($this->status) {
            'pending' => 'Pendiente',
            'in_progress' => 'En progreso',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
            default => $this->status
        };
    }

    public function getStatusColorAttribute()
    {
        return match($this->status) {
            'pending' => 'warning',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getFormattedUnitPriceAttribute()
    {
        return '$' . number_format($this->unit_price, 2);
    }

    public function getFormattedTotalPriceAttribute()
    {
        return '$' . number_format($this->total_price, 2);
    }

    public function getFormattedCompletedDateAttribute()
    {
        return $this->completed_date ? $this->completed_date->format('d/m/Y') : 'Pendiente';
    }

    public function getToothDisplayAttribute()
    {
        if (!$this->tooth_number) return 'N/A';
        
        $toothNumber = $this->tooth_number;
        $surface = $this->surface ? " ({$this->surface})" : '';
        
        return "Diente {$toothNumber}{$surface}";
    }

    public function getFullDescriptionAttribute()
    {
        $description = $this->description;
        $tooth = $this->tooth_display;
        
        if ($tooth !== 'N/A') {
            $description = "{$tooth} - {$description}";
        }
        
        return $description;
    }

    public function calculateTotalPrice()
    {
        $totalPrice = $this->quantity * $this->unit_price;
        $this->update(['total_price' => $totalPrice]);
        return $totalPrice;
    }

    public function markAsInProgress()
    {
        $this->update(['status' => 'in_progress']);
        return $this;
    }

    public function markAsCompleted($userId = null)
    {
        $this->update([
            'status' => 'completed',
            'completed_date' => now(),
            'completed_by' => $userId
        ]);
        return $this;
    }

    public function markAsCancelled($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason ? $this->notes . "\nCancelado: " . $reason : $this->notes
        ]);
        return $this;
    }

    public function getProgressPercentage()
    {
        // Calcular progreso basado en el estado
        return match($this->status) {
            'pending' => 0,
            'in_progress' => 50,
            'completed' => 100,
            'cancelled' => 0,
            default => 0
        };
    }

    public function getProgressBarColor()
    {
        return match($this->status) {
            'pending' => 'warning',
            'in_progress' => 'primary',
            'completed' => 'success',
            'cancelled' => 'danger',
            default => 'secondary'
        };
    }

    public function getDuration()
    {
        if (!$this->completed_date) return null;
        
        // Asumiendo que el tratamiento comenzó cuando se marcó como in_progress
        // En una implementación real, podrías tener una fecha de inicio específica
        $startDate = $this->updated_at; // Fecha de última actualización como aproximación
        return $startDate->diffInDays($this->completed_date);
    }

    public function getFormattedDurationAttribute()
    {
        $duration = $this->getDuration();
        return $duration ? $duration . ' días' : 'N/A';
    }

    public function getSequenceDisplayAttribute()
    {
        return "Paso {$this->sequence_order}";
    }

    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pendiente',
            'in_progress' => 'En progreso',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
        ];
    }

    public static function getToothNumbers()
    {
        return [
            '11' => '11 - Incisivo Central Superior Izquierdo',
            '12' => '12 - Incisivo Lateral Superior Izquierdo',
            '13' => '13 - Canino Superior Izquierdo',
            '14' => '14 - Primer Premolar Superior Izquierdo',
            '15' => '15 - Segundo Premolar Superior Izquierdo',
            '16' => '16 - Primer Molar Superior Izquierdo',
            '17' => '17 - Segundo Molar Superior Izquierdo',
            '18' => '18 - Tercer Molar Superior Izquierdo',
            '21' => '21 - Incisivo Central Superior Derecho',
            '22' => '22 - Incisivo Lateral Superior Derecho',
            '23' => '23 - Canino Superior Derecho',
            '24' => '24 - Primer Premolar Superior Derecho',
            '25' => '25 - Segundo Premolar Superior Derecho',
            '26' => '26 - Primer Molar Superior Derecho',
            '27' => '27 - Segundo Molar Superior Derecho',
            '28' => '28 - Tercer Molar Superior Derecho',
            '31' => '31 - Incisivo Central Inferior Derecho',
            '32' => '32 - Incisivo Lateral Inferior Derecho',
            '33' => '33 - Canino Inferior Derecho',
            '34' => '34 - Primer Premolar Inferior Derecho',
            '35' => '35 - Segundo Premolar Inferior Derecho',
            '36' => '36 - Primer Molar Inferior Derecho',
            '37' => '37 - Segundo Molar Inferior Derecho',
            '38' => '38 - Tercer Molar Inferior Derecho',
            '41' => '41 - Incisivo Central Inferior Izquierdo',
            '42' => '42 - Incisivo Lateral Inferior Izquierdo',
            '43' => '43 - Canino Inferior Izquierdo',
            '44' => '44 - Primer Premolar Inferior Izquierdo',
            '45' => '45 - Segundo Premolar Inferior Izquierdo',
            '46' => '46 - Primer Molar Inferior Izquierdo',
            '47' => '47 - Segundo Molar Inferior Izquierdo',
            '48' => '48 - Tercer Molar Inferior Izquierdo',
        ];
    }

    public static function getSurfaceOptions()
    {
        return [
            'mesial' => 'Mesial',
            'distal' => 'Distal',
            'lingual' => 'Lingual',
            'palatal' => 'Palatal',
            'vestibular' => 'Vestibular',
            'oclusal' => 'Oclusal',
            'incisal' => 'Incisal',
            'cervical' => 'Cervical',
        ];
    }

    public static function getTotalValue($treatmentPlanId = null)
    {
        $query = self::where('status', '!=', 'cancelled');
        
        if ($treatmentPlanId) {
            $query->where('treatment_plan_id', $treatmentPlanId);
        }
        
        return $query->sum('total_price');
    }

    public static function getCompletedItemsCount($treatmentPlanId = null)
    {
        $query = self::where('status', 'completed');
        
        if ($treatmentPlanId) {
            $query->where('treatment_plan_id', $treatmentPlanId);
        }
        
        return $query->count();
    }

    public static function getPendingItemsCount($treatmentPlanId = null)
    {
        $query = self::where('status', 'pending');
        
        if ($treatmentPlanId) {
            $query->where('treatment_plan_id', $treatmentPlanId);
        }
        
        return $query->count();
    }

    public static function getInProgressItemsCount($treatmentPlanId = null)
    {
        $query = self::where('status', 'in_progress');
        
        if ($treatmentPlanId) {
            $query->where('treatment_plan_id', $treatmentPlanId);
        }
        
        return $query->count();
    }
}
