<?php

namespace Database\Factories;

use App\Models\MedicalRecord;
use App\Models\Patient;
use App\Models\Staff;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MedicalRecord>
 */
class MedicalRecordFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = MedicalRecord::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $recordTypes = ['consulta', 'tratamiento', 'seguimiento', 'urgencia'];
        $recordType = fake()->randomElement($recordTypes);
        
        // Motivos de consulta realistas
        $chiefComplaints = [
            'Dolor dental en molar superior derecho',
            'Sangrado de encías al cepillarse',
            'Sensibilidad dental al frío y calor',
            'Diente fracturado por accidente',
            'Mal aliento persistente',
            'Dolor al masticar',
            'Encías inflamadas',
            'Diente flojo',
            'Manchas en los dientes',
            'Dolor de muela constante',
            'Revisión de rutina',
            'Limpieza dental',
            'Consulta por ortodoncia',
            'Dolor en la mandíbula',
            'Problema con prótesis dental'
        ];
        
        // Enfermedades actuales
        $presentIllness = [
            'El dolor comenzó hace 3 días, es constante y se intensifica con el frío',
            'El sangrado se presenta desde hace una semana, especialmente en la mañana',
            'La sensibilidad apareció después de un tratamiento de blanqueamiento',
            'La fractura ocurrió durante una caída, no hay dolor intenso',
            'El mal aliento persiste a pesar de una buena higiene oral',
            'El dolor al masticar se presenta solo en el lado izquierdo',
            'Las encías están rojas e inflamadas, especialmente en la parte posterior',
            'El diente se mueve ligeramente, no hay dolor',
            'Las manchas aparecieron gradualmente en los últimos meses',
            'El dolor es pulsátil y se irradia hacia el oído'
        ];
        
        // Exámenes clínicos
        $clinicalExaminations = [
            'Paciente en buen estado general. Examen oral: encías ligeramente inflamadas en cuadrante superior derecho',
            'Estado general estable. Cavidad oral: presencia de placa bacteriana, encías con signos de gingivitis',
            'Paciente colaborador. Examen dental: sensibilidad positiva en diente 16, sin signos de pulpitis',
            'Estado general bueno. Examen oral: fractura coronal en diente 21, sin exposición pulpar',
            'Paciente asintomático. Examen dental: halitosis evidente, presencia de cálculo dental',
            'Estado general estable. Examen oral: dolor a la palpación en ATM izquierda',
            'Paciente en buen estado. Examen dental: movilidad grado I en diente 36',
            'Estado general bueno. Examen oral: manchas intrínsecas en incisivos superiores',
            'Paciente colaborador. Examen dental: dolor a la percusión en diente 26',
            'Estado general estable. Examen oral: prótesis parcial con ajuste deficiente'
        ];
        
        // Exámenes orales
        $oralExaminations = [
            'Dentición mixta, higiene oral regular, presencia de placa en molares',
            'Dentición completa, encías con signos de inflamación, cálculo dental presente',
            'Ausencia de diente 18, resto de dentición en buen estado',
            'Prótesis parcial superior, ajuste adecuado, tejidos blandos sin lesiones',
            'Ortodoncia fija en tratamiento, higiene oral adecuada',
            'Dentición natural, desgaste oclusal leve, sin signos de bruxismo',
            'Implante en posición 16, integración adecuada, tejidos perimplantarios sanos',
            'Dentición completa, restauraciones en buen estado, sin caries activas',
            'Ausencia de dientes posteriores, sobrecarga en dientes anteriores',
            'Dentición mixta, apiñamiento leve en sector anterior inferior'
        ];
        
        // Impresiones diagnósticas
        $diagnosticImpressions = [
            'Caries dental en diente 16, posible pulpitis reversible',
            'Gingivitis crónica, requiere limpieza dental profesional',
            'Sensibilidad dental post-tratamiento, manejo conservador',
            'Fractura coronal sin compromiso pulpar, restauración directa',
            'Halitosis por acumulación de placa bacteriana',
            'Disfunción temporomandibular, manejo conservador',
            'Movilidad dental por pérdida ósea, evaluación periodontal',
            'Manchas intrínsecas, opciones de tratamiento estético',
            'Pulpitis irreversible en diente 26, requiere endodoncia',
            'Ajuste deficiente de prótesis, rebase o nueva prótesis'
        ];
        
        // Planes de tratamiento
        $treatmentPlans = [
            '1. Limpieza dental profesional\n2. Restauración con resina en diente 16\n3. Control en 1 mes',
            '1. Detartraje y alisado radicular\n2. Instrucciones de higiene oral\n3. Control en 3 meses',
            '1. Aplicación de flúor tópico\n2. Uso de pasta desensibilizante\n3. Control en 2 semanas',
            '1. Restauración con resina compuesta\n2. Control de vitalidad pulpar\n3. Control en 1 mes',
            '1. Limpieza dental profunda\n2. Técnica de cepillado\n3. Control en 1 mes',
            '1. Férula oclusal nocturna\n2. Ejercicios de relajación\n3. Control en 1 mes',
            '1. Evaluación periodontal completa\n2. Tratamiento periodontal si es necesario\n3. Control en 1 mes',
            '1. Blanqueamiento dental\n2. Carillas de porcelana (opcional)\n3. Control estético',
            '1. Endodoncia en diente 26\n2. Corona post-endodoncia\n3. Control en 1 mes',
            '1. Rebasing de prótesis\n2. Ajuste oclusal\n3. Control en 1 mes'
        ];
        
        // Recomendaciones
        $recommendations = [
            'Mantener higiene oral adecuada, cepillado 3 veces al día',
            'Uso de hilo dental diario, enjuague bucal con flúor',
            'Evitar alimentos muy fríos o calientes por 1 semana',
            'No masticar alimentos duros en el lado afectado',
            'Cepillado suave de encías, masaje gingival',
            'Aplicar calor húmedo en ATM, evitar apertura excesiva',
            'Dieta blanda, evitar alimentos pegajosos',
            'Uso de pasta blanqueadora, evitar alimentos que manchen',
            'No masticar en el lado tratado por 24 horas',
            'Limpieza diaria de prótesis, remoción nocturna'
        ];
        
        return [
            'patient_id' => Patient::factory(),
            'staff_id' => Staff::factory(),
            'record_type' => $recordType,
            'chief_complaint' => fake()->randomElement($chiefComplaints),
            'present_illness' => fake()->randomElement($presentIllness),
            'medical_history' => fake()->optional(0.6)->sentence(),
            'dental_history' => fake()->optional(0.7)->sentence(),
            'family_history' => fake()->optional(0.3)->sentence(),
            'social_history' => fake()->optional(0.2)->sentence(),
            'clinical_examination' => fake()->randomElement($clinicalExaminations),
            'vital_signs' => fake()->optional(0.4)->randomElement([
                'TA: 120/80 mmHg, FC: 72 lpm, Temp: 36.5°C',
                'TA: 110/70 mmHg, FC: 68 lpm, Temp: 36.8°C',
                'TA: 130/85 mmHg, FC: 75 lpm, Temp: 36.2°C',
                'TA: 125/80 mmHg, FC: 70 lpm, Temp: 36.6°C'
            ]),
            'oral_examination' => fake()->randomElement($oralExaminations),
            'diagnostic_impression' => fake()->randomElement($diagnosticImpressions),
            'treatment_plan' => fake()->randomElement($treatmentPlans),
            'recommendations' => fake()->randomElement($recommendations),
            'notes' => fake()->optional(0.3)->sentence(),
            'is_confidential' => fake()->boolean(10), // 10% confidenciales
            'created_by' => User::factory(),
        ];
    }

    /**
     * Indicate that the record is a consultation.
     */
    public function consultation(): static
    {
        return $this->state(fn (array $attributes) => [
            'record_type' => 'consulta',
        ]);
    }

    /**
     * Indicate that the record is a treatment.
     */
    public function treatment(): static
    {
        return $this->state(fn (array $attributes) => [
            'record_type' => 'tratamiento',
        ]);
    }

    /**
     * Indicate that the record is a follow-up.
     */
    public function followUp(): static
    {
        return $this->state(fn (array $attributes) => [
            'record_type' => 'seguimiento',
        ]);
    }

    /**
     * Indicate that the record is an emergency.
     */
    public function emergency(): static
    {
        return $this->state(fn (array $attributes) => [
            'record_type' => 'urgencia',
        ]);
    }

    /**
     * Indicate that the record is confidential.
     */
    public function confidential(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_confidential' => true,
        ]);
    }
}