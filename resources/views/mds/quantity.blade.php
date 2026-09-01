@props([
    'value' => null,
    'min' => 1,
    'max' => null,
    'step' => 1,
    'size' => null,
    'name' => null,
    'error' => null,
    'invalid' => false,
    'fa' => null,
    'incrementLabel' => null,
    'decrementLabel' => null,
])

@php
// fa picks the built-in strings' language along with the digits.
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag. These
// primitives sit inside the app's own field, which renders the message — so
// the error only drives the invalid state here, never a second message block.
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

$incrementLabel ??= $fa ? 'افزایش تعداد' : 'Increase quantity';
$decrementLabel ??= $fa ? 'کاهش تعداد' : 'Decrease quantity';
$value = (int) ($value ?? $min);

$buttonClasses = match ($size) {
    'sm' => 'size-7',
    'lg' => 'size-11',
    default => 'size-9',
};

$textClasses = match ($size) {
    'sm' => 'text-xs min-w-6',
    'lg' => 'text-base min-w-10',
    default => 'text-sm min-w-8',
};
@endphp

@include('mds::partials.digits')

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsQuantity', (config = {}) => ({
        value: config.value ?? 0,
        min: config.min ?? 0,
        max: config.max ?? null,
        step: config.step ?? 1,
        fa: config.fa ?? true,

        display() {
            return window.mds.digits(this.value, this.fa)
        },

        set(n) {
            n = Math.max(this.min, this.max === null ? n : Math.min(this.max, n))

            if (n === this.value) return

            this.value = n
            this.$refs.input.value = n
            this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }))
        },
    }))
})
</script>
@endonce

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([
        'inline-flex items-center rounded-lg border bg-white shadow-xs dark:bg-white/10',
        'border-red-500 dark:border-red-400' => $invalid,
        'border-zinc-200 dark:border-white/10' => ! $invalid,
    ]) }}
    @if ($invalid) aria-invalid="true" @endif
    x-data="mdsQuantity({
        value: {{ $value }},
        min: {{ (int) $min }},
        max: {{ $max === null ? 'null' : (int) $max }},
        step: {{ (int) $step }},
        fa: {{ $fa ? 'true' : 'false' }},
    })"
    data-mds-quantity
>
    <input
        type="hidden"
        x-ref="input"
        value="{{ $value }}"
        @if ($name) name="{{ $name }}" @endif
        {{ $attributes->whereStartsWith('wire:model') }}
    >

    <button
        type="button"
        class="{{ $buttonClasses }} flex items-center justify-center rounded-lg text-accent-content transition-colors hover:bg-zinc-50 disabled:cursor-not-allowed disabled:text-zinc-300 dark:hover:bg-white/5 dark:disabled:text-zinc-600"
        x-on:click="set(value + step)"
        x-bind:disabled="max !== null && value >= max"
        aria-label="{{ $incrementLabel }}"
    >
        <svg class="size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M8.75 3.75a.75.75 0 0 0-1.5 0v3.5h-3.5a.75.75 0 0 0 0 1.5h3.5v3.5a.75.75 0 0 0 1.5 0v-3.5h3.5a.75.75 0 0 0 0-1.5h-3.5v-3.5Z"/></svg>
    </button>

    <span class="{{ $textClasses }} text-center font-semibold tabular-nums text-zinc-800 dark:text-white" aria-live="polite" aria-atomic="true" x-text="display()">{{ $fa ? \MajidDs\Support\Persian::digits($value) : $value }}</span>

    <button
        type="button"
        class="{{ $buttonClasses }} flex items-center justify-center rounded-lg text-accent-content transition-colors hover:bg-zinc-50 disabled:cursor-not-allowed disabled:text-zinc-300 dark:hover:bg-white/5 dark:disabled:text-zinc-600"
        x-on:click="set(value - step)"
        x-bind:disabled="value <= min"
        aria-label="{{ $decrementLabel }}"
    >
        <svg class="size-4" viewBox="0 0 16 16" fill="currentColor" aria-hidden="true"><path d="M3.75 7.25a.75.75 0 0 0 0 1.5h8.5a.75.75 0 0 0 0-1.5h-8.5Z"/></svg>
    </button>
</div>
