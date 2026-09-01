<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DentalPeriodontogram extends Model
{
    use HasFactory;

    protected $fillable = [
        'dental_treatment_plan_id',
        'tooth_number',
        'measurement_points',
        'pocket_depth',
        'bleeding',
        'mobility',
        'gingival_recession',
        'clinical_attachment_loss',
        'furcation_involvement',
        'suppuration',
        'notes',
    ];

    protected $casts = [
        'measurement_points' => 'array',
        'pocket_depth' => 'array',
        'bleeding' => 'array',
        'gingival_recession' => 'decimal:2',
        'clinical_attachment_loss' => 'decimal:2',
        'furcation_involvement' => 'boolean',
        'suppuration' => 'boolean',
    ];

    public function treatmentPlan()
    {
        return $this->belongsTo(DentalTreatmentPlan::class, 'dental_treatment_plan_id');
    }

    public function procedures()
    {
        return $this->hasMany(DentalProcedure::class);
    }

    public const MEASUREMENT_POINTS = [
        'mesiobuccal' => 'Mesio-vestibular',
        'buccal' => 'Vestibular',
        'distobuccal' => 'Disto-vestibular',
        'mesiolingual' => 'Mesio-lingual',
        'lingual' => 'Lingual',
        'distolingual' => 'Disto-lingual',
    ];

    public function getAveragePocketDepth(): float
    {
        $depths = array_filter($this->pocket_depth ?? []);

        if (empty($depths)) {
            return 0;
        }

        return round(array_sum($depths) / count($depths), 1);
    }

    public function getBleedingPercentage(): float
    {
        $points = $this->bleeding ?? [];
        $total = count($points);

        if ($total === 0) {
            return 0;
        }

        $positive = count(array_filter($points));

        return round(($positive / $total) * 100, 1);
    }

    public function hasCriticalPocket(): bool
    {
        foreach (($this->pocket_depth ?? []) as $depth) {
            if ($depth >= 5) {
                return true;
            }
        }

        return false;
    }

    public function NeedsAdvancedTherapy(): bool
    {
        return $this->hasCriticalPocket() || $this->clinical_attachment_loss >= 5 || $this->furcation_involvement;
    }
}
