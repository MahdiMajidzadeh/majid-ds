@props([
    'fa' => null,
])

@include('mds::partials.digits')

@once
<script @mdsNonce>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsCommand', () => ({
        query: '',
        active: 0,
        activeId: '',
        options: [],
        serial: 0,
        observer: null,
        pending: false,

        init() {
            // The option list is cached: matches() and isActive() run for every
            // option on every reactive pass, and re-querying the DOM and
            // re-normalising all of it there made each keystroke quadratic.
            this.refresh()

            // But the cache must follow the DOM. Items arrive as slot content
            // and a Livewire morph — or an x-for — can add, remove or relabel
            // them later, so watch the subtree and re-scan when it changes.
            // Attributes are not observed: our own id and data-* writes below
            // would otherwise feed straight back into the observer.
            this.observer = new MutationObserver(() => this.schedule())
            this.observer.observe(this.$root, { childList: true, subtree: true, characterData: true })

            this.$watch('query', () => { this.active = 0; this.sync() })
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

        // Re-read the options and keep the cursor on the same item if it is
        // still there. Public: a consumer that mutates the list in a way the
        // observer cannot see (none known) can call it directly.
        refresh() {
            const keep = this.activeId

            this.options = [...this.$root.querySelectorAll('[data-mds-command-item]')]

            this.options.forEach(el => {
                // Options need ids for aria-activedescendant, and they cannot
                // know their own index from inside a Blade slot. A running
                // serial, not the array index: an item inserted mid-list must
                // not take an id an existing neighbour already holds.
                el.id ||= this.$id('mds-command-option', this.serial++)
                el.dataset.mdsHaystack = this.normalize(el.textContent)
            })

            const found = this.items()
            const still = keep ? found.findIndex(el => el.id === keep) : -1

            if (still !== -1) this.active = still

            this.sync()
        },

        // Arabic spellings of Persian letters fold together, and digits of
        // either script fold to Latin, so typing 'كتاب' or '٢' still matches.
        normalize(s) {
            return window.mds.latinDigits(
                String(s).toLowerCase().replace(/[يى]/g, 'ی').replace(/ك/g, 'ک'),
            ).trim()
        },

        matches(el) {
            return (el.dataset.mdsHaystack ?? '').includes(this.normalize(this.query))
        },

        items() {
            const needle = this.normalize(this.query)

            return this.options.filter(el => (el.dataset.mdsHaystack ?? '').includes(needle))
        },

        get empty() {
            return this.items().length === 0
        },

        // One pass per change instead of one per option: clamp the cursor and
        // remember which id it points at, so isActive() is a string compare.
        sync() {
            const found = this.items()

            this.active = found.length ? Math.max(0, Math.min(this.active, found.length - 1)) : 0
            this.activeId = found[this.active]?.id ?? ''
        },

        move(delta) {
            const found = this.items()

            if (! found.length) return

            this.active = Math.max(0, Math.min(this.active + delta, found.length - 1))
            this.activeId = found[this.active]?.id ?? ''

            this.$nextTick(() => found[this.active]?.scrollIntoView({ block: 'nearest' }))
        },

        point(el) {
            const index = this.items().indexOf(el)

            if (index === -1) return

            this.active = index
            this.activeId = el.id
        },

        select() {
            this.items()[this.active]?.click()
        },

        isActive(el) {
            return el.id !== '' && el.id === this.activeId
        },
    }))
})
</script>
@endonce

<div
    {{ $attributes->class('flex w-full flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-white/10 dark:bg-zinc-800') }}
    x-id="['mds-command-listbox', 'mds-command-option']"
    x-data="mdsCommand()"
    data-mds-command
>
    {{ $slot }}
</div>
