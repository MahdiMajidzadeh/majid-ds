@props([
    'icon' => null,
    'title' => null,
    'description' => null,
])

<div {{ $attributes->class('flex flex-col items-center gap-2 px-6 py-12 text-center') }} data-mds-empty-state>
    @if ($icon)
        <div class="mb-2 flex size-16 items-center justify-center rounded-full bg-zinc-100 text-zinc-400 dark:bg-white/10 dark:text-zinc-500">
            <mds:icon :icon="$icon" class="size-8" />
        </div>
    @endif

    @if ($title)
        <flux:heading size="lg">{{ $title }}</flux:heading>
    @endif

    @if ($description)
        <flux:text class="max-w-sm">{{ $description }}</flux:text>
    @endif

    @if ($slot->isNotEmpty())
        <div class="mt-4 flex items-center justify-center gap-3">
            {{ $slot }}
        </div>
    @endif
</div>
