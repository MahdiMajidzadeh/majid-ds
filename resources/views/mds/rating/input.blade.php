@props([
    'value' => 0,
    'max' => 5,
    'name' => null,
    'size' => null,
    'label' => null,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in label's language.
$fa ??= config('mds.persian_digits', true);

$label ??= $fa ? 'امتیاز' : 'Rating';

$starClasses = match ($size) {
    'sm' => 'size-4',
    'lg' => 'size-8',
    default => 'size-6',
};
@endphp

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsRatingInput', (config = {}) => ({
        value: config.value ?? 0,
        max: config.max ?? 5,
        hover: 0,

        commit(n) {
            this.value = Math.min(Math.max(n, 1), this.max)
            this.$refs.input.value = this.value
            this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }))
        },

        // A radiogroup is one tab stop: the checked star takes it, or the
        // first star when nothing is checked yet.
        tabindex(n) {
            return n === (this.value || 1) ? 0 : -1
        },

        // Arrow keys follow reading order, so left and right swap in RTL.
        // Read off the root, not the document, so an RTL island inside an
        // LTR page still mirrors.
        step(delta) {
            const rtl = getComputedStyle(this.$root).direction === 'rtl'

            this.focus((this.value || 1) + (rtl ? -delta : delta))
        },

        focus(n) {
            this.commit(n)
            this.$root.querySelectorAll('[role="radio"]')[this.value - 1]?.focus()
        },
    }))
})
</script>
@endonce

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class('inline-flex items-center') }}
    x-data="mdsRatingInput({ value: {{ (int) $value }}, max: {{ (int) $max }} })"
    x-on:keydown.right.prevent="step(1)"
    x-on:keydown.left.prevent="step(-1)"
    x-on:keydown.up.prevent="focus((value || 1) + 1)"
    x-on:keydown.down.prevent="focus((value || 1) - 1)"
    x-on:keydown.home.prevent="focus(1)"
    x-on:keydown.end.prevent="focus(max)"
    role="radiogroup"
    aria-label="{{ $label }}"
    data-mds-rating-input
>
    <input
        type="hidden"
        x-ref="input"
        value="{{ (int) $value }}"
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->whereStartsWith('wire:model') }}
    >

    @for ($i = 1; $i <= (int) $max; $i++)
        <button
            type="button"
            class="cursor-pointer p-0.5 transition-transform duration-100 hover:scale-110"
            x-on:click="commit({{ $i }})"
            x-on:mouseenter="hover = {{ $i }}"
            x-on:mouseleave="hover = 0"
            x-bind:class="(hover ? hover >= {{ $i }} : value >= {{ $i }}) ? 'text-amber-400' : 'text-zinc-300 dark:text-zinc-600'"
            x-bind:aria-checked="value === {{ $i }} ? 'true' : 'false'"
            x-bind:tabindex="tabindex({{ $i }})"
            role="radio"
            aria-label="{{ $fa ? \MajidDs\Support\Persian::digits($i) : $i }}"
        >
            <svg class="{{ $starClasses }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        </button>
    @endfor
</div>
