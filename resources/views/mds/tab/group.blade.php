@props([
    'value' => null,
    'fa' => null,
])

@php
// fa picks the built-in tablist label's language; the tabs inside inherit it.
$fa ??= config('mds.persian_digits', true);

// A component's slot renders BEFORE its own view, so by the time this runs
// the tablist inside has already decided which tab is active — the explicit
// `value`, else its first enabled tab — and left that name on a per-request
// stack (see tabs.blade.php). Pop it here: the stack is what let the panels,
// which render after the tablist, agree with it without JavaScript, and a
// sibling group further down the page must start from a clean one.
$registry = app()->bound('mds.tabs') ? app('mds.tabs') : ['pending' => [], 'active' => []];
$active = $registry['active'] === [] ? $value : array_pop($registry['active']);
app()->instance('mds.tabs', $registry);

// Flux puts the binding on the group; here the hidden control Livewire reads
// lives on the tablist, so a wire:model written here would land on a plain
// div and be ignored without a word. Say so instead of swallowing it.
if ($attributes->whereStartsWith('wire:model')->getAttributes() !== []) {
    throw new \InvalidArgumentException('mds:tab.group does not take wire:model — put it on the mds:tabs inside, which owns the control Livewire binds to.');
}
@endphp

@once('mds-tabs')
<script @mdsNonce>
window.mds = window.mds || {}

// Registered through a guard rather than straight from alpine:init: a page
// reached by wire:navigate runs this block after Alpine has already started.
window.mds.registerTabs = (Alpine) => {
    if (window.mds.tabsRegistered) return
    window.mds.tabsRegistered = true

    Alpine.data('mdsTabs', (config = {}) => ({
        active: config.value ?? null,
        observer: null,

        init() {
            const input = this.input()

            if (input) {
                // A Livewire morph that changes the bound property rewrites the
                // hidden input's value ATTRIBUTE; Alpine state does not follow
                // on its own. Read the attribute, not the property — once a
                // value has been set from script the property stops mirroring it.
                this.observer = new MutationObserver(() => this.apply(input.getAttribute('value')))
                this.observer.observe(input, { attributes: true, attributeFilter: ['value'] })
            }

            // wire:model seeds the hidden input from the Livewire property, but
            // that directive sits on a child and runs after this init — so the
            // first read waits a tick, and takes the property it wrote.
            this.$nextTick(() => this.apply(input ? input.value : this.active))
        },

        destroy() {
            this.observer?.disconnect()
            this.observer = null
        },

        input() {
            return this.$refs.tabsInput ?? null
        },

        // This group's own tabs — a group nested inside a panel keeps its own.
        tabs() {
            return [...this.$root.querySelectorAll('[data-mds-tab]')]
                .filter(el => el.closest('[data-mds-tab-group]') === this.$root)
        },

        enabled() {
            return this.tabs().filter(el => ! el.disabled)
        },

        has(name) {
            return this.enabled().some(el => el.getAttribute('data-mds-tab') === name)
        },

        // A value arriving from the server or the bound property: take it when
        // it names an enabled tab, else fall back to the first one — the same
        // rule the Blade side applies. The hidden control is corrected quietly:
        // this was not a change the user made, so no event is dispatched.
        apply(name) {
            this.active = this.has(name) ? name : (this.enabled()[0]?.getAttribute('data-mds-tab') ?? null)

            const input = this.input()

            if (input && this.active !== null && input.value !== this.active) input.value = this.active
        },

        select(name) {
            if (name === this.active || ! this.has(name)) return

            this.active = name

            const input = this.input()

            if (! input) return

            input.value = name
            input.dispatchEvent(new Event('input', { bubbles: true }))
        },

        isActive(name) {
            return this.active === name
        },

        // WAI-ARIA tabs, automatic activation: the arrows move focus AND
        // select, wrapping at the ends; Home/End jump; disabled tabs are
        // skipped. Left/Right follow the visual order, so they flip in RTL —
        // read off the tablist itself, not the document, so an RTL island in
        // an LTR page (or the reverse) still moves the way it looks.
        keydown(event) {
            if (! ['ArrowLeft', 'ArrowRight', 'Home', 'End'].includes(event.key)) return

            const list = event.currentTarget
            const tabs = [...list.querySelectorAll('[data-mds-tab]:not([disabled])')]

            if (! tabs.length) return

            const current = tabs.indexOf(event.target.closest('[data-mds-tab]'))
            const rtl = getComputedStyle(list).direction === 'rtl'
            let next

            if (event.key === 'Home') {
                next = 0
            } else if (event.key === 'End') {
                next = tabs.length - 1
            } else {
                const forward = (event.key === 'ArrowRight') !== rtl

                next = current === -1 ? 0 : (current + (forward ? 1 : -1) + tabs.length) % tabs.length
            }

            event.preventDefault()

            this.select(tabs[next].getAttribute('data-mds-tab'))
            tabs[next].focus()
        },
    }))
}

if (window.Alpine) {
    window.mds.registerTabs(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerTabs(window.Alpine))
}
</script>
@endonce

<div
    {{ $attributes }}
    x-data="mdsTabs({ value: @js($active) })"
    data-mds-tab-group
>
    {{ $slot }}
</div>
