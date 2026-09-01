@props([
    'amount' => 0,
    'original' => null,
    'currency' => null,
    'decimals' => 0,
    'size' => null,
    'fa' => null,
    'badge' => true,
])

@aware(['fa' => null])

@php
use MajidDs\Support\Persian;

$fa ??= config('mds.persian_digits', true);
$currency ??= config('mds.currency', 'toman');

$label = Persian::currencyLabel($currency, $fa);

$fmt = fn ($n) => $fa
    ? Persian::number($n, $decimals)
    : number_format((float) $n, $decimals);

$percent = ($original !== null && $original > $amount)
    ? (int) round((1 - $amount / $original) * 100)
    : null;

$amountClasses = match ($size) {
    'sm' => 'text-sm',
    'lg' => 'text-xl',
    default => 'text-base',
};
@endphp

<div {{ $attributes->class('inline-flex items-center gap-2') }} data-mds-price>
    @if ($percent !== null && $badge)
        <mds:discount-badge :percent="$percent" :fa="$fa" :size="$size" />
    @endif

    <div class="flex flex-col items-end">
        @if ($percent !== null)
            <del class="text-xs leading-4 text-zinc-400 dark:text-zinc-500" aria-label="{{ $fa ? 'قیمت قبلی' : 'Original price' }}">{{ $fmt($original) }}</del>
        @endif

        <div class="flex items-baseline gap-1">
            <span class="{{ $amountClasses }} font-bold tabular-nums text-zinc-800 dark:text-white">{{ $fmt($amount) }}</span>

            @if ($label !== '')
                <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $label }}</span>
            @endif
        </div>
    </div>
</div>
