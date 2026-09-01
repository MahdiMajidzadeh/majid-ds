@props([
    'value' => 0,
    'max' => 5,
    'count' => null,
    'size' => null,
    'showValue' => true,
    'fa' => null,
])

@aware(['fa' => null])

@php
$fa ??= config('mds.persian_digits', true);

$value = max(0, min((float) $value, (int) $max));
$percent = $max > 0 ? number_format(($value / $max) * 100, 4, '.', '') : '0';

$starClasses = match ($size) {
    'sm' => 'size-3.5',
    'lg' => 'size-6',
    default => 'size-4.5',
};

$textClasses = match ($size) {
    'sm' => 'text-xs',
    'lg' => 'text-base',
    default => 'text-sm',
};

$display = number_format($value, $value == floor($value) ? 0 : 1, '.', '');

if ($fa) {
    $display = \MajidDs\Support\Persian::number($display, $value == floor($value) ? 0 : 1);
    $countDisplay = $count !== null ? \MajidDs\Support\Persian::number($count) : null;
} else {
    $countDisplay = $count !== null ? number_format((int) $count) : null;
}
@endphp

<div
    {{ $attributes->class('inline-flex items-center gap-1.5') }}
    role="img"
    aria-label="{{ $display }} / {{ $fa ? \MajidDs\Support\Persian::digits($max) : $max }}"
    data-mds-rating
>
    <div class="inline-grid align-middle">
        <div class="flex items-center gap-0.5 text-zinc-300 dark:text-zinc-600 [grid-area:1/1]">
            @for ($i = 0; $i < $max; $i++)
{{-- Inline on purpose: the bundled Hugeicons set is Stroke Rounded,
                 and a rating needs a solid star it can fill by a fraction. --}}
            <svg class="{{ $starClasses }} shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            @endfor
        </div>

        <div class="flex overflow-hidden [grid-area:1/1]" style="width: {{ $percent }}%">
            <div class="flex items-center gap-0.5 shrink-0 text-amber-400">
                @for ($i = 0; $i < $max; $i++)
                    <svg class="{{ $starClasses }} shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.363-1.118l-2.8-2.034c-.784-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                @endfor
            </div>
        </div>
    </div>

    @if ($showValue)
        <span class="{{ $textClasses }} font-medium text-zinc-700 dark:text-zinc-200">{{ $display }}</span>
    @endif

    @if ($countDisplay !== null)
        <span class="{{ $textClasses }} text-zinc-400 dark:text-zinc-500">({{ $countDisplay }})</span>
    @endif
</div>
