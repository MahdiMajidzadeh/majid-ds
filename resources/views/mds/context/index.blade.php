@props([
    'disabled' => false,
    'longPress' => true,
    'focusable' => false,
    'fa' => null,
])

@php
// fa is inherited by the menu subcomponent; the context has no built-in
// strings of its own today, so the prop is a contract, not a switch.
$fa ??= config('mds.persian_digits', true);
@endphp

@once('mds-context')
<script @mdsNonce>
window.mds = window.mds || {}

// A right-click menu around any content — the open version of Flux Pro's
// context. The menu itself is the caller's flux:menu (free tier), which
// Flux renders as a `popover="manual"` element: hidden until shown, and
// shown into the top layer, above modals and clipping ancestors alike. So
// the component drives the popover API rather than a display toggle, and
// positions the menu element itself in physical viewport pixels (a
// top-layer box ignores its parent's position). A slot that is not a
// popover — a plain div of buttons — is positioned through the wrapper
// instead, the same numbers written to both.
window.mds.registerContext = (Alpine) => {
    if (window.mds.contextRegistered) return
    window.mds.contextRegistered = true

    Alpine.data('mdsContext', (config = {}) => ({
        open: false,
        x: 0,
        y: 0,
        disabled: config.disabled ?? false,
        longPress: config.longPress ?? true,

        // Registered from x-init — $refs cannot reach through x-teleport.
        panelEl: null,

        // Where focus was when the menu opened; it goes back there on close.
        returnFocus: null,
        // Set by the keyboard path: focus lands on the first item, not the menu.
        viaKeyboard: false,
        // Keyboard-opened menus are followed by a browser-synthesised
        // contextmenu event; the timestamp lets that duplicate be ignored.
        keyboardAt: 0,
        // Whether we showed the popover (kept locally: matches(':popover-open')
        // throws where the popover API is polyfilled).
        shown: false,

        pressTimer: null,
        pressPoint: null,
        placeFrame: null,

        init() {
            // Anything that moves the page under the menu closes it — the
            // behaviour of every native context menu.
            this.onScroll = () => { this.cancelPress(); if (this.open) this.close() }
            this.onBlur = () => { this.cancelPress(); if (this.open) this.close(false) }
            this.onResize = () => { if (this.open) this.place() }
            // Capture, so a target that stops propagation still dismisses it.
            this.onPointerDown = (event) => {
                if (! this.open) return
                if (this.panelEl?.contains(event.target)) return

                this.close(false)
            }

            window.addEventListener('scroll', this.onScroll, { capture: true, passive: true })
            window.addEventListener('blur', this.onBlur)
            window.addEventListener('resize', this.onResize, { passive: true })
            document.addEventListener('pointerdown', this.onPointerDown, { capture: true })
        },

        destroy() {
            this.cancelPress()

            if (this.placeFrame) cancelAnimationFrame(this.placeFrame)
            this.placeFrame = null

            window.removeEventListener('scroll', this.onScroll, { capture: true })
            window.removeEventListener('blur', this.onBlur)
            window.removeEventListener('resize', this.onResize)
            document.removeEventListener('pointerdown', this.onPointerDown, { capture: true })

            if (this.open) this.close(false)
        },

        // The caller's flux:menu when there is one, else whatever the slot holds.
        get menu() {
            const panel = this.panelEl

            if (! panel) return null

            return panel.querySelector('[data-flux-menu], [role="menu"]') ?? panel.firstElementChild
        },

        get rtl() {
            return getComputedStyle(this.$root).direction === 'rtl'
        },

        // Right-click (and, on Android, the long-press the browser turns into
        // a contextmenu event): the menu opens at the pointer.
        contextmenu(event) {
            if (this.disabled) return

            event.preventDefault()

            // The keyboard handler already opened this one.
            if (performance.now() - this.keyboardAt < 300) return

            this.openAt(event.clientX, event.clientY, false)
        },

        // The keyboard's own context-menu gesture: the ContextMenu key, or
        // Shift+F10 where the keyboard has none. The menu opens at the
        // bottom-start corner of whatever has focus.
        keydown(event) {
            if (this.disabled) return
            if (! (event.key === 'ContextMenu' || (event.key === 'F10' && event.shiftKey))) return

            event.preventDefault()

            const target = event.target instanceof Element ? event.target : this.$root
            const rect = target.getBoundingClientRect()

            this.keyboardAt = performance.now()
            this.openAt(this.rtl ? rect.right : rect.left, rect.bottom, true)
        },

        // Touch: a 500ms press opens the menu; moving, lifting or scrolling
        // before then cancels it. iOS never fires contextmenu for a press on
        // ordinary content, which is what this path exists for.
        pointerdown(event) {
            if (this.disabled || ! this.longPress || event.pointerType !== 'touch') return

            this.cancelPress()

            this.pressPoint = { x: event.clientX, y: event.clientY }
            this.pressTimer = setTimeout(() => {
                this.pressTimer = null
                this.openAt(this.pressPoint.x, this.pressPoint.y, false)
            }, 500)
        },

        pointermove(event) {
            if (! this.pressTimer || ! this.pressPoint) return

            if (Math.hypot(event.clientX - this.pressPoint.x, event.clientY - this.pressPoint.y) > 10) this.cancelPress()
        },

        cancelPress() {
            clearTimeout(this.pressTimer)
            this.pressTimer = null
            this.pressPoint = null
        },

        openAt(x, y, viaKeyboard) {
            if (! this.open) this.returnFocus = document.activeElement

            this.viaKeyboard = viaKeyboard
            this.x = x
            this.y = y
            this.open = true

            // x-show has to reveal the wrapper before the popover can be shown
            // and measured — a top-layer box under a display:none ancestor
            // still renders nothing.
            this.$nextTick(() => this.show())
        },

        show() {
            const menu = this.menu

            if (! menu) return

            if (menu.popover && ! this.shown) {
                try { menu.showPopover(); this.shown = true } catch (e) {}
            }

            this.place()

            // Alpine writes x-show's display through its own deferred DOM
            // queue, which can still be pending here: the panel is then
            // display:none, a top-layer box under it measures 0 x 0, and the
            // menu is placed as if it had no size (no flip, no RTL offset).
            // One more pass on the next frame, when the layout is certainly
            // there; place() is idempotent, so the common case is a no-op.
            if (this.placeFrame) cancelAnimationFrame(this.placeFrame)

            this.placeFrame = requestAnimationFrame(() => {
                this.placeFrame = null

                if (this.open) this.place()
            })

            // Pointer: the menu container takes focus so Escape and the arrow
            // keys work without highlighting an item the pointer did not
            // choose. Keyboard: straight onto the first item, as the menu
            // pattern asks.
            const target = this.viaKeyboard
                ? menu.querySelector('[role="menuitem"]:not([disabled]), [role="menuitemcheckbox"]:not([disabled]), [role="menuitemradio"]:not([disabled])') ?? menu
                : menu

            if (menu.tabIndex < 0 && ! menu.hasAttribute('tabindex')) menu.tabIndex = -1

            target.focus({ preventScroll: true })
        },

        /*
        | Physical viewport pixels on purpose — the anchor is a pointer, not
        | an element. The menu's START edge sits at the pointer: its left edge
        | on an LTR page, its right edge on an RTL one, the way native menus
        | open in each direction. When that side runs out of room the menu
        | flips to the other side of the pointer; same for below/above; then
        | it is clamped so no part ever leaves the viewport.
        */
        place() {
            const menu = this.menu
            const panel = this.panelEl

            if (! menu || ! panel) return

            const w = menu.offsetWidth
            const h = menu.offsetHeight
            const vw = window.innerWidth
            const vh = window.innerHeight
            const pad = 4
            const rtl = this.rtl

            let x = rtl ? this.x - w : this.x
            let y = this.y

            if (rtl && x < pad && this.x + w <= vw - pad) x = this.x
            else if (! rtl && x + w > vw - pad && this.x - w >= pad) x = this.x - w

            if (y + h > vh - pad && this.y - h >= pad) y = this.y - h

            x = Math.min(Math.max(x, pad), Math.max(vw - w - pad, pad))
            y = Math.min(Math.max(y, pad), Math.max(vh - h - pad, pad))

            panel.style.left = x + 'px'
            panel.style.top = y + 'px'

            if (menu.popover) {
                // The UA sheet centres a popover with inset:0 + margin:auto;
                // pin it to the corner instead.
                menu.style.position = 'fixed'
                menu.style.inset = 'auto'
                menu.style.margin = '0'
                menu.style.left = x + 'px'
                menu.style.top = y + 'px'
            }
        },

        // An activated item closes the menu. Flux's items announce themselves
        // with lofi-close-popovers; plain buttons and links in a custom slot
        // are caught by the click itself.
        clicked(event) {
            const item = event.target.closest('[role^="menuitem"], button, a')

            if (! item || item.hasAttribute('disabled') || item.getAttribute('aria-disabled') === 'true') return

            this.close()
        },

        close(restoreFocus = true) {
            if (! this.open) return

            const menu = this.menu

            if (menu?.popover && this.shown) {
                try { menu.hidePopover() } catch (e) {}
            }

            if (this.placeFrame) cancelAnimationFrame(this.placeFrame)
            this.placeFrame = null

            this.shown = false
            this.open = false

            const target = this.returnFocus
            this.returnFocus = null

            if (restoreFocus && target?.isConnected && typeof target.focus === 'function') {
                target.focus({ preventScroll: true })
            }
        },
    }))
}

if (window.Alpine) { window.mds.registerContext(window.Alpine) }
else { document.addEventListener('alpine:init', () => window.mds.registerContext(window.Alpine)) }
</script>
@endonce

{{--
    The wrapper is not a button, so it claims no aria-haspopup: a context
    menu has no trigger to describe. It is inert to the accessibility tree
    unless `focusable` asks for a tab stop — for content with nothing
    focusable of its own (an image, a plain card), so keyboard users have
    somewhere to press Shift+F10.
--}}
<div
    {{ $attributes->class('relative') }}
    x-data="mdsContext({ disabled: @js((bool) $disabled), longPress: @js((bool) $longPress) })"
    @unless ($disabled)
        x-on:contextmenu="contextmenu($event)"
        x-on:keydown="keydown($event)"
        x-on:pointerdown="pointerdown($event)"
        x-on:pointermove="pointermove($event)"
        x-on:pointerup="cancelPress()"
        x-on:pointercancel="cancelPress()"
    @endunless
    @if ($focusable) tabindex="0" @endif
    @if ($disabled) data-disabled @endif
    data-mds-context
>
    {{ $slot }}
</div>
