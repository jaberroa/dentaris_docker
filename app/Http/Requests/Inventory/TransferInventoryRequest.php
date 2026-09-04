<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Concerns\UsesClinicContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TransferInventoryRequest extends FormRequest
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
            'inventory_id' => ['required', 'integer', Rule::exists('inventory', 'id')->where('clinic_id', $this->clinicContext()?->clinicId ?? 0)],
            'destination_inventory_id' => ['required', 'integer', 'different:inventory_id', Rule::exists('inventory', 'id')->where('clinic_id', $this->clinicContext()?->clinicId ?? 0)],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
