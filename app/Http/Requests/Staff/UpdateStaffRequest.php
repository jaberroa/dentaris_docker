<?php

namespace App\Http\Requests\Staff;

use App\Models\Staff;
use Illuminate\Validation\Rule;

class UpdateStaffRequest extends StaffRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        /** @var Staff $staff */
        $staff = $this->route('staff');

        return array_merge($this->commonRules(), [
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($staff->user_id),
            ],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')->where('is_active', true),
            ],
            'role' => ['prohibited'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function userData(): array
    {
        return $this->identityData(includePassword: true);
    }

    /**
     * @return array<string, mixed>
     */
    public function staffData(): array
    {
        return array_merge($this->clinicalData(), [
            // La vista existente usa checkbox: ausencia equivale a desactivado.
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function roleId(): int
    {
        return (int) $this->validated('role_id');
    }
}
