@props([
    'label' => 'Icerik',
    'name',
    'id',
    'value' => '',
    'rows' => 10,
    'required' => false,
    'wrapperClass' => 'mb-3',
])

<div class="{{ $wrapperClass }}">
    <label class="form-label fw-semibold">{{ $label }}</label>
    <textarea
        name="{{ $name }}"
        id="{{ $id }}"
        rows="{{ $rows }}"
        class="form-control"
        @if($required) required @endif
    >{{ $value }}</textarea>
</div>

