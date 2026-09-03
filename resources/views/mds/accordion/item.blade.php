@props([
    'expanded' => false,
    'disabled' => false,
    'heading' => null,
    'fa' => null,
])

@aware([
    'exclusive' => false,
    'transition' => false,
    'name' => null,
    'fa' => null,
])

@php
$fa ??= config('mds.persian_digits', true);
@endphp

@once('mds-accordion')
<script @mdsNonce>
window.mds = window.mds || {}

// Each item is a native details/summary pair, so open/close, Enter and Space,
// focus and the expanded state all come from the browser. Alpine only adds
// what the element lacks: the disabled guard, aria-expanded/data-expanded
// mirrors, JS-side exclusivity (browsers without the details `name`
// attribute, and animated closing of the sibling), and the open/close
// animation itself. A details element hides its content the instant `open`
// goes, so the animation runs on the content's height and the attribute is
// only removed once the box has reached zero.
window.mds.registerAccordion = (Alpine) => {
    if (window.mds.accordionRegistered) return
    window.mds.accordionRegistered = true

    const DURATION = 200
    const EASING = 'cubic-bezier(0.4, 0, 0.2, 1)'

    Alpine.data('mdsAccordionItem', (config = {}) => ({
        expanded: config.expanded ?? false,
        disabled: config.disabled ?? false,
        transition: config.transition ?? false,
        exclusive: config.exclusive ?? false,
        animation: null,

        init() {
            // The server rendered `open` from the prop; a Livewire morph or a
            // find-in-page reveal may already disagree with the config.
            this.expanded = this.$el.open

            if (! this.exclusive) return

            const root = this.$el.closest('[data-mds-accordion]')

            if (this.transition) {
                // A native name group closes the sibling before any script
                // runs — instantly. With JS on, closeSiblings() does it
                // animated instead, so the attribute has to go.
                this.$el.removeAttribute('name')
            } else if (root && ! root.hasAttribute('data-mds-accordion-name')) {
                // Unnamed accordions all rendered the same constant; give each
                // its own group so two on one page stop closing each other.
                this.$el.setAttribute('name', this.$id('mds-accordion'))
            }
        },

        destroy() {
            this.animation?.cancel()
        },

        animates() {
            return this.transition
                && this.$refs.content !== undefined
                && ! window.matchMedia('(prefers-reduced-motion: reduce)').matches
        },

        // The summary's click — a keyboard Enter/Space arrives here as well.
        toggle(event) {
            if (this.disabled) {
                event.preventDefault()

                return
            }

            // Plain items toggle natively; onToggle() mirrors the result.
            if (! this.animates()) return

            event.preventDefault()

            this.expanded ? this.collapse() : this.expand()
        },

        expand() {
            if (this.disabled) return

            if (this.$el.open) {
                // Re-opened mid-collapse: grow back from wherever the box is.
                if (! this.animation) return

                const from = this.$refs.content.getBoundingClientRect().height

                this.animation.cancel()
                this.expanded = true
                this.animate(from, this.$refs.content.offsetHeight)

                return
            }

            // Fires `toggle`, and onToggle() takes it from there.
            this.$el.open = true
        },

        collapse() {
            if (! this.$el.open) return

            if (! this.animates()) {
                this.$el.open = false

                return
            }

            const from = this.$refs.content.getBoundingClientRect().height

            this.animation?.cancel()
            this.expanded = false
            this.animate(from, 0, () => { this.$el.open = false })
        },

        // Every change of `open`: ours, a native click, a Livewire morph, the
        // browser closing a sibling in a name group, a find-in-page reveal.
        onToggle() {
            if (! this.$el.open) {
                this.expanded = false

                return
            }

            this.expanded = true

            if (this.exclusive) this.closeSiblings()

            if (this.animates() && ! this.animation) {
                this.animate(0, this.$refs.content.offsetHeight)
            }
        },

        closeSiblings() {
            const root = this.$el.closest('[data-mds-accordion]')

            if (! root) return

            root.querySelectorAll('[data-mds-accordion-item][open]').forEach((sibling) => {
                // Items of a nested accordion belong to their own group.
                if (sibling === this.$el || sibling.closest('[data-mds-accordion]') !== root) return

                Alpine.$data(sibling).collapse()
            })
        },

        animate(from, to, done = null) {
            const content = this.$refs.content

            this.animation?.cancel()

            content.style.overflow = 'hidden'

            this.animation = content.animate(
                [{ height: `${from}px` }, { height: `${to}px` }],
                { duration: DURATION, easing: EASING },
            )

            const finish = () => {
                this.animation = null
                content.style.overflow = ''
            }

            this.animation.onfinish = () => {
                finish()
                done?.()
            }
            this.animation.oncancel = finish
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerAccordion(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerAccordion(window.Alpine))
}
</script>
@endonce

<details
    {{ $attributes->class('group/mds-accordion') }}
    @if ($expanded) open @endif
    @if ($exclusive) name="{{ $name ?? 'mds-accordion' }}" @endif
    @if ($expanded) data-expanded @endif
    @if ($disabled) data-disabled @endif
    x-data="mdsAccordionItem({
        expanded: {{ $expanded ? 'true' : 'false' }},
        disabled: {{ $disabled ? 'true' : 'false' }},
        transition: {{ $transition ? 'true' : 'false' }},
        exclusive: {{ $exclusive ? 'true' : 'false' }},
    })"
    x-bind:data-expanded="expanded ? '' : false"
    x-on:toggle="onToggle()"
    data-mds-accordion-item
>
    @if ($heading !== null)
        <mds:accordion.heading>{{ $heading }}</mds:accordion.heading>

        <mds:accordion.content>{{ $slot }}</mds:accordion.content>
    @else
        {{ $slot }}
    @endif
</details>
