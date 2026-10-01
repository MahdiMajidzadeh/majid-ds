@props([
    'value' => null,
    'name' => null,
    'icons' => null,
    'variant' => null,
    'label' => null,
    'description' => null,
    'placeholder' => null,
    'searchPlaceholder' => null,
    'empty' => null,
    'columns' => 8,
    'clearable' => false,
    'size' => null,
    'disabled' => false,
    'invalid' => false,
    'error' => null,
    'fa' => null,
])

@include('mds::partials.digits')

@php
// fa picks the built-in labels' language.
$fa ??= config('mds.persian_digits', true);

$placeholder ??= $fa ? 'انتخاب آیکون' : 'Pick an icon';
$searchPlaceholder ??= $fa ? 'جستجوی آیکون…' : 'Search icons…';
$empty ??= $fa ? 'آیکونی یافت نشد.' : 'No icons found.';
$clearLabel = $fa ? 'پاک کردن' : 'Clear';

// An explicit :error wins; otherwise fall back to the validation bag...
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

// The menu: a list of Hugeicons names, or a name => label map when the
// icons should be searchable by a word in the reader's language. Nothing
// given means the kit's curated default set.
$options = collect($icons ?? \MajidDs\Support\Icons::PICKER)
    ->mapWithKeys(fn ($item, $key) => is_int($key) ? [(string) $item => null] : [(string) $key => $item]);

$columns = max(1, (int) $columns);

$triggerSize = match ($size) {
    'sm' => 'py-1.5',
    default => 'py-2',
};
@endphp

