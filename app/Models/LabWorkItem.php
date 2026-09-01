<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LabWorkItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_work_id', 'prosthesis_id', 'quantity', 'unit_cost', 'total_cost',
        'specifications', 'status', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'integer',
            'unit_cost' => 'decimal:2',
            'total_cost' => 'decimal:2',
        ];
    }

    // Relaciones
    public function labWork()
    {
        return $this->belongsTo(LabWork::class);
    }

    public function prosthesis()
    {
        return $this->belongsTo(Prosthesis::class);
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

    public function scopeByLabWork($query, $labWorkId)
    {
        return $query->where('lab_work_id', $labWorkId);
    }

    public function scopeByProsthesis($query, $prosthesisId)
    {
        return $query->where('prosthesis_id', $prosthesisId);
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

    public function getFormattedUnitCostAttribute()
    {
        return '$' . number_format($this->unit_cost, 2);
    }

    public function getFormattedTotalCostAttribute()
    {
        return '$' . number_format($this->total_cost, 2);
    }

    public function calculateTotalCost()
    {
        $totalCost = $this->quantity * $this->unit_cost;
        $this->update(['total_cost' => $totalCost]);
        return $totalCost;
    }

    public function getSpecificationsArray()
    {
        if (!$this->specifications) return [];
        
        return array_filter(array_map('trim', explode("\n", $this->specifications)));
    }

    public function updateStatus($status)
    {
        $this->update(['status' => $status]);
        return $this;
    }

    public function markAsCompleted()
    {
        $this->updateStatus('completed');
        return $this;
    }

    public function markAsInProgress()
    {
        $this->updateStatus('in_progress');
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

    public static function getStatusOptions()
    {
        return [
            'pending' => 'Pendiente',
            'in_progress' => 'En progreso',
            'completed' => 'Completado',
            'cancelled' => 'Cancelado',
        ];
    }

    public static function getTotalValue($labWorkId = null)
    {
        $query = self::where('status', '!=', 'cancelled');
        
        if ($labWorkId) {
            $query->where('lab_work_id', $labWorkId);
        }
        
        return $query->sum('total_cost');
    }

    public static function getCompletedItemsCount($labWorkId = null)
    {
        $query = self::where('status', 'completed');
        
        if ($labWorkId) {
            $query->where('lab_work_id', $labWorkId);
        }
        
        return $query->count();
    }

    public static function getPendingItemsCount($labWorkId = null)
    {
        $query = self::where('status', 'pending');
        
        if ($labWorkId) {
            $query->where('lab_work_id', $labWorkId);
        }
        
        return $query->count();
    }
}
