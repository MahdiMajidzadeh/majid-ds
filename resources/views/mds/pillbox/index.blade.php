@props([
    'value' => null,
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => null,
    'searchLabel' => null,
    'clearable' => false,
    'max' => null,
    'empty' => true,
    'disabled' => false,
    'error' => null,
    'invalid' => false,
    'fa' => null,
])

@php
// fa picks the built-in strings' language.
$fa ??= config('mds.persian_digits', true);

// Like flux:input, the field is named after its binding when no name is
// given — that is the key the validation bag is read with below.
$name ??= $attributes->whereStartsWith('wire:model')->first();

// An explicit :error wins; otherwise fall back to the validation bag...
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

$errorId = 'mds-pillbox-error-'.substr(md5((string) $name), 0, 8);

// The selection: an array (the Livewire shape), a comma-separated string
// (the plain-form shape), or nothing. Values are compared as strings, the
// way the DOM hands them back.
$selected = match (true) {
    is_array($value) => $value,
    $value instanceof \Illuminate\Contracts\Support\Arrayable => $value->toArray(),
    blank($value) => [],
    default => preg_split('/\s*,\s*/', trim((string) $value)) ?: [],
};

$selected = array_values(array_unique(array_filter(array_map('strval', $selected), 'strlen')));

$max = filled($max) ? max(0, (int) $max) : null;

// `empty` is the no-match line: a string is shown as given, a bare `empty`
// (or true, the default) asks for the built-in text, and `:empty="false"`
// leaves the list to simply hide when nothing matches.
if ($empty === true || $empty === '') {
    $empty = $fa ? 'موردی یافت نشد.' : 'No matches.';
} elseif ($empty === false) {
    $empty = null;
}

$searchLabel ??= $label ?: ($fa ? 'جستجو' : 'Search');
$clearLabel = $fa ? 'پاک کردن' : 'Clear';
$removeLabel = $fa ? 'حذف' : 'Remove';
$statusSuffix = $fa ? ' مورد انتخاب شده' : ' selected';
$statusText = ($fa ? \MajidDs\Support\Persian::digits(count($selected)) : count($selected)).$statusSuffix;

// The parent never sees the option views' variables: Blade renders the slot
// before this wrapper runs. So each option carries its value and label as
// data attributes and the first paint reads them back out of the rendered
// slot — that is the only way the pills and the hidden select can be right
// before Alpine boots. From there Alpine keeps its own map of the same two
// attributes, rebuilt after every morph.
$rendered = (string) $slot;
$labels = [];

preg_match_all('/data-value="([^"]*)"\s+data-label="([^"]*)"/', $rendered, $pairs, PREG_SET_ORDER);

foreach ($pairs as [, $optionValue, $optionLabel]) {
    $labels[html_entity_decode($optionValue, ENT_QUOTES)] = html_entity_decode($optionLabel, ENT_QUOTES);
}

$labelFor = fn ($v) => $labels[$v] ?? $v;

$pillClasses = 'inline-flex max-w-full items-center gap-1 rounded-md bg-zinc-100 py-0.5 ps-2 pe-1 text-sm text-zinc-800 dark:bg-white/10 dark:text-white';
$removeClasses = 'shrink-0 rounded-sm p-0.5 text-zinc-400 hover:text-zinc-700 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent dark:hover:text-zinc-100';
@endphp

@include('mds::partials.digits')

@once('mds-pillbox')
<script @mdsNonce>
window.mds = window.mds || {}

