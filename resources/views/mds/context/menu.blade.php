@props([
    'fa' => null,
])

@aware(['fa' => null])

@php
$fa ??= config('mds.persian_digits', true);
@endphp

{{--
    The template does two jobs, as in preview-card: its content is inert to
    the HTML parser, so a block-level panel can legally sit inside a <p> or a
    <span> — without it the parser closes the paragraph at the <div> and
    reparents the panel out of the Alpine scope. And x-teleport moves the
    panel to <body>, so it inherits the page's dir. $refs cannot cross a
    teleport boundary, so the panel registers itself into the component
    scope from x-init.

    The panel carries no role: the caller's flux:menu already renders the
    menu role (and Flux's arrow-key handling). Flux's menu is a manual
    popover — the component shows it into the top layer and positions it
    there; the wrapper's own left/top only matter for a slot that is not a
    popover.
--}}
<template x-teleport="body">
<div
    {{ $attributes->class('fixed z-50') }}
    x-init="panelEl = $el"
    x-show="open"
    x-cloak
    x-on:keydown.escape.prevent.stop="close()"
    x-on:keydown.tab="close()"
    x-on:contextmenu.prevent
    x-on:lofi-close-popovers="close()"
    x-on:click="clicked($event)"
    style="top: -9999px; left: -9999px"
    data-mds-context-menu
>
    {{ $slot }}
</div>
</template>
