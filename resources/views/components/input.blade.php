<div class="form-group mb-3" id="{{ $name === 'creditlimit' ? 'creditlimit-form' : '' }}">
    <label class="form-label">{{ $label }}</label>
    <input
        type="{{ $type ?? 'text' }}"
        name="{{ $name }}"
        class="form-control {{ $class ?? '' }}"
        value="{{ old($name, $value ?? '') }}"
        {!! $attributes ?? '' !!}
    >

     <span class="invalid-feedback d-block" role="alert" id="{{ $name }}_error"></span>
</div>
