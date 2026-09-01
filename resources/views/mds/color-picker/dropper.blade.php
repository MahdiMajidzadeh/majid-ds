@props([
    'label' => null,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in label's language; inherited from the picker.
$fa ??= config('mds.persian_digits', true);

$label ??= $fa ? 'قطره‌چکان' : 'Eyedropper';
@endphp

<button
    type="button"
    {{ $attributes->class('shrink-0 rounded-lg border border-zinc-200 p-2 text-zinc-500 hover:bg-zinc-50 dark:border-white/10 dark:text-zinc-400 dark:hover:bg-white/5') }}
    x-show="!! window.EyeDropper"
    x-cloak
    x-on:click="eyeDropper()"
    aria-label="{{ $label }}"
    data-mds-color-picker-dropper
>
    <mds:icon icon="eye-dropper" variant="micro" class="size-4" />
</button>
