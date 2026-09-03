{{--
    The Latin -> Persian digit map, once per page. Five views used to inline
    their own copy of the same replace(), which is the JS twin of
    Persian::digits(). @once keys on this block, so however many components
    pull it in, it is emitted a single time.

    The same block registers x-mds-digits, the directive that normalises
    what a user TYPES: Persian keyboards produce ۰۱۲۳, Arabic ones ٠١٢٣, and
    a wire:model bound to a plain input would post those to the server. The
    directive rewrites them to Latin as they arrive, so the bound value is
    always the machine form — the same rule the hidden inputs follow.
--}}
@once('mds-digits')
<script @mdsNonce>
window.mds = window.mds || {}

// Display-only: machine values stay Latin in the hidden inputs.
window.mds.digits = (value, fa = true) => {
    const s = String(value)

    return fa ? s.replace(/[0-9]/g, d => '۰۱۲۳۴۵۶۷۸۹'[+d]) : s
}

// The inverse, for reading a Persian- or Arabic-typed value back.
window.mds.latinDigits = (value) => String(value)
    .replace(/[۰-۹]/g, d => '۰۱۲۳۴۵۶۷۸۹'.indexOf(d))
    .replace(/[٠-٩]/g, d => '٠١٢٣٤٥٦٧٨٩'.indexOf(d))

// x-mds-digits — Latin digits in the value, whatever keyboard typed them.
// Modifier `.only` also drops everything that is not a digit (codes, phone
// numbers, card numbers).
//
// Two layers. `beforeinput` catches typing, paste and drop before the
// browser applies them and inserts the normalised text instead, so the
// field sees ONE input event, already Latin, and undo history stays intact.
// Composition input (Android keyboards commit words through an IME) cannot
// be cancelled, so a second pass on `input` normalises whatever slipped
// through, restores the caret, and re-announces the value.
window.mds.registerDigitsDirective = (Alpine) => {
    if (window.mds.digitsDirectiveRegistered) return
    window.mds.digitsDirectiveRegistered = true

    // Alpine walks only the trees under a root — x-data or x-init — so an
    // input in a plain form carrying nothing but this directive would never
    // be initialised. Registering the attribute as a root selector makes a
    // bare mds:input start up like any Alpine component; inside an x-data
    // scope it is reached by the parent's walk as usual. (No angle brackets
    // in this comment: the kit's tag compiler would compile them.)
    Alpine.addRootSelector(() => '[x-mds-digits],[x-mds-digits\\.only]')

    Alpine.directive('mds-digits', (el, { modifiers }, { cleanup }) => {
        const only = modifiers.includes('only')

        const normalise = (s) => {
            s = window.mds.latinDigits(s)

            return only ? s.replace(/\D+/g, '') : s
        }

        const onBeforeInput = (e) => {
            if (! e.inputType.startsWith('insert') || e.inputType === 'insertCompositionText') return

            const data = e.data ?? e.dataTransfer?.getData('text/plain') ?? null

            if (data === null) return

            const clean = normalise(data)

            if (clean === data) return

            e.preventDefault()

            // execCommand is the one insertion path that keeps the browser's
            // undo stack and fires a real `input` event of its own.
            if (clean !== '' && ! document.execCommand('insertText', false, clean)) {
                el.setRangeText(clean, el.selectionStart, el.selectionEnd, 'end')
                el.dispatchEvent(new Event('input', { bubbles: true }))
            }
        }

        let resyncing = false

        const onInput = () => {
            if (resyncing) return

            const before = el.value
            const after = normalise(before)

            if (after === before) return

            // Whatever was dropped sat before the caret; count what survives there.
            const caret = el.selectionStart
            const position = caret === null ? null : normalise(before.slice(0, caret)).length

            resyncing = true
            el.value = after

            if (position !== null) {
                try { el.setSelectionRange(position, position) } catch (_) {}
            }

            el.dispatchEvent(new Event('input', { bubbles: true }))
            resyncing = false
        }

        el.addEventListener('beforeinput', onBeforeInput)
        el.addEventListener('input', onInput)

        // A re-edited form may arrive with Persian digits already in the value.
        onInput()

        cleanup(() => {
            el.removeEventListener('beforeinput', onBeforeInput)
            el.removeEventListener('input', onInput)
        })
    })
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerDigitsDirective(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerDigitsDirective(window.Alpine))
}
</script>
@endonce
