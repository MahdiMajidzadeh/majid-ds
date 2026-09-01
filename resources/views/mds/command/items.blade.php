@props([
    'empty' => null,
])

@php
// fa (via config) picks the built-in empty text's language.
$empty ??= config('mds.persian_digits', true) ? 'نتیجه‌ای یافت نشد.' : 'No results found.';
@endphp

<div {{ $attributes->class('max-h-72 overflow-y-auto p-1.5') }} data-mds-command-items>
    <div role="listbox" x-bind:id="$id('mds-command-listbox')">
        {{ $slot }}
    </div>

    {{-- Outside the listbox: a listbox takes options, not prose, and this is
         a status the reader should hear when a search comes up empty. --}}
    <div class="px-2 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500" role="status" x-show="empty" x-cloak data-mds-command-empty>
        {{ $empty }}
    </div>
</div>
