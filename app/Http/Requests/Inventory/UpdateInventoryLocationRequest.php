<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Concerns\UsesClinicContext;
use App\Models\InventoryLocation;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInventoryLocationRequest extends FormRequest
{
    use UsesClinicContext;

    public function authorize(): bool
    {
        $location = $this->route('inventoryLocation');

        return $location instanceof InventoryLocation
            && (int) $location->clinic_id === $this->clinicContext()?->clinicId
            && $this->hasClinicPermission('manage_inventory');
    }

    public function rules(): array
    {
        $locationId = $this->route('inventoryLocation')?->id;

        return [
            'clinic_id' => ['prohibited'],
            'code' => ['required', 'string', 'max:50', 'alpha_dash', Rule::unique('inventory_locations', 'code')->ignore($locationId)],
            'name' => ['required', 'string', 'max:255', Rule::unique('inventory_locations', 'name')->ignore($locationId)],
            'type' => ['required', Rule::in(['storage', 'clinic', 'warehouse'])],
            'is_active' => ['required', 'boolean'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
