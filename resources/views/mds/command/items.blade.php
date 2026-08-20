@props([
    'empty' => 'نتیجه‌ای یافت نشد.',
])

<div {{ $attributes->class('max-h-72 overflow-y-auto p-1.5') }} role="listbox" data-mds-command-items>
    {{ $slot }}

    <div class="px-2 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500" x-show="empty" x-cloak data-mds-command-empty>
        {{ $empty }}
    </div>
</div>