/*
| WAI-ARIA combobox with a multi-selectable listbox popup. The text field
| filters; ArrowUp/ArrowDown move a virtual cursor (aria-activedescendant)
| and Enter or a click TOGGLES that option, so the popup stays open for the
| next pick. Chosen values become pills in the control; the machine value is
| a visually hidden multiple select, which is what Livewire reads.
*/
window.mds.registerPillbox = (Alpine) => {
    if (window.mds.pillboxRegistered) return
    window.mds.pillboxRegistered = true

    Alpine.data('mdsPillbox', (config = {}) => ({
        ready: false,
        open: false,
        query: '',
        active: -1,
        activeId: '',
        selected: config.selected ?? [],
        labels: {},
        options: [],
        serial: 0,
        observer: null,
        selectObserver: null,
        pending: false,
        picking: false,
        max: config.max ?? null,
        hasEmpty: config.hasEmpty ?? false,
        disabled: config.disabled ?? false,
        removeLabel: config.removeLabel ?? '',
        statusSuffix: config.statusSuffix ?? '',
        fa: config.fa ?? true,

        init() {
            // The option list is cached — matches(), isSelected() and
            // isDisabled() run for every option on every reactive pass — but
            // it must follow the DOM: a Livewire morph (server-side search)
            // or an x-for adds, removes and relabels options after init.
            // Attributes are not observed there: our own id and data-* writes
            // would feed straight back.
            this.refresh()

            // Alpine is running, so the x-for pills take over from the
            // server-rendered ones.
            this.ready = true

            this.observer = new MutationObserver(() => this.schedule())
            this.observer.observe(this.list(), { childList: true, subtree: true, characterData: true })

            // Morph re-sync: when the server changes the bound property,
            // Livewire patches the hidden select's options and Alpine state
            // does not follow by itself.
            if (this.$refs.select) {
                this.selectObserver = new MutationObserver(() => this.adopt())
                this.selectObserver.observe(this.$refs.select, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['selected', 'value'],
                })
            }
        },

        destroy() {
            this.observer?.disconnect()
            this.selectObserver?.disconnect()
            this.observer = null
            this.selectObserver = null
        },

        list() {
            return this.$refs.list ?? this.$root
        },

        // Read the hidden select back into Alpine state. Our own commit()
        // drops its records first, so this only ever runs for a server patch.
        adopt() {
            const next = [...(this.$refs.select?.options ?? [])].filter(option => option.selected).map(option => option.value)

            if (JSON.stringify(next) === JSON.stringify(this.selected)) return

            this.selected = next
            this.sync()
        },

        // A morph that touches twenty options fires twenty mutations —
        // collapse them into one re-scan on the next microtask.
        schedule() {
            if (this.pending) return

            this.pending = true

            queueMicrotask(() => {
                this.pending = false
                this.refresh()
            })
        },

        // Re-read the options, keep the cursor on the same one if it is still
        // there, and rebuild the value to label map the pills are drawn from.
        refresh() {
            const keep = this.activeId

            this.options = [...this.list().querySelectorAll('[data-mds-pillbox-option]')]
            this.labels = {}

            this.options.forEach(el => {
                // A running serial, not the array index: an option inserted
                // mid-list must not take an id a neighbour already holds.
                el.id ||= this.$id('mds-pillbox-option', this.serial++)
                el.dataset.mdsHaystack = this.normalize(this.label(el))
                this.labels[this.value(el)] = this.label(el)
            })

            const found = this.enabled()

            this.active = keep ? found.findIndex(el => el.id === keep) : -1

            this.sync()
        },

        // Arabic spellings of Persian letters fold together, and digits of
        // either script fold to Latin, so typing 'كتاب' or '٢' still matches.
        normalize(s) {
            return window.mds.latinDigits(
                String(s).toLowerCase().replace(/[يى]/g, 'ی').replace(/ك/g, 'ک'),
            ).trim()
        },

        // The visible text of an option — live, so a server-relabelled option
        // brings its new text along — falling back to the rendered attribute.
        label(el) {
            const text = (el.querySelector('[data-mds-pillbox-label]') ?? el).textContent.trim()

            return text !== '' ? text : (el.dataset.label ?? '')
        },

        value(el) {
            return el.dataset.value ?? this.label(el)
        },

        matches(el) {
            return (el.dataset.mdsHaystack ?? '').includes(this.normalize(this.query))
        },

        items() {
            const needle = this.normalize(this.query)

            return this.options.filter(el => (el.dataset.mdsHaystack ?? '').includes(needle))
        },

        // Disabled options stay visible but the cursor skips them.
        enabled() {
            return this.items().filter(el => ! this.isDisabled(el))
        },

        isSelected(el) {
            return this.selected.includes(this.value(el))
        },

        // Marked disabled by the caller, or unreachable because the cap is
        // full — an already-chosen option can always be taken off again.
        isDisabled(el) {
            return el.dataset.disabled !== undefined || (this.full && ! this.isSelected(el))
        },

        isActive(el) {
            return el.id !== '' && el.id === this.activeId
        },

        get full() {
            return this.max !== null && this.selected.length >= this.max
        },

        get pills() {
            return this.selected.map(value => ({ value, label: this.labels[value] ?? value }))
        },

        // Announced politely: a pill appearing or leaving is otherwise silent
        // for a screen reader whose focus stays in the text field.
        get status() {
            return window.mds.digits(this.selected.length, this.fa) + this.statusSuffix
        },

        // What aria-expanded reports and x-show paints: open, and either
        // something to show or an empty line to show instead.
        get expanded() {
            return this.open && ! this.disabled && (this.items().length > 0 || this.hasEmpty)
        },

        get empty() {
            return this.expanded && this.items().length === 0
        },

        // One pass per change instead of one per option: clamp the cursor,
        // remember which id it points at, and let isActive() be a compare.
        sync() {
            const found = this.enabled()

            if (this.active >= found.length) this.active = found.length - 1

            this.activeId = this.active >= 0 ? found[this.active].id : ''
        },

        show() {
            if (this.picking || this.disabled) return

            this.open = true
        },

        // Clicking the control anywhere but on a pill's remove button puts
        // the caret in the field and drops the list open.
        activate() {
            if (this.disabled) return

            this.focusInput()
            this.open = true
        },

        type(event) {
            this.query = event.target.value
            this.open = true
            this.active = -1
            this.sync()
        },

        // The list closes when focus leaves. Clicks inside the popup prevent
        // mousedown, so the input never blurs for them and this does not run.
        blur() {
            this.open = false
        },

        keydown(event) {
            if (event.isComposing) return

            switch (event.key) {
                case 'ArrowDown':
                    event.preventDefault()
                    this.move(1)
                    break
                case 'ArrowUp':
                    event.preventDefault()
                    this.move(-1)
                    break
                case 'Enter':
                    // Only while the list is open: a closed pillbox is an
                    // ordinary field and Enter submits its form.
                    if (! this.expanded) return
                    event.preventDefault()
                    if (this.active >= 0) this.toggle(this.enabled()[this.active])
                    break
                case 'Escape':
                    if (! this.open) return
                    event.preventDefault()
                    event.stopPropagation()
                    this.open = false
                    break
                case 'Home':
                case 'End':
                    if (! this.expanded) return
                    event.preventDefault()
                    this.jump(event.key === 'Home' ? 0 : -1)
                    break
                case 'Backspace':
                    // Only on an empty query, where Backspace has nothing to
                    // delete: then it takes the last pill off.
                    if (event.target.value !== '' || ! this.selected.length) return
                    event.preventDefault()
                    this.remove(this.selected[this.selected.length - 1])
                    break
                case 'Tab':
                    this.open = false
                    break
            }
        },

        // Arrow keys wrap; from "nothing active" Down takes the first and Up
        // the last option. They also open a closed list.
        move(delta) {
            if (this.disabled) return

            this.open = true

            const found = this.enabled()

            if (! found.length) return

            this.active = this.active < 0
                ? (delta > 0 ? 0 : found.length - 1)
                : (this.active + delta + found.length) % found.length

            this.reveal(found)
        },

        jump(index) {
            const found = this.enabled()

            if (! found.length) return

            this.active = index < 0 ? found.length - 1 : Math.min(index, found.length - 1)

            this.reveal(found)
        },

        reveal(found) {
            this.activeId = found[this.active].id

            this.$nextTick(() => found[this.active]?.scrollIntoView({ block: 'nearest' }))
        },

        point(el) {
            const index = this.enabled().indexOf(el)

            if (index === -1) return

            this.active = index
            this.activeId = el.id
        },

        // Enter and a click do the same thing, and neither closes the popup:
        // picking a second value is the point of a pillbox.
        toggle(el) {
            if (! el || this.disabled || this.isDisabled(el)) return

            const value = this.value(el)

            this.selected = this.isSelected(el)
                ? this.selected.filter(v => v !== value)
                : [...this.selected, value]

            this.commit()
            this.focusInput()
            this.open = true
        },

        remove(value) {
            if (this.disabled) return

            this.selected = this.selected.filter(v => v !== value)

            this.commit()

            // The button that was clicked is gone with its pill; focus would
            // fall to the body if we did not take it back.
            this.focusInput()
        },

        clear() {
            if (this.disabled) return

            this.selected = []

            this.commit()
            this.focusInput()
        },

        // Livewire reads a multiple select as an array — rebuild its options
        // from the selection, all of them selected, then say so twice: an
        // input event for wire:model, a change event for a listening form.
        commit() {
            const el = this.$refs.select

            if (el) {
                el.replaceChildren(...this.selected.map(value => {
                    const option = document.createElement('option')

                    option.value = value
                    option.textContent = this.labels[value] ?? value
                    option.selected = true

                    return option
                }))

                el.dispatchEvent(new Event('input', { bubbles: true }))
                el.dispatchEvent(new Event('change', { bubbles: true }))

                // Our own rewrite is a mutation too; drop it before the
                // observer sees it, or every commit would re-adopt itself.
                this.selectObserver?.takeRecords()
            }

            this.sync()
        },

        // Return focus without the focus handler reopening the list.
        focusInput() {
            this.picking = true
            this.$refs.input?.focus()
            this.picking = false
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerPillbox(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerPillbox(window.Alpine))
}
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class('relative') }}
        x-id="['mds-pillbox-listbox', 'mds-pillbox-option']"
        x-data="mdsPillbox({
            selected: @js($selected),
            max: @js($max),
            hasEmpty: @js(filled($empty)),
            disabled: @js((bool) $disabled),
            removeLabel: @js($removeLabel),
            statusSuffix: @js($statusSuffix),
            fa: @js((bool) $fa),
        })"
        @if ($disabled) data-disabled @endif
        @if ($invalid) data-invalid @endif
        data-mds-pillbox
    >
        {{--
            The machine value. Livewire reads a multiple select as an array,
            so the chosen values are its selected options — rebuilt by Alpine
            after every change, and watched for the server rebuilding them.
        --}}
        <select
            multiple
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
            x-ref="select"
            @if ($name) name="{{ $name }}[]" @endif
            @if ($disabled) disabled @endif
            {{ $attributes->whereStartsWith('wire:model') }}
            data-mds-pillbox-select
        >
            @foreach ($selected as $chosen)
                <option value="{{ $chosen }}" selected>{{ $labelFor($chosen) }}</option>
            @endforeach
        </select>

        <div
            class="{{ collect([
                'flex w-full flex-wrap items-center gap-1.5 rounded-lg border bg-white px-2 py-1.5 shadow-xs dark:bg-white/10',
                'has-[:focus-visible]:outline-2 has-[:focus-visible]:outline-offset-2 has-[:focus-visible]:outline-accent',
                $invalid ? 'border-red-500 dark:border-red-400' : 'border-zinc-200 dark:border-white/10',
                $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-text',
            ])->implode(' ') }}"
            x-on:click="activate()"
            data-mds-pillbox-control
        >
            {{-- The first paint, before Alpine runs: the same pills, drawn
                 from the server's own selection. It steps aside the moment
                 the x-for below can take over. --}}
            <span class="contents" x-show="! ready" data-mds-pillbox-initial>
                @foreach ($selected as $chosen)
                    <span class="{{ $pillClasses }}" data-mds-pillbox-pill>
                        <span class="truncate" data-mds-pillbox-pill-label>{{ $labelFor($chosen) }}</span>

                        @unless ($disabled)
                            <button
                                type="button"
                                class="{{ $removeClasses }}"
                                x-on:click.stop="remove(@js($chosen))"
                                x-on:mousedown.prevent
                                aria-label="{{ $removeLabel }} {{ $labelFor($chosen) }}"
                                data-mds-pillbox-remove
                            >
                                <mds:icon icon="x-mark" variant="micro" class="size-3" />
                            </button>
                        @endunless
                    </span>
                @endforeach
            </span>

            <template x-for="pill in pills" :key="pill.value">
                <span class="{{ $pillClasses }}" data-mds-pillbox-pill>
                    <span class="truncate" x-text="pill.label" data-mds-pillbox-pill-label></span>

                    @unless ($disabled)
                        <button
                            type="button"
                            class="{{ $removeClasses }}"
                            x-on:click.stop="remove(pill.value)"
                            x-on:mousedown.prevent
                            x-bind:aria-label="removeLabel + ' ' + pill.label"
                            data-mds-pillbox-remove
                        >
                            <mds:icon icon="x-mark" variant="micro" class="size-3" />
                        </button>
                    @endunless
                </span>
            </template>

            {{-- The combobox itself: it holds focus for the whole widget and
                 points at the active option through aria-activedescendant. --}}
            <input
                type="text"
                class="min-w-24 flex-1 bg-transparent py-0.5 text-sm text-zinc-800 outline-none placeholder:text-zinc-400 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-zinc-500"
                autocomplete="off"
                role="combobox"
                aria-autocomplete="list"
                aria-label="{{ $searchLabel }}"
                @if ($placeholder && ! count($selected)) placeholder="{{ $placeholder }}" @endif
                @if ($placeholder) x-bind:placeholder="pills.length ? '' : @js($placeholder)" @endif
                @if ($disabled) disabled @endif
                @if ($invalid) aria-invalid="true" @endif
                @if (filled($error)) aria-describedby="{{ $errorId }}" @endif
                x-ref="input"
                x-bind:aria-expanded="expanded ? 'true' : 'false'"
                x-bind:aria-controls="$id('mds-pillbox-listbox')"
                x-bind:aria-activedescendant="activeId"
                x-on:focus="show()"
                x-on:blur="blur()"
                x-on:input="type($event)"
                x-on:keydown="keydown($event)"
                data-mds-pillbox-input
            >

            @if ($clearable && ! $disabled)
                <button
                    type="button"
                    class="shrink-0 rounded p-1 text-zinc-400 hover:text-zinc-600 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent dark:hover:text-zinc-200"
                    x-show="pills.length > 0"
                    x-cloak
                    x-on:click.stop="clear()"
                    x-on:mousedown.prevent
                    aria-label="{{ $clearLabel }}"
                    data-mds-pillbox-clear
                >
                    <mds:icon icon="x-mark" variant="micro" class="size-4" />
                </button>
            @endif

            {{-- Decorative: the field opens the list, so a second control
                 doing the same thing would only be one more tab stop. --}}
            <span
                class="pointer-events-none shrink-0 text-zinc-400 transition-transform motion-reduce:transition-none dark:text-zinc-500"
                x-bind:class="expanded && 'rotate-180'"
                aria-hidden="true"
                data-mds-pillbox-chevron
            >
                <mds:icon icon="chevron-down" variant="micro" class="size-4" />
            </span>
        </div>

        {{-- Focus stays in the text field, so a pill coming or going is
             silent without this. --}}
        <span class="sr-only" role="status" aria-live="polite" x-text="status" data-mds-pillbox-status>{{ $statusText }}</span>

        {{--
            Under the control, in the tree (no teleport): the field's own
            stacking context is the one a form is laid out in, and the popup
            follows it through scrolling for free. mousedown.prevent on the
            whole popup keeps the input focused for option clicks and for a
            drag on the scrollbar alike.
        --}}
        <div
            class="absolute start-0 end-0 top-full z-50 mt-1 rounded-lg border border-zinc-200 bg-white p-1 shadow-lg dark:border-white/10 dark:bg-zinc-800"
            x-show="expanded"
            x-cloak
            x-transition:enter="transition duration-100 ease-out motion-reduce:transition-none"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition duration-75 ease-in motion-reduce:transition-none"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            x-on:mousedown.prevent
            data-mds-pillbox-popup
        >
            <div
                class="max-h-60 overflow-auto"
                role="listbox"
                aria-multiselectable="true"
                x-ref="list"
                x-bind:id="$id('mds-pillbox-listbox')"
                @if ($label) aria-label="{{ $label }}" @endif
                data-mds-pillbox-list
            >
                {{ $slot }}
            </div>

            @if (filled($empty))
                {{-- Outside the listbox: a listbox takes options, not prose. --}}
                <div class="px-2.5 py-2 text-sm text-zinc-400 dark:text-zinc-500" role="status" x-show="empty" x-cloak data-mds-pillbox-empty>{{ $empty }}</div>
            @endif
        </div>
    </div>

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div id="{{ $errorId }}" role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</flux:field>
