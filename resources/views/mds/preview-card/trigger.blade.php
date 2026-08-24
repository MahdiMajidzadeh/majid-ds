{{-- A real link: it keeps navigating on click; the preview is supplementary. --}}
<a
    {{ $attributes->class('font-medium text-accent underline-offset-4 hover:underline') }}
    x-init="triggerEl = $el"
    x-on:mouseenter="enter()"
    x-on:mouseleave="leave()"
    x-on:focus="enter(true)"
    x-on:blur="leave(true)"
    data-mds-preview-card-trigger
>{{ $slot }}</a>
