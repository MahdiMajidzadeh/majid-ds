<input
    type="text"
    dir="ltr"
    {{ $attributes->merge(['placeholder' => '#000000'])->class('w-full flex-1 rounded-lg border border-zinc-200 bg-white px-2.5 py-1.5 text-sm text-zinc-800 outline-none placeholder:text-zinc-400 dark:border-white/10 dark:bg-white/10 dark:text-white dark:placeholder:text-zinc-500') }}
    x-bind:value="output"
    x-on:change="pick($event.target.value)"
    data-mds-color-picker-input
>
