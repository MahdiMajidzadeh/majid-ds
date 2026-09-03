@props([
    'fa' => null,
])

@aware([
    'transition' => false,
    'fa' => null,
])

{{--
    The padding sits on an inner box: the transition animates this element's
    height down to zero, and with border-box sizing a padded element would
    stop at its padding instead.
--}}
<div
    {{ $attributes->class('text-sm text-zinc-500 dark:text-zinc-400') }}
    @if ($transition) x-ref="content" @endif
    data-mds-accordion-content
>
    <div class="pb-4">
        {{ $slot }}
    </div>
</div>
