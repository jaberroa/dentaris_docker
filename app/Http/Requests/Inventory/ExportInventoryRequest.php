<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Concerns\UsesClinicContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExportInventoryRequest extends FormRequest
{
    use UsesClinicContext;

    public function authorize(): bool
    {
        return $this->hasClinicPermission('export_inventory');
    }

    public function rules(): array
    {
        return [
            'format' => ['nullable', 'string', 'in:csv,xlsx,pdf'],
            'clinic_id' => ['prohibited'],
            'inventory_location_id' => ['nullable', 'integer', Rule::exists('inventory_locations', 'id')->where('clinic_id', $this->clinicContext()?->clinicId ?? 0)],
            'category' => ['sometimes', 'string', 'max:100'],
            'stock_level' => ['nullable', 'string', 'in:low,out,normal'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
