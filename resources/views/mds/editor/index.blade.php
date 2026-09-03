@props([
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'label' => null,
    'labelSrOnly' => false,
    'description' => null,
    'descriptionSrOnly' => false,
    'toolbar' => null,
    'toolbarLabel' => null,
    'rows' => 6,
    'dir' => null,
    'disabled' => false,
    'invalid' => false,
    'error' => null,
    'fa' => null,
])

@php
// fa picks the built-in labels' language.
$fa ??= config('mds.persian_digits', true);

// Flux writes `label:sr-only`, which is not a name a PHP variable can hold —
// read it off the attribute bag instead, then keep it out of the markup...
$labelSrOnly = $labelSrOnly || $attributes->has('label:sr-only');
$descriptionSrOnly = $descriptionSrOnly || $attributes->has('description:sr-only');

$attributes = $attributes->except(['label:sr-only', 'description:sr-only']);

// An explicit :error wins; otherwise fall back to the validation bag for this name...
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

$rows = max(1, (int) $rows);
@endphp

@once('mds-editor')
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerEditor = (Alpine) => {
    if (window.mds.editorRegistered) return
    window.mds.editorRegistered = true

    /*
    | The allow-list IS the feature set. Everything the toolbar can produce is
    | here and nothing else is: pasted (or dropped) markup is filtered down to
    | this before it reaches the surface, and the string handed to the hidden
    | input — the one Livewire stores — is filtered again on the way out. A
    | rich-text field that round-trips whatever the clipboard held is a stored
    | XSS hole, so the sanitiser runs on BOTH sides of the surface.
    */
    const ALLOWED = ['p', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'pre', 'strong', 'em', 'u', 's', 'code', 'a', 'br']

    // Tags the browser still produces (or a Word/Docs paste carries) that mean
    // one of ours. Renamed, keeping their children.
    const RENAME = { b: 'strong', i: 'em', strike: 's', del: 's', ins: 'u', div: 'p', h4: 'h3', h5: 'h3', h6: 'h3', section: 'p', article: 'p' }

    // Dropped whole, children and all — nothing inside them is content.
    const DROP = ['script', 'style', 'title', 'head', 'link', 'meta', 'noscript', 'template', 'iframe', 'object', 'embed', 'applet', 'form', 'input', 'button', 'select', 'textarea', 'svg', 'math', 'img', 'video', 'audio', 'canvas']

    const BLOCKS = ['p', 'h1', 'h2', 'h3', 'blockquote', 'pre', 'li']

    // Blocks worth deleting when they end up holding nothing. The HTML parser
    // closes a <p> the moment a list opens inside it — which is exactly what
    // Chrome's insertUnorderedList produces — and leaves two empty shells.
    const PRUNE = ['p', 'h1', 'h2', 'h3', 'blockquote', 'pre', 'ul', 'ol', 'li']

    // href schemes that cannot execute script. Everything else — javascript:,
    // data:, vbscript:, a stray "  jAvAsCrIpT:" — loses the attribute, which
    // unwraps the anchor because an <a> with no href is not a link.
    const SAFE_HREF = /^(?:https?:|mailto:|tel:|ftp:|#|\/|\.{1,2}\/|[^:/?#]*(?:[/?#]|$))/i

    const escape = (text) => String(text ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[c])

    const unwrap = (el) => {
        while (el.firstChild) el.parentNode.insertBefore(el.firstChild, el)
        el.remove()
    }

    const scrub = (root, doc) => {
        for (const node of [...root.childNodes]) {
            // Comments can hide conditional markup (Word pastes are full of
            // them); text is content and passes through untouched.
            if (node.nodeType === Node.COMMENT_NODE) { node.remove(); continue }
            if (node.nodeType === Node.TEXT_NODE) continue
            if (node.nodeType !== Node.ELEMENT_NODE) { node.remove(); continue }

            const tag = node.tagName.toLowerCase()

            if (DROP.includes(tag)) { node.remove(); continue }

            const mapped = RENAME[tag] ?? tag

            if (! ALLOWED.includes(mapped)) {
                scrub(node, doc)
                unwrap(node)
                continue
            }

            let el = node

            if (mapped !== tag) {
                el = doc.createElement(mapped)
                while (node.firstChild) el.appendChild(node.firstChild)
                node.parentNode.replaceChild(el, node)
            }

            for (const attr of [...el.attributes]) {
                const name = attr.name.toLowerCase()
                const value = String(attr.value ?? '').trim()

                const keep = (name === 'href' && mapped === 'a' && SAFE_HREF.test(value))
                    || (name === 'dir' && ['ltr', 'rtl'].includes(value.toLowerCase()))

                if (! keep) el.removeAttribute(attr.name)
            }

            if (mapped === 'a' && ! el.getAttribute('href')) {
                scrub(el, doc)
                unwrap(el)
                continue
            }

            scrub(el, doc)

            // A deliberate blank line is <p><br></p>, so a <br> keeps a block.
            if (PRUNE.includes(mapped) && (el.textContent ?? '').trim() === '' && ! el.querySelector('br')) el.remove()
        }
    }

    /*
    | Parsed in a detached document created by document.implementation, not in
    | a live div: nothing there loads, runs or fires an error handler, so even
    | the markup we are about to throw away never executes.
    */
    const sanitize = (html) => {
        const doc = document.implementation.createHTMLDocument('')

        doc.body.innerHTML = String(html ?? '')

        scrub(doc.body, doc)

        return doc.body.innerHTML
    }

    const blank = (html) => {
        const doc = document.implementation.createHTMLDocument('')

        doc.body.innerHTML = String(html ?? '')

        return (doc.body.textContent ?? '').trim() === ''
    }

    const exec = (command, value = null) => {
        try {
            return document.execCommand(command, false, value)
        } catch (e) {
            return false
        }
    }

    const state = (command) => {
        try {
            return document.queryCommandState(command)
        } catch (e) {
            return false
        }
    }

    Alpine.data('mdsEditor', (config = {}) => ({
        fa: config.fa ?? true,
        disabled: config.disabled ?? false,
        linkPrompt: config.linkPrompt ?? 'Link URL',
        empty: true,
        value: '',
        marks: {},
        observer: null,
        onSelection: null,

        init() {
            // Ask for <p> paragraphs and tag-based marks rather than the
            // <div> + inline-style soup Chrome defaults to. Both are
            // no-ops where unsupported; the sanitiser cleans up either way.
            exec('defaultParagraphSeparator', 'p')
            exec('styleWithCSS', 'false')

            this.value = this.$refs.input?.getAttribute('value') ?? ''
            this.paint(this.value)

            this.roving()
            this.sync()

            // Selection state drives every aria-pressed in the toolbar.
            this.onSelection = () => { if (this.inside()) this.sync() }
            document.addEventListener('selectionchange', this.onSelection)

            /*
            | Morph re-sync. Livewire patches the hidden input's `value`
            | ATTRIBUTE when the server changes the bound property; the
            | property does not follow once we have written to it, so read
            | the attribute and compare against what we last committed —
            | otherwise our own commit would bounce back as a repaint and
            | eat the caret.
            */
            if (this.$refs.input) {
                this.observer = new MutationObserver(() => this.resync())
                this.observer.observe(this.$refs.input, { attributes: true, attributeFilter: ['value'] })
            }
        },

        destroy() {
            this.observer?.disconnect()
            this.observer = null

            if (this.onSelection) document.removeEventListener('selectionchange', this.onSelection)

            this.onSelection = null
        },

        resync() {
            const next = this.$refs.input.getAttribute('value') ?? ''

            if (next === this.value) return

            this.value = next
            this.paint(next)
        },

        // The ONLY place server-supplied HTML enters the document, and it is
        // sanitised on the way in.
        paint(html) {
            const clean = sanitize(html)

            if (this.$refs.surface) this.$refs.surface.innerHTML = clean

            this.empty = blank(clean)
        },

        inside() {
            const selection = document.getSelection()

            return !! (selection && selection.anchorNode && this.$refs.surface?.contains(selection.anchorNode))
        },

        // The element the caret sits in. A Ctrl+A (or any selectNodeContents)
        // anchors on the surface itself with an index offset rather than on a
        // text node, so walk down to the child it points at — otherwise every
        // block lookup below returns null and the toggles stop toggling.
        at() {
            const selection = document.getSelection()

            if (! selection || ! selection.rangeCount) return null

            const range = selection.getRangeAt(0)

            let node = range.startContainer

            if (node.nodeType === Node.ELEMENT_NODE) node = node.childNodes[range.startOffset] ?? node.lastChild ?? node
            if (node && node.nodeType === Node.TEXT_NODE) node = node.parentNode

            return node?.nodeType === Node.ELEMENT_NODE ? node : null
        },

        block() {
            let node = this.at()

            while (node && node !== this.$refs.surface) {
                if (BLOCKS.includes(node.tagName?.toLowerCase())) return node

                node = node.parentNode
            }

            return null
        },

        anchor() {
            const node = this.at()

            return node && this.$refs.surface?.contains(node) ? node.closest('a') : null
        },

        sync() {
            const name = this.block()?.tagName?.toLowerCase() ?? null
            const dir = this.block() ? getComputedStyle(this.block()).direction : null

            this.marks = {
                bold: state('bold'),
                italic: state('italic'),
                underline: state('underline'),
                strike: state('strikeThrough'),
                bullet: state('insertUnorderedList'),
                ordered: state('insertOrderedList'),
                h1: name === 'h1',
                h2: name === 'h2',
                h3: name === 'h3',
                paragraph: name === 'p',
                quote: !! this.block()?.closest?.('blockquote'),
                code: name === 'pre',
                link: !! this.anchor(),
                direction: dir === 'rtl',
            }
        },

        active(command) {
            return this.marks[command] === true
        },

        focusSurface() {
            const surface = this.$refs.surface

            if (! surface) return

            if (! this.inside()) surface.focus()
        },

        // formatBlock is the one command browsers disagree about; when it
        // refuses, swap the block element by hand and put the caret back.
        formatBlock(tag) {
            const current = this.block()

            if (current && current.tagName.toLowerCase() === tag && tag !== 'p') tag = 'p'

            if (exec('formatBlock', '<' + tag + '>')) return

            const block = this.block()

            if (! block || block.tagName.toLowerCase() === tag) return

            const replacement = document.createElement(tag)

            while (block.firstChild) replacement.appendChild(block.firstChild)

            block.replaceWith(replacement)

            const range = document.createRange()

            range.selectNodeContents(replacement)
            range.collapse(false)

            const selection = document.getSelection()

            selection.removeAllRanges()
            selection.addRange(range)
        },

        insert(html) {
            if (exec('insertHTML', html)) return

            const selection = document.getSelection()

            if (! selection || ! selection.rangeCount) return

            const range = selection.getRangeAt(0)

            range.deleteContents()

            const fragment = range.createContextualFragment(html)

            range.insertNode(fragment)
            selection.collapseToEnd()
        },

        link() {
            const current = this.anchor()?.getAttribute('href') ?? ''
            const answer = window.prompt(this.linkPrompt, current)

            if (answer === null) return

            let url = answer.trim()

            if (! url) return exec('unlink')

            // A bare "shop.example.com" is a host, not a relative path.
            if (! /^[a-z][a-z0-9+.-]*:/i.test(url) && ! /^[#/]/.test(url) && /\./.test(url.split('/')[0])) url = 'https://' + url

            if (! SAFE_HREF.test(url)) return

            const selection = document.getSelection()

            if (selection && selection.isCollapsed && ! this.anchor()) {
                this.insert('<a href="' + escape(url) + '">' + escape(url) + '</a>')

                return
            }

            exec('createLink', url)
        },

        direction() {
            let block = this.block()

            if (! block) {
                this.formatBlock('p')
                block = this.block()
            }

            if (! block) return

            block.setAttribute('dir', getComputedStyle(block).direction === 'rtl' ? 'ltr' : 'rtl')
        },

        clear() {
            exec('removeFormat')
            exec('unlink')

            const name = this.block()?.tagName?.toLowerCase()

            if (['h1', 'h2', 'h3', 'blockquote', 'pre'].includes(name)) this.formatBlock('p')
        },

        run(command) {
            if (this.disabled) return

            this.focusSurface()

            switch (command) {
                case 'bold': exec('bold'); break
                case 'italic': exec('italic'); break
                case 'underline': exec('underline'); break
                case 'strike': exec('strikeThrough'); break
                case 'bullet': exec('insertUnorderedList'); break
                case 'ordered': exec('insertOrderedList'); break
                case 'h1': case 'h2': case 'h3': this.formatBlock(command); break
                case 'paragraph': this.formatBlock('p'); break
                case 'quote': this.formatBlock('blockquote'); break
                case 'code': this.formatBlock('pre'); break
                case 'link': this.link(); break
                case 'unlink': exec('unlink'); break
                case 'direction': this.direction(); break
                case 'clear': this.clear(); break
                default: return
            }

            this.sync()
            this.commit()
        },

        // Ctrl/Cmd shortcuts for the marks a writer reaches for mid-sentence.
        // Tab is deliberately NOT captured: it has to move focus out.
        keydown(event) {
            if (! (event.metaKey || event.ctrlKey) || event.altKey) return

            const key = (event.key ?? '').toLowerCase()

            let command = null

            if (event.shiftKey) {
                if (key === 'x') command = 'strike'
                else if (event.code === 'Digit7') command = 'ordered'
                else if (event.code === 'Digit8') command = 'bullet'
            } else if (key === 'b') command = 'bold'
            else if (key === 'i') command = 'italic'
            else if (key === 'u') command = 'underline'
            else if (key === 'k') command = 'link'
            else if (key === '\\') command = 'clear'

            if (! command) return

            event.preventDefault()

            this.run(command)
        },

        paste(event) {
            event.preventDefault()

            this.take(event.clipboardData)
        },

        drop(event) {
            event.preventDefault()

            this.take(event.dataTransfer)
        },

        take(data) {
            if (! data) return

            const html = data.getData('text/html')
            const text = data.getData('text/plain')

            this.insert(html ? sanitize(html) : escape(text).replace(/\r?\n/g, '<br>'))

            this.sync()
            this.commit()
        },

        // Reads the surface, never writes it: rewriting innerHTML on every
        // keystroke would throw the caret to the top of the document.
        commit() {
            const input = this.$refs.input

            if (! input || ! this.$refs.surface) return

            const html = sanitize(this.$refs.surface.innerHTML)
            const next = blank(html) ? '' : html

            this.empty = next === ''

            if (input.value === next && this.value === next) return

            this.value = next
            input.value = next
            input.dispatchEvent(new Event('input', { bubbles: true }))
        },

        tools() {
            return [...this.$root.querySelectorAll('[data-mds-editor-tool]')].filter((el) => ! el.disabled)
        },

        // Roving tabindex: the toolbar is one tab stop, arrows move inside it.
        roving(target = null) {
            const tools = this.tools()

            if (! tools.length) return

            const active = target ?? tools[0]

            tools.forEach((el) => { el.tabIndex = el === active ? 0 : -1 })
        },

        toolbarKeydown(event) {
            const tools = this.tools()
            const index = tools.indexOf(document.activeElement)

            if (index < 0) return

            // Left/Right follow what the reader sees, so they stay "previous"
            // and "next" on an RTL page.
            const rtl = getComputedStyle(event.currentTarget).direction === 'rtl'

            let next = null

            if (event.key === 'ArrowRight') next = index + (rtl ? -1 : 1)
            else if (event.key === 'ArrowLeft') next = index + (rtl ? 1 : -1)
            else if (event.key === 'Home') next = 0
            else if (event.key === 'End') next = tools.length - 1
            else return

            event.preventDefault()

            next = (next + tools.length) % tools.length

            this.roving(tools[next])
            tools[next].focus()
        },
    }))
}

if (window.Alpine) {
    window.mds.registerEditor(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerEditor(window.Alpine))
}
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label @class(['sr-only' => $labelSrOnly])>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class([
            'overflow-hidden rounded-lg border bg-white shadow-xs dark:bg-white/10',
            'has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-accent/40',
            'border-red-500 dark:border-red-500' => $invalid,
            'border-zinc-200 border-b-zinc-300/80 dark:border-white/10' => ! $invalid,
            'opacity-70' => $disabled,
        ]) }}
        x-id="['mds-editor-surface']"
        x-data="mdsEditor({
            fa: @js((bool) $fa),
            disabled: @js((bool) $disabled),
            linkPrompt: @js($fa ? 'نشانی پیوند' : 'Link URL'),
        })"
        @if ($disabled) inert aria-disabled="true" data-disabled @endif
        data-mds-editor
    >
        <input
            type="hidden"
            x-ref="input"
            value="{{ $value }}"
            @if ($name) name="{{ $name }}" @endif
            data-mds-editor-input
            {{ $attributes->whereStartsWith('wire:model') }}
        >

        @if ($slot->isNotEmpty())
            {{ $slot }}
        @else
            @if ($toolbar !== false)
                <mds:editor.toolbar :tools="$toolbar" :label="$toolbarLabel" :fa="$fa" />
            @endif

            <mds:editor.content
                :placeholder="$placeholder"
                :rows="$rows"
                :dir="$dir"
                :label="$label"
                :disabled="$disabled"
                :invalid="$invalid"
                :fa="$fa"
            />
        @endif
    </div>

    @if ($description)
        <flux:description @class(['sr-only' => $descriptionSrOnly])>{{ $description }}</flux:description>
    @endif

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</flux:field>
