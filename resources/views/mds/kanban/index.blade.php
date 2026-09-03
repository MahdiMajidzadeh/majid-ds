@props([
    'label' => null,
    'disabled' => false,
    'fa' => null,
])

@php
// fa picks the built-in strings' language along with the digits inside the
// announcements the live region reads out.
$fa ??= config('mds.persian_digits', true);

$label ??= $fa ? 'تخته کانبان' : 'Kanban board';

$hint = $fa
    ? 'برای برداشتن کارت کلید فاصله یا Enter را بزنید، با کلیدهای جهت‌نما جابه‌جا کنید، دوباره فاصله را برای رها کردن و Esc را برای لغو بزنید.'
    : 'Press Space or Enter to pick up a card, the arrow keys to move it, Space or Enter to drop it, Escape to cancel.';

// The announcement templates ride on the live region as data attributes
// rather than inside the x-data JSON: @js() escapes every non-ASCII byte to
// \uXXXX, which would put unreadable Persian in the markup. Alpine fills in
// :card, :column, :index and :total.
$strings = $fa ? [
    'grab' => '«:card» برداشته شد. جایگاه :index از :total در ستون «:column».',
    'move' => '«:card» به جایگاه :index از :total در ستون «:column» رفت.',
    'drop' => '«:card» در جایگاه :index از :total در ستون «:column» رها شد.',
    'cancel' => 'جابه‌جایی «:card» لغو شد.',
] : [
    'grab' => '“:card” picked up. Position :index of :total in “:column”.',
    'move' => '“:card” moved to position :index of :total in “:column”.',
    'drop' => '“:card” dropped at position :index of :total in “:column”.',
    'cancel' => 'Move of “:card” cancelled.',
];
@endphp

@include('mds::partials.digits')

@once('mds-kanban')
<script @mdsNonce>
window.mds = window.mds || {}

