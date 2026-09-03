@props([
    'key' => null,
    'heading' => null,
    'name' => null,
    'limit' => null,
    'empty' => null,
    'error' => null,
    'invalid' => false,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the count badge's digits and the built-in strings' language;
// inherited from the board.
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag. The
// column posts an array, so rules report against `name.0`, `name.1`, … —
// the `name.*` key is the second half of that fallback.
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: ($errors->first($name.'.*') ?: null);
}

$invalid = $invalid || filled($error);

// The state key: what the moved event and the counts are keyed on. Derived
// from the name, else from the heading — deterministically, so a rebuilt
// page is byte-identical.
$key = (string) ($key ?? '');

if ($key === '') {
    $key = (string) ($name ?: (filled($heading) ? 'c'.substr(md5((string) $heading), 0, 8) : 'column'));
}

$limit = is_numeric($limit) && (int) $limit > 0 ? (int) $limit : null;

$empty ??= $fa ? 'کارتی نیست' : 'No cards';

// The slot is already rendered when this view runs, so the cards in it are
// readable here — that is where the column's order comes from. Nothing else
// knows it: the cards are slot content, not a prop.
$ids = [];
preg_match_all('/data-mds-kanban-card="([^"]*)"/', (string) $slot, $matches);

foreach ($matches[1] as $id) {
    $ids[] = html_entity_decode($id, ENT_QUOTES, 'UTF-8');
}

$total = count($ids);
$over = $limit !== null && $total > $limit;

// Templates for the count badge and the region name. Alpine re-renders both
// from these as cards move, so the two can never drift apart.
$countTemplate = $fa ? ':n کارت' : ':n cards';
$countOneTemplate = $fa ? ':n کارت' : ':n card';
$countLimitTemplate = $fa ? ':n از :limit کارت' : ':n of :limit cards';
$overText = $fa ? 'بیش از حد مجاز' : 'Over limit';

$digits = fn ($value) => $fa ? \MajidDs\Support\Persian::digits((string) $value) : (string) $value;

$countText = $limit !== null
    ? str_replace([':n', ':limit'], [$digits($total), $digits($limit)], $countLimitTemplate)
    : str_replace(':n', $digits($total), $total === 1 ? $countOneTemplate : $countTemplate);

$regionLabel = implode(' — ', array_filter([
    filled($heading) ? $heading : $key,
    $countText,
    $over ? $overText : null,
]));
@endphp

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([
        'flex w-72 shrink-0 flex-col self-stretch rounded-xl border bg-zinc-50 dark:bg-white/5',
        'border-red-500 dark:border-red-400' => $invalid,
        'border-zinc-200 dark:border-white/10' => ! $invalid,
    ]) }}
    role="region"
    aria-label="{{ $regionLabel }}"
    x-bind:aria-label="regionLabel($el)"
    @if ($invalid) aria-invalid="true" @endif
    @if ($over) data-mds-kanban-over @endif
    x-bind:data-mds-kanban-over="over($el) ? '' : false"
    @if ($limit !== null) data-mds-kanban-limit="{{ $limit }}" @endif
    data-mds-kanban-count="{{ $countTemplate }}"
    data-mds-kanban-count-one="{{ $countOneTemplate }}"
    data-mds-kanban-count-limit="{{ $countLimitTemplate }}"
    data-mds-kanban-over-text="{{ $overText }}"
    data-mds-kanban-column="{{ $key }}"
>
    <div class="flex items-center gap-2 border-b border-zinc-200 px-3 py-2.5 dark:border-white/10" data-mds-kanban-header>
        <span class="min-w-0 flex-1 truncate text-sm font-medium text-zinc-800 dark:text-white" data-mds-kanban-heading>{{ filled($heading) ? $heading : $key }}</span>

        {{-- Over the limit is never colour alone: the badge turns red AND
             gains an icon with a word behind it. --}}
        <span
            class="inline-flex shrink-0 items-center gap-1 text-red-600 dark:text-red-400"
            x-show="over($el.closest('[data-mds-kanban-column]'))"
            @if (! $over) style="display: none" @endif
            data-mds-kanban-warning
        >
            <mds:icon icon="exclamation-triangle" variant="micro" class="size-4" />
            <span class="sr-only">{{ $overText }}</span>
        </span>

        <span
            @class([
                'shrink-0 rounded-full px-2 py-0.5 text-xs tabular-nums',
                'bg-red-100 font-medium text-red-700 dark:bg-red-400/10 dark:text-red-400' => $over,
                'bg-zinc-200/70 text-zinc-600 dark:bg-white/10 dark:text-zinc-300' => ! $over,
            ])
            {{-- Object syntax, not a ternary: only this form REMOVES the
                 classes the server rendered, and two backgrounds of the same
                 specificity would otherwise fight over stylesheet order. --}}
            x-bind:class="{
                'bg-red-100 font-medium text-red-700 dark:bg-red-400/10 dark:text-red-400': over($el.closest('[data-mds-kanban-column]')),
                'bg-zinc-200/70 text-zinc-600 dark:bg-white/10 dark:text-zinc-300': ! over($el.closest('[data-mds-kanban-column]')),
            }"
            x-text="countText($el.closest('[data-mds-kanban-column]'))"
            data-mds-kanban-count-badge
        >{{ $countText }}</span>

        @isset($actions)
            <span class="shrink-0" data-mds-kanban-actions>{{ $actions }}</span>
        @endisset
    </div>

    <ul role="list" class="flex min-h-24 flex-1 flex-col gap-2 p-2" data-mds-kanban-cards>
        {{ $slot }}

        {{-- Last on purpose: a card dropped at the end is appended after it,
             and by then the placeholder is hidden anyway. --}}
        <li
            class="pointer-events-none rounded-lg border border-dashed border-zinc-300 px-3 py-6 text-center text-xs text-zinc-400 dark:border-white/15 dark:text-zinc-500"
            x-show="count($el.closest('[data-mds-kanban-column]')) === 0"
            @if ($total > 0) style="display: none" @endif
            aria-hidden="true"
            data-mds-kanban-empty
        >{{ $empty }}</li>
    </ul>

    {{-- The Livewire half: a multiple select reads back as an array, so the
         bound property is the column's card ids in order. Hidden, so it is
         neither focusable nor announced; it still posts and still morphs. --}}
    <select
        multiple
        hidden
        tabindex="-1"
        aria-hidden="true"
        @if ($name) name="{{ $name }}[]" @endif
        data-mds-kanban-state="{{ $key }}"
        {{ $attributes->whereStartsWith('wire:model') }}
    >
        @foreach ($ids as $id)
            <option value="{{ $id }}" selected>{{ $id }}</option>
        @endforeach
    </select>

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session
             error bag: an explicit :error has to render outside a request. --}}
        <div role="alert" aria-live="polite" aria-atomic="true" class="border-t border-zinc-200 px-3 py-2 text-sm font-medium text-red-500 dark:border-white/10 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</div>
