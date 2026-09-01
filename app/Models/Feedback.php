<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Feedback extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id', 'staff_id', 'appointment_id', 'type', 'category',
        'message', 'rating', 'priority', 'status', 'admin_response',
        'responded_at', 'responded_by', 'is_anonymous', 'is_public', 'notes'
    ];

    protected function casts(): array
    {
        return [
            'responded_at' => 'datetime',
            'is_anonymous' => 'boolean',
            'is_public' => 'boolean',
        ];
    }

    // Relaciones
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }

    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    public function appointment()
    {
        return $this->belongsTo(Appointment::class);
    }

    public function responder()
    {
        return $this->belongsTo(User::class, 'responded_by');
    }

    // Scopes
    public function scopeNew($query)
    {
        return $query->where('status', 'new');
    }

    public function scopeInReview($query)
    {
        return $query->where('status', 'in_review');
    }

    public function scopeResponded($query)
    {
        return $query->where('status', 'responded');
    }

    public function scopeResolved($query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function scopeByPriority($query, $priority)
    {
        return $query->where('priority', $priority);
    }

    public function scopeHighPriority($query)
    {
        return $query->whereIn('priority', ['high', 'urgent']);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeWithRating($query)
    {
        return $query->whereNotNull('rating');
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    // Métodos de utilidad
    public function isNew()
    {
        return $this->status === 'new';
    }

    public function isInReview()
    {
        return $this->status === 'in_review';
    }

    public function isResponded()
    {
        return $this->status === 'responded';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isHighPriority()
    {
        return in_array($this->priority, ['high', 'urgent']);
    }

    public function isAnonymous()
    {
        return $this->is_anonymous;
    }

    public function isPublic()
    {
        return $this->is_public;
    }

    public function hasRating()
    {
        return !is_null($this->rating);
    }

    public function getRatingLabelAttribute()
    {
        if (!$this->hasRating()) {
            return null;
        }

        switch ($this->rating) {
            case 5:
                return 'Excelente';
            case 4:
                return 'Muy Bueno';
            case 3:
                return 'Bueno';
            case 2:
                return 'Regular';
            case 1:
                return 'Malo';
            default:
                return 'Sin calificar';
        }
    }

    public function getPriorityLabelAttribute()
    {
        switch ($this->priority) {
            case 'urgent':
                return 'Urgente';
            case 'high':
                return 'Alta';
            case 'medium':
                return 'Media';
            case 'low':
                return 'Baja';
            default:
                return 'Sin prioridad';
        }
    }

    public function getStatusLabelAttribute()
    {
        switch ($this->status) {
            case 'new':
                return 'Nuevo';
            case 'in_review':
                return 'En Revisión';
            case 'responded':
                return 'Respondido';
            case 'resolved':
                return 'Resuelto';
            case 'closed':
                return 'Cerrado';
            default:
                return 'Desconocido';
        }
    }

    public function getRespondentNameAttribute()
    {
        if ($this->is_anonymous) {
            return 'Anónimo';
        }

        if ($this->patient) {
            return $this->patient->full_name;
        }

        if ($this->staff) {
            return $this->staff->full_name;
        }

        return 'Desconocido';
    }

    public function getDaysSinceCreatedAttribute()
    {
        return $this->created_at->diffInDays(now());
    }

    public function getDaysSinceResponseAttribute()
    {
        if (!$this->responded_at) {
            return null;
        }

        return $this->responded_at->diffInDays(now());
    }

    public function markAsResponded($response, $respondedBy = null)
    {
        $this->update([
            'status' => 'responded',
            'admin_response' => $response,
            'responded_at' => now(),
            'responded_by' => $respondedBy ?? auth()->id()
        ]);
    }

    public function markAsResolved()
    {
        $this->update(['status' => 'resolved']);
    }

    public function markAsClosed()
    {
        $this->update(['status' => 'closed']);
    }
}
