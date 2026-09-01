@props([
    'steps' => [],
    'current' => 1,
    'fa' => null,
])

@php
$fa ??= config('mds.persian_digits', true);
$current = (int) $current;
$total = count($steps);
@endphp

<nav {{ $attributes->class('flex items-center gap-2') }} aria-label="{{ $fa ? 'مراحل' : 'Steps' }}" data-mds-stepper>
    @foreach (array_values($steps) as $index => $step)
        @php
        $number = $index + 1;
        $state = $number < $current ? 'completed' : ($number === $current ? 'current' : 'upcoming');
        @endphp

        <div class="flex items-center gap-2" aria-current="{{ $state === 'current' ? 'step' : 'false' }}" data-mds-stepper-step="{{ $state }}">
            <span @class([
                'flex size-7 shrink-0 items-center justify-center rounded-full text-sm font-medium',
                'bg-accent text-accent-foreground' => $state === 'completed',
                'border-2 border-accent text-accent-content font-semibold' => $state === 'current',
                'border border-zinc-300 text-zinc-400 dark:border-zinc-600 dark:text-zinc-500' => $state === 'upcoming',
            ])>
                @if ($state === 'completed')
                    <mds:icon icon="check" variant="micro" class="size-4" />
                @else
                    {{ $fa ? \MajidDs\Support\Persian::digits($number) : $number }}
                @endif
            </span>

            <span class="sr-only">{{ $fa
                ? 'مرحله '.\MajidDs\Support\Persian::digits($number).' از '.\MajidDs\Support\Persian::digits($total)
                : "Step {$number} of {$total}" }}</span>

            <span @class([
                'text-sm whitespace-nowrap',
                'text-zinc-500 dark:text-zinc-400' => $state === 'completed',
                'font-semibold text-accent-content' => $state === 'current',
                'text-zinc-400 dark:text-zinc-500' => $state === 'upcoming',
            ])>{{ $step }}</span>
        </div>

        @if ($number < $total)
            <div @class([
                'h-px min-w-4 flex-1',
                'bg-accent' => $number < $current,
                'bg-zinc-200 dark:bg-zinc-700' => $number >= $current,
            ]) aria-hidden="true"></div>
        @endif
    @endforeach
</nav>
