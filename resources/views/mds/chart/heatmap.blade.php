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

$num = fn ($n) => Persian::decimal($n, $fa);
@endphp

@once
<script @mdsNonce>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsHeatmap', (config = {}) => ({
        hover: null,
        rows: config.rows ?? 7,
        // The grid is one tab stop, not one per cell — a year of them would
        // otherwise be 365 stops between the chart and whatever follows it.
        active: 0,

        cells() {
            return [...this.$refs.grid.children]
        },

        move(delta) {
            const cells = this.cells()

            this.active = Math.min(Math.max(this.active + delta, 0), cells.length - 1)
            cells[this.active]?.focus()
        },

        // Weeks run with the page direction, so left and right swap in RTL.
        column(direction) {
            const rtl = getComputedStyle(this.$root).direction === 'rtl'

            this.move((rtl ? -direction : direction) * this.rows)
        },
    }))
})
</script>
@endonce

<div
    {{ $attributes }}
    x-data="mdsHeatmap({ rows: {{ max((int) $rows, 1) }} })"
    x-on:keydown.down.prevent="move(1)"
    x-on:keydown.up.prevent="move(-1)"
    x-on:keydown.right.prevent="column(1)"
    x-on:keydown.left.prevent="column(-1)"
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
        x-ref="grid"
        data-mds-chart-heatmap-grid
        style="grid-template-rows: repeat({{ max((int) $rows, 1) }}, 1fr)"
        @if ($callout) x-on:mouseleave="hover = null" @endif
    >
        @foreach ($values as $i => $v)
            <span
                data-mds-chart-cell
                data-level="{{ $level($v) }}"
                title="{{ $num($v) }} {{ $unit }}"
                role="img"
                aria-label="{{ $num($v) }} {{ $unit }}"
                x-bind:tabindex="active === {{ $i }} ? 0 : -1"
                x-on:focus="active = {{ $i }}; hover = @js($num($v).' '.$unit)"
                @if ($callout) x-on:mouseenter="hover = @js($num($v).' '.$unit)" @endif
            ></span>
        @endforeach
    </div>

    @if ($callout)
        <div data-mds-chart-heatmap-callout aria-live="polite" x-text="hover ?? @js($idleText)">{{ $idleText }}</div>
    @endif
</div>
