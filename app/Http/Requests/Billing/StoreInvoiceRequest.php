<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\Concerns\UsesClinicContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreInvoiceRequest extends FormRequest
{
    use UsesClinicContext;

    public function authorize(): bool
    {
        return $this->hasClinicPermission('manage_billing');
    }

    public function rules(): array
    {
        $clinicId = $this->clinicContext()?->clinicId ?? 0;

        return [
            'clinic_id' => ['prohibited'],
            'patient_id' => [
                'required',
                'integer',
                Rule::exists('patients', 'id')->where(fn ($query) => $query
                    ->where('clinic_id', $clinicId)
                    ->where('is_active', true)),
            ],
            'staff_id' => [
                'required',
                'integer',
                Rule::exists('staff', 'id')->where(fn ($query) => $query
                    ->where('clinic_id', $clinicId)
                    ->where('is_active', true)),
            ],
            'invoice_date' => ['required', 'date'],
            'due_date' => ['nullable', 'date', 'after_or_equal:invoice_date'],
            'tax_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:5000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.cdt_catalog_id' => ['required', 'integer', 'exists:cdt_catalog,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
        ];
    }
}
