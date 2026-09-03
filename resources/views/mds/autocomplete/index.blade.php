@props([
    'value' => null,
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => null,
    'icon' => null,
    'clearable' => false,
    'minChars' => 0,
    'empty' => null,
    'strict' => false,
    'disabled' => false,
    'size' => null,
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

$minChars = max(0, (int) $minChars);

// `empty` is the no-match line. Absent (or false), the list simply hides
// when nothing matches; a bare `empty` attribute asks for the built-in text;
// a string is shown as given.
if ($empty === true || $empty === '') {
    $empty = $fa ? 'موردی یافت نشد.' : 'No matches.';
} elseif ($empty === false) {
    $empty = null;
}

$clearLabel = $fa ? 'پاک کردن' : 'Clear';
@endphp

@include('mds::partials.digits')

@once('mds-autocomplete')
<script @mdsNonce>
window.mds = window.mds || {}

/*
| WAI-ARIA combobox with a listbox popup, list autocomplete with manual
| selection: typing filters the options but highlights none of them; the
| arrow keys move a virtual cursor (aria-activedescendant) and Enter or a
| click copies that option's value into the input. The value stays free
| text unless `strict` asks otherwise.
*/
window.mds.registerAutocomplete = (Alpine) => {
    if (window.mds.autocompleteRegistered) return
    window.mds.autocompleteRegistered = true

    Alpine.data('mdsAutocomplete', (config = {}) => ({
        open: false,
        query: '',
        active: -1,
        activeId: '',
        options: [],
        serial: 0,
        observer: null,
        pending: false,
        picking: false,
        minChars: config.minChars ?? 0,
        strict: config.strict ?? false,
        hasEmpty: config.hasEmpty ?? false,
        disabled: config.disabled ?? false,

        init() {
            this.query = this.$refs.input?.value ?? ''

            // The option list is cached — matches() and isActive() run for
            // every option on every reactive pass — but it must follow the
            // DOM: a Livewire morph (server-side search) or an x-for adds,
            // removes and relabels items after init. Attributes are not
            // observed: our own id and data-* writes would feed straight back.
            this.refresh()

            this.observer = new MutationObserver(() => this.schedule())
            this.observer.observe(this.$root, { childList: true, subtree: true, characterData: true })
        },

        destroy() {
            this.observer?.disconnect()
            this.observer = null
        },

        // A morph that touches twenty items fires twenty mutations — collapse
        // them into one re-scan on the next microtask.
        schedule() {
            if (this.pending) return

            this.pending = true

            queueMicrotask(() => {
                this.pending = false
                this.refresh()
            })
        },

        // Re-read the options, keep the cursor on the same item if it is
        // still there, then repaint the highlights.
        refresh() {
            const keep = this.activeId

            this.options = [...this.$root.querySelectorAll('[data-mds-autocomplete-item]')]

            this.options.forEach(el => {
                // A running serial, not the array index: an item inserted
                // mid-list must not take an id a neighbour already holds.
                el.id ||= this.$id('mds-autocomplete-option', this.serial++)
                el.dataset.mdsHaystack = this.normalize(this.label(el))
            })

            const found = this.enabled()
            const still = keep ? found.findIndex(el => el.id === keep) : -1

            this.active = still

            this.sync()
        },

        // Arabic spellings of Persian letters fold together, and digits of
        // either script fold to Latin, so typing 'كتاب' or '٢' still matches.
        normalize(s) {
            return window.mds.latinDigits(
                String(s).toLowerCase().replace(/[يى]/g, 'ی').replace(/ك/g, 'ک'),
            ).trim()
        },

        // The visible text of an option; its value when the item sets none.
        label(el) {
            return (el.querySelector('[data-mds-autocomplete-label]') ?? el).textContent.trim()
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
            return this.items().filter(el => el.getAttribute('aria-disabled') !== 'true')
        },

        get ready() {
            return this.query.length >= this.minChars
        },

        // What aria-expanded reports and x-show paints: open, enough typed,
        // and either something to show or an empty line to show instead.
        get expanded() {
            return this.open && ! this.disabled && this.ready && (this.items().length > 0 || this.hasEmpty)
        },

        get empty() {
            return this.expanded && this.items().length === 0
        },

        // One pass per change instead of one per option: clamp the cursor,
        // remember which id it points at, and repaint the marks.
        sync() {
            const found = this.enabled()

            if (this.active >= found.length) this.active = found.length - 1

            this.activeId = this.active >= 0 ? found[this.active].id : ''

            this.paint()
        },

        // Wrap the typed substring of every label in a mark element. Built
        // from text nodes only, so typed text is never parsed as markup. The
        // haystack is the label lower-cased and digit-folded one character
        // for one, so its indices line up with the label; if a locale ever
        // changes the length, the label is shown plain.
        paint() {
            const needle = this.normalize(this.query)

            this.options.forEach(el => {
                const target = el.querySelector('[data-mds-autocomplete-label]')

                if (! target) return

                const label = target.textContent.trim()
                const haystack = el.dataset.mdsHaystack ?? ''
                const at = needle !== '' && haystack.length === label.length ? haystack.indexOf(needle) : -1

                if (at === -1) {
                    if (target.childNodes.length === 1 && target.firstChild.nodeType === 3 && target.firstChild.data === label) return

                    target.textContent = label

                    return
                }

                const mark = document.createElement('mark')

                mark.className = 'bg-transparent font-semibold text-inherit'
                mark.textContent = label.slice(at, at + needle.length)

                target.replaceChildren(
                    document.createTextNode(label.slice(0, at)),
                    mark,
                    document.createTextNode(label.slice(at + needle.length)),
                )
            })

            // Our own rewrites are mutations too; drop them before the
            // observer sees them, or every paint would schedule a re-scan.
            this.observer?.takeRecords()
        },

        show() {
            if (this.picking) return

            this.query = this.$refs.input.value
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
        // A strict field settles on an option FIRST: snap() commits, commit()
        // dispatches the input event our own handler reopens the list on, so
        // closing has to come last or the popup outlives the focus.
        blur() {
            if (this.strict) this.snap()

            this.open = false
        },

        // On leaving a strict field, make the text one of the options.
        snap() {
            const typed = this.$refs.input.value

            if (typed === '') return

            const needle = this.normalize(typed)
            const match = this.options.find(el => el.getAttribute('aria-disabled') !== 'true'
                && (this.normalize(this.value(el)) === needle || el.dataset.mdsHaystack === needle))

            // Snap to the option's own spelling, or drop text that is no option.
            if (match) {
                if (this.value(match) !== typed) this.commit(this.value(match))
            } else {
                this.commit('')
            }
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
                    // Only while the list is open: a closed autocomplete is
                    // an ordinary text field and Enter submits its form.
                    if (! this.expanded) return
                    event.preventDefault()
                    this.active >= 0 ? this.pick(this.enabled()[this.active]) : this.open = false
                    break
                case 'Escape':
                    if (! this.expanded) return
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
                case 'Tab':
                    this.open = false
                    break
            }
        },

        // Arrow keys wrap; from "nothing active" Down takes the first and Up
        // the last option. They also open a closed list.
        move(delta) {
            if (this.disabled || ! this.ready) return

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

        isActive(el) {
            return el.id !== '' && el.id === this.activeId
        },

        pick(el) {
            if (! el || el.getAttribute('aria-disabled') === 'true') return

            this.commit(this.value(el))
            this.open = false
            this.focusInput()
        },

        clear() {
            this.commit('')
            this.active = -1
            this.focusInput()
            this.open = true
        },

        // Write the input and tell Livewire: a bubbling input event is what
        // wire:model listens for. Our own x-on:input handler hears it too and
        // reopens the list, so callers set `open` after this, not before.
        commit(value) {
            const input = this.$refs.input

            input.value = value
            this.query = value
            input.dispatchEvent(new Event('input', { bubbles: true }))
            this.sync()
        },

        // Return focus without the focus handler reopening the list.
        focusInput() {
            this.picking = true
            this.$refs.input.focus()
            this.picking = false
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerAutocomplete(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerAutocomplete(window.Alpine))
}
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class('relative') }}
        x-id="['mds-autocomplete-listbox', 'mds-autocomplete-option']"
        x-data="mdsAutocomplete({
            minChars: @js($minChars),
            strict: @js((bool) $strict),
            hasEmpty: @js(filled($empty)),
            disabled: @js((bool) $disabled),
        })"
        @if ($disabled) data-disabled @endif
        data-mds-autocomplete
    >
        {{--
            Flux's own input, so the control matches every other field on the
            page. Flux puts the attribute bag on the input element itself,
            which is where the combobox role, the key handlers and wire:model
            all belong. No x-mds-digits: the value is free text.
        --}}
        <flux:input
            type="text"
            :name="$name"
            :value="$value"
            :placeholder="$placeholder"
            :icon="$icon"
            :size="$size"
            :disabled="$disabled"
            :invalid="$invalid"
            autocomplete="off"
            role="combobox"
            aria-autocomplete="list"
            x-ref="input"
            x-bind:aria-expanded="expanded ? 'true' : 'false'"
            x-bind:aria-controls="$id('mds-autocomplete-listbox')"
            x-bind:aria-activedescendant="activeId"
            x-on:focus="show()"
            x-on:blur="blur()"
            x-on:input="type($event)"
            x-on:keydown="keydown($event)"
            data-mds-autocomplete-input=""
            {{ $attributes->whereStartsWith('wire:model') }}
        >
            @if ($clearable && ! $disabled)
                <x-slot:icon-trailing>
                    {{-- mousedown.prevent: focus stays in the input, so no blur closes the list under the click. --}}
                    <button
                        type="button"
                        class="rounded p-1 text-zinc-400 hover:text-zinc-600 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent dark:hover:text-zinc-200"
                        x-show="query !== ''"
                        x-cloak
                        x-on:mousedown.prevent
                        x-on:click="clear()"
                        aria-label="{{ $clearLabel }}"
                        data-mds-autocomplete-clear
                    >
                        <mds:icon icon="x-mark" variant="micro" class="size-4" />
                    </button>
                </x-slot:icon-trailing>
            @endif
        </flux:input>

        {{--
            Under the control, in the tree (no teleport): the input's own
            stacking context is the one a form is laid out in, and the popup
            follows the field through scrolling for free. mousedown.prevent
            on the whole popup keeps the input focused for option clicks and
            for a drag on the scrollbar alike.
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
            data-mds-autocomplete-popup
        >
            <div
                class="max-h-60 overflow-auto"
                role="listbox"
                x-bind:id="$id('mds-autocomplete-listbox')"
                @if ($label) aria-label="{{ $label }}" @endif
                data-mds-autocomplete-list
            >
                {{ $slot }}
            </div>

            @if (filled($empty))
                {{-- Outside the listbox: a listbox takes options, not prose. --}}
                <div class="px-2.5 py-2 text-sm text-zinc-400 dark:text-zinc-500" role="status" x-show="empty" x-cloak data-mds-autocomplete-empty>
                    {{ $empty }}
                </div>
            @endif
        </div>
    </div>

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</flux:field>
