@props([
    'tools' => null,
    'label' => null,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in labels' language.
$fa ??= config('mds.persian_digits', true);

$label ??= $fa ? 'نوار ابزار قالب‌بندی' : 'Formatting toolbar';

// The default set, written the way a caller writes it: `|` is a separator.
$tools ??= 'bold italic underline strike | h1 h2 h3 | bullet ordered | quote code | link unlink | direction clear';

$items = is_array($tools) ? $tools : preg_split('/\s+/', trim((string) $tools), -1, PREG_SPLIT_NO_EMPTY);
@endphp

<div
    {{ $attributes->class([
        'flex flex-wrap items-center gap-0.5 border-b border-zinc-200 bg-zinc-50/80 p-1 dark:border-white/10 dark:bg-white/[3%]',
    ]) }}
    role="toolbar"
    aria-label="{{ $label }}"
    x-bind:aria-controls="$id('mds-editor-surface')"
    x-on:keydown="toolbarKeydown($event)"
    data-mds-editor-toolbar
>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @else
        @foreach ($items as $item)
            @if ($item === '|')
                <span
                    class="mx-1 h-5 w-px shrink-0 bg-zinc-200 dark:bg-white/15"
                    role="separator"
                    aria-orientation="vertical"
                    data-mds-editor-separator
                ></span>
            @else
                <mds:editor.button :command="$item" :fa="$fa" />
            @endif
        @endforeach
    @endif
</div>
