<?php

namespace App\Http\Requests\Inventory;

use App\Http\Requests\Concerns\UsesClinicContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateInventoryLocationStockRequest extends FormRequest
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
            'inventory_location_id' => [
                'required',
                'integer',
                Rule::exists('inventory_locations', 'id')->where(fn ($query) => $query
                    ->where('clinic_id', $this->clinicContext()?->clinicId ?? 0)
                    ->where('is_active', true)),
            ],
        ];
    }
}
