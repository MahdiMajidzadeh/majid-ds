@props([
    'variant' => null,
    'status' => null,
    'color' => null,
])

@php
$bare = $variant === 'bare';

// An explicit color outranks the item's status — these are real Tailwind
// classes (not :where() defaults), so they win on specificity.
//
// Light mode picks the text by the hue's own brightness, the way dark mode
// already does: white on a 500 fails WCAG AA for 16 of the 18 hues (yellow
// and lime sit near 1.9:1), so the bright hues take their own 950 as ink and
// the deep hues step to 600 under white. ContrastTest holds every pair to 4.5.
$colorClasses = match ($color) {
    'red' => 'bg-red-600 text-white dark:bg-red-400 dark:text-red-950',
    'orange' => 'bg-orange-500 text-orange-950 dark:bg-orange-400 dark:text-orange-950',
    'amber' => 'bg-amber-500 text-amber-950 dark:bg-amber-400 dark:text-amber-950',
    'yellow' => 'bg-yellow-500 text-yellow-950 dark:bg-yellow-400 dark:text-yellow-950',
    'lime' => 'bg-lime-500 text-lime-950 dark:bg-lime-400 dark:text-lime-950',
    'green' => 'bg-green-500 text-green-950 dark:bg-green-400 dark:text-green-950',
    'emerald' => 'bg-emerald-500 text-emerald-950 dark:bg-emerald-400 dark:text-emerald-950',
    'teal' => 'bg-teal-500 text-teal-950 dark:bg-teal-400 dark:text-teal-950',
    'cyan' => 'bg-cyan-500 text-cyan-950 dark:bg-cyan-400 dark:text-cyan-950',
    'sky' => 'bg-sky-500 text-sky-950 dark:bg-sky-400 dark:text-sky-950',
    'blue' => 'bg-blue-600 text-white dark:bg-blue-400 dark:text-blue-950',
    'indigo' => 'bg-indigo-600 text-white dark:bg-indigo-400 dark:text-indigo-950',
    'violet' => 'bg-violet-600 text-white dark:bg-violet-400 dark:text-violet-950',
    'purple' => 'bg-purple-600 text-white dark:bg-purple-400 dark:text-purple-950',
    'fuchsia' => 'bg-fuchsia-600 text-white dark:bg-fuchsia-400 dark:text-fuchsia-950',
    'pink' => 'bg-pink-600 text-white dark:bg-pink-400 dark:text-pink-950',
    'rose' => 'bg-rose-600 text-white dark:bg-rose-400 dark:text-rose-950',
    'zinc' => 'bg-zinc-600 text-white dark:bg-zinc-400 dark:text-zinc-950',
    default => null,
};
@endphp

<div
    {{ $attributes->class([
        'grid shrink-0 place-items-center',
        'rounded-full text-sm font-semibold tabular-nums' => ! $bare,
        $colorClasses => ! $bare && $colorClasses,
    ]) }}
    @if ($status) data-mds-timeline-status="{{ $status }}" @endif
    @if ($bare) data-mds-timeline-bare @endif
    data-mds-timeline-indicator
>
    {{ $slot }}
</div>
