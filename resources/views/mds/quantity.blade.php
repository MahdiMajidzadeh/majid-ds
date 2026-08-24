@props([
    'value' => null,
    'min' => 1,
    'max' => null,
    'step' => 1,
    'size' => null,
    'name' => null,
    'fa' => null,
    'incrementLabel' => null,
    'decrementLabel' => null,
])

@php
// fa picks the built-in strings' language along with the digits.
$fa ??= config('mds.persian_digits', true);

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

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class('inline-flex items-center rounded-lg border border-zinc-200 bg-white shadow-xs dark:border-white/10 dark:bg-white/10') }}
    x-data="{
        value: {{ $value }},
        min: {{ (int) $min }},
        max: {{ $max === null ? 'null' : (int) $max }},
        step: {{ (int) $step }},
        fa: {{ $fa ? 'true' : 'false' }},
        display() {
            const s = String(this.value)
            return this.fa ? s.replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[+d]) : s
        },
        set(n) {
            n = Math.max(this.min, this.max === null ? n : Math.min(this.max, n))
            if (n === this.value) return
            this.value = n
            this.$refs.input.value = n
            this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }))
        },
    }"
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

    <span class="{{ $textClasses }} text-center font-semibold tabular-nums text-zinc-800 dark:text-white" x-text="display()">{{ $fa ? \MajidDs\Support\Persian::digits($value) : $value }}</span>

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
