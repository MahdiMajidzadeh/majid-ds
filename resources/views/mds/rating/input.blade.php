@props([
    'value' => 0,
    'max' => 5,
    'name' => null,
    'size' => null,
    'label' => 'امتیاز',
])

@php
$starClasses = match ($size) {
    'sm' => 'size-4',
    'lg' => 'size-8',
    default => 'size-6',
};
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class('inline-flex items-center') }}
    x-data="{ value: {{ (int) $value }}, hover: 0 }"
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
            x-on:click="value = {{ $i }}; $refs.input.value = {{ $i }}; $refs.input.dispatchEvent(new Event('input', { bubbles: true }))"
            x-on:mouseenter="hover = {{ $i }}"
            x-on:mouseleave="hover = 0"
            x-bind:class="(hover ? hover >= {{ $i }} : value >= {{ $i }}) ? 'text-amber-400' : 'text-zinc-300 dark:text-zinc-600'"
            x-bind:aria-checked="value === {{ $i }} ? 'true' : 'false'"
            role="radio"
            aria-label="{{ \MajidDs\Support\Persian::digits($i) }}"
        >
            <svg class="{{ $starClasses }}" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
        </button>
    @endfor
</div>
