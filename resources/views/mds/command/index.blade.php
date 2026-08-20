<div
    {{ $attributes->class('flex w-full flex-col overflow-hidden rounded-xl border border-zinc-200 bg-white shadow-lg dark:border-white/10 dark:bg-zinc-800') }}
    x-data="{
        query: '',
        active: 0,
        normalize(s) {
            return s
                .toLowerCase()
                .replace(/[يى]/g, 'ی')
                .replace(/ك/g, 'ک')
                .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
                .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))
                .trim()
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
    }"
    x-effect="query; active = 0"
    data-mds-command
>
    {{ $slot }}
</div>
