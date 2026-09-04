<?php

namespace App\Http\Requests\Billing;

use App\Http\Requests\Concerns\UsesClinicContext;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    use UsesClinicContext;

    public function authorize(): bool
    {
        return $this->hasClinicPermission('manage_payments');
    }

    public function rules(): array
    {
        $clinicId = $this->clinicContext()?->clinicId ?? 0;

        return [
            'clinic_id' => ['prohibited'],
            'patient_id' => ['prohibited'],
            'processed_by' => ['prohibited'],
            'payment_number' => ['prohibited'],
            'status' => ['prohibited'],
            'invoice_id' => [
                'required',
                'integer',
                Rule::exists('invoices', 'id')->where(fn ($query) => $query
                    ->where('clinic_id', $clinicId)
                    ->whereNotIn('status', ['paid', 'cancelled'])),
            ],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', Rule::in(['cash', 'card', 'transfer', 'check', 'other'])],
            'payment_date' => ['required', 'date'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
