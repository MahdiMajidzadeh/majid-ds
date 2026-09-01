@props([
    'icon' => 'magnifying-glass',
    'clearable' => false,
    'closable' => false,
    'clearLabel' => null,
    'closeLabel' => null,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in labels' language; inherited from the palette.
$fa ??= config('mds.persian_digits', true);

$clearLabel ??= $fa ? 'پاک کردن' : 'Clear';
$closeLabel ??= $fa ? 'بستن' : 'Close';
@endphp

<div class="flex items-center gap-2 border-b border-zinc-200 px-3 dark:border-white/10" data-mds-command-input>
    @if ($icon)
        <mds:icon :icon="$icon" variant="micro" class="size-4 shrink-0 text-zinc-400 dark:text-zinc-500" />
    @endif

    <input
        type="text"
        x-model="query"
        x-on:keydown.down.prevent="move(1)"
        x-on:keydown.up.prevent="move(-1)"
        x-on:keydown.enter.prevent="select()"
        role="combobox"
        aria-expanded="true"
        aria-autocomplete="list"
        x-bind:aria-controls="$id('mds-command-listbox')"
        x-bind:aria-activedescendant="activeId"
        {{ $attributes->class('w-full flex-1 bg-transparent py-3 text-sm text-zinc-800 outline-none placeholder:text-zinc-400 dark:text-white dark:placeholder:text-zinc-500') }}
    >

    @if ($clearable)
        <button
            type="button"
            class="shrink-0 rounded p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
            x-show="query !== ''"
            x-cloak
            x-on:click="query = ''; $el.closest('[data-mds-command-input]').querySelector('input').focus()"
            aria-label="{{ $clearLabel }}"
        >
            <mds:icon icon="x-mark" variant="micro" class="size-4" />
        </button>
    @endif

    @if ($closable)
        <button
            type="button"
            class="shrink-0 rounded p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
            x-on:click="$el.closest('dialog')?.close()"
            aria-label="{{ $closeLabel }}"
        >
            <mds:icon icon="x-mark" variant="micro" class="size-4" />
        </button>
    @endif
</div>
