<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_name', 'description', 'type', 'target_audience', 'status',
        'start_date', 'end_date', 'is_anonymous', 'requires_login',
        'max_responses', 'instructions', 'settings', 'created_by'
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_anonymous' => 'boolean',
            'requires_login' => 'boolean',
            'settings' => 'array',
        ];
    }

    // Relaciones
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(SurveyQuestion::class)->orderBy('sort_order');
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByTarget($query, $target)
    {
        return $query->where('target_audience', $target);
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'active')
                    ->where(function ($q) {
                        $q->whereNull('start_date')->orWhere('start_date', '<=', now());
                    })
                    ->where(function ($q) {
                        $q->whereNull('end_date')->orWhere('end_date', '>=', now());
                    });
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->status === 'active';
    }

    public function isAvailable()
    {
        if (!$this->isActive()) {
            return false;
        }

        $now = now();
        
        if ($this->start_date && $this->start_date > $now) {
            return false;
        }

        if ($this->end_date && $this->end_date < $now) {
            return false;
        }

        if ($this->max_responses && $this->responses()->count() >= $this->max_responses) {
            return false;
        }

        return true;
    }

    public function isExpired()
    {
        return $this->end_date && $this->end_date < now();
    }

    public function hasReachedMaxResponses()
    {
        return $this->max_responses && $this->responses()->count() >= $this->max_responses;
    }

    public function getResponseCountAttribute()
    {
        return $this->responses()->count();
    }

    public function getCompletionRateAttribute()
    {
        $totalQuestions = $this->questions()->count();
        if ($totalQuestions === 0) {
            return 0;
        }

        $totalResponses = $this->responses()->count();
        $completeResponses = $this->responses()
            ->selectRaw('survey_id, patient_id, staff_id, COUNT(*) as response_count')
            ->groupBy('survey_id', 'patient_id', 'staff_id')
            ->havingRaw('COUNT(*) = ?', [$totalQuestions])
            ->count();

        return $totalResponses > 0 ? ($completeResponses / $totalResponses) * 100 : 0;
    }
}
