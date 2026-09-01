<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyQuestion extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id', 'question_text', 'description', 'question_type',
        'options', 'is_required', 'sort_order', 'validation_rules',
        'help_text', 'is_active'
    ];

    protected function casts(): array
    {
        return [
            'options' => 'array',
            'validation_rules' => 'array',
            'is_required' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    // Relaciones
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function responses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeRequired($query)
    {
        return $query->where('is_required', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('question_type', $type);
    }

    // Métodos de utilidad
    public function isActive()
    {
        return $this->is_active;
    }

    public function isRequired()
    {
        return $this->is_required;
    }

    public function isMultipleChoice()
    {
        return in_array($this->question_type, ['multiple_choice', 'single_choice']);
    }

    public function isTextBased()
    {
        return in_array($this->question_type, ['text', 'number']);
    }

    public function isRatingBased()
    {
        return in_array($this->question_type, ['rating', 'nps']);
    }

    public function getResponseCountAttribute()
    {
        return $this->responses()->count();
    }

    public function getAverageRatingAttribute()
    {
        if (!$this->isRatingBased()) {
            return null;
        }

        return $this->responses()
            ->whereNotNull('numeric_value')
            ->avg('numeric_value');
    }

    public function getResponseDistributionAttribute()
    {
        if (!$this->isMultipleChoice()) {
            return null;
        }

        return $this->responses()
            ->selectRaw('response_value, COUNT(*) as count')
            ->groupBy('response_value')
            ->orderBy('count', 'desc')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->response_value => $item->count];
            });
    }

    public function getOptionsArrayAttribute()
    {
        return $this->options ?? [];
    }

    public function hasOptions()
    {
        return !empty($this->options);
    }

    public function validateResponse($response)
    {
        if ($this->is_required && empty($response)) {
            return false;
        }

        if ($this->validation_rules) {
            // Implementar validaciones personalizadas
            foreach ($this->validation_rules as $rule => $value) {
                switch ($rule) {
                    case 'min_length':
                        if (strlen($response) < $value) {
                            return false;
                        }
                        break;
                    case 'max_length':
                        if (strlen($response) > $value) {
                            return false;
                        }
                        break;
                    case 'min_value':
                        if (is_numeric($response) && $response < $value) {
                            return false;
                        }
                        break;
                    case 'max_value':
                        if (is_numeric($response) && $response > $value) {
                            return false;
                        }
                        break;
                }
            }
        }

        return true;
    }
}
