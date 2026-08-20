@props([
    'value',
    'label' => null,
])

<button
    type="button"
    {{ $attributes->class('size-5 cursor-pointer rounded-md border border-black/10 transition-transform hover:scale-110 dark:border-white/20') }}
    style="background: {{ $value }}"
    x-on:click="pick(@js($value))"
    aria-label="{{ $label ?? $value }}"
    data-mds-color-picker-swatch
></button>
