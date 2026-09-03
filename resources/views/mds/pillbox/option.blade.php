@props([
    'value' => null,
    'disabled' => false,
    'fa' => null,
])

@aware(['fa' => null])

@php
// Inherited from the pillbox. An option has no built-in text of its own
// today; the prop is declared so every mds subcomponent shares one contract
// and a future label (a "recent" tag, say) switches language with its parent.
$fa ??= config('mds.persian_digits', true);

// The label is the slot, flattened to text: it names the pill, the hidden
// option and the haystack the search runs over. Icons or markup inside the
// slot still render in the row itself.
$label = trim((string) preg_replace('/\s+/u', ' ', strip_tags((string) $slot)));

// A row without a value is worth its label — an option whose slot reads
// "PHP" selects "PHP".
$value = (string) ($value ?? $label);
@endphp

{{--
    A div, not a button: options in a combobox popup are never in the tab
    order — the text field holds focus and points at the active one through
    aria-activedescendant, and the popup prevents mousedown so a click never
    moves focus. The value and the flattened label ride along as attributes:
    the pillbox reads them twice — once on the server, scanning its rendered
    slot to draw the first pills and fill the hidden select; once at runtime,
    when Alpine caches the rows and rebuilds the select from them.
--}}
<div
    {{ $attributes->class([
        'flex items-center gap-2.5 rounded-md px-2.5 py-2 text-start text-sm text-zinc-700 select-none dark:text-zinc-200',
        'cursor-pointer' => ! $disabled,
        'cursor-not-allowed opacity-50' => $disabled,
    ]) }}
    role="option"
    aria-selected="false"
    data-value="{{ $value }}"
    data-label="{{ $label }}"
    @if ($disabled) data-disabled aria-disabled="true" @endif
    x-show="matches($el)"
    x-bind:class="{ 'bg-zinc-100 dark:bg-white/10': isActive($el), 'cursor-not-allowed opacity-50': isDisabled($el) }"
    x-bind:aria-selected="isSelected($el) ? 'true' : 'false'"
    x-bind:aria-disabled="isDisabled($el) ? 'true' : null"
    x-on:click="toggle($el)"
    x-on:mouseenter="point($el)"
    data-mds-pillbox-option
>
    <span class="flex-1 truncate" data-mds-pillbox-label>{{ $slot }}</span>

    {{-- The check is one of two signals — aria-selected is the other — so a
         selected row is never told by colour alone. $el inside this span is
         the span; the row it belongs to is its parent. --}}
    <span class="shrink-0 text-accent-content" x-show="isSelected($el.parentElement)" x-cloak data-mds-pillbox-check>
        <mds:icon icon="check" variant="micro" class="size-4" />
    </span>
</div>
