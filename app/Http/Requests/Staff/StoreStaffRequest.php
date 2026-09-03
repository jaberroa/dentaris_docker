<?php

namespace App\Http\Requests\Staff;

use App\Models\Role;
use Illuminate\Validation\Rule;

class StoreStaffRequest extends StaffRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return array_merge($this->commonRules(), [
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'role' => [
                'required',
                'string',
                Rule::exists('roles', 'name')->where('is_active', true),
            ],
            'role_id' => ['prohibited'],
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
            'is_active' => $this->has('is_active') ? $this->boolean('is_active') : true,
            'is_available' => $this->has('is_available') ? $this->boolean('is_available') : true,
        ]);
    }

    public function roleId(): int
    {
        return (int) Role::query()
            ->where('name', $this->validated('role'))
            ->where('is_active', true)
            ->valueOrFail('id');
    }
}
