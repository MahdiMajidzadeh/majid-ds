@props([
    'name' => null,
    'value' => null,
    'placeholder' => null,
    'label' => null,
    'labelSrOnly' => false,
    'description' => null,
    'descriptionSrOnly' => false,
    'rows' => 2,
    'maxRows' => null,
    'maxlength' => null,
    'counter' => false,
    'inline' => false,
    'variant' => null,
    'submit' => 'cmd+enter',
    'autofocus' => false,
    'dir' => null,
    'disabled' => false,
    'invalid' => false,
    'error' => null,
    'fa' => null,
    'input' => null,
    'header' => null,
    'footer' => null,
    'actionsLeading' => null,
    'actionsTrailing' => null,
])

@php
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
$maxRows = $maxRows === null ? null : max($rows, (int) $maxRows);
$maxlength = $maxlength === null ? null : max(1, (int) $maxlength);

// The named slots are the API; a default slot is treated as the initial text...
$text = $value ?? (($trimmed = trim((string) $slot)) === '' ? null : $trimmed);

$count = $text === null ? 0 : mb_strlen($text);
$counterText = $maxlength === null ? (string) $count : $count.' / '.$maxlength;
$counterText = $fa ? \MajidDs\Support\Persian::digits($counterText) : $counterText;

// Deterministic id: stable across rebuilds — a random one would also shift
// the global random sequence Flux draws its own ids from. A same-props
// collision points at an identical counter, which is harmless.
$counterId = 'mds-composer-counter-'.substr(md5(json_encode([$name, $placeholder, $maxlength])), 0, 8);

// Grid tracks: [leading actions][input][input][trailing actions]. A row of
// their own in the stacked layout; one shared row when `inline`...
$row = $header ? 'row-start-2' : 'row-start-1';
@endphp

@include('mds::partials.digits')

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsComposer', (config = {}) => ({
        rows: config.rows ?? 2,
        maxRows: config.maxRows ?? null,
        maxlength: config.maxlength ?? null,
        submit: config.submit ?? 'cmd+enter',
        fa: config.fa ?? true,
        count: 0,

        init() {
            if (this.$refs.input) this.sync()
        },

        // Display counter — «۱۲ / ۵۰۰» when there is a limit, «۱۲» otherwise...
        get counter() {
            const digits = (n) => window.mds.digits(n, this.fa)

            return this.maxlength === null
                ? digits(this.count)
                : digits(this.count) + ' / ' + digits(this.maxlength)
        },

        sync() {
            this.count = [...(this.$refs.input.value ?? '')].length
            this.resize()
        },

        // Grow with the content, from `rows` lines up to `max-rows`...
        resize() {
            const input = this.$refs.input

            if (! input) return

            const styles = getComputedStyle(input)
            const line = parseFloat(styles.lineHeight) || parseFloat(styles.fontSize) * 1.5
            const frame = parseFloat(styles.paddingBlockStart) + parseFloat(styles.paddingBlockEnd)
                + parseFloat(styles.borderBlockStartWidth) + parseFloat(styles.borderBlockEndWidth)

            const min = line * this.rows + frame
            const max = this.maxRows === null ? Infinity : line * this.maxRows + frame

            input.style.height = 'auto'

            const height = Math.min(Math.max(input.scrollHeight, min), max)

            input.style.height = height + 'px'
            input.style.overflowY = input.scrollHeight > height ? 'auto' : 'hidden'
        },

        keydown(event) {
            // isComposing: an IME candidate window owns Enter while it is open...
            if (event.key !== 'Enter' || event.isComposing) return

            // Ctrl/Cmd + Enter sends in either mode, from any input...
            if (event.metaKey || event.ctrlKey) {
                event.preventDefault()

                return this.send()
            }

            // A bare Enter only sends in `enter` mode. Shift keeps the newline,
            // and a rich-text input in the `input` slot keeps Enter for its own
            // paragraphs...
            if (this.submit !== 'enter' || event.shiftKey) return
            if (event.target !== this.$refs.input) return

            event.preventDefault()

            this.send()
        },

        send() {
            const form = this.$root.closest('form')

            // requestSubmit() fires a real submit event, so wire:submit and
            // native validation both run — form.submit() skips both...
            if (form) form.requestSubmit()
        },
    }))
})

