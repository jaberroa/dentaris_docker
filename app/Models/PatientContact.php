<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PatientContact extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'name',
        'relationship',
        'phone',
        'email',
        'address',
        'is_emergency_contact',
        'is_primary_contact',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'is_emergency_contact' => 'boolean',
            'is_primary_contact' => 'boolean',
        ];
    }

    /**
     * Relación con el paciente
     */
    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
