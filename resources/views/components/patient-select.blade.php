@props([
    'name' => 'patient_id',
    'id' => 'patient_id',
    'label' => 'Paciente',
    'required' => true,
    'selected' => null,
    'patients' => null,
    'error' => null
])

@php
    if ($patients === null) {
        $clinicContext = request()->attributes->get(\App\Modules\Clinics\Data\ClinicContext::class);
        $patients = $clinicContext instanceof \App\Modules\Clinics\Data\ClinicContext
            ? \App\Models\Patient::forClinic($clinicContext)
                ->select('id', 'first_name', 'last_name', 'patient_code', 'gender')
                ->where('is_active', true)
                ->orderBy('last_name')
                ->get()
            : collect();
    }
@endphp

<div class="mb-3">
    <label for="{{ $id }}" class="form-label">{{ $label }} @if($required)<span class="text-danger">*</span>@endif</label>
    <select class="form-select @error($name) is-invalid @enderror" id="{{ $id }}" name="{{ $name }}" @if($required) required @endif>
        <option value="">Seleccionar paciente</option>
        @foreach($patients as $patient)
            <option value="{{ $patient->id }}" data-gender="{{ $patient->gender ?? 'male' }}" {{ (old($name, $selected) == $patient->id) ? 'selected' : '' }}>
                {{ $patient->first_name }} {{ $patient->last_name }} - {{ $patient->patient_code }}
            </option>
        @endforeach
    </select>
    @error($name)
        <div class="invalid-feedback">{{ $message }}</div>
    @enderror
</div>
