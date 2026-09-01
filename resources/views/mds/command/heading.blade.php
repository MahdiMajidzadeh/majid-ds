<div
    {{ $attributes->class('px-2.5 pb-1 pt-2.5 text-xs font-medium text-zinc-400 dark:text-zinc-500') }}
    x-show="query === ''"
    role="presentation"
    data-mds-command-heading
>
    {{ $slot }}
</div>
