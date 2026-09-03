<?php

namespace App\Exports;

use App\Models\Staff;
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

class StaffExport implements FromCollection, WithHeadings, WithMapping, WithStyles, WithColumnWidths, WithEvents
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
        $query = Staff::forClinic($this->clinicContext)->with(['user.roles']);

        if (!empty($this->filters['search'])) {
            $search = $this->filters['search'];
            $query->where(function ($query) use ($search) {
                $query->whereHas('user', function ($userQuery) use ($search) {
                    $userQuery->where(function ($identityQuery) use ($search) {
                        $identityQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
                })->orWhere('specialty', 'like', "%{$search}%");
            });
        }

        if (!empty($this->filters['specialty'])) {
            $query->where('specialty', $this->filters['specialty']);
        }

        if (!empty($this->filters['is_active'])) {
            $query->where('is_active', $this->filters['is_active']);
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
            'Nombre',
            'Email',
            'Teléfono',
            'Especialidad',
            'Rol',
            'Estado',
            'Fecha de Registro',
        ];
    }

    /**
     * @param Staff $staff
     * @return array
     */
    public function map($staff): array
    {
        return [
            $staff->employee_id,
            $staff->user->name ?? 'N/A',
            $staff->user->email ?? 'N/A',
            $staff->user->phone ?? 'N/A',
            $staff->specialty ?? 'General',
            $staff->user->roles->first()->display_name ?? 'Sin rol',
            $staff->is_active ? 'Activo' : 'Inactivo',
            $staff->created_at->format('d/m/Y H:i'),
        ];
    }

    /**
     * @param Worksheet $sheet
     * @return array
     */
    public function styles(Worksheet $sheet)
    {
        return [
            // Estilo del encabezado
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => [
                        'rgb' => '0d6efd',
                    ],
                ],
                'font' => [
                    'color' => [
                        'rgb' => 'FFFFFF',
                    ],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ],
        ];
    }

    /**
     * @return array
     */
    public function columnWidths(): array
    {
        return [
            'A' => 15, // Código
            'B' => 25, // Nombre
            'C' => 30, // Email
            'D' => 15, // Teléfono
            'E' => 20, // Especialidad
            'F' => 15, // Rol
            'G' => 12, // Estado
            'H' => 18, // Fecha de Registro
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
                            'color' => ['rgb' => '000000'],
                        ],
                    ],
                ]);
                
                // Centrar texto en columnas específicas
                $sheet->getStyle('A:A')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('G:G')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle('H:H')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            },
        ];
    }

    private function contextFromRequest(): ClinicContext
    {
        $context = request()->attributes->get(ClinicContext::class);

        if (!$context instanceof ClinicContext) {
            throw new LogicException('A validated clinic context is required to export staff.');
        }

        return $context;
    }
}





