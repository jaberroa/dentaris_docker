<?php

namespace App\Modules\Clinics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'code',
        'name',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function membershipSites(): HasMany
    {
        return $this->hasMany(ClinicMembershipSite::class);
    }

    public function memberships(): BelongsToMany
    {
        return $this->belongsToMany(
            ClinicMembership::class,
            'clinic_membership_sites'
        )->withPivot('id')->withTimestamps();
    }
}
