<?php

namespace App\Exports;

use App\Repositories\InventoryExportRepository;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class InventoryExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private readonly array $filters) {}

    public function collection()
    {
        return app(InventoryExportRepository::class)->query($this->filters)->orderBy('id')->limit($this->filters['limit'] ?? 10000)->get();
    }

    public function headings(): array
    {
        return ['Código', 'Producto', 'Categoría', 'Ubicación', 'Stock actual', 'Reservado', 'Disponible', 'Costo promedio', 'Valor total', 'Estado'];
    }

    public function map($inventory): array
    {
        $available = (int) $inventory->available_stock;
        $minimum = (int) ($inventory->product->minimum_stock ?? 0);
        return [$inventory->product->product_code ?? '', $inventory->product->name ?? '', $inventory->product->category ?? '', $inventory->inventoryLocation->name ?? $inventory->location ?? 'Sin asignar', $inventory->current_stock, $inventory->reserved_stock, $available, $inventory->average_cost, $inventory->current_stock * $inventory->average_cost, $available <= 0 ? 'Agotado' : ($available <= $minimum ? 'Stock bajo' : 'Disponible')];
    }
}
