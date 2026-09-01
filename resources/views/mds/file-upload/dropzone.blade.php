@props([
    'heading' => null,
    'text' => null,
    'icon' => 'cloud-arrow-up',
    'inline' => false,
    'withProgress' => false,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in strings' language; inherited from the upload field.
$fa ??= config('mds.persian_digits', true);

$heading ??= $fa
    ? 'فایل را اینجا رها کنید یا برای انتخاب کلیک کنید'
    : 'Drop a file here or click to browse';
@endphp

<div
    {{ $attributes->class([
        'rounded-xl border border-dashed border-zinc-200 transition-colors dark:border-white/15',
        'flex items-center gap-3 px-4 py-3' => $inline,
        'flex flex-col items-center justify-center gap-2 px-6 py-8 text-center' => ! $inline,
    ]) }}
    x-bind:class="{
        'border-accent bg-accent/5': dragging,
        'ring-2 ring-accent': focused,
    }"
    data-mds-file-upload-dropzone
>
    <div @class([
        'flex shrink-0 items-center justify-center rounded-full bg-zinc-100 text-zinc-500 dark:bg-white/10 dark:text-zinc-400',
        'size-9' => $inline,
        'size-12' => ! $inline,
    ])>
        <span x-show="loading" x-cloak>
            <svg @class(['animate-spin', 'size-4' => $inline, 'size-5' => ! $inline]) viewBox="0 0 24 24" fill="none" aria-hidden="true">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="2.5" opacity="0.25"/>
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"/>
            </svg>
        </span>

        <span x-show="! loading">
            <mds:icon :icon="$icon" @class(['size-4' => $inline, 'size-6' => ! $inline]) />
        </span>
    </div>

    <div @class(['min-w-0', 'flex-1' => $inline, 'flex flex-col items-center gap-1' => ! $inline])>
        <div @class(['text-sm font-medium text-zinc-800 dark:text-white', 'truncate' => $inline])>{{ $heading }}</div>

        @if ($withProgress)
            <div @class(['flex w-full items-center gap-2', 'mt-1.5' => $inline]) x-show="loading" x-cloak>
                <div class="h-1.5 flex-1 overflow-hidden rounded-full bg-zinc-200 dark:bg-white/10">
                    <div
                        class="h-full rounded-full bg-accent transition-[width] duration-150"
                        x-bind:style="{ width: Math.round(progress) + '%' }"
                        role="progressbar"
                        aria-label="{{ $fa ? 'در حال بارگذاری' : 'Uploading' }}"
                        aria-valuemin="0"
                        aria-valuemax="100"
                        x-bind:aria-valuenow="Math.round(progress)"
                    ></div>
                </div>

                <span class="shrink-0 text-xs tabular-nums text-zinc-500 dark:text-zinc-400" x-text="percent"></span>
            </div>

            @if ($text)
                <div @class(['text-xs text-zinc-500 dark:text-zinc-400', 'truncate' => $inline]) x-show="! loading">{{ $text }}</div>
            @endif
        @elseif ($text)
            <div @class(['text-xs text-zinc-500 dark:text-zinc-400', 'truncate' => $inline])>{{ $text }}</div>
        @endif

        {{ $slot }}
    </div>
</div>
