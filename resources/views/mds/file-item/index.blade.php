@props([
    'heading' => null,
    'text' => null,
    'image' => null,
    'size' => null,
    'icon' => 'document',
    'invalid' => false,
    'fa' => null,
    'actions' => null,
])

@php
$fa ??= config('mds.persian_digits', true);

// «۱۵۹ کیلوبایت» — derived from the byte count unless text is given explicitly...
$text ??= $size === null ? null : \MajidDs\Support\Persian::fileSize($size, $fa);
@endphp

<div
    {{ $attributes->class([
        'flex items-center gap-3 rounded-xl border bg-white px-3 py-2.5 dark:bg-white/5',
        'border-red-500 dark:border-red-400' => $invalid,
        'border-zinc-200 dark:border-white/10' => ! $invalid,
    ]) }}
    data-mds-file-item
>
    @if ($image)
        <img src="{{ $image }}" alt="" class="size-10 shrink-0 rounded-lg object-cover">
    @else
        <div @class([
            'flex size-10 shrink-0 items-center justify-center rounded-lg',
            'bg-red-50 text-red-500 dark:bg-red-400/10 dark:text-red-400' => $invalid,
            'bg-zinc-100 text-zinc-500 dark:bg-white/10 dark:text-zinc-400' => ! $invalid,
        ])>
            <flux:icon :icon="$icon" class="size-5" />
        </div>
    @endif

    <div class="min-w-0 flex-1">
        @if ($heading)
            {{-- dir="auto" keeps Latin file names readable inside an RTL list... --}}
            <div class="truncate text-sm font-medium text-zinc-800 dark:text-white" dir="auto" title="{{ $heading }}">{{ $heading }}</div>
        @endif

        @if ($text)
            <div @class([
                'truncate text-xs',
                'text-red-500 dark:text-red-400' => $invalid,
                'text-zinc-500 dark:text-zinc-400' => ! $invalid,
            ])>{{ $text }}</div>
        @endif

        @if ($slot->isNotEmpty())
            {{ $slot }}
        @endif
    </div>

    @if ($actions)
        <div class="ms-auto flex shrink-0 items-center gap-1">{{ $actions }}</div>
    @endif
</div>
