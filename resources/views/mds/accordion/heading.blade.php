@props([
    'fa' => null,
])

{{-- expanded/disabled are inherited from the item, so the first paint is right before Alpine boots... --}}
@aware([
    'expanded' => false,
    'disabled' => false,
    'fa' => null,
])

<summary
    {{ $attributes->class([
        'flex w-full list-none items-center justify-between gap-3 py-4 text-start text-sm font-medium [&::-webkit-details-marker]:hidden',
        'cursor-pointer text-zinc-800 dark:text-white' => ! $disabled,
        'pointer-events-none text-zinc-400 dark:text-zinc-500' => $disabled,
        'rounded-md outline-none focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
    ]) }}
    role="button"
    aria-expanded="{{ $expanded ? 'true' : 'false' }}"
    x-bind:aria-expanded="expanded ? 'true' : 'false'"
    @if ($disabled) aria-disabled="true" tabindex="-1" @endif
    x-on:click="toggle($event)"
    data-mds-accordion-heading
>
    <span class="min-w-0 flex-1">{{ $slot }}</span>

    <mds:icon
        icon="chevron-down"
        variant="micro"
        class="{{ $expanded ? 'rotate-180 ' : '' }}size-4 shrink-0 text-zinc-400 motion-safe:transition-transform motion-safe:duration-200 dark:text-zinc-500"
        x-bind:class="{ 'rotate-180': expanded }"
        data-mds-accordion-chevron=""
    />
</summary>
