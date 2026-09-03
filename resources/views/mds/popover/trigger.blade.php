{{--
    Wraps the caller's button: Blade cannot inject attributes into slot
    content, so the click lands on this span and the ARIA state is written
    onto the real button at runtime (syncTrigger, via x-effect) — where a
    screen reader actually reads it. Wrap exactly one focusable element.
--}}
<span
    {{ $attributes->class('inline-flex') }}
    x-ref="trigger"
    x-bind:id="$id('mds-popover-trigger')"
    x-effect="syncTrigger()"
    x-on:click="toggle()"
    x-on:mouseenter="enter()"
    x-on:mouseleave="leave()"
    x-on:focusin="focusin($event)"
    data-mds-popover-trigger
>{{ $slot }}</span>