// Registered through a guard rather than straight from alpine:init: a page
// reached by wire:navigate runs this block after alpine:init has fired.
window.mds.registerKanban = (Alpine) => {
    if (window.mds.kanbanRegistered) return
    window.mds.kanbanRegistered = true

    Alpine.data('mdsKanban', (config = {}) => ({
        fa: config.fa ?? true,
        disabled: config.disabled ?? false,

        // key -> number of cards. Reactive, so the column headings and the
        // region labels re-render themselves as cards move; the DOM order of
        // the cards is the state, this is only what is derived from it.
        counts: {},
        announcement: '',

        grabbed: null,  // the card a keyboard move is carrying
        origin: null,   // where it was picked up, for Escape
        drag: null,     // pointer drag bookkeeping
        focused: null,  // the roving tabindex holder
        moving: false,  // guards the blur our own DOM moves cause
        observer: null,
        queued: false,

        init() {
            this.refresh()

            // The hidden selects must agree with the cards from the first
            // paint on: an app may render them from a different source.
            this.write(false)

            // Cards and options both arrive as slot content, so a Livewire
            // morph (or an x-for) can add, remove or reorder either one.
            // resync() is a fixed point — it returns without writing once the
            // two agree — so our own writes cannot loop back through it.
            this.observer = new MutationObserver(() => this.schedule())
            this.observer.observe(this.$root, { childList: true, subtree: true })
        },

        destroy() {
            this.observer?.disconnect()
            this.observer = null
            this.drag = null
            this.grabbed = null
        },

        schedule() {
            if (this.queued) return

            this.queued = true

            queueMicrotask(() => {
                this.queued = false
                this.resync()
            })
        },

        // ---- reading the board ------------------------------------------

        lists() {
            return [...this.$root.querySelectorAll('[data-mds-kanban-cards]')]
        },

        cards() {
            return [...this.$root.querySelectorAll('[data-mds-kanban-card]')]
        },

        cardsIn(list) {
            return [...list.querySelectorAll(':scope > [data-mds-kanban-card]')]
        },

        columnOf(el) {
            return el.closest('[data-mds-kanban-column]')
        },

        listIn(column) {
            return column.querySelector('[data-mds-kanban-cards]')
        },

        keyOf(column) {
            return column ? (column.dataset.mdsKanbanColumn ?? '') : ''
        },

        idOf(card) {
            return card.dataset.mdsKanbanCard ?? ''
        },

        titleOf(card) {
            return card.dataset.mdsKanbanCardTitle
                || card.textContent.replace(/\s+/g, ' ').trim().slice(0, 80)
        },

        headingOf(column) {
            const heading = column?.querySelector('[data-mds-kanban-heading]')

            return heading ? heading.textContent.trim() : this.keyOf(column)
        },

        digits(value) {
            return window.mds.digits(value, this.fa)
        },

        // ---- derived state ----------------------------------------------

        refresh() {
            this.lists().forEach(list => {
                this.counts[this.keyOf(this.columnOf(list))] = this.cardsIn(list).length
            })

            const cards = this.cards()

            if (! cards.includes(this.focused)) this.focused = cards[0] ?? null

            cards.forEach(card => { card.tabIndex = card === this.focused ? 0 : -1 })
        },

        count(column) {
            return this.counts[this.keyOf(column)] ?? 0
        },

        limitOf(column) {
            const limit = Number(column.dataset.mdsKanbanLimit)

            return Number.isFinite(limit) && limit > 0 ? limit : null
        },

        over(column) {
            const limit = this.limitOf(column)

            return limit !== null && this.count(column) > limit
        },

        // The count badge and the region name, in the column's own language:
        // each column carries its own templates, because fa is per component.
        countText(column) {
            const n = this.count(column)
            const limit = this.limitOf(column)
            const template = limit !== null
                ? column.dataset.mdsKanbanCountLimit
                : (n === 1 ? column.dataset.mdsKanbanCountOne : column.dataset.mdsKanbanCount)

            return String(template ?? '')
                .split(':n').join(this.digits(n))
                .split(':limit').join(this.digits(limit ?? ''))
        },

        regionLabel(column) {
            const parts = [this.headingOf(column), this.countText(column)]

            if (this.over(column)) parts.push(column.dataset.mdsKanbanOverText ?? '')

            return parts.filter(Boolean).join(' — ')
        },

        // ---- the hidden controls ----------------------------------------

        // One visually hidden multi-select per column mirrors that column's
        // card order. Livewire reads a multiple select as an array, so the
        // bound property round-trips as the ordered list of ids.
        write(notify = true) {
            this.lists().forEach(list => {
                const select = this.columnOf(list)?.querySelector('[data-mds-kanban-state]')

                if (! select) return

                const ids = this.cardsIn(list).map(card => this.idOf(card))
                const now = [...select.options].map(option => option.value)
                const same = now.length === ids.length
                    && now.every((value, i) => value === ids[i])
                    && [...select.options].every(option => option.selected)

                if (same) return

                select.replaceChildren(...ids.map(id => new Option(id, id, true, true)))

                if (notify) {
                    select.dispatchEvent(new Event('input', { bubbles: true }))
                    select.dispatchEvent(new Event('change', { bubbles: true }))
                }
            })
        },

        // The morph half of the contract: the server changed a bound array,
        // Livewire patched the options, and the cards have to follow.
        resync() {
            this.lists().forEach(list => {
                const select = this.columnOf(list)?.querySelector('[data-mds-kanban-state]')

                if (! select) return

                const wanted = [...select.selectedOptions].map(option => option.value)
                const now = this.cardsIn(list).map(card => this.idOf(card))

                if (wanted.length === now.length && wanted.every((value, i) => value === now[i])) return

                const cards = this.cards()

                wanted.forEach(id => {
                    const card = cards.find(card => this.idOf(card) === id)

                    if (card) list.appendChild(card)
                })
            })

            this.refresh()

            // Whatever the two disagreed about, the cards are the state — so
            // fold the result back into the selects without telling Livewire.
            this.write(false)
        },

        // ---- announcements -----------------------------------------------

        announce(kind, card) {
            const template = this.$refs.live?.dataset[kind]

            if (! template) return

            const list = card.closest('[data-mds-kanban-cards]')
            const column = this.columnOf(card)
            const cards = list ? this.cardsIn(list) : []

            this.announcement = String(template)
                .split(':card').join(this.titleOf(card))
                .split(':column').join(this.headingOf(column))
                .split(':index').join(this.digits(cards.indexOf(card) + 1))
                .split(':total').join(this.digits(cards.length))
        },

        // ---- moving a card ------------------------------------------------

        // Re-inserting the focused card blurs it, and that blur must not be
        // read as "focus left the board" — hence the flag onBlur() checks.
        placeAt(list, card, index) {
            const others = this.cardsIn(list).filter(el => el !== card)

            this.moving = true
            list.insertBefore(card, others[index] ?? null)
        },

        // Everything a completed move owes the outside world: the derived
        // state, the hidden selects, one descriptive event per card moved.
        settle(card, from, index) {
            this.refresh()

            const list = card.closest('[data-mds-kanban-cards]')
            const to = this.keyOf(this.columnOf(card))
            const at = list ? this.cardsIn(list).indexOf(card) : -1
            const moved = to !== from || at !== index

            this.write(moved)

            if (moved) {
                this.$root.dispatchEvent(new CustomEvent('mds-kanban-moved', {
                    bubbles: true,
                    detail: { card: this.idOf(card), from, to, index: at },
                }))
            }

            return moved
        },

        // ---- keyboard ------------------------------------------------------

        onFocus(e) {
            const card = e.target.closest('[data-mds-kanban-card]')

            if (! card || ! this.$root.contains(card)) return

            // Focus left the card being carried — that ends the move without
            // stealing the focus back.
            if (this.grabbed && card !== this.grabbed) this.cancel(false)

            this.focused = card
            this.cards().forEach(el => { el.tabIndex = el === this.focused ? 0 : -1 })
        },

        // Focus left the board altogether while a card was being carried —
        // put it back where it came from rather than leaving it in limbo.
        onBlur(e) {
            if (! this.grabbed || this.moving) return

            const to = e.relatedTarget

            if (to && this.$root.contains(to)) return

            this.cancel(false)
        },

        onKey(e) {
            const card = e.target.closest('[data-mds-kanban-card]')

            if (! card || ! this.$root.contains(card)) return

            const carrying = this.grabbed === card

            if (e.key === 'Escape') {
                if (! carrying) return

                e.preventDefault()

                return this.cancel()
            }

            if (e.key === ' ' || e.key === 'Spacebar' || e.key === 'Enter') {
                // Enter belongs to a link or a button inside the card.
                if (e.target !== card) return

                e.preventDefault()

                return carrying ? this.drop() : this.pickUp(card)
            }

            // Horizontal keys follow the VISUAL order: on an RTL page the
            // first column is the rightmost one, so ArrowRight walks back.
            const rtl = getComputedStyle(this.$root).direction === 'rtl'
            const step = {
                ArrowRight: rtl ? -1 : 1,
                ArrowLeft: rtl ? 1 : -1,
            }[e.key]

            if (step !== undefined) {
                e.preventDefault()

                return carrying ? this.moveAcross(step) : this.focusAcross(card, step)
            }

            if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
                e.preventDefault()

                const delta = e.key === 'ArrowDown' ? 1 : -1

                return carrying ? this.moveDown(delta) : this.focusDown(card, delta)
            }

            if (e.key === 'Home' || e.key === 'End') {
                e.preventDefault()

                return carrying ? this.moveEnd(e.key === 'Home') : this.focusEnd(card, e.key === 'Home')
            }
        },

        movable(card) {
            return ! this.disabled && card.dataset.disabled === undefined
        },

        pickUp(card) {
            if (! this.movable(card)) return

            const list = card.closest('[data-mds-kanban-cards]')

            if (! list) return

            this.grabbed = card
            this.origin = {
                list,
                next: card.nextElementSibling,
                key: this.keyOf(this.columnOf(card)),
                index: this.cardsIn(list).indexOf(card),
            }

            card.setAttribute('data-grabbed', '')
            this.announce('grab', card)
        },

        drop() {
            const card = this.grabbed

            if (! card) return

            card.removeAttribute('data-grabbed')
            this.grabbed = null

            this.settle(card, this.origin.key, this.origin.index)
            this.origin = null
            this.announce('drop', card)
            card.focus()
        },

        cancel(refocus = true) {
            const card = this.grabbed

            if (! card) return

            const { list, next } = this.origin

            this.moving = true
            list.insertBefore(card, next && next.parentElement === list ? next : null)

            card.removeAttribute('data-grabbed')
            this.grabbed = null
            this.origin = null

            this.refresh()
            this.announce('cancel', card)

            if (refocus) card.focus()

            this.moving = false
        },

        // Re-inserting an element drops the focus, so every move takes it back.
        after(card) {
            this.refresh()
            this.announce('move', card)
            card.focus()
            card.scrollIntoView({ block: 'nearest', inline: 'nearest' })
            this.moving = false
        },

        moveDown(delta) {
            const card = this.grabbed
            const list = card.closest('[data-mds-kanban-cards]')
            const cards = this.cardsIn(list)
            const at = cards.indexOf(card)
            const to = Math.min(Math.max(at + delta, 0), cards.length - 1)

            if (to === at) return

            this.placeAt(list, card, to)
            this.after(card)
        },

        moveEnd(first) {
            const card = this.grabbed
            const list = card.closest('[data-mds-kanban-cards]')

            this.placeAt(list, card, first ? 0 : this.cardsIn(list).length - 1)
            this.after(card)
        },

        moveAcross(step) {
            const card = this.grabbed
            const lists = this.lists()
            const list = card.closest('[data-mds-kanban-cards]')
            const to = lists.indexOf(list) + step

            if (to < 0 || to >= lists.length) return

            const at = this.cardsIn(list).indexOf(card)

            this.placeAt(lists[to], card, Math.min(at, this.cardsIn(lists[to]).length))
            this.after(card)
        },

        focus(card) {
            if (! card) return

            this.focused = card
            card.focus()
            card.scrollIntoView({ block: 'nearest', inline: 'nearest' })
        },

        focusDown(card, delta) {
            const cards = this.cardsIn(card.closest('[data-mds-kanban-cards]'))

            this.focus(cards[cards.indexOf(card) + delta])
        },

        focusEnd(card, first) {
            const cards = this.cardsIn(card.closest('[data-mds-kanban-cards]'))

            this.focus(first ? cards[0] : cards[cards.length - 1])
        },

        focusAcross(card, step) {
            const lists = this.lists()
            const list = card.closest('[data-mds-kanban-cards]')
            const at = this.cardsIn(list).indexOf(card)

            // Walk on past empty columns rather than stopping dead in one.
            for (let i = lists.indexOf(list) + step; i >= 0 && i < lists.length; i += step) {
                const cards = this.cardsIn(lists[i])

                if (cards.length) return this.focus(cards[Math.min(at, cards.length - 1)])
            }
        },

        // ---- pointer -------------------------------------------------------

        pointerDown(e) {
            if (this.drag || this.grabbed || e.button !== 0) return

            const card = e.target.closest('[data-mds-kanban-card]')

            if (! card || ! this.$root.contains(card) || ! this.movable(card)) return

            const handle = e.target.closest('[data-mds-kanban-handle]')

            // Touch drags start on the handle only: the handle is the one
            // patch of the card that turns off touch scrolling, so the rest
            // of the board still scrolls under a finger.
            if (e.pointerType === 'touch' && ! handle) return

            // A press on something interactive is a press on that thing.
            if (! handle && e.target.closest('a, button, input, select, textarea, [contenteditable]')) return

            const list = card.closest('[data-mds-kanban-cards]')

            if (! list) return

            this.drag = {
                el: card,
                pointer: e.pointerId,
                x: e.clientX,
                y: e.clientY,
                active: false,
                list,
                next: card.nextElementSibling,
                key: this.keyOf(this.columnOf(card)),
                index: this.cardsIn(list).indexOf(card),
            }

            try { card.setPointerCapture(e.pointerId) } catch (_) {}
        },

        pointerMove(e) {
            if (! this.drag || e.pointerId !== this.drag.pointer) return

            const dx = e.clientX - this.drag.x
            const dy = e.clientY - this.drag.y

            if (! this.drag.active) {
                if (Math.hypot(dx, dy) < 5) return

                this.drag.active = true
                this.drag.el.setAttribute('data-dragging', '')
                this.drag.el.style.pointerEvents = 'none'
                this.drag.el.style.position = 'relative'
                this.drag.el.style.zIndex = '30'
                this.$root.classList.add('select-none')
                document.getSelection()?.removeAllRanges()
            }

            e.preventDefault()
            this.drag.el.style.transform = 'translate(' + dx + 'px, ' + dy + 'px)'
            this.dragOver(e.clientX, e.clientY)
        },

        // Live reordering: the card itself is moved into place as it travels,
        // so the gap under the pointer is the real drop position.
        dragOver(x, y) {
            const card = this.drag.el
            const under = document.elementFromPoint(x, y)
            const list = under?.closest('[data-mds-kanban-cards]')

            if (! list || ! this.$root.contains(list)) return

            let before = null

            for (const other of this.cardsIn(list)) {
                if (other === card) continue

                const rect = other.getBoundingClientRect()

                if (y < rect.top + rect.height / 2) { before = other; break }
            }

            if (card.parentElement === list && (before ? card.nextElementSibling === before : this.cardsIn(list).at(-1) === card)) return

            // Both rects carry the same transform, so their difference is the
            // layout shift alone: fold it into the origin and the card does
            // not jump under the finger.
            const was = card.getBoundingClientRect()

            list.insertBefore(card, before)

            const now = card.getBoundingClientRect()

            this.drag.x += now.left - was.left
            this.drag.y += now.top - was.top

            card.style.transform = 'translate(' + (x - this.drag.x) + 'px, ' + (y - this.drag.y) + 'px)'
            this.refresh()
        },

        pointerUp(e) {
            if (! this.drag || e.pointerId !== this.drag.pointer) return

            const { el, active, key, index } = this.drag

            this.release()

            if (! active) return

            this.settle(el, key, index)
            this.announce('drop', el)
        },

        pointerCancel(e) {
            if (! this.drag || e.pointerId !== this.drag.pointer) return

            const { el, active, list, next } = this.drag

            this.release()

            if (! active) return

            list.insertBefore(el, next && next.parentElement === list ? next : null)
            this.refresh()
            this.announce('cancel', el)
        },

        release() {
            const card = this.drag.el

            try { card.releasePointerCapture(this.drag.pointer) } catch (_) {}

            card.style.transform = ''
            card.style.pointerEvents = ''
            card.style.position = ''
            card.style.zIndex = ''
            card.removeAttribute('data-dragging')
            this.$root.classList.remove('select-none')
            this.drag = null
        },
    }))
}

