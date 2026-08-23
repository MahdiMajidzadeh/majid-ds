{{--
    Wireframe of a layout's grid areas. Expects $grid:

        cols   — grid-template-columns for the miniature
        areas  — the rows of grid-template-areas, exactly as flux.css names them
        inset  — draw a dashed max-width guide inside each area (container layouts)
        dashed — areas to outline instead of fill (an off-canvas sidebar)
        pins   — areas to mark as sticky

    In Persian (RTL) the first column lands on the right — the same side the
    sidebar shows up on in the live layout; in English it lands on the left.
--}}
@php
$height = $height ?? 'h-28';

$labels = [
    'header'  => __('هدر'),
    'sidebar' => __('سایدبار'),
    'main'    => __('محتوا'),
    'aside'   => __('کنار'),
    'footer'  => __('فوتر'),
];

$styles = [
    'header'  => 'bg-zinc-200 text-zinc-600 dark:bg-zinc-600 dark:text-zinc-200',
    'sidebar' => 'bg-accent/15 text-accent-content dark:bg-accent/25',
    'main'    => 'bg-zinc-50 text-zinc-500 dark:bg-zinc-700/60 dark:text-zinc-300',
    'aside'   => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-600/60 dark:text-zinc-300',
    'footer'  => 'bg-zinc-200/70 text-zinc-500 dark:bg-zinc-600/60 dark:text-zinc-300',
];

$dashed = $grid['dashed'] ?? [];
$pins = $grid['pins'] ?? [];

$areas = collect($grid['areas'] ?? [])
    ->flatMap(fn ($row) => preg_split('/\s+/', trim($row)))
    ->unique()
    ->values();

$template = collect($grid['areas'] ?? [])->map(fn ($row) => '"'.$row.'"')->implode(' ');
@endphp

<div
    class="grid gap-1 overflow-hidden rounded-lg border border-zinc-200 bg-white p-1 dark:border-zinc-600 dark:bg-zinc-800 {{ $height }}"
    style="grid-template-columns: {{ $grid['cols'] ?? '1fr' }}; grid-template-rows: 1.25rem 1fr 0.9rem; grid-template-areas: {{ $template }};"
>
    @foreach ($areas as $area)
        <div
            class="relative flex items-center justify-center rounded-sm text-[10px] font-medium {{ in_array($area, $dashed) ? 'border border-dashed border-accent/60 text-accent-content' : ($styles[$area] ?? '') }}"
            style="grid-area: {{ $area }};"
        >
            @if (in_array($area, $pins))
                <flux:icon.bookmark class="absolute end-1 top-1 size-2.5 opacity-60" />
            @endif

            @if (($grid['inset'] ?? false) && $area !== 'footer')
                <div class="mx-auto flex h-full w-[62%] items-center justify-center rounded-sm border border-dashed border-accent/50">
                    {{ $labels[$area] ?? $area }}
                </div>
            @else
                {{ $labels[$area] ?? $area }}
            @endif
        </div>
    @endforeach
</div>
