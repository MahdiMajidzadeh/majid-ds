@props([
    'value' => null,
    'disabled' => false,
    'fa' => null,
])

@aware(['fa' => null])

@php
// Inherited from the autocomplete. An option has no built-in text of its own
// today; the prop is declared so every mds subcomponent shares one contract
// and a future label (a "recent" tag, say) switches language with its parent.
$fa ??= config('mds.persian_digits', true);
@endphp

{{--
    A div, not a button: options in a combobox popup are never in the tab
    order — the input holds focus and points at the active one through
    aria-activedescendant. The popup prevents mousedown for the same reason.
--}}
<div
    {{ $attributes->class([
        'flex items-center gap-2.5 rounded-md px-2.5 py-2 text-start text-sm text-zinc-700 dark:text-zinc-200',
        'cursor-pointer' => ! $disabled,
        'cursor-not-allowed opacity-50' => $disabled,
    ]) }}
    role="option"
    x-show="matches($el)"
    x-bind:class="isActive($el) && 'bg-zinc-100 dark:bg-white/10'"
    x-bind:aria-selected="isActive($el) ? 'true' : 'false'"
    @if ($disabled)
        aria-disabled="true"
        data-disabled
    @else
        x-on:mouseenter="point($el)"
        x-on:click="pick($el)"
    @endif
    @if ($value !== null) data-value="{{ $value }}" @endif
    data-mds-autocomplete-item
>
    <span class="flex-1 truncate" data-mds-autocomplete-label>{{ $slot }}</span>
</div>
