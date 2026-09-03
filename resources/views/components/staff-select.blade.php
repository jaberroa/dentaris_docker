@props([
    'name' => 'staff_id',
    'id' => 'staff_id',
    'label' => 'Profesional',
    'required' => true,
    'selected' => null,
    'staff' => null,
    'error' => null
])

@php
    if ($staff === null) {
        $clinicContext = request()->attributes->get(\App\Modules\Clinics\Data\ClinicContext::class);
        $staff = $clinicContext instanceof \App\Modules\Clinics\Data\ClinicContext
            ? \App\Models\Staff::forClinic($clinicContext)
                ->with('user')
                ->where('is_active', true)
                ->get()
            : collect();
    }
@endphp

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <select class="form-select @error($name) is-invalid @enderror" id="{{ $id }}" name="{{ $name }}" @if($required) required @endif>
        <option value="">Seleccionar profesional</option>
        @foreach($staff as $member)
            <option value="{{ $member->id }}" {{ (old($name, $selected) == $member->id) ? 'selected' : '' }}>
                Dr(a). {{ $member->user->first_name }} {{ $member->user->last_name }} - {{ $member->specialty }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>


