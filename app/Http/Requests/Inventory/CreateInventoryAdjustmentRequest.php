<?php

namespace App\Http\Requests\Inventory;

use App\Models\Inventory;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInventoryAdjustmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $inventory = $this->route('inventory');

        return $inventory instanceof Inventory
            && ($this->user()?->can('adjust', $inventory) ?? false);
    }

    public function rules(): array
    {
        return [
            'inventory_id' => ['required', 'integer', 'exists:inventory,id'],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', Rule::in(['adjustment', 'restock', 'consumption'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
