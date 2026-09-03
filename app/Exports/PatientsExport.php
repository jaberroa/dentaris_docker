<?php

namespace App\Exports;

use App\Models\Patient;
use App\Modules\Clinics\Data\ClinicContext;
use LogicException;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PatientsExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
{
    protected array $filters;

    private ClinicContext $clinicContext;

    public function __construct(array $filters = [], ?ClinicContext $clinicContext = null)
    {
        $this->filters = $filters;
        $this->clinicContext = $clinicContext ?? $this->contextFromRequest();
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $query = Patient::forClinic($this->clinicContext)->with(['creator']);

        // Aplicar filtros si existen
        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                  ->orWhere('last_name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('patient_code', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['gender'])) {
            $query->where('gender', $this->filters['gender']);
        }

        if (!empty($this->filters['is_active'])) {
            $query->where('is_active', $this->filters['is_active']);
        }

        if (!empty($this->filters['date_from'])) {
            $query->whereDate('created_at', '>=', $this->filters['date_from']);
        }

        if (!empty($this->filters['date_to'])) {
            $query->whereDate('created_at', '<=', $this->filters['date_to']);
        }

        return $query->orderBy('created_at', 'desc')->get();
    }

    /**
     * @return array
     */
    public function headings(): array
    {
        return [
            'Código',
            'Nombre Completo',
            'Email',
            'Teléfono',
            'Fecha de Nacimiento',
            'Edad',
            'Género',
            'Ciudad',
            'Estado',
            'Tipo de Sangre',
            'Ocupación',
            'Estado Civil',
            'Contacto de Emergencia',
            'Teléfono de Emergencia',
            'Estado',
            'Fecha de Registro',
            'Registrado por'
        ];
    }

    /**
     * @param Patient $patient
     * @return array
     */
    public function map($patient): array
    {
        return [
            $patient->patient_code,
            $patient->first_name . ' ' . $patient->last_name,
            $patient->email,
            $patient->phone,
            $patient->birth_date ? $patient->birth_date->format('d/m/Y') : '',
            $patient->birth_date ? $patient->birth_date->age . ' años' : '',
            ucfirst($patient->gender),
            $patient->city,
            $patient->state,
            $patient->blood_type,
            $patient->occupation,
            ucfirst($patient->marital_status),
            $patient->emergency_contact_name,
            $patient->emergency_contact_phone,
            $patient->is_active ? 'Activo' : 'Inactivo',
            $patient->created_at->format('d/m/Y H:i'),
            $patient->creator->name ?? 'N/A'
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo para el encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF']
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '0D6EFD']
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER
                ]
            ]
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 12, // Código
            'B' => 25, // Nombre Completo
            'C' => 30, // Email
            'D' => 15, // Teléfono
            'E' => 15, // Fecha de Nacimiento
            'F' => 10, // Edad
            'G' => 10, // Género
            'H' => 20, // Ciudad
            'I' => 15, // Estado
            'J' => 12, // Tipo de Sangre
            'K' => 20, // Ocupación
            'L' => 15, // Estado Civil
            'M' => 25, // Contacto de Emergencia
            'N' => 18, // Teléfono de Emergencia
            'O' => 10, // Estado
            'P' => 18, // Fecha de Registro
            'Q' => 20, // Registrado por
        ];
    }

    /**
     * @return array
     */
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                
                // Aplicar bordes a todas las celdas con datos
                $lastRow = $sheet->getHighestRow();
                $lastColumn = $sheet->getHighestColumn();
                
                $sheet->getStyle('A1:' . $lastColumn . $lastRow)->applyFromArray([
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'CCCCCC']
                        ]
                    ]
                ]);

                // Centrar texto en columnas específicas
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('F:F')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('J:J')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('O:O')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('P:P')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            }
        ];
    }

    private function contextFromRequest(): ClinicContext
    {
        $context = request()->attributes->get(ClinicContext::class);

        if (!$context instanceof ClinicContext) {
            throw new LogicException('A validated clinic context is required to export patients.');
        }

        return $context;
    }
}
