<div class="form-group mb-3">
    <label class="form-label">{{ $label }}</label>
    <select name="{{ $name }}" class="form-select {{ $class ?? '' }}" {{ $attributes }}>
        <option value="" selected disabled>-- No selected --</option>
        @php
        $isAssoc = function ($array) {
        return array_keys($array) !== range(0, count($array) - 1);
        };
        @endphp

        @foreach ($options as $key => $option)
        @php
        if ($isAssoc($options)) {
        // Associative: $key is ID, $option is label
        $value = $key;
        $label = $option;
        } else {
        // Not associative: value and label are same
        $value = $option;
        $label = ucfirst($option);
        }
        $isSelected = old($name, $selected ?? '') == $value ? 'selected' : '';
        @endphp
        <option value="{{ $value }}" {{ $isSelected }}>{{ $label }}</option>
        @endforeach


    </select>

    <span class="invalid-feedback d-block" role="alert" id="{{ $name }}_error"></span>
</div>