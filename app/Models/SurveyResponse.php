<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SurveyResponse extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id', 'question_id', 'patient_id', 'staff_id',
        'response_value', 'response_text', 'numeric_value',
        'multiple_values', 'response_type', 'submitted_at',
        'ip_address', 'user_agent', 'is_anonymous'
    ];

    protected function casts(): array
    {
        return [
            'numeric_value' => 'decimal:2',
            'multiple_values' => 'array',
            'submitted_at' => 'datetime',
            'is_anonymous' => 'boolean',
        ];
    }

    // Relaciones
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    public function question()
    {
        return $this->belongsTo(SurveyQuestion::class);
    }

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    // Scopes
    public function scopeBySurvey($query, $surveyId)
    {
        return $query->where('survey_id', $surveyId);
    }

    public function scopeByQuestion($query, $questionId)
    {
        return $query->where('question_id', $questionId);
    }

    public function scopeByPatient($query, $patientId)
    {
        return $query->where('patient_id', $patientId);
    }

    public function scopeByStaff($query, $staffId)
    {
        return $query->where('staff_id', $staffId);
    }

    public function scopeAnonymous($query)
    {
        return $query->where('is_anonymous', true);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('submitted_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('submitted_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    // Métodos de utilidad
    public function isAnonymous()
    {
        return $this->is_anonymous;
    }

    public function getRespondentAttribute()
    {
        if ($this->patient_id) {
            return $this->patient;
        }
        
        if ($this->staff_id) {
            return $this->staff;
        }

        return null;
    }

    public function getRespondentNameAttribute()
    {
        $respondent = $this->respondent;
        
        if ($respondent) {
            if ($respondent instanceof Patient) {
                return $respondent->full_name;
            } elseif ($respondent instanceof Staff) {
                return $respondent->full_name;
            }
        }

        return $this->is_anonymous ? 'Anónimo' : 'Desconocido';
    }

    public function getFormattedResponseAttribute()
    {
        switch ($this->response_type) {
            case 'number':
            case 'rating':
                return $this->numeric_value;
            case 'choice':
                return $this->response_value;
            case 'text':
                return $this->response_text;
            default:
                return $this->response_value;
        }
    }

    public function isNPSResponse()
    {
        return $this->question && $this->question->question_type === 'nps';
    }

    public function isRatingResponse()
    {
        return $this->question && $this->question->question_type === 'rating';
    }

    public function getNPSCategoryAttribute()
    {
        if (!$this->isNPSResponse()) {
            return null;
        }

        $score = $this->numeric_value;
        
        if ($score >= 9) {
            return 'Promotor';
        } elseif ($score >= 7) {
            return 'Neutral';
        } else {
            return 'Detractor';
        }
    }

    public function getRatingLabelAttribute()
    {
        if (!$this->isRatingResponse()) {
            return null;
        }

        $rating = $this->numeric_value;
        
        switch (true) {
            case $rating >= 4.5:
                return 'Excelente';
            case $rating >= 3.5:
                return 'Bueno';
            case $rating >= 2.5:
                return 'Regular';
            case $rating >= 1.5:
                return 'Malo';
            default:
                return 'Muy Malo';
        }
    }
}
