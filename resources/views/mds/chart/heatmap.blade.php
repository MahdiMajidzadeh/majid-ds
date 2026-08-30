@props([
    'data' => [],
    'rows' => 7,
    'labels' => [],
    'color' => null,
    'unit' => null,
    'callout' => true,
    'fa' => null,
])

@aware(['fa' => null])

@php
use MajidDs\Support\Persian;

$fa ??= config('mds.persian_digits', true);

$unit ??= $fa ? 'مورد' : 'items';
$idleText = $fa ? 'برای جزئیات روی خانه‌ها بروید' : 'Hover tiles for details';

$values = array_values(array_map(floatval(...), $data));
$peak = max([1e-9, ...$values]);

// Five tones: silent, then four steps of intensity.
$level = fn (float $v) => $v <= 0 ? 0 : min(4, (int) ceil($v / $peak * 4));

$num = fn ($n) => $fa
    ? Persian::number($n, $n == floor($n) ? 0 : 1)
    : number_format((float) $n, $n == floor($n) ? 0 : 1);
@endphp

<div
    {{ $attributes }}
    @if ($callout) x-data="{ hover: null }" @endif
    @if ($color) data-mds-chart-heatmap-color="{{ $color }}" @endif
    data-mds-chart-stage
    data-mds-chart-heatmap
>
    @if ($labels !== [])
        <div data-mds-chart-heatmap-labels>
            @foreach ($labels as $monthLabel)
                <span>{{ $fa ? Persian::digits($monthLabel) : $monthLabel }}</span>
            @endforeach
        </div>
    @endif

    {{-- grid-auto-flow: column stacks each week into a column, so the weeks
         run with the page direction — newest-last reads right-to-left in RTL. --}}
    <div
        data-mds-chart-heatmap-grid
        style="grid-template-rows: repeat({{ max((int) $rows, 1) }}, 1fr)"
        @if ($callout) x-on:mouseleave="hover = null" @endif
    >
        @foreach ($values as $v)
            <span
                data-mds-chart-cell
                data-level="{{ $level($v) }}"
                title="{{ $num($v) }} {{ $unit }}"
                @if ($callout) x-on:mouseenter="hover = @js($num($v).' '.$unit)" @endif
            ></span>
        @endforeach
    </div>

    @if ($callout)
        <div data-mds-chart-heatmap-callout x-text="hover ?? @js($idleText)">{{ $idleText }}</div>
    @endif
</div>
