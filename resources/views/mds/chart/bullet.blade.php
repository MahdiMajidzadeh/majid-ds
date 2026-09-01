@props([
    'items' => [],
    'max' => 100,
    'unit' => '%',
    'fa' => null,
])

@aware(['fa' => null])

@php
use MajidDs\Support\Charts;
use MajidDs\Support\Persian;

$fa ??= config('mds.persian_digits', true);

$max = max((float) $max, 1e-9);

$num = fn ($n) => Persian::decimal($n, $fa).Persian::auto($unit, $fa);

$percent = fn ($n) => Charts::n(min(100, max(0, (float) $n / $max * 100)));
@endphp

<div {{ $attributes }} data-mds-chart-stage data-mds-chart-bullet>
    @foreach ($items as $item)
        @php $target = $item['target'] ?? null; @endphp
        <div data-mds-chart-bullet-item>
            <div data-mds-chart-bullet-head>
                <span data-mds-chart-row-label>{{ Persian::auto($item['label'] ?? '', $fa) }}</span>
                <span data-mds-chart-row-value>{{ $num($item['value'] ?? 0) }}@if ($target !== null) / {{ $num($target) }}@endif</span>
            </div>
            <span data-mds-chart-track>
                <span data-mds-chart-fill style="inline-size: {{ $percent($item['value'] ?? 0) }}%"></span>
                @if ($target !== null)
                    <span data-mds-chart-target style="inset-inline-start: {{ $percent($target) }}%"></span>
                @endif
            </span>
        </div>
    @endforeach
</div>
