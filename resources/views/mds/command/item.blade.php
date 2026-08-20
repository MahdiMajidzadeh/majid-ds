@props([
    'icon' => null,
    'iconVariant' => 'micro',
    'kbd' => null,
    'href' => null,
])

@php
$tag = $href ? 'a' : 'button';

$attributes = $attributes->merge($href ? ['href' => $href] : ['type' => 'button']);
@endphp

<{{ $tag }}
    {{ $attributes->class('flex w-full cursor-pointer items-center gap-2.5 rounded-lg px-2.5 py-2 text-start text-sm text-zinc-700 dark:text-zinc-200') }}
    x-show="matches($el)"
    x-bind:class="isActive($el) && 'bg-zinc-100 dark:bg-white/10'"
    x-on:mouseenter="active = items().indexOf($el)"
    x-bind:aria-selected="isActive($el) ? 'true' : 'false'"
    role="option"
    data-mds-command-item
>
    @if ($icon)
        <flux:icon :icon="$icon" :variant="$iconVariant" class="size-4 shrink-0 text-zinc-400 dark:text-zinc-500" />
    @endif

    <span class="flex-1 truncate">{{ $slot }}</span>

    @if ($kbd)
        <kbd class="ms-auto shrink-0 rounded border border-zinc-200 bg-zinc-50 px-1.5 py-0.5 font-sans text-[11px] text-zinc-400 dark:border-white/10 dark:bg-white/5 dark:text-zinc-500" dir="ltr">{{ $kbd }}</kbd>
    @endif
</{{ $tag }}>