/*
| Livewire writes the textarea's value straight into the DOM when the server
| sends a new one (a sent message clears the property), and that is not an
| input event. One global morph hook re-syncs the height and the counter.
*/
document.addEventListener('livewire:init', () => {
    Livewire.hook('morph.updated', ({ el }) => {
        if (el.matches?.('[data-mds-composer-input]')) {
            el.dispatchEvent(new CustomEvent('mds-composer-morphed', { bubbles: true }))
        }
    })
})
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label @class(['sr-only' => $labelSrOnly])>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class([
            'grid w-full grid-cols-[auto_1fr_1fr_auto] border bg-white p-2',
            'rounded-lg' => $variant === 'input',
            'rounded-2xl [&_[data-flux-button]]:rounded-lg' => $variant !== 'input',
            'has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-accent/40',
            'border-red-500 dark:border-red-500' => $invalid,
            'border-zinc-200 border-b-zinc-300/80 dark:border-white/10' => ! $invalid,
            'shadow-xs dark:bg-white/10' => ! $disabled,
            'dark:bg-white/[7%]' => $disabled,
        ]) }}
        x-data="mdsComposer({
            rows: @js($rows),
            maxRows: @js($maxRows),
            maxlength: @js($maxlength),
            submit: @js($submit === 'enter' ? 'enter' : 'cmd+enter'),
            fa: @js((bool) $fa),
        })"
        x-on:keydown="keydown($event)"
        x-on:mds-composer-morphed="sync()"
        role="group"
        @if ($disabled) inert aria-disabled="true" data-disabled @endif
        data-mds-composer
    >
        @if ($header)
            <div @class(['col-span-4 row-start-1 mb-2 flex items-center gap-1', 'opacity-50' => $disabled])>{{ $header }}</div>
        @endif

        <div @class(['min-w-0', $inline ? 'col-span-2 col-start-2 '.$row : 'col-span-4'])>
            @if ($input)
                {{ $input }}
            @else
                <textarea
                    class="block w-full resize-none bg-transparent px-2 py-1.5 text-base text-zinc-700 outline-none! placeholder-zinc-400 disabled:text-zinc-500 disabled:placeholder-zinc-400/70 sm:text-sm dark:text-zinc-300 dark:placeholder-zinc-400 dark:disabled:text-zinc-400 dark:disabled:placeholder-zinc-500"
                    rows="{{ $rows }}"
                    x-ref="input"
                    x-on:input="sync()"
                    @if ($name) name="{{ $name }}" @endif
                    @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                    @if ($maxlength) maxlength="{{ $maxlength }}" @endif
                    @if ($dir) dir="{{ $dir }}" @endif
                    @if ($autofocus) autofocus @endif
                    @if ($disabled) disabled @endif
                    @if ($invalid) aria-invalid="true" @endif
                    @if ($counter) aria-describedby="{{ $counterId }}" @endif
                    data-flux-control
                    data-mds-composer-input
                    {{ $attributes->whereStartsWith('wire:model') }}
                >{{ $text }}</textarea>
            @endif
        </div>

        @if ($actionsLeading)
            <div @class([
                'flex items-start gap-1',
                $inline ? 'col-start-1 '.$row : 'col-span-2',
                'opacity-50' => $disabled,
            ])>{{ $actionsLeading }}</div>
        @endif

        @if ($actionsTrailing)
            <div @class([
                'flex items-start justify-end gap-1',
                $inline ? 'col-start-4 '.$row : 'col-span-2 col-start-3',
                'opacity-50' => $disabled,
            ])>{{ $actionsTrailing }}</div>
        @endif

        @if ($footer || $counter)
            <div @class(['col-span-4 mt-1.5 flex items-center justify-between gap-2 px-2 text-xs text-zinc-500 dark:text-zinc-400', 'opacity-50' => $disabled])>
                <div class="min-w-0">{{ $footer }}</div>

                @if ($counter)
                    {{-- dir=ltr: «۱۲ / ۵۰۰» reads current-then-limit either way... --}}
                    <span class="shrink-0 tabular-nums" dir="ltr" id="{{ $counterId }}" x-text="counter">{{ $counterText }}</span>
                @endif
            </div>
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
