@props([
    'until',
    'days' => true,
    'labels' => false,
    'size' => null,
    'fa' => null,
    'expiredText' => 'به پایان رسید',
])

@php
use MajidDs\Support\Persian;

$fa ??= config('mds.persian_digits', true);

$until = Persian::toDateTime($until);

// Server-side initial values so the countdown isn't blank before Alpine boots...
$total = max(0, $until->getTimestamp() - time());
$initial = [
    'd' => intdiv($total, 86400),
    'h' => $days ? intdiv($total % 86400, 3600) : intdiv($total, 3600),
    'm' => intdiv($total % 3600, 60),
    's' => $total % 60,
];

$pad = fn ($n) => str_pad((string) $n, 2, '0', STR_PAD_LEFT);
$seg = fn ($n) => $fa ? Persian::digits($pad($n)) : $pad($n);

$boxClasses = match ($size) {
    'sm' => 'min-w-6 px-1 py-0.5 text-xs',
    'lg' => 'min-w-10 px-1.5 py-1.5 text-lg',
    default => 'min-w-8 px-1 py-1 text-sm',
};

$box = "flex items-center justify-center rounded-md bg-zinc-800 font-bold tabular-nums text-white dark:bg-white/15 {$boxClasses}";

$segments = [];

if ($days) {
    $segments[] = ['key' => 'd', 'label' => 'روز', 'initial' => $seg($initial['d'])];
}

$segments[] = ['key' => 'h', 'label' => 'ساعت', 'initial' => $seg($initial['h'])];
$segments[] = ['key' => 'm', 'label' => 'دقیقه', 'initial' => $seg($initial['m'])];
$segments[] = ['key' => 's', 'label' => 'ثانیه', 'initial' => $seg($initial['s'])];
@endphp

<div
    {{ $attributes->class('inline-flex items-center') }}
    x-data="{
        end: {{ $until->getTimestamp() }} * 1000,
        now: Date.now(),
        fa: {{ $fa ? 'true' : 'false' }},
        withDays: {{ $days ? 'true' : 'false' }},
        get total() { return Math.max(0, Math.floor((this.end - this.now) / 1000)) },
        get expired() { return this.total <= 0 },
        get d() { return Math.floor(this.total / 86400) },
        get h() { return this.withDays ? Math.floor((this.total % 86400) / 3600) : Math.floor(this.total / 3600) },
        get m() { return Math.floor((this.total % 3600) / 60) },
        get s() { return this.total % 60 },
        seg(n) {
            const s = String(n).padStart(2, '0')
            return this.fa ? s.replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[+d]) : s
        },
    }"
    x-init="setInterval(() => now = Date.now(), 1000)"
    role="timer"
    data-mds-countdown
>
    {{--
        Labeled boxes follow the Iranian e-commerce convention (days on the right,
        labels underneath). The plain variant is a wall-clock style time, which is
        always written left-to-right (HH:MM:SS) — even in Persian.
    --}}
    <div class="flex items-{{ $labels ? 'start' : 'center' }} gap-1" @unless ($labels) dir="ltr" @endunless @if ($total <= 0) style="display: none" @endif x-show="!expired">
        @foreach ($segments as $index => $segment)
            @if ($index > 0)
                <span class="{{ $labels ? 'mt-0.5' : '' }} font-bold text-zinc-400 dark:text-zinc-500" aria-hidden="true">:</span>
            @endif

            <div class="flex flex-col items-center gap-1">
                <span class="{{ $box }}" x-text="seg({{ $segment['key'] }})">{{ $segment['initial'] }}</span>

                @if ($labels)
                    <span class="text-[10px] text-zinc-500 dark:text-zinc-400">{{ $segment['label'] }}</span>
                @endif
            </div>
        @endforeach
    </div>

    <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400" @if ($total > 0) style="display: none" @endif x-show="expired">{{ $expiredText }}</span>
</div>
