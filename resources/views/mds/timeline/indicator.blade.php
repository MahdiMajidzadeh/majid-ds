@props([
    'variant' => null,
    'status' => null,
    'color' => null,
])

@php
$bare = $variant === 'bare';

// An explicit color outranks the item's status — these are real Tailwind
// classes (not :where() defaults), so they win on specificity...
$colorClasses = match ($color) {
    'red' => 'bg-red-500 text-white dark:bg-red-400 dark:text-red-950',
    'orange' => 'bg-orange-500 text-white dark:bg-orange-400 dark:text-orange-950',
    'amber' => 'bg-amber-500 text-white dark:bg-amber-400 dark:text-amber-950',
    'yellow' => 'bg-yellow-500 text-white dark:bg-yellow-400 dark:text-yellow-950',
    'lime' => 'bg-lime-500 text-white dark:bg-lime-400 dark:text-lime-950',
    'green' => 'bg-green-500 text-white dark:bg-green-400 dark:text-green-950',
    'emerald' => 'bg-emerald-500 text-white dark:bg-emerald-400 dark:text-emerald-950',
    'teal' => 'bg-teal-500 text-white dark:bg-teal-400 dark:text-teal-950',
    'cyan' => 'bg-cyan-500 text-white dark:bg-cyan-400 dark:text-cyan-950',
    'sky' => 'bg-sky-500 text-white dark:bg-sky-400 dark:text-sky-950',
    'blue' => 'bg-blue-500 text-white dark:bg-blue-400 dark:text-blue-950',
    'indigo' => 'bg-indigo-500 text-white dark:bg-indigo-400 dark:text-indigo-950',
    'violet' => 'bg-violet-500 text-white dark:bg-violet-400 dark:text-violet-950',
    'purple' => 'bg-purple-500 text-white dark:bg-purple-400 dark:text-purple-950',
    'fuchsia' => 'bg-fuchsia-500 text-white dark:bg-fuchsia-400 dark:text-fuchsia-950',
    'pink' => 'bg-pink-500 text-white dark:bg-pink-400 dark:text-pink-950',
    'rose' => 'bg-rose-500 text-white dark:bg-rose-400 dark:text-rose-950',
    'zinc' => 'bg-zinc-500 text-white dark:bg-zinc-400 dark:text-zinc-950',
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
