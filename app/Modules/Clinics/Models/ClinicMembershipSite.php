<?php

namespace App\Modules\Clinics\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClinicMembershipSite extends Model
{
    use HasFactory;

    protected $fillable = [
        'clinic_membership_id',
        'clinic_site_id',
        'clinic_id',
    ];

    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    public function membership(): BelongsTo
    {
        return $this->belongsTo(ClinicMembership::class, 'clinic_membership_id');
    }

    public function site(): BelongsTo
    {
        return $this->belongsTo(ClinicSite::class, 'clinic_site_id');
    }
}
