<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DentalLab extends Model
{
    use HasFactory;

    protected $fillable = [
        'lab_code', 'lab_name', 'contact_name', 'email', 'phone', 'address', 'city',
        'state', 'postal_code', 'country', 'specialties', 'services', 'average_turnaround_days',
        'quality_rating', 'notes', 'is_active', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'specialties' => 'array',
            'services' => 'array',
            'average_turnaround_days' => 'decimal:2',
            'quality_rating' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function labWorks()
    {
        return $this->hasMany(LabWork::class);
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

    public function scopeByCity($query, $city)
    {
        return $query->where('city', $city);
    }

    public function scopeBySpecialty($query, $specialty)
    {
        return $query->whereJsonContains('specialties', $specialty);
    }

    public function scopeByService($query, $service)
    {
        return $query->whereJsonContains('services', $service);
    }

    public function scopeHighQuality($query, $rating = 4.0)
    {
        return $query->where('quality_rating', '>=', $rating);
    }

    public function scopeFastDelivery($query, $days = 7)
    {
        return $query->where('average_turnaround_days', '<=', $days);
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->is_active;
    }

    public function getFullAddressAttribute()
    {
        $address = $this->address;
        if ($this->city) $address .= ', ' . $this->city;
        if ($this->state) $address .= ', ' . $this->state;
        if ($this->postal_code) $address .= ' ' . $this->postal_code;
        if ($this->country) $address .= ', ' . $this->country;
        
        return $address;
    }

    public function getFormattedQualityRatingAttribute()
    {
        return $this->quality_rating ? number_format($this->quality_rating, 1) . '/5.0' : 'Sin calificación';
    }

    public function getQualityStarsAttribute()
    {
        if (!$this->quality_rating) return '';
        
        $stars = '';
        $rating = round($this->quality_rating);
        
        for ($i = 1; $i <= 5; $i++) {
            $stars .= $i <= $rating ? '★' : '☆';
        }
        
        return $stars;
    }

    public function getFormattedTurnaroundDaysAttribute()
    {
        return $this->average_turnaround_days ? number_format($this->average_turnaround_days, 1) . ' días' : 'N/A';
    }

    public function getSpecialtiesDisplayAttribute()
    {
        return $this->specialties ? implode(', ', $this->specialties) : 'N/A';
    }

    public function getServicesDisplayAttribute()
    {
        return $this->services ? implode(', ', $this->services) : 'N/A';
    }

    public function getTotalLabWorksAttribute()
    {
        return $this->labWorks()->count();
    }

    public function getCompletedLabWorksAttribute()
    {
        return $this->labWorks()->where('status', 'completed')->count();
    }

    public function getInProgressLabWorksAttribute()
    {
        return $this->labWorks()->whereIn('status', ['pending', 'sent', 'in_progress'])->count();
    }

    public function getCompletionRateAttribute()
    {
        if ($this->total_lab_works == 0) return 0;
        return round(($this->completed_lab_works / $this->total_lab_works) * 100, 2);
    }

    public function getAverageCompletionTime()
    {
        $completedWorks = $this->labWorks()
            ->where('status', 'completed')
            ->whereNotNull('actual_delivery')
            ->whereNotNull('work_date')
            ->get();

        if ($completedWorks->isEmpty()) return null;

        $totalDays = 0;
        foreach ($completedWorks as $work) {
            $totalDays += $work->work_date->diffInDays($work->actual_delivery);
        }

        return round($totalDays / $completedWorks->count(), 1);
    }

    public function getFormattedAverageCompletionTimeAttribute()
    {
        $time = $this->getAverageCompletionTime();
        return $time ? $time . ' días' : 'N/A';
    }

    public function getLastLabWorkDate()
    {
        $lastWork = $this->labWorks()->latest()->first();
        return $lastWork ? $lastWork->work_date : null;
    }

    public function getFormattedLastLabWorkDateAttribute()
    {
        return $this->last_lab_work_date ? $this->last_lab_work_date->format('d/m/Y') : 'Nunca';
    }

    public function hasSpecialty($specialty)
    {
        return $this->specialties && in_array($specialty, $this->specialties);
    }

    public function hasService($service)
    {
        return $this->services && in_array($service, $this->services);
    }

    public function addSpecialty($specialty)
    {
        $specialties = $this->specialties ?: [];
        if (!in_array($specialty, $specialties)) {
            $specialties[] = $specialty;
            $this->update(['specialties' => $specialties]);
        }
        return $this;
    }

    public function removeSpecialty($specialty)
    {
        $specialties = $this->specialties ?: [];
        $specialties = array_filter($specialties, function($s) use ($specialty) {
            return $s !== $specialty;
        });
        $this->update(['specialties' => array_values($specialties)]);
        return $this;
    }

    public function addService($service)
    {
        $services = $this->services ?: [];
        if (!in_array($service, $services)) {
            $services[] = $service;
            $this->update(['services' => $services]);
        }
        return $this;
    }

    public function removeService($service)
    {
        $services = $this->services ?: [];
        $services = array_filter($services, function($s) use ($service) {
            return $s !== $service;
        });
        $this->update(['services' => array_values($services)]);
        return $this;
    }

    public function updateQualityRating($rating)
    {
        $this->update(['quality_rating' => max(0, min(5, $rating))]);
        return $this;
    }

    public function updateTurnaroundDays($days)
    {
        $this->update(['average_turnaround_days' => max(1, $days)]);
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
            'quality_rating' => $this->quality_rating,
            'average_turnaround_days' => $this->average_turnaround_days,
        ];
    }

    public function getFormattedPerformanceMetricsAttribute()
    {
        $metrics = $this->getPerformanceMetrics();
        return [
            'total_works' => $metrics['total_works'] . ' trabajos',
            'completed_works' => $metrics['completed_works'] . ' completados',
            'in_progress_works' => $metrics['in_progress_works'] . ' en progreso',
            'completion_rate' => $metrics['completion_rate'] . '%',
            'average_completion_time' => $this->formatted_average_completion_time,
            'quality_rating' => $this->formatted_quality_rating,
            'average_turnaround_days' => $this->formatted_turnaround_days,
        ];
    }

    public static function getSpecialtyOptions()
    {
        return [
            'crowns' => 'Coronas',
            'bridges' => 'Puentes',
            'dentures' => 'Dentaduras',
            'implants' => 'Implantes',
            'veneers' => 'Carillas',
            'inlays_onlays' => 'Inlays/Onlays',
            'orthodontics' => 'Ortodoncia',
            'ceramic' => 'Cerámica',
            'metal' => 'Metal',
            'zirconia' => 'Zirconia',
            'acrylic' => 'Acrílico',
        ];
    }

    public static function getServiceOptions()
    {
        return [
            'design' => 'Diseño',
            'manufacturing' => 'Fabricación',
            'finishing' => 'Acabado',
            'repair' => 'Reparación',
            'adjustment' => 'Ajuste',
            'pickup' => 'Recogida',
            'delivery' => 'Entrega',
            'warranty' => 'Garantía',
            'consultation' => 'Consultoría',
        ];
    }

    public static function getTopPerformers($limit = 5)
    {
        return self::active()
            ->withCount(['labWorks as completed_works' => function($query) {
                $query->where('status', 'completed');
            }])
            ->having('completed_works', '>', 0)
            ->orderBy('quality_rating', 'desc')
            ->orderBy('completed_works', 'desc')
            ->limit($limit)
            ->get();
    }

    public static function getLabsBySpecialty($specialty)
    {
        return self::active()->bySpecialty($specialty)->get();
    }

    public static function getAverageTurnaroundByCity()
    {
        return self::active()
            ->selectRaw('city, AVG(average_turnaround_days) as avg_turnaround')
            ->groupBy('city')
            ->orderBy('avg_turnaround')
            ->get();
    }

    public static function getQualityStats()
    {
        return self::active()
            ->selectRaw('
                COUNT(*) as total_labs,
                AVG(quality_rating) as avg_quality,
                MIN(quality_rating) as min_quality,
                MAX(quality_rating) as max_quality
            ')
            ->first();
    }
}
