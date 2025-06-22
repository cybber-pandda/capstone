<div class="form-group mb-3">
    <label class="form-label">{{ $label }}</label>
    <select name="{{ $name }}" class="form-select {{ $class ?? '' }}" {{ $attributes }}>
        @foreach ($options as $key => $option)
            @php
                $value = is_string($key) ? $key : $option;
                $label = is_string($key) ? $option : ucfirst($option);
                $isSelected = old($name, $selected ?? '') == $value ? 'selected' : '';
            @endphp
            <option value="{{ $value }}" {{ $isSelected }}>{{ $label }}</option>
        @endforeach
    </select>

    <span class="invalid-feedback d-block" role="alert" id="{{ $name }}_error"></span>
</div>
