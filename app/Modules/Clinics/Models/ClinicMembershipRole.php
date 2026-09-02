<?php

namespace App\Modules\Clinics\Models;

use App\Models\Role;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicMembershipRole extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_membership_id',
        'role_id',
    ];

    public function membership(): BelongsTo
    {
        return $this->belongsTo(ClinicMembership::class, 'clinic_membership_id');
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }
}
