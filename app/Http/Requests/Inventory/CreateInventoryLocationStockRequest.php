<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInventoryLocationStockRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_inventory') ?? false;
    }

    public function rules(): array
    {
        return [
            'inventory_location_id' => [
                'required',
                'integer',
                Rule::exists('inventory_locations', 'id')->where('is_active', true),
            ],
        ];
    }
}
