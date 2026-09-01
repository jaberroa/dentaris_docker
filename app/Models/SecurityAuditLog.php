<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class SecurityAuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'event_type',
        'event_description',
        'ip_address',
        'user_agent',
        'session_id',
        'metadata',
        'risk_level',
        'is_suspicious',
        'location',
        'device_fingerprint',
        'event_time',
    ];

    protected $casts = [
        'metadata' => 'array',
        'event_time' => 'datetime',
        'is_suspicious' => 'boolean',
    ];

    /**
     * Get the user that owns the audit log
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope for high risk events
     */
    public function scopeHighRisk($query)
    {
        return $query->whereIn('risk_level', ['high', 'critical']);
    }

    /**
     * Scope for suspicious events
     */
    public function scopeSuspicious($query)
    {
        return $query->where('is_suspicious', true);
    }

    /**
     * Scope for events by type
     */
    public function scopeByEventType($query, string $eventType)
    {
        return $query->where('event_type', $eventType);
    }

    /**
     * Scope for events by user
     */
    public function scopeByUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope for events by IP
     */
    public function scopeByIp($query, string $ipAddress)
    {
        return $query->where('ip_address', $ipAddress);
    }

    /**
     * Scope for recent events
     */
    public function scopeRecent($query, int $hours = 24)
    {
        return $query->where('event_time', '>=', now()->subHours($hours));
    }

    /**
     * Get risk level color
     */
    public function getRiskLevelColorAttribute(): string
    {
        return match ($this->risk_level) {
            'low' => 'success',
            'medium' => 'warning',
            'high' => 'danger',
            'critical' => 'dark',
            default => 'secondary',
        };
    }

    /**
     * Get formatted event time
     */
    public function getFormattedEventTimeAttribute(): string
    {
        return $this->event_time->format('Y-m-d H:i:s');
    }

    /**
     * Get user display name
     */
    public function getUserDisplayNameAttribute(): string
    {
        return $this->user ? $this->user->name : 'Sistema';
    }

    /**
     * Get location display
     */
    public function getLocationDisplayAttribute(): string
    {
        return $this->location ?: 'Desconocida';
    }

    /**
     * Check if event is recent
     */
    public function isRecent(int $minutes = 60): bool
    {
        return $this->event_time->isAfter(now()->subMinutes($minutes));
    }

    /**
     * Get similar events from same IP
     */
    public function getSimilarEventsFromIp(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('ip_address', $this->ip_address)
            ->where('id', '!=', $this->id)
            ->where('event_time', '>=', now()->subDays(7))
            ->orderBy('event_time', 'desc')
            ->get();
    }

    /**
     * Get similar events from same user
     */
    public function getSimilarEventsFromUser(): \Illuminate\Database\Eloquent\Collection
    {
        if (!$this->user_id) {
            return collect();
        }

        return static::where('user_id', $this->user_id)
            ->where('id', '!=', $this->id)
            ->where('event_time', '>=', now()->subDays(7))
            ->orderBy('event_time', 'desc')
            ->get();
    }

    /**
     * Mark as suspicious
     */
    public function markAsSuspicious(string $reason = null): void
    {
        $this->update([
            'is_suspicious' => true,
            'metadata' => array_merge($this->metadata ?? [], [
                'marked_suspicious_at' => now()->toISOString(),
                'suspicious_reason' => $reason,
            ]),
        ]);
    }

    /**
     * Get security statistics
     */
    public static function getSecurityStats(int $days = 30): array
    {
        $startDate = now()->subDays($days);

        return [
            'total_events' => static::where('event_time', '>=', $startDate)->count(),
            'suspicious_events' => static::where('event_time', '>=', $startDate)
                ->where('is_suspicious', true)->count(),
            'high_risk_events' => static::where('event_time', '>=', $startDate)
                ->whereIn('risk_level', ['high', 'critical'])->count(),
            'failed_logins' => static::where('event_time', '>=', $startDate)
                ->where('event_type', 'failed_login')->count(),
            'unique_ips' => static::where('event_time', '>=', $startDate)
                ->distinct('ip_address')->count('ip_address'),
            'events_by_type' => static::where('event_time', '>=', $startDate)
                ->selectRaw('event_type, COUNT(*) as count')
                ->groupBy('event_type')
                ->pluck('count', 'event_type'),
        ];
    }
}