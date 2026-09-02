<?php

namespace App\Models;

use App\Modules\Clinics\Models\Clinic;
use App\Modules\Clinics\Data\ClinicContext;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_code',
        'first_name',
        'last_name',
        'email',
        'phone',
        'phone_secondary',
        'birth_date',
        'gender',
        'address',
        'city',
        'state',
        'postal_code',
        'country',
        'medical_history',
        'dental_history',
        'allergies',
        'medications',
        'family_history',
        'social_history',
        'blood_type',
        'occupation',
        'marital_status',
        'emergency_contact_name',
        'emergency_contact_phone',
        'emergency_contact_relationship',
        'emergency_contact_address',
        'notes',
        'preferences',
        'consent_marketing',
        'consent_data_processing',
        'is_active',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'birth_date' => 'date',
            'is_active' => 'boolean',
            'consent_marketing' => 'boolean',
            'consent_data_processing' => 'boolean',
            'preferences' => 'array',
        ];
    }

    /**
     * Relación con el usuario que creó el paciente
     */
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Clínica propietaria del registro. La asignación se hace en servidor
     * desde un contexto clínico validado, nunca desde una entrada libre.
     */
    public function clinic(): BelongsTo
    {
        return $this->belongsTo(Clinic::class);
    }

    /**
     * Limita consultas de forma explícita a una clínica; no es un global scope
     * para preservar el comportamiento de las rutas heredadas durante la transición.
     */
    public function scopeForClinic(Builder $query, ClinicContext $context): Builder
    {
        return $query->where('clinic_id', $context->clinicId);
    }

    /**
     * Relación con contactos del paciente
     */
    public function contacts()
    {
        return $this->hasMany(PatientContact::class);
    }

    /**
     * Relación con documentos del paciente
     */
    public function documents()
    {
        return $this->hasMany(PatientDocument::class);
    }

    /**
     * Relación con citas del paciente
     */
    public function appointments()
    {
        return $this->hasMany(Appointment::class);
    }

    /**
     * Relación con registros médicos
     */
    public function medicalRecords()
    {
        return $this->hasMany(MedicalRecord::class);
    }

    /**
     * Relación con planes de tratamiento
     */
    public function treatmentPlans()
    {
        return $this->hasMany(TreatmentPlan::class);
    }

    /**
     * Relación con facturas
     */
    public function invoices()
    {
        return $this->hasMany(Invoice::class);
    }

    /**
     * Relación con pagos
     */
    public function payments()
    {
        return $this->hasMany(Payment::class);
    }

    /**
     * Relación con seguros
     */
    public function insurances()
    {
        return $this->belongsToMany(Insurance::class, 'patient_insurances')
                    ->withPivot(['policy_number', 'coverage_percentage', 'is_primary', 'expiry_date'])
                    ->withTimestamps();
    }

    /**
     * Relación con trabajos de laboratorio
     */
    public function labWorks()
    {
        return $this->hasMany(LabWork::class);
    }

    /**
     * Relación con cotizaciones
     */
    public function quotes()
    {
        return $this->hasMany(Quote::class);
    }

    /**
     * Relación con cuentas por cobrar
     */
    public function accountsReceivable()
    {
        return $this->hasMany(AccountsReceivable::class, 'patient_id', 'id');
    }

    /**
     * Relación con respuestas de encuestas
     */
    public function surveyResponses()
    {
        return $this->hasMany(SurveyResponse::class);
    }

    /**
     * Obtener el nombre completo del paciente
     */
    public function getFullNameAttribute()
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Obtener la edad del paciente
     */
    public function getAgeAttribute()
    {
        return $this->birth_date->age;
    }

    /**
     * Scope para pacientes activos
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope para buscar por nombre
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('first_name', 'like', "%{$search}%")
              ->orWhere('last_name', 'like', "%{$search}%")
              ->orWhere('patient_code', 'like', "%{$search}%")
              ->orWhere('email', 'like', "%{$search}%")
              ->orWhere('phone', 'like', "%{$search}%");
        });
    }

    /**
     * Scope para pacientes por género
     */
    public function scopeByGender($query, $gender)
    {
        return $query->where('gender', $gender);
    }

    /**
     * Scope para pacientes por edad
     */
    public function scopeByAgeRange($query, $minAge, $maxAge)
    {
        $minDate = now()->subYears($maxAge)->format('Y-m-d');
        $maxDate = now()->subYears($minAge)->format('Y-m-d');
        
        return $query->whereBetween('birth_date', [$minDate, $maxDate]);
    }

    /**
     * Scope para pacientes con alergias
     */
    public function scopeWithAllergies($query)
    {
        return $query->whereNotNull('allergies')->where('allergies', '!=', '');
    }

    /**
     * Scope para pacientes con consentimiento de marketing
     */
    public function scopeWithMarketingConsent($query)
    {
        return $query->where('consent_marketing', true);
    }

    /**
     * Obtener la dirección completa
     */
    public function getFullAddressAttribute()
    {
        $address = collect([
            $this->address,
            $this->city,
            $this->state,
            $this->postal_code,
            $this->country
        ])->filter()->implode(', ');
        
        return $address ?: 'No especificada';
    }

    /**
     * Obtener información de contacto de emergencia
     */
    public function getEmergencyContactInfoAttribute()
    {
        if (!$this->emergency_contact_name) {
            return null;
        }
        
        return [
            'name' => $this->emergency_contact_name,
            'phone' => $this->emergency_contact_phone,
            'relationship' => $this->emergency_contact_relationship,
            'address' => $this->emergency_contact_address,
        ];
    }

    /**
     * Verificar si el paciente tiene alergias
     */
    public function hasAllergies()
    {
        return !empty($this->allergies);
    }

    /**
     * Verificar si el paciente está activo
     */
    public function isActive()
    {
        return $this->is_active;
    }

    /**
     * Obtener estadísticas del paciente
     */
    public function getStatsAttribute()
    {
        return [
            'total_appointments' => $this->appointments()->count(),
            'completed_appointments' => $this->appointments()->whereHas('status', function($query) {
                $query->where('name', 'completed');
            })->count(),
            'total_invoices' => $this->invoices()->count(),
            'total_payments' => $this->payments()->sum('amount'),
            'pending_balance' => $this->accountsReceivable()->sum('balance_due') ?? 0,
            'treatment_plans' => $this->treatmentPlans()->count(),
        ];
    }

    /**
     * Generar código único de paciente
     * Formato: Primera letra del nombre + Primera letra del apellido + Auto-increment (00001-99999)
     */
    public static function generateUniquePatientCode($firstName, $lastName, $patientId = null)
    {
        // Validar y limpiar nombres
        $firstName = trim($firstName) ?: 'X';
        $lastName = trim($lastName) ?: 'X';
        
        // Obtener primeras letras en mayúsculas
        $firstLetter = strtoupper(substr($firstName, 0, 1));
        $lastLetter = strtoupper(substr($lastName, 0, 1));
        
        // Obtener el siguiente número auto-increment para esta combinación de iniciales
        $nextNumber = self::getNextIncrementNumber($firstLetter . $lastLetter);
        
        return $firstLetter . $lastLetter . str_pad($nextNumber, 5, '0', STR_PAD_LEFT);
    }
    
    /**
     * Obtener el siguiente número auto-increment para una combinación de iniciales
     */
    private static function getNextIncrementNumber($initials)
    {
        // Buscar el número más alto existente para estas iniciales
        $existingCodes = self::where('patient_code', 'LIKE', $initials . '%')
            ->pluck('patient_code')
            ->toArray();
        
        $maxNumber = 0;
        
        foreach ($existingCodes as $code) {
            // Extraer el número del código (últimos 5 dígitos)
            $number = (int) substr($code, 2);
            if ($number > $maxNumber) {
                $maxNumber = $number;
            }
        }
        
        // Retornar el siguiente número (empezando en 1)
        return $maxNumber + 1;
    }

    /**
     * Obtener el código de paciente formateado para mostrar
     */
    public function getDisplayCodeAttribute()
    {
        // Si no tiene patient_code o es del formato antiguo, generar uno nuevo
        if (empty($this->patient_code) || strpos($this->patient_code, 'PAT-') === 0) {
            return $this->generateDisplayCode();
        }
        
        return $this->patient_code;
    }

    /**
     * Generar código para mostrar basado en el ID actual
     */
    private function generateDisplayCode()
    {
        $firstName = $this->first_name ?: 'X';
        $lastName = $this->last_name ?: 'X';
        
        $firstLetter = strtoupper(substr($firstName, 0, 1));
        $lastLetter = strtoupper(substr($lastName, 0, 1));
        $idSuffix = str_pad($this->id % 100000, 5, '0', STR_PAD_LEFT);
        
        return $firstLetter . $lastLetter . $idSuffix;
    }
}
