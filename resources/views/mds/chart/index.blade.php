@props([
    'label' => null,
    'badge' => null,
    'value' => null,
    'unit' => null,
    'delta' => null,
    'footerStart' => null,
    'footerEnd' => null,
    'fa' => null,
])

@php
use MajidDs\Support\Persian;

// fa picks the digits of the big stat and the delta along with everything
// the stages inside inherit (they read it via @aware).
$fa ??= config('mds.persian_digits', true);

// Numbers get thousands separators; strings ("84%", "$48,920") keep their
// shape and only have their digits localized.
$stat = $value === null ? null : Persian::auto($value, $fa);

// The sign is the meaning: a leading minus reads as a drop and colors red.
$deltaDown = $delta !== null && str_starts_with(ltrim((string) $delta), '-');
$deltaText = $delta === null ? null : ($fa ? Persian::digits($delta) : (string) $delta);
@endphp

<div {{ $attributes }} data-mds-chart>
    @if ($label !== null || $badge !== null || $stat !== null || isset($header))
        <div data-mds-chart-header>
            <div class="min-w-0">
                @if ($label !== null || $badge !== null)
                    <div class="flex items-center gap-2">
                        @if ($label !== null)<span data-mds-chart-label>{{ $label }}</span>@endif
                        @if ($badge !== null)<span data-mds-chart-badge>{{ $badge }}</span>@endif
                    </div>
                @endif

                @if ($stat !== null)
                    <div data-mds-chart-value>{{ $stat }}@if ($unit !== null) <span data-mds-chart-unit>{{ $unit }}</span>@endif @if ($deltaText !== null)<span data-mds-chart-delta @if ($deltaDown) data-mds-chart-delta-down @endif dir="ltr">{{ $deltaText }}</span>@endif</div>
                @endif
            </div>

            {{ $header ?? '' }}
        </div>
    @endif

    {{ $slot }}

    @if ($footerStart !== null || $footerEnd !== null || isset($footer))
        <div data-mds-chart-footer>
            @isset($footer)
                {{ $footer }}
            @else
                <span>{{ $footerStart }}</span>
                <span>{{ $footerEnd }}</span>
            @endisset
        </div>
    @endif
</div>
