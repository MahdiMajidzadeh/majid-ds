@props([
    'fa' => null,
])

@include('mds::partials.digits')

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsCommand', () => ({
        query: '',
        active: 0,
        activeId: '',
        options: [],

        init() {
            // Read the option list once. It is slot content and does not
            // change between keystrokes, but matches() and isActive() run for
            // every option on every reactive pass — re-querying the DOM and
            // re-normalising all of it there made each keystroke quadratic.
            this.options = [...this.$root.querySelectorAll('[data-mds-command-item]')]

            this.options.forEach((el, i) => {
                el.id ||= this.$id('mds-command-option', i)
                // Options need ids for aria-activedescendant, and they cannot
                // know their own index from inside a Blade slot.
                el.dataset.mdsHaystack = this.normalize(el.textContent)
            })

            this.$watch('query', () => { this.active = 0; this.sync() })

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