@once
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerIconPicker = (Alpine) => {
    if (window.mds.iconPickerRegistered) return
    window.mds.iconPickerRegistered = true

    Alpine.data('mdsIconPicker', (config) => ({
        open: false,
        query: '',
        needle: '',
        value: config.value ?? '',
        svg: '',
        columns: config.columns ?? 8,
        trigger: null,
        observer: null,

        init() {
            // The trigger's preview is whatever the grid drew for the chosen
            // name — the server already rendered that SVG once, so the
            // client never needs an icon source of its own.
            this.svg = this.svgFor(this.value) || this.$refs.preview.innerHTML

            this.$watch('query', (query) => { this.needle = this.normalize(query) })

            /*
            | The server can change the value too — a form reset, a record
            | loaded for editing — and that arrives as a Livewire morph, which
            | rewrites the hidden input's `value` ATTRIBUTE. Alpine hears
            | nothing, so the preview went on showing the old icon while the
            | bound property held another. The observer fires on our own
            | writes as well; adopt() returning early once the names already
            | match is what stops the loop.
            */
            const input = this.$refs.input

            this.observer = new MutationObserver(() => this.adopt(input.getAttribute('value')))
            this.observer.observe(input, { attributes: true, attributeFilter: ['value'] })
        },

        destroy() {
            this.observer?.disconnect()
            this.observer = null
        },

        get empty() {
            return this.value === ''
        },

        // No visible option is left for the query — the grid shows the
        // empty line instead.
        get none() {
            return this.needle !== '' && this.visible().length === 0
        },

        // Arabic spellings of Persian letters fold together, digits of either
        // script fold to Latin, and a hyphen reads as a space — so 'search 01'
        // finds search-01, and a label in Persian finds its icon too.
        normalize(s) {
            return window.mds.latinDigits(
                String(s).toLowerCase().replace(/[يى]/g, 'ی').replace(/ك/g, 'ک'),
            ).replace(/[-_]+/g, ' ').trim()
        },

        shows(el) {
            return this.needle === '' || (el.dataset.mdsHaystack ?? '').includes(this.needle)
        },

        options() {
            return [...this.$refs.grid.querySelectorAll('[data-mds-icon-picker-option]')]
        },

        visible() {
            return this.options().filter(el => this.shows(el))
        },

        optionFor(name) {
            return name === '' ? null : this.$refs.grid.querySelector(`[data-mds-icon-picker-option="${CSS.escape(name)}"]`)
        },

        svgFor(name) {
            return this.optionFor(name)?.innerHTML ?? ''
        },

        isSelected(name) {
            return name !== '' && name === this.value
        },

        toggle(state = null) {
            const next = state ?? ! this.open

            if (next === this.open) return

            this.open = next

            this.$nextTick(() => {
                // Focus follows the panel, and comes back to whatever opened
                // it — otherwise Escape drops the reader at the top of the page.
                if (this.open) {
                    this.trigger = document.activeElement
                    this.query = ''
                    this.$refs.search?.focus()
                } else {
                    this.trigger?.focus()
                    this.trigger = null
                }
            })
        },

        commit() {
            const input = this.$refs.input

            input.value = this.value
            input.dispatchEvent(new Event('input', { bubbles: true }))
        },

        pick(name) {
            this.value = name
            this.svg = this.svgFor(name)
            this.commit()
            this.toggle(false)
        },

        clear() {
            this.value = ''
            this.svg = ''
            this.commit()
        },

        // Silent, because a morph is the server talking rather than the
        // reader: the preview follows, no input event goes back.
        adopt(raw) {
            const next = (raw ?? '').trim()

            if (next === this.value) return

            this.value = next
            this.svg = this.svgFor(next)
        },

        // Roving focus across the visible options. Left and right are
        // visual: on an RTL page the next option sits to the left, so the
        // arrows swap to keep "right" meaning right.
        move(delta, horizontal = false) {
            const items = this.visible()

            if (! items.length) return

            if (horizontal && getComputedStyle(this.$refs.grid).direction === 'rtl') delta = -delta

            const index = items.indexOf(document.activeElement)

            if (index === -1) {
                items[0].focus()
                return
            }

            const next = index + delta

            // Up past the first row lands back in the search box.
            if (next < 0) {
                if (delta < 0 && ! horizontal) this.$refs.search?.focus()
                return
            }

            items[Math.min(next, items.length - 1)].focus()
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerIconPicker(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerIconPicker(window.Alpine))
}
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative', 'opacity-50' => $disabled]) }}
        @if ($disabled) inert aria-disabled="true" @endif
        x-id="['mds-icon-picker-panel']"
        x-data="mdsIconPicker({ value: @js($value), columns: @js($columns) })"
        x-on:keydown.escape.window="toggle(false)"
        data-mds-icon-picker
    >
        <input
            type="hidden"
            x-ref="input"
            value="{{ $value }}"
            @if ($name) name="{{ $name }}" @endif
            {{ $attributes->whereStartsWith('wire:model') }}
        >

        <div @class([
            'flex items-center gap-1 rounded-lg border bg-white pe-2 shadow-xs dark:bg-white/10',
            'border-red-500 dark:border-red-400' => $invalid,
            'border-zinc-200 dark:border-white/10' => ! $invalid,
        ])>
            <button
                type="button"
                class="flex min-w-0 flex-1 items-center gap-2 ps-3 {{ $triggerSize }} text-start text-sm outline-none focus-visible:ring-2 focus-visible:ring-accent/50 rounded-lg"
                x-on:click="toggle()"
                x-bind:aria-expanded="open ? 'true' : 'false'"
                aria-haspopup="dialog"
                x-bind:aria-controls="$id('mds-icon-picker-panel')"
                data-mds-icon-picker-trigger
            >
                <span class="flex size-5 shrink-0 items-center justify-center text-zinc-700 dark:text-zinc-200 [&>svg]:size-5" x-ref="preview" x-html="svg">@if ($value)<mds:icon :icon="$value" :variant="$variant" class="size-5" />@endif</span>
                <span class="truncate text-zinc-400 dark:text-zinc-500" x-show="empty">{{ $placeholder }}</span>
                <span class="truncate text-zinc-800 dark:text-white" x-show="! empty" x-text="value" x-cloak dir="ltr"></span>
                <mds:icon icon="chevron-down" variant="micro" class="ms-auto size-4 shrink-0 text-zinc-400 dark:text-zinc-500" />
            </button>

            @if ($clearable)
                <button
                    type="button"
                    class="shrink-0 rounded p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                    x-show="! empty"
                    x-cloak
                    x-on:click="clear()"
                    aria-label="{{ $clearLabel }}"
                >
                    <mds:icon icon="x-mark" variant="micro" class="size-4" />
                </button>
            @endif
        </div>

        <div
            class="absolute start-0 top-full z-50 mt-2 w-72 rounded-xl border border-zinc-200 bg-white p-2 shadow-lg dark:border-white/10 dark:bg-zinc-800"
            x-ref="panel"
            x-bind:id="$id('mds-icon-picker-panel')"
            role="dialog"
            aria-label="{{ $label ?? $placeholder }}"
            x-show="open"
            x-cloak
            x-transition.opacity.duration.100ms
            x-on:click.outside="toggle(false)"
            data-mds-icon-picker-panel
        >
            <div class="mb-2 flex items-center gap-2 border-b border-zinc-200 px-2 dark:border-white/10">
                <mds:icon icon="magnifying-glass" variant="micro" class="size-4 shrink-0 text-zinc-400 dark:text-zinc-500" />

                <input
                    type="text"
                    x-ref="search"
                    x-model="query"
                    x-on:keydown.down.prevent="move(1)"
                    dir="ltr"
                    placeholder="{{ $searchPlaceholder }}"
                    aria-label="{{ $searchPlaceholder }}"
                    class="w-full flex-1 bg-transparent py-2 text-sm text-zinc-800 outline-none placeholder:text-zinc-400 dark:text-white dark:placeholder:text-zinc-500"
                    data-mds-icon-picker-search
                >
            </div>

            <div class="max-h-64 overflow-y-auto">
                <div
                    role="listbox"
                    x-ref="grid"
                    aria-label="{{ $label ?? $placeholder }}"
                    class="grid gap-1"
                    style="grid-template-columns: repeat({{ $columns }}, minmax(0, 1fr))"
                    x-on:keydown.right.prevent="move(1, true)"
                    x-on:keydown.left.prevent="move(-1, true)"
                    x-on:keydown.down.prevent="move(columns)"
                    x-on:keydown.up.prevent="move(-columns)"
                    data-mds-icon-picker-grid
                >
                    @foreach ($options as $iconName => $iconLabel)
                        <button
                            type="button"
                            role="option"
                            tabindex="-1"
                            class="flex aspect-square items-center justify-center rounded-md text-zinc-600 outline-none hover:bg-zinc-100 focus-visible:ring-2 focus-visible:ring-accent/50 dark:text-zinc-300 dark:hover:bg-white/10"
                            x-bind:class="isSelected($el.dataset.mdsIconPickerOption) && 'bg-accent/10 text-accent ring-1 ring-accent'"
                            x-bind:aria-selected="isSelected($el.dataset.mdsIconPickerOption) ? 'true' : 'false'"
                            x-show="shows($el)"
                            x-on:click="pick($el.dataset.mdsIconPickerOption)"
                            title="{{ $iconLabel ?? $iconName }}"
                            aria-label="{{ $iconLabel ?? $iconName }}"
                            data-mds-icon-picker-option="{{ $iconName }}"
                            data-mds-haystack="{{ strtolower(trim(str_replace(['-', '_'], ' ', $iconName.' '.$iconLabel))) }}"
                        >
                            <mds:icon :icon="$iconName" :variant="$variant" class="size-5" />
                        </button>
                    @endforeach
                </div>

                {{-- Outside the listbox: a listbox takes options, not prose. --}}
                <div class="px-2 py-6 text-center text-sm text-zinc-400 dark:text-zinc-500" role="status" x-show="none" x-cloak data-mds-icon-picker-empty>
                    {{ $empty }}
                </div>
            </div>
        </div>
    </div>

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif
</flux:field>
