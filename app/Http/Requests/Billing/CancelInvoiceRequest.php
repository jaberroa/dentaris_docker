<?php

namespace App\Http\Requests\Billing;

use Illuminate\Foundation\Http\FormRequest;

class CancelInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('delete', $this->route('invoice')) ?? false;
    }

    public function rules(): array
    {
        return [
            'clinic_id' => ['prohibited'],
            'reason' => ['required', 'string', 'max:1000'],
        ];
    }
}