if (window.Alpine) {
    window.mds.registerKanban(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerKanban(window.Alpine))
}
</script>
@endonce

<div
    {{ $attributes->class([
        'flex w-full items-start gap-4 overflow-x-auto pb-2',
        'opacity-60' => $disabled,
    ]) }}
    role="group"
    aria-label="{{ $label }}"
    x-data="mdsKanban({ fa: {{ $fa ? 'true' : 'false' }}, disabled: {{ $disabled ? 'true' : 'false' }} })"
    x-id="['mds-kanban-hint']"
    x-on:keydown="onKey($event)"
    x-on:focusin="onFocus($event)"
    x-on:focusout="onBlur($event)"
    x-on:pointerdown="pointerDown($event)"
    x-on:pointermove.window="pointerMove($event)"
    x-on:pointerup.window="pointerUp($event)"
    x-on:pointercancel.window="pointerCancel($event)"
    x-on:dragstart.prevent
    @if ($disabled) data-disabled aria-disabled="true" @endif
    data-mds-kanban
>
    {{ $slot }}

    <div class="sr-only" x-bind:id="$id('mds-kanban-hint')" data-mds-kanban-hint>{{ $hint }}</div>

    <div
        class="sr-only"
        role="status"
        aria-live="polite"
        aria-atomic="true"
        x-ref="live"
        x-text="announcement"
        data-grab="{{ $strings['grab'] }}"
        data-move="{{ $strings['move'] }}"
        data-drop="{{ $strings['drop'] }}"
        data-cancel="{{ $strings['cancel'] }}"
        data-mds-kanban-live
    ></div>
</div>
