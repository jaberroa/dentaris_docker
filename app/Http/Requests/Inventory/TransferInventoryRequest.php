<?php

namespace App\Http\Requests\Inventory;

use Illuminate\Foundation\Http\FormRequest;

class TransferInventoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('manage_inventory') ?? false;
    }

    public function rules(): array
    {
        return [
            'inventory_id' => ['required', 'integer', 'exists:inventory,id'],
            'destination_inventory_id' => ['required', 'integer', 'different:inventory_id', 'exists:inventory,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
