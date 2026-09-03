@props([
    'value' => 0,
    'max' => 5,
    'name' => null,
    'error' => null,
    'invalid' => false,
    'size' => null,
    'label' => null,
    'reverse' => false,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in label's language.
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag. The
// message belongs to the surrounding field; here it only marks the state.
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

$label ??= $fa ? 'امتیاز' : 'Rating';

$starClasses = match ($size) {
    'sm' => 'size-4',
    'lg' => 'size-8',
    default => 'size-6',
};
@endphp

@once
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerRatingInput = (Alpine) => {
    if (window.mds.ratingInputRegistered) return
    window.mds.ratingInputRegistered = true

    Alpine.data('mdsRatingInput', (config = {}) => ({
        value: config.value ?? 0,
        max: config.max ?? 5,
        reverse: config.reverse ?? false,
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

        // Arrow keys map the same way in RTL and LTR: Right/Down = next,
        // Left/Up = previous. Nothing is inferred from the page direction —
        // `reverse` flips the horizontal pair for callers who want Right to
        // follow the visual order of an RTL star row instead.
        step(delta) {
            this.focus((this.value || 1) + delta)
        },

        horizontal(delta) {
            this.step(this.reverse ? -delta : delta)
        },

        focus(n) {
            this.commit(n)
            this.$root.querySelectorAll('[role="radio"]')[this.value - 1]?.focus()
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerRatingInput(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerRatingInput(window.Alpine))
}
</script>
@endonce

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class('inline-flex items-center') }}
    x-data="mdsRatingInput({ value: {{ (int) $value }}, max: {{ (int) $max }}, reverse: {{ $reverse ? 'true' : 'false' }} })"
    x-on:keydown.right.prevent="horizontal(1)"
    x-on:keydown.left.prevent="horizontal(-1)"
    x-on:keydown.down.prevent="step(1)"
    x-on:keydown.up.prevent="step(-1)"
    x-on:keydown.home.prevent="focus(1)"
    x-on:keydown.end.prevent="focus(max)"
    role="radiogroup"
    aria-label="{{ $label }}"
    @if ($invalid) aria-invalid="true" @endif
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
{{-- Inline on purpose: the bundled Hugeicons set is Stroke Rounded,
                 and a rating needs a solid star it can fill by a fraction. --}}
            <svg class="{{ $starClasses }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        </button>
    @endfor
</div>
