@props([
    'delay' => 600,
    'closeDelay' => 300,
])

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsPreviewCard', (config = {}) => ({
        open: false,
        delay: config.delay ?? 600,
        closeDelay: config.closeDelay ?? 300,
        openTimer: null,
        closeTimer: null,

        // Registered from x-init — $refs cannot reach through x-teleport...
        triggerEl: null,
        contentEl: null,
        arrowEl: null,

        init() {
            // Reposition while open so the card tracks its link through
            // scrolling and resizes — there is no portal to re-anchor it.
            this.reposition = () => { if (this.open) this.place() }

            window.addEventListener('scroll', this.reposition, { passive: true, capture: true })
            window.addEventListener('resize', this.reposition, { passive: true })
        },

        destroy() {
            window.removeEventListener('scroll', this.reposition, { capture: true })
            window.removeEventListener('resize', this.reposition)
        },

        // Hover waits `delay`; keyboard focus is intentional, so it opens now...
        enter(immediate = false) {
            clearTimeout(this.closeTimer)

            if (this.open) return

            clearTimeout(this.openTimer)

            immediate
                ? this.show()
                : this.openTimer = setTimeout(() => this.show(), this.delay)
        },

        leave(immediate = false) {
            clearTimeout(this.openTimer)
            clearTimeout(this.closeTimer)

            immediate
                ? this.open = false
                : this.closeTimer = setTimeout(() => this.open = false, this.closeDelay)
        },

        show() {
            this.open = true
            this.$nextTick(() => this.place())
        },

        /*
        | position: fixed + measured placement, because the popup stays in the
        | tree (no portal): fixed escapes overflow-hidden ancestors the same
        | way a portal escapes them. `start`/`end` resolve against the page
        | direction, so placement mirrors on RTL pages by itself.
        */
        place() {
            const content = this.contentEl
            const trigger = this.triggerEl

            if (! content || ! trigger) return

            const t = trigger.getBoundingClientRect()
            // The component's own direction, not the document's — so an RTL
            // island inside an LTR page (or the reverse) still mirrors correctly.
            const rtl = getComputedStyle(this.$root).direction === 'rtl'
            const pad = 5

            let side = content.dataset.side
            let align = content.dataset.align
            const offset = parseFloat(content.dataset.sideOffset)

            if (side === 'start') side = rtl ? 'right' : 'left'
            if (side === 'end') side = rtl ? 'left' : 'right'

            const c = { w: content.offsetWidth, h: content.offsetHeight }
            const vw = window.innerWidth
            const vh = window.innerHeight

            // Flip to the other side when the preferred one runs out of room...
            if (side === 'bottom' && t.bottom + offset + c.h > vh && t.top - offset - c.h > 0) side = 'top'
            else if (side === 'top' && t.top - offset - c.h < 0 && t.bottom + offset + c.h < vh) side = 'bottom'
            else if (side === 'right' && t.right + offset + c.w > vw && t.left - offset - c.w > 0) side = 'left'
            else if (side === 'left' && t.left - offset - c.w < 0 && t.right + offset + c.w < vw) side = 'right'

            const vertical = side === 'top' || side === 'bottom'

            // The alignment axis: start hugs the leading edge, which is the
            // trigger's right edge on an RTL page...
            let x, y

            if (vertical) {
                y = side === 'bottom' ? t.bottom + offset : t.top - offset - c.h

                x = align === 'center' ? t.left + t.width / 2 - c.w / 2
                    : (align === 'start') !== rtl ? t.left
                    : t.right - c.w

                x = Math.min(Math.max(x, pad), vw - c.w - pad)
            } else {
                x = side === 'right' ? t.right + offset : t.left - offset - c.w

                y = align === 'center' ? t.top + t.height / 2 - c.h / 2
                    : align === 'start' ? t.top
                    : t.bottom - c.h

                y = Math.min(Math.max(y, pad), vh - c.h - pad)
            }

            content.style.left = x + 'px'
            content.style.top = y + 'px'
            content.dataset.renderedSide = side

            // The arrow points at the middle of the link, wherever the card
            // was clamped to...
            const arrow = this.arrowEl

            if (arrow) {
                if (vertical) {
                    const ax = Math.min(Math.max(t.left + t.width / 2 - x, 12), c.w - 12)
                    arrow.style.left = ax + 'px'
                    arrow.style.top = ''
                } else {
                    const ay = Math.min(Math.max(t.top + t.height / 2 - y, 12), c.h - 12)
                    arrow.style.top = ay + 'px'
                    arrow.style.left = ''
                }
            }
        },
    }))
})
</script>
@endonce

<span
    {{ $attributes->class('relative inline-block') }}
    x-data="mdsPreviewCard({ delay: @js((int) $delay), closeDelay: @js((int) $closeDelay) })"
    x-on:keydown.escape="leave(true)"
    data-mds-preview-card
>{{ $slot }}</span>
