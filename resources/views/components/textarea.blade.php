@php
    $attributes = $attributes ?? '';
@endphp

<div class="form-group mb-3">
    <label class="form-label">{{ $label }}</label>
    <textarea
        name="{{ $name }}"
        class="form-control {{ $class ?? '' }}"
        rows="{{ $rows ?? 3 }}"
        {{ $attributes }}
    >{{ old($name, $value ?? '') }}</textarea>
</div>
