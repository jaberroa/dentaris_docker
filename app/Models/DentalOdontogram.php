<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalOdontogram extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_treatment_plan_id',
        'tooth_number',
        'tooth_type',
        'surfaces',
        'conditions',
        'procedures',
        'is_missing',
        'needs_attention',
        'notes',
    ];

    protected $casts = [
        'surfaces' => 'array',
        'conditions' => 'array',
        'procedures' => 'array',
        'is_missing' => 'boolean',
        'needs_attention' => 'boolean',
    ];

    public function treatmentPlan()
    {
        return $this->belongsTo(DentalTreatmentPlan::class, 'dental_treatment_plan_id');
    }

    public function procedures()
    {
        return $this->hasMany(DentalProcedure::class);
    }

    public function getToothLabel(): string
    {
        return self::TOOTH_LABELS[$this->tooth_number] ?? "Diente {$this->tooth_number}";
    }

    public const SURFACES = [
        'occlusal' => 'Oclusal/Incisal',
        'mesial' => 'Mesial',
        'distal' => 'Distal',
        'vestibular' => 'Vestibular',
        'lingual' => 'Lingual/Palatina',
    ];

    public const CONDITIONS = [
        'healthy' => 'Sano',
        'caries' => 'Caries',
        'restoration' => 'Restauración',
        'endodontics' => 'Endodoncia',
        'extraction' => 'Exodoncia',
        'implant' => 'Implante',
        'prosthesis' => 'Prótesis',
        'fraktur' => 'Fractura',
        'wear' => 'Desgaste',
        'missing' => 'Ausente',
    ];

    public const TOOTH_LABELS = [
        '11' => 'Incisivo central superior derecho',
        '12' => 'Incisivo lateral superior derecho',
        '13' => 'Canino superior derecho',
        '14' => 'Primer premolar superior derecho',
        '15' => 'Segundo premolar superior derecho',
        '16' => 'Primer molar superior derecho',
        '17' => 'Segundo molar superior derecho',
        '18' => 'Tercer molar superior derecho',
        '21' => 'Incisivo central superior izquierdo',
        '22' => 'Incisivo lateral superior izquierdo',
        '23' => 'Canino superior izquierdo',
        '24' => 'Primer premolar superior izquierdo',
        '25' => 'Segundo premolar superior izquierdo',
        '26' => 'Primer molar superior izquierdo',
        '27' => 'Segundo molar superior izquierdo',
        '28' => 'Tercer molar superior izquierdo',
        '31' => 'Incisivo central inferior izquierdo',
        '32' => 'Incisivo lateral inferior izquierdo',
        '33' => 'Canino inferior izquierdo',
        '34' => 'Primer premolar inferior izquierdo',
        '35' => 'Segundo premolar inferior izquierdo',
        '36' => 'Primer molar inferior izquierdo',
        '37' => 'Segundo molar inferior izquierdo',
        '38' => 'Tercer molar inferior izquierdo',
        '41' => 'Incisivo central inferior derecho',
        '42' => 'Incisivo lateral inferior derecho',
        '43' => 'Canino inferior derecho',
        '44' => 'Primer premolar inferior derecho',
        '45' => 'Segundo premolar inferior derecho',
        '46' => 'Primer molar inferior derecho',
        '47' => 'Segundo molar inferior derecho',
        '48' => 'Tercer molar inferior derecho',
    ];

    public function hasCondition(string $condition): bool
    {
        return (bool) ($this->conditions[$condition] ?? false);
    }

    public function needsAction(): bool
    {
        if ($this->needs_attention || $this->is_missing) {
            return true;
        }

        $attentionConditions = ['caries', 'endodontics', 'extraction', 'implant'];

        foreach ($attentionConditions as $condition) {
            if ($this->hasCondition($condition)) {
                return true;
            }
        }

        return false;
    }
}
