@props([
    'fa' => null,
])

@include('mds::partials.digits')

<div
    {{ $attributes->class('flex w-full flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-white/10 dark:bg-zinc-800') }}
    x-id="['mds-command-listbox', 'mds-command-option']"
    x-data="{
        query: '',
        active: 0,
        init() {
            // Options need ids for aria-activedescendant, but they arrive as
            // slot content and cannot know their own index in Blade.
            this.$root.querySelectorAll('[data-mds-command-item]').forEach((el, i) => {
                el.id ||= this.$id('mds-command-option', i)
            })
        },
        // Arabic spellings of Persian letters fold together, and digits of
        // either script fold to Latin, so typing 'كتاب' or '٢' still matches.
        normalize(s) {
            return window.mds.latinDigits(
                s.toLowerCase().replace(/[يى]/g, 'ی').replace(/ك/g, 'ک'),
            ).trim()
        },
        matches(el) {
            return this.normalize(el.textContent).includes(this.normalize(this.query))
        },
        items() {
            return [...this.$root.querySelectorAll('[data-mds-command-item]')].filter(el => this.matches(el))
        },
        get empty() {
            return this.items().length === 0
        },
        move(delta) {
            const count = this.items().length
            if (! count) return
            this.active = Math.max(0, Math.min(this.active + delta, count - 1))
            this.$nextTick(() => this.items()[this.active]?.scrollIntoView({ block: 'nearest' }))
        },
        select() {
            this.items()[this.active]?.click()
        },
        isActive(el) {
            return this.items()[this.active] === el
        },
        // What the input points at, so arrowing the list is announced.
        get activeId() {
            return this.items()[this.active]?.id ?? ''
        },
    }"
    x-effect="query; active = 0"
    data-mds-command
>
    {{ $slot }}
</div>
