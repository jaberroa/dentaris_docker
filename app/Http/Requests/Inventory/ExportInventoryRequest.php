<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class ExportInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('export_inventory') ?? false;
    }

    public function rules(): array
    {
        return [
            'format' => ['nullable', 'string', 'in:csv,xlsx,pdf'],
            'inventory_location_id' => ['nullable', 'integer', 'exists:inventory_locations,id'],
            'category' => ['sometimes', 'string', 'max:100'],
            'stock_level' => ['nullable', 'string', 'in:low,out,normal'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
