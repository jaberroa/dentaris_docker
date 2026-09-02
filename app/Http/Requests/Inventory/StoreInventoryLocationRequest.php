<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInventoryLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_inventory') ?? false;
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('inventory_locations', 'code')],
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory_locations', 'name')],
            'type' => ['required', Rule::in(['storage', 'clinic', 'warehouse'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
