<?php

namespace App\Modules\Clinics\Models;

use App\Models\Patient;
use App\Models\Staff;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Clinic extends Model
{
    use HasFactory;

    protected $fillable = [
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

    public function sites(): HasMany
    {
        return $this->hasMany(ClinicSite::class);
    }

    public function memberships(): HasMany
    {
        return $this->hasMany(ClinicMembership::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(ClinicSetting::class);
    }

    public function patients(): HasMany
    {
        return $this->hasMany(Patient::class);
    }

    public function staff(): HasMany
    {
        return $this->hasMany(Staff::class);
    }
}
