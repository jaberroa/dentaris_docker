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
            'format' => ['sometimes', 'string', 'in:csv,xlsx,pdf'],
            'location' => ['sometimes', 'string', 'max:255'],
            'category' => ['sometimes', 'string', 'max:100'],
            'limit' => ['sometimes', 'integer', 'min:1', 'max:10000'],
        ];
    }
}
