<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_inventory') ?? false;
    }

    public function rules(): array
    {
        $locationId = $this->route('inventoryLocation')?->id;

        return [
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('inventory_locations', 'code')->ignore($locationId)],
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory_locations', 'name')->ignore($locationId)],
            'type' => ['required', Rule::in(['storage', 'clinic', 'warehouse'])],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
