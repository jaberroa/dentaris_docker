<?php

namespace App\Http\Requests\Inventory;

use App\Models\Inventory;
use App\Http\Requests\Concerns\UsesClinicContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInventoryAdjustmentRequest extends FormRequest
{
    use UsesClinicContext;

    public function authorize(): bool
    {
        $inventory = $this->route('inventory');

        return $this->hasClinicPermission('adjust_inventory')
            && $inventory instanceof Inventory
            && (int) $inventory->clinic_id === $this->clinicContext()?->clinicId
            && ($this->user()?->can('adjust', $inventory) ?? false);
    }

    public function rules(): array
    {
        return [
            'clinic_id' => ['prohibited'],
            'inventory_id' => ['required', 'integer', Rule::exists('inventory', 'id')->where('clinic_id', $this->clinicContext()?->clinicId ?? 0)],
            'product_id' => ['required', 'integer', 'exists:products,id'],
            'type' => ['required', Rule::in(['adjustment', 'restock', 'consumption'])],
            'quantity' => ['required', 'integer', 'min:1'],
            'reason' => ['required', 'string', 'max:255'],
        ];
    }
}
