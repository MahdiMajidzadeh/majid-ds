@props([
    'side' => 'bottom',
    'align' => 'center',
    'sideOffset' => null,
    'arrow' => true,
])

@php
// Only the four values placement understands; anything else falls back...
$side = in_array($side, ['top', 'bottom', 'start', 'end'], true) ? $side : 'bottom';
$align = in_array($align, ['start', 'center', 'end'], true) ? $align : 'center';

// Without the arrow the card sits a little closer to the link, like Appica's...
$sideOffset = (int) ($sideOffset ?? ($arrow ? 10 : 6));
@endphp

{{--
    The template does two jobs. Its content is inert to the HTML parser, so a
    block-level popup can legally sit inside a <p> — without it the parser
    closes the paragraph at the <div> and reparents the popup out of the Alpine
    scope. And x-teleport moves the popup to <body>, the same portal every
    hover-card library uses. $refs cannot cross a teleport boundary, so the
    popup and arrow register themselves into the component scope from x-init.
--}}
<template x-teleport="body">
<div
    {{ $attributes->class('fixed z-50 w-72 rounded-xl border border-zinc-200 bg-white p-4 text-start shadow-lg dark:border-white/10 dark:bg-zinc-800') }}
    x-init="contentEl = $el"
    x-show="open"
    x-cloak
    x-transition:enter="transition duration-150 ease-out motion-reduce:transition-none"
    x-transition:enter-start="scale-95 opacity-0"
    x-transition:enter-end="scale-100 opacity-100"
    x-transition:leave="transition duration-100 ease-in motion-reduce:transition-none"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-on:mouseenter="enter(true)"
    x-on:mouseleave="leave()"
    x-on:keydown.escape="leave(true)"
    style="top: -9999px; left: -9999px"
    data-side="{{ $side }}"
    data-align="{{ $align }}"
    data-side-offset="{{ $sideOffset }}"
    data-mds-preview-card-content
>
    @if ($arrow)
        {{--
            A rotated square halfway out of the card, borrowing its border.
            data-rendered-side is the side placement actually chose (after any
            flip), so the arrow follows the card to the opposite edge. The
            borders are physical on purpose: which two edges of a rotated
            square face the link is geometry, not reading direction — and
            place() positions it in physical viewport coordinates anyway.
        --}}
        <span
            x-init="arrowEl = $el"
            class="absolute size-2.5 -translate-x-1/2 rotate-45 border-zinc-200 bg-white dark:border-white/10 dark:bg-zinc-800
                in-data-[rendered-side=bottom]:-top-[6px] in-data-[rendered-side=bottom]:border-t in-data-[rendered-side=bottom]:border-l
                in-data-[rendered-side=top]:-bottom-[6px] in-data-[rendered-side=top]:border-b in-data-[rendered-side=top]:border-r
                in-data-[rendered-side=left]:-right-[6px] in-data-[rendered-side=left]:translate-x-0 in-data-[rendered-side=left]:-translate-y-1/2 in-data-[rendered-side=left]:border-t in-data-[rendered-side=left]:border-r
                in-data-[rendered-side=right]:-left-[6px] in-data-[rendered-side=right]:translate-x-0 in-data-[rendered-side=right]:-translate-y-1/2 in-data-[rendered-side=right]:border-b in-data-[rendered-side=right]:border-l"
            aria-hidden="true"
            data-mds-preview-card-arrow
        ></span>
    @endif

    <div class="flex flex-col gap-2 text-sm text-zinc-600 dark:text-zinc-300">{{ $slot }}</div>
</div>
</template>
