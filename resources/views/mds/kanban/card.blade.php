@props([
    'key' => null,
    'heading' => null,
    'disabled' => false,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in strings' language; inherited from the board.
$fa ??= config('mds.persian_digits', true);

// The card's id in the board state — what the hidden selects hold and what
// the moved event names. Derived from the content when the caller gives
// none: deterministic, so a rebuilt page is byte-identical.
$key = (string) ($key ?? '');

if ($key === '') {
    $key = 'card-'.substr(md5(trim(preg_replace('/\s+/u', ' ', strip_tags((string) $slot).' '.$heading))), 0, 8);
}

// The name a screen reader hears for the card's own role, and the title the
// announcements use — the text content is the fallback for the latter.
$roleDescription = $fa ? 'کارت جابه‌جایی‌پذیر' : 'Draggable card';
$handleLabel = $fa ? 'دستگیره جابه‌جایی' : 'Drag handle';
@endphp

<li
    {{ $attributes->class([
        'group relative flex items-start gap-2 rounded-lg border border-zinc-200 bg-white p-3 text-sm text-zinc-700 shadow-xs transition-shadow dark:border-white/10 dark:bg-zinc-800 dark:text-zinc-200',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
        'data-grabbed:shadow-lg data-grabbed:ring-2 data-grabbed:ring-accent data-dragging:shadow-lg data-dragging:opacity-90',
        'opacity-60' => $disabled,
    ]) }}
    role="listitem"
    tabindex="0"
    aria-roledescription="{{ $roleDescription }}"
    x-bind:aria-describedby="$id('mds-kanban-hint')"
    @if ($disabled) data-disabled aria-disabled="true" @endif
    @if (filled($heading)) data-mds-kanban-card-title="{{ $heading }}" @endif
    data-mds-kanban-card="{{ $key }}"
>
    {{-- The handle is the only patch of the card that turns off touch
         scrolling, so a finger can still scroll the board everywhere else.
         Decoration: the card itself is the focusable, keyboard-movable
         thing, and a second tab stop would only get in the way. --}}
    <span
        @class([
            'mt-0.5 shrink-0 touch-none text-zinc-300 dark:text-zinc-600',
            'cursor-grab active:cursor-grabbing' => ! $disabled,
            'cursor-not-allowed' => $disabled,
        ])
        aria-hidden="true"
        title="{{ $handleLabel }}"
        data-mds-kanban-handle
    >
        <mds:icon icon="bars-3" variant="micro" class="size-4" />
    </span>

    <span class="min-w-0 flex-1">
        @if (filled($heading))
            <span class="block font-medium text-zinc-800 dark:text-white" data-mds-kanban-card-heading>{{ $heading }}</span>
        @endif

        {{ $slot }}
    </span>
</li>
