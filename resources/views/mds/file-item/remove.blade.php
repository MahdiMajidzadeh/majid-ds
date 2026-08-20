@props([
    'icon' => 'x-mark',
    'label' => 'حذف فایل',
])

<button
    type="button"
    {{ $attributes->merge(['aria-label' => $label])->class('flex size-7 items-center justify-center rounded-lg text-zinc-400 transition-colors hover:bg-zinc-100 hover:text-zinc-700 dark:hover:bg-white/10 dark:hover:text-zinc-200') }}
    data-mds-file-item-remove
>
    <mds:icon :icon="$icon" variant="micro" class="size-4" />
</button>
