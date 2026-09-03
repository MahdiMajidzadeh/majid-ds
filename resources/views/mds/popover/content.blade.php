@props([
    'arrow' => null,
    'closable' => false,
    'fa' => null,
])

@aware(['arrow' => false, 'fa' => null])

@php
// fa picks the close button's label language; inherited from the root.
$fa ??= config('mds.persian_digits', true);

// A caller's own name for the dialog wins over the trigger's text.
$named = $attributes->has('aria-label') || $attributes->has('aria-labelledby');
@endphp

{{--
    The template does two jobs. Its content is inert to the HTML parser, so a
    block-level panel can legally sit inside a <p>, and x-teleport moves the
    panel to <body> — out of overflow-hidden and transformed ancestors. The
    panel registers itself into the component scope from x-init.
--}}
<template x-teleport="body">
<div
    {{ $attributes->class(['fixed z-50 min-w-48 rounded-lg border border-zinc-200 bg-white p-4 text-start shadow-sm outline-none dark:border-white/10 dark:bg-zinc-800', 'pe-10' => $closable]) }}
    x-init="contentEl = $el"
    x-ref="content"
    x-bind:id="$id('mds-popover')"
    x-show="open"
    x-cloak
    x-transition:enter="transition duration-100 ease-out motion-reduce:transition-none"
    x-transition:enter-start="opacity-0"
    x-transition:enter-end="opacity-100"
    x-transition:leave="transition duration-75 ease-in motion-reduce:transition-none"
    x-transition:leave-start="opacity-100"
    x-transition:leave-end="opacity-0"
    x-on:click.outside="outside($event)"
    x-on:keydown.tab="tab($event)"
    x-on:mouseenter="enter()"
    x-on:mouseleave="leave()"
    x-on:focusout="focusout($event)"
    role="dialog"
    aria-modal="false"
    @unless ($named) x-bind:aria-labelledby="$id('mds-popover-trigger')" @endunless
    tabindex="-1"
    style="top: -9999px; left: -9999px"
    data-mds-popover-content
>
    {{ $slot }}

    @if ($closable)
        {{-- Last in DOM order so the content's own controls receive focus first. --}}
        <button
            type="button"
            class="absolute end-2 top-2 rounded-md p-1 text-zinc-400 hover:text-zinc-800 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:text-zinc-500 dark:hover:text-white"
            x-on:click="close()"
            aria-label="{{ $fa ? 'بستن' : 'Close' }}"
            data-mds-popover-close
        >
            <mds:icon icon="x-mark" variant="micro" class="size-4" />
        </button>
    @endif

    @if ($arrow)
        {{--
            A rotated square halfway out of the panel, borrowing its border.
            data-rendered-side is the side place() actually chose (after any
            flip), so the arrow follows the panel to the opposite edge. The
            borders are physical on purpose: which two edges of a rotated
            square face the trigger is geometry, not reading direction.
        --}}
        <span
            x-init="arrowEl = $el"
            class="absolute size-2.5 -translate-x-1/2 rotate-45 border-zinc-200 bg-white dark:border-white/10 dark:bg-zinc-800
                in-data-[rendered-side=bottom]:-top-[6px] in-data-[rendered-side=bottom]:border-t in-data-[rendered-side=bottom]:border-l
                in-data-[rendered-side=top]:-bottom-[6px] in-data-[rendered-side=top]:border-b in-data-[rendered-side=top]:border-r
                in-data-[rendered-side=left]:-right-[6px] in-data-[rendered-side=left]:translate-x-0 in-data-[rendered-side=left]:-translate-y-1/2 in-data-[rendered-side=left]:border-t in-data-[rendered-side=left]:border-r
                in-data-[rendered-side=right]:-left-[6px] in-data-[rendered-side=right]:translate-x-0 in-data-[rendered-side=right]:-translate-y-1/2 in-data-[rendered-side=right]:border-b in-data-[rendered-side=right]:border-l"
            aria-hidden="true"
            data-mds-popover-arrow
        ></span>
    @endif
</div>
</template>
