<?php

namespace App\Modules\Clinics\Models;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ClinicMembership extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_id',
        'user_id',
        'status',
        'activated_at',
        'suspended_at',
    ];

    protected function casts(): array
    {
        return [
            'activated_at' => 'datetime',
            'suspended_at' => 'datetime',
        ];
    }

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(ClinicMembershipRole::class);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'clinic_membership_roles'
        )->withPivot('id')->withTimestamps();
    }

    public function siteAssignments(): HasMany
    {
        return $this->hasMany(ClinicMembershipSite::class);
    }

    public function sites(): BelongsToMany
    {
        return $this->belongsToMany(
            ClinicSite::class,
            'clinic_membership_sites'
        )->withPivot('id')->withTimestamps();
    }
}
