@props([
    'percent' => null,
    'amount' => null,
    'original' => null,
    'size' => null,
    'fa' => null,
])

@aware(['fa' => null])

@php
$fa ??= config('mds.persian_digits', true);

if ($percent === null && $amount !== null && $original !== null && $original > 0) {
    $percent = (int) round((1 - $amount / $original) * 100);
}

$percent = $percent === null ? null : (int) $percent;

$classes = match ($size) {
    'sm' => 'px-1.5 py-px text-[10px]',
    'lg' => 'px-2.5 py-1 text-sm',
    default => 'px-2 py-0.5 text-xs',
};
@endphp

{{-- Nothing to say without a discount: (int) null used to render «۰٪». --}}
@if ($percent !== null)
<span
    {{-- red-600, not 500: white on red-500 is 3.8:1, under AA for 10-12px text. The dark pair mirrors the timeline indicator. --}}
    {{ $attributes->class("inline-flex items-center justify-center rounded-full bg-red-600 font-bold text-white tabular-nums dark:bg-red-400 dark:text-red-950 {$classes}") }}
    aria-label="{{ $fa ? \MajidDs\Support\Persian::digits($percent).' درصد تخفیف' : $percent.'% off' }}"
    data-mds-discount-badge
>{{ $fa ? \MajidDs\Support\Persian::digits($percent).'٪' : $percent.'%' }}</span>
@endif
