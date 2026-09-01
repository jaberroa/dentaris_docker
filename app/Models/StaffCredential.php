<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class StaffCredential extends Model
{
    use HasFactory;

    protected $fillable = [
        'staff_id',
        'credential_type',
        'credential_name',
        'issuing_authority',
        'credential_number',
        'issue_date',
        'expiry_date',
        'file_path',
        'description',
        'is_verified',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'issue_date' => 'date',
            'expiry_date' => 'date',
            'is_verified' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Relación con el staff
     */
    public function staff()
    {
        return $this->belongsTo(Staff::class);
    }

    /**
     * Verificar si la credencial ha expirado
     */
    public function isExpired()
    {
        return $this->expiry_date && $this->expiry_date->isPast();
    }

    /**
     * Verificar si la credencial está próxima a expirar (30 días)
     */
    public function isExpiringSoon()
    {
        return $this->expiry_date && $this->expiry_date->isFuture() && $this->expiry_date->diffInDays() <= 30;
    }

    /**
     * Scope para credenciales activas
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para credenciales verificadas
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope para credenciales expiradas
     */
    public function scopeExpired($query)
    {
        return $query->where('expiry_date', '<', now());
    }
}
