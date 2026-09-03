@props([
    'position' => 'bottom',
    'align' => 'start',
    'offset' => 8,
    'arrow' => false,
    'hover' => false,
    'fa' => null,
])

@php
// fa picks the built-in labels' language; the content inherits it.
$fa ??= config('mds.persian_digits', true);

// Only the placements place() understands; anything else falls back.
$position = in_array($position, ['top', 'bottom', 'start', 'end'], true) ? $position : 'bottom';
$align = in_array($align, ['start', 'center', 'end'], true) ? $align : 'start';
$offset = max(0, (int) $offset);
@endphp

@once('mds-popover')
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerPopover = (Alpine) => {
    if (window.mds.popoverRegistered) return
    window.mds.popoverRegistered = true

    Alpine.data('mdsPopover', (config = {}) => ({
        open: false,
        position: config.position ?? 'bottom',
        align: config.align ?? 'start',
        offset: config.offset ?? 8,
        hover: config.hover ?? false,

        // Set by a click (or the keyboard) rather than by a passing cursor:
        // a pinned popover ignores mouseleave and blur in hover mode, so a
        // reader who clicked to keep it open is not chased away by the mouse.
        pinned: false,

        openTimer: null,
        closeTimer: null,

        // Registered from x-init — the panel is teleported to body, and $refs
        // are not relied on across that boundary.
        contentEl: null,
        arrowEl: null,

        init() {
            // Reposition while open so the panel tracks its trigger through
            // scrolling and resizes. Listeners live only while open.
            this.reposition = () => { if (this.open) this.place() }
        },

        destroy() {
            this.unlisten()
            clearTimeout(this.openTimer)
            clearTimeout(this.closeTimer)
        },

        listen() {
            window.addEventListener('scroll', this.reposition, { passive: true, capture: true })
            window.addEventListener('resize', this.reposition, { passive: true })
        },

        unlisten() {
            window.removeEventListener('scroll', this.reposition, { capture: true })
            window.removeEventListener('resize', this.reposition)
        },

        // The element the reader actually operates: the button (or link) the
        // caller put inside the trigger; the wrapper itself when there is none.
        triggerButton() {
            const wrapper = this.$refs.trigger

            return wrapper?.querySelector('button, a[href], input, [role="button"], [tabindex]') ?? wrapper ?? null
        },

        // ARIA lives on the real button, not on the wrapper span — aria-expanded
        // on a generic span means nothing to a screen reader. Runs as an
        // x-effect, so it follows `open`.
        syncTrigger() {
            const button = this.triggerButton()

            if (! button) return

            button.setAttribute('aria-haspopup', 'dialog')
            button.setAttribute('aria-expanded', this.open ? 'true' : 'false')
            button.setAttribute('aria-controls', this.$id('mds-popover'))
        },

        toggle() {
            clearTimeout(this.openTimer)
            clearTimeout(this.closeTimer)

            if (! this.open) {
                this.pinned = true
                this.show()
            } else if (this.hover && ! this.pinned) {
                // Hover opened it; the click keeps it open and hands focus over.
                this.pinned = true
                this.focusInside()
            } else {
                this.close()
            }
        },

        show(options = {}) {
            clearTimeout(this.openTimer)
            clearTimeout(this.closeTimer)

            if (this.open) return

            this.open = true
            this.listen()

            this.$nextTick(() => {
                this.place()

                if (options.focus ?? true) this.focusInside()
            })

            // From the root, whichever element's handler got here: consumers
            // listen on the mds:popover tag itself.
            this.$root.dispatchEvent(new CustomEvent('mds-popover-open', { bubbles: true }))
        },

        close(options = {}) {
            clearTimeout(this.openTimer)
            clearTimeout(this.closeTimer)

            if (! this.open) return

            const active = document.activeElement
            const inside = this.contentEl?.contains(active) || active === document.body || active === null

            this.open = false
            this.pinned = false
            this.unlisten()

            // Focus comes back to the trigger when it was inside the panel
            // (or was lost with it); a click elsewhere keeps the focus it set.
            if ((options.focus ?? true) && inside) this.triggerButton()?.focus()

            this.$root.dispatchEvent(new CustomEvent('mds-popover-close', { bubbles: true }))
        },

        // Hover mode only. A short delay each way: the panel neither flickers
        // under a passing cursor nor vanishes on the way to it.
        enter() {
            if (! this.hover) return

            clearTimeout(this.closeTimer)

            if (this.open) return

            clearTimeout(this.openTimer)
            this.openTimer = setTimeout(() => this.show({ focus: false }), 100)
        },

        leave() {
            if (! this.hover || this.pinned) return

            clearTimeout(this.openTimer)
            clearTimeout(this.closeTimer)
            this.closeTimer = setTimeout(() => this.close({ focus: false }), 300)
        },

        // Keyboard focus landing on the trigger opens a hover popover, the
        // way focusing a link opens its preview. Mouse focus does not: a
        // click is a toggle, and would otherwise open and close in one go.
        focusin(event) {
            if (! this.hover || ! event.target.matches(':focus-visible')) return

            this.show({ focus: false })
        },

        // Focus moving to something outside trigger and panel closes a hover
        // popover. relatedTarget null is a click on plain text — maybe inside
        // the panel itself — so that case is left to mouseleave.
        focusout(event) {
            if (! this.hover || this.pinned || ! this.open) return

            const to = event.relatedTarget

            if (! to || this.$root.contains(to) || this.contentEl?.contains(to)) return

            this.close({ focus: false })
        },

        // The panel is teleported, so a click on the trigger is "outside" it
        // as far as the DOM knows — and toggle() already handled that click.
        outside(event) {
            if (this.$refs.trigger?.contains(event.target)) return

            this.close({ focus: false })
        },

        tabbables(root) {
            if (! root) return []

            const selector = 'a[href], button:not([disabled]), input:not([disabled]):not([type="hidden"]), select:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"]):not([disabled])'

            return [...root.querySelectorAll(selector)].filter(el => el.getClientRects().length > 0)
        },

        focusInside() {
            const target = this.tabbables(this.contentEl)[0] ?? this.contentEl

            target?.focus({ preventScroll: true })
        },

        // Non-modal, so no trap — but the panel sits at the end of body, and
        // tabbing off its last control would land in the browser chrome.
        // Tab out closes and continues from the trigger; Shift+Tab out
        // closes and returns to it.
        tab(event) {
            const items = this.tabbables(this.contentEl)
            const active = document.activeElement
            const first = items[0] ?? this.contentEl
            const last = items[items.length - 1] ?? this.contentEl

            if (event.shiftKey && active === first) {
                event.preventDefault()
                this.close({ focus: false })
                this.triggerButton()?.focus()
            } else if (! event.shiftKey && active === last) {
                event.preventDefault()
                this.close({ focus: false })
                this.focusAfterTrigger()
            }
        },

        focusAfterTrigger() {
            const button = this.triggerButton()
            const all = this.tabbables(document.body).filter(el => ! this.contentEl?.contains(el))
            const index = all.indexOf(button)

            ;(index === -1 ? button : all[index + 1] ?? button)?.focus()
        },

        /*
        | position: fixed + measured placement in physical viewport
        | coordinates. `start`/`end` resolve against the component's own
        | direction, so placement mirrors on RTL pages — and inside RTL
        | islands — by itself.
        */
        place() {
            const content = this.contentEl
            const trigger = this.$refs.trigger

            if (! content || ! trigger) return

            const t = trigger.getBoundingClientRect()
            const rtl = getComputedStyle(this.$root).direction === 'rtl'
            const pad = 5
            const offset = this.offset
            const align = this.align

            let side = this.position

            if (side === 'start') side = rtl ? 'right' : 'left'
            if (side === 'end') side = rtl ? 'left' : 'right'

            const c = { w: content.offsetWidth, h: content.offsetHeight }
            const vw = window.innerWidth
            const vh = window.innerHeight

            // Flip to the other side when the preferred one runs out of room
            // and the other has it.
            if (side === 'bottom' && t.bottom + offset + c.h > vh && t.top - offset - c.h > 0) side = 'top'
            else if (side === 'top' && t.top - offset - c.h < 0 && t.bottom + offset + c.h < vh) side = 'bottom'
            else if (side === 'right' && t.right + offset + c.w > vw && t.left - offset - c.w > 0) side = 'left'
            else if (side === 'left' && t.left - offset - c.w < 0 && t.right + offset + c.w < vw) side = 'right'

            const vertical = side === 'top' || side === 'bottom'

            let x, y

            if (vertical) {
                y = side === 'bottom' ? t.bottom + offset : t.top - offset - c.h

                // start hugs the leading edge — the trigger's right edge on an RTL page.
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

            // The arrow points at the middle of the trigger, wherever the
            // panel was clamped to.
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
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerPopover(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerPopover(window.Alpine))
}
</script>
@endonce

<span
    {{ $attributes->class('relative inline-block') }}
    x-id="['mds-popover', 'mds-popover-trigger']"
    x-data="mdsPopover({ position: @js($position), align: @js($align), offset: @js($offset), hover: @js((bool) $hover) })"
    x-on:keydown.escape.window="open && close()"
    x-on:focusout="focusout($event)"
    data-mds-popover
>{{ $slot }}</span>
