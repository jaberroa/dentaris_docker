<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Prosthesis extends Model
{
    use HasFactory;

    protected $fillable = [
        'prosthesis_code', 'prosthesis_name', 'description', 'type', 'material', 'color',
        'size', 'cost', 'estimated_days', 'specifications', 'care_instructions',
        'requires_lab', 'is_active', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'cost' => 'decimal:2',
            'estimated_days' => 'integer',
            'requires_lab' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function labWorkItems()
    {
        return $this->hasMany(LabWorkItem::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByMaterial($query, $material)
    {
        return $query->where('material', $material);
    }

    public function scopeRequiresLab($query)
    {
        return $query->where('requires_lab', true);
    }

    public function scopeFastDelivery($query, $days = 7)
    {
        return $query->where('estimated_days', '<=', $days);
    }

    public function scopeByPriceRange($query, $minPrice, $maxPrice)
    {
        return $query->whereBetween('cost', [$minPrice, $maxPrice]);
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->is_active;
    }

    public function requiresLab()
    {
        return $this->requires_lab;
    }

    public function getFormattedCostAttribute()
    {
        return $this->cost ? '$' . number_format($this->cost, 2) : 'Consultar precio';
    }

    public function getFormattedEstimatedDaysAttribute()
    {
        return $this->estimated_days ? $this->estimated_days . ' días' : 'Por definir';
    }

    public function getTypeDisplayAttribute()
    {
        return match($this->type) {
            'crown' => 'Corona',
            'bridge' => 'Puente',
            'denture' => 'Dentadura',
            'implant' => 'Implante',
            'veneer' => 'Carilla',
            'inlay' => 'Inlay',
            'onlay' => 'Onlay',
            'other' => 'Otro',
            default => $this->type
        };
    }

    public function getMaterialDisplayAttribute()
    {
        return match($this->material) {
            'ceramic' => 'Cerámica',
            'porcelain' => 'Porcelana',
            'metal' => 'Metal',
            'zirconia' => 'Zirconia',
            'acrylic' => 'Acrílico',
            'composite' => 'Composite',
            'gold' => 'Oro',
            'titanium' => 'Titanio',
            'other' => 'Otro',
            default => $this->material
        };
    }

    public function getColorDisplayAttribute()
    {
        return match($this->color) {
            'white' => 'Blanco',
            'ivory' => 'Marfil',
            'natural' => 'Natural',
            'custom' => 'Personalizado',
            'other' => 'Otro',
            default => $this->color
        };
    }

    public function getSizeDisplayAttribute()
    {
        return match($this->size) {
            'small' => 'Pequeño',
            'medium' => 'Mediano',
            'large' => 'Grande',
            'custom' => 'Personalizado',
            'standard' => 'Estándar',
            default => $this->size
        };
    }

    public function getTotalLabWorksAttribute()
    {
        return $this->labWorkItems()->count();
    }

    public function getCompletedLabWorksAttribute()
    {
        return $this->labWorkItems()->where('status', 'completed')->count();
    }

    public function getInProgressLabWorksAttribute()
    {
        return $this->labWorkItems()->whereIn('status', ['pending', 'in_progress'])->count();
    }

    public function getCompletionRateAttribute()
    {
        if ($this->total_lab_works == 0) return 0;
        return round(($this->completed_lab_works / $this->total_lab_works) * 100, 2);
    }

    public function getAverageCompletionTime()
    {
        $completedWorks = $this->labWorkItems()
            ->where('status', 'completed')
            ->with('labWork')
            ->get();

        if ($completedWorks->isEmpty()) return null;

        $totalDays = 0;
        $count = 0;

        foreach ($completedWorks as $item) {
            if ($item->labWork && $item->labWork->actual_delivery && $item->labWork->work_date) {
                $totalDays += $item->labWork->work_date->diffInDays($item->labWork->actual_delivery);
                $count++;
            }
        }

        return $count > 0 ? round($totalDays / $count, 1) : null;
    }

    public function getFormattedAverageCompletionTimeAttribute()
    {
        $time = $this->getAverageCompletionTime();
        return $time ? $time . ' días' : 'Sin datos';
    }

    public function getPopularityScore()
    {
        // Score basado en número de trabajos y tasa de finalización
        $baseScore = $this->total_lab_works * 10;
        $completionBonus = $this->completion_rate * 0.5;
        
        return round($baseScore + $completionBonus, 1);
    }

    public function getPopularityLevel()
    {
        $score = $this->getPopularityScore();
        
        if ($score >= 80) return 'high';
        if ($score >= 40) return 'medium';
        return 'low';
    }

    public function getPopularityLevelDisplayAttribute()
    {
        return match($this->getPopularityLevel()) {
            'high' => 'Alta',
            'medium' => 'Media',
            'low' => 'Baja',
            default => 'Sin datos'
        };
    }

    public function getPopularityLevelColorAttribute()
    {
        return match($this->getPopularityLevel()) {
            'high' => 'success',
            'medium' => 'warning',
            'low' => 'secondary',
            default => 'light'
        };
    }

    public function getLastUsedDate()
    {
        $lastWork = $this->labWorkItems()
            ->with('labWork')
            ->latest()
            ->first();
            
        return $lastWork && $lastWork->labWork ? $lastWork->labWork->work_date : null;
    }

    public function getFormattedLastUsedDateAttribute()
    {
        return $this->last_used_date ? $this->last_used_date->format('d/m/Y') : 'Nunca';
    }

    public function getCareInstructionsArray()
    {
        if (!$this->care_instructions) return [];
        
        return array_filter(array_map('trim', explode("\n", $this->care_instructions)));
    }

    public function getSpecificationsArray()
    {
        if (!$this->specifications) return [];
        
        return array_filter(array_map('trim', explode("\n", $this->specifications)));
    }

    public function updateCost($newCost)
    {
        $this->update(['cost' => max(0, $newCost)]);
        return $this;
    }

    public function updateEstimatedDays($days)
    {
        $this->update(['estimated_days' => max(1, $days)]);
        return $this;
    }

    public function getPerformanceMetrics()
    {
        return [
            'total_works' => $this->total_lab_works,
            'completed_works' => $this->completed_lab_works,
            'in_progress_works' => $this->in_progress_lab_works,
            'completion_rate' => $this->completion_rate,
            'average_completion_time' => $this->getAverageCompletionTime(),
            'popularity_score' => $this->getPopularityScore(),
            'popularity_level' => $this->getPopularityLevel(),
            'last_used_date' => $this->last_used_date,
        ];
    }

    public static function getTypeOptions()
    {
        return [
            'crown' => 'Corona',
            'bridge' => 'Puente',
            'denture' => 'Dentadura',
            'implant' => 'Implante',
            'veneer' => 'Carilla',
            'inlay' => 'Inlay',
            'onlay' => 'Onlay',
            'other' => 'Otro',
        ];
    }

    public static function getMaterialOptions()
    {
        return [
            'ceramic' => 'Cerámica',
            'porcelain' => 'Porcelana',
            'metal' => 'Metal',
            'zirconia' => 'Zirconia',
            'acrylic' => 'Acrílico',
            'composite' => 'Composite',
            'gold' => 'Oro',
            'titanium' => 'Titanio',
            'other' => 'Otro',
        ];
    }

    public static function getColorOptions()
    {
        return [
            'white' => 'Blanco',
            'ivory' => 'Marfil',
            'natural' => 'Natural',
            'custom' => 'Personalizado',
            'other' => 'Otro',
        ];
    }

    public static function getSizeOptions()
    {
        return [
            'small' => 'Pequeño',
            'medium' => 'Mediano',
            'large' => 'Grande',
            'custom' => 'Personalizado',
            'standard' => 'Estándar',
        ];
    }

    public static function getMostPopular($limit = 10)
    {
        return self::active()
            ->withCount('labWorkItems')
            ->having('lab_work_items_count', '>', 0)
            ->orderBy('lab_work_items_count', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getByType($type)
    {
        return self::active()->byType($type)->get();
    }

    public static function getByMaterial($material)
    {
        return self::active()->byMaterial($material)->get();
    }

    public static function getPriceStats()
    {
        return self::active()
            ->selectRaw('
                COUNT(*) as total_prostheses,
                AVG(cost) as avg_cost,
                MIN(cost) as min_cost,
                MAX(cost) as max_cost
            ')
            ->first();
    }

    public static function getDeliveryStats()
    {
        return self::active()
            ->selectRaw('
                COUNT(*) as total_prostheses,
                AVG(estimated_days) as avg_delivery_days,
                MIN(estimated_days) as min_delivery_days,
                MAX(estimated_days) as max_delivery_days
            ')
            ->first();
    }

    public static function search($query)
    {
        return self::active()
            ->where(function($q) use ($query) {
                $q->where('prosthesis_name', 'like', "%{$query}%")
                  ->orWhere('description', 'like', "%{$query}%")
                  ->orWhere('prosthesis_code', 'like', "%{$query}%");
            })
            ->get();
    }
}
