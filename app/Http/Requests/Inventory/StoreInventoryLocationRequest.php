<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Concerns\UsesClinicContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryLocationRequest extends FormRequest
{
    use UsesClinicContext;

    public function authorize(): bool
    {
        return $this->hasClinicPermission('manage_inventory');
    }

    public function rules(): array
    {
        return [
            'clinic_id' => ['prohibited'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('inventory_locations', 'code')],
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory_locations', 'name')],
            'type' => ['required', Rule::in(['storage', 'clinic', 'warehouse'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
