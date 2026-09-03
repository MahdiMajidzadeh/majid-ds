@props([
    'placeholder' => null,
    'rows' => 6,
    'dir' => null,
    'label' => null,
    'disabled' => false,
    'invalid' => false,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in label's language.
$fa ??= config('mds.persian_digits', true);

$label ??= $fa ? 'ویرایشگر متن' : 'Rich text editor';

// Lines, not pixels — the same unit the composer's `rows` speaks. Logical
// property: it is the block axis that grows, whichever way the text runs.
$minHeight = max(1, (int) $rows) * 1.75;
@endphp

<div class="relative" data-mds-editor-body>
    {{--
        The surface starts empty on the server and is filled by the sanitiser
        on init, so stored HTML never reaches the page as markup. wire:ignore
        keeps a Livewire morph from emptying it again — the hidden input's
        value attribute is what a server-side change arrives through.
    --}}
    <div
        {{ $attributes->class([
            'w-full overflow-y-auto px-3 py-2 text-base text-zinc-700 outline-none sm:text-sm dark:text-zinc-200',
            'focus-visible:outline-none',
            '[&_h1]:mb-2 [&_h1]:text-2xl [&_h1]:font-bold',
            '[&_h2]:mb-2 [&_h2]:text-xl [&_h2]:font-semibold',
            '[&_h3]:mb-2 [&_h3]:text-lg [&_h3]:font-semibold',
            '[&_p]:mb-2',
            '[&_ul]:mb-2 [&_ul]:list-disc [&_ul]:ps-6 [&_ol]:mb-2 [&_ol]:list-decimal [&_ol]:ps-6',
            '[&_blockquote]:mb-2 [&_blockquote]:border-s-4 [&_blockquote]:border-zinc-300 [&_blockquote]:ps-3 [&_blockquote]:text-zinc-500 [&_blockquote]:italic dark:[&_blockquote]:border-white/20',
            '[&_pre]:mb-2 [&_pre]:overflow-x-auto [&_pre]:rounded-md [&_pre]:bg-zinc-100 [&_pre]:p-3 [&_pre]:font-mono [&_pre]:text-sm dark:[&_pre]:bg-white/10',
            '[&_code]:rounded [&_code]:bg-zinc-100 [&_code]:px-1 [&_code]:font-mono [&_code]:text-[0.9em] dark:[&_code]:bg-white/10',
            '[&_a]:text-accent [&_a]:underline',
            '[&_strong]:font-semibold [&_s]:line-through [&_u]:underline',
        ]) }}
        style="min-block-size: {{ $minHeight }}rem"
        x-ref="surface"
        x-on:input="commit(); sync()"
        x-on:keydown="keydown($event)"
        x-on:paste="paste($event)"
        x-on:drop="drop($event)"
        x-on:blur="commit()"
        x-bind:id="$id('mds-editor-surface')"
        contenteditable="{{ $disabled ? 'false' : 'true' }}"
        role="textbox"
        aria-multiline="true"
        aria-label="{{ $label }}"
        @if ($invalid) aria-invalid="true" @endif
        @if ($dir) dir="{{ $dir }}" @endif
        spellcheck="true"
        wire:ignore
        data-mds-editor-content
    ></div>

    @if ($placeholder)
        <div
            class="pointer-events-none absolute start-0 top-0 px-3 py-2 text-base text-zinc-400 select-none sm:text-sm dark:text-zinc-500"
            x-show="empty"
            x-cloak
            aria-hidden="true"
            data-mds-editor-placeholder
        >{{ $placeholder }}</div>
    @endif
</div>
