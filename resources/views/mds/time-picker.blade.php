@props([
    'value' => null,
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => '--:--',
    'step' => 30,
    'min' => null,
    'max' => null,
    'hours' => 24,
    'clearable' => false,
    'disabled' => false,
    'size' => null,
    'icon' => 'clock',
    'error' => null,
    'invalid' => false,
    'fa' => null,
])

@php
use MajidDs\Support\Persian;

// fa picks the digits and the built-in words' language (AM/PM, Clear).
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag...
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

$hours = (int) $hours === 12 ? 12 : 24;
$step = max(1, (int) $step);

// "HH:MM" (or "H:MM", "HH:MM:SS") -> minutes since midnight, or null when
// the string is not a time. Lenient on digits: a Persian-typed value that
// somehow reached the server still reads.
$toMinutes = function ($time): ?int {
    if (! is_string($time) && ! is_int($time)) {
        return null;
    }

    if (! preg_match('/^(\d{1,2}):(\d{2})(?::\d{2})?$/', trim(Persian::latinDigits($time)), $m)) {
        return null;
    }

    [$h, $i] = [(int) $m[1], (int) $m[2]];

    return $h > 23 || $i > 59 ? null : $h * 60 + $i;
};

$toTime = fn (int $minutes): string => sprintf('%02d:%02d', intdiv($minutes, 60), $minutes % 60);

$am = $fa ? 'ق.ظ' : 'AM';
$pm = $fa ? 'ب.ظ' : 'PM';

// Display form: «۱۴:۳۰» / "14:30", or «۲:۳۰ ب.ظ» / "2:30 PM" in 12-hour mode.
// The machine value stays 24-hour whichever is shown.
$display = function (int $minutes) use ($hours, $fa, $am, $pm): string {
    $h = intdiv($minutes, 60);
    $i = $minutes % 60;

    $text = $hours === 12
        ? sprintf('%d:%02d %s', $h % 12 ?: 12, $i, $h < 12 ? $am : $pm)
        : sprintf('%02d:%02d', $h, $i);

    return $fa ? Persian::digits($text) : $text;
};

$minMinutes = $toMinutes($min) ?? 0;
$maxMinutes = $toMinutes($max) ?? 1439;
$maxMinutes = max($minMinutes, $maxMinutes);

// The machine value, normalised — or empty when the prop is not a time.
$minutes = $toMinutes($value);
$value = $minutes === null ? '' : $toTime($minutes);
$text = $minutes === null ? '' : $display($minutes);

// The list: every `step` minutes from min to max, both ends inclusive.
$options = [];

for ($t = $minMinutes; $t <= $maxMinutes; $t += $step) {
    $options[] = [$toTime($t), $display($t)];
}

$inputClasses = match ($size) {
    'sm' => 'h-8 text-sm',
    default => 'h-10 text-base sm:text-sm',
};
@endphp

@include('mds::partials.digits')

@once('mds-time-picker')
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerTimePicker = (Alpine) => {
    if (window.mds.timePickerRegistered) return
    window.mds.timePickerRegistered = true

    Alpine.data('mdsTimePicker', (config = {}) => ({
        value: config.value || '',
        text: '',
        open: false,
        active: -1,
        dirty: false,
        step: config.step ?? 30,
        min: config.min ?? 0,
        max: config.max ?? 1439,
        hours: config.hours ?? 24,
        fa: config.fa ?? true,
        am: config.am ?? 'AM',
        pm: config.pm ?? 'PM',
        options: [],
        observer: null,

        init() {
            // The options are server-rendered; read them once. Their ids feed
            // aria-activedescendant and are scoped by x-id, so two pickers on
            // one page never share one.
            this.options = [...this.$root.querySelectorAll('[data-mds-time-picker-option]')].map((el, i) => {
                el.id = this.$id('mds-time-picker-option', i)

                return { el, value: el.dataset.value, minutes: this.toMinutes(el.dataset.value) }
            })

            this.text = this.format(this.value)

            // Livewire patches the hidden input's value attribute when the
            // server changes the bound property; Alpine state must follow.
            const input = this.$refs.input

            if (input) {
                this.observer = new MutationObserver(() => this.set(input.value, false))
                this.observer.observe(input, { attributes: true, attributeFilter: ['value'] })
            }
        },

        destroy() {
            this.observer?.disconnect()
            this.observer = null
        },

        pad(n) {
            return String(n).padStart(2, '0')
        },

        toMinutes(value) {
            const m = /^(\d{1,2}):(\d{2})$/.exec(value ?? '')

            return m ? +m[1] * 60 + +m[2] : null
        },

        toTime(minutes) {
            return this.pad(Math.floor(minutes / 60)) + ':' + this.pad(minutes % 60)
        },

        // Display form of a machine value: digits by fa, 12-hour with the
        // AM/PM word when configured. Empty in, empty out.
        format(value) {
            const minutes = this.toMinutes(value)

            if (minutes === null) return ''

            const h = Math.floor(minutes / 60), i = minutes % 60

            const text = this.hours === 12
                ? (h % 12 || 12) + ':' + this.pad(i) + ' ' + (h < 12 ? this.am : this.pm)
                : this.pad(h) + ':' + this.pad(i)

            return window.mds.digits(text, this.fa)
        },

        // What the user typed -> "HH:MM", '' for an empty field, or null when
        // it is not a time. Accepts "1430", "14:30", "2:30 pm", and the same typed with
        // Persian or Arabic digits, separators and AM/PM words. Bare
        // digits without an AM/PM word are read as a 24-hour time. The minute
        // is kept as typed (no rounding to the step); the result is clamped to
        // min/max.
        parse(raw) {
            let s = window.mds.latinDigits(String(raw ?? '')).toLowerCase().trim()

            if (s === '') return ''

            let meridiem = null

            if (/p\.?\s?m|ب[.٫]?\s?ظ|بعد|عصر|شب/.test(s)) meridiem = 'pm'
            else if (/a\.?\s?m|ق[.٫]?\s?ظ|قبل|صبح/.test(s)) meridiem = 'am'

            let h, i
            let m = /(\d{1,2})\s*[:：.٫،٬\-\s]\s*(\d{1,2})/.exec(s)

            if (m) {
                h = +m[1]; i = +m[2]
            } else {
                m = /\d+/.exec(s)

                if (! m || m[0].length > 4) return null

                const d = m[0]

                if (d.length <= 2) { h = +d; i = 0 }
                else { h = +d.slice(0, d.length - 2); i = +d.slice(-2) }
            }

            if (i > 59) return null

            if (meridiem) {
                if (h < 1 || h > 12) return null

                h = h % 12 + (meridiem === 'pm' ? 12 : 0)
            } else if (h > 23) {
                return null
            }

            const minutes = Math.min(Math.max(h * 60 + i, this.min), this.max)

            return this.toTime(minutes)
        },

        // Write a machine value: state, display, and — unless the change came
        // from the server — the hidden input plus the input event Livewire
        // listens for.
        set(value, emit = true) {
            value = this.toMinutes(value) === null ? '' : value

            const changed = value !== this.value

            this.value = value
            this.text = this.format(value)
            this.dirty = false
            this.active = this.indexOf(value)

            const input = this.$refs.input

            if (! emit || ! input) return

            if (input.value !== value) input.value = value

            if (changed) input.dispatchEvent(new Event('input', { bubbles: true }))
        },

        indexOf(value) {
            return this.options.findIndex(o => o.value === value)
        },

        // The option closest to a time — the selected one when there is a
        // match, otherwise the neighbour the list opens on.
        nearest(minutes) {
            if (! this.options.length) return -1

            let best = 0

            this.options.forEach((o, i) => {
                if (Math.abs(o.minutes - minutes) < Math.abs(this.options[best].minutes - minutes)) best = i
            })

            return best
        },

        currentMinutes() {
            const own = this.toMinutes(this.value)

            if (own !== null) return own

            const now = new Date()

            return now.getHours() * 60 + now.getMinutes()
        },

        show() {
            if (this.open || ! this.options.length) return

            this.open = true
            this.active = this.indexOf(this.value)

            const target = this.active === -1 ? this.nearest(this.currentMinutes()) : this.active

            this.$nextTick(() => this.options[target]?.el.scrollIntoView({ block: 'nearest' }))
        },

        hide() {
            this.open = false
        },

        // The typed text becomes the value on Enter and blur; text that is not
        // a time reverts to the last valid value.
        commitText() {
            if (! this.dirty) return

            const parsed = this.parse(this.text)

            if (parsed === null) {
                this.text = this.format(this.value)
                this.dirty = false

                return
            }

            this.set(parsed)
        },

        revert() {
            this.text = this.format(this.value)
            this.dirty = false
        },

        pick(value) {
            this.set(value)
            this.hide()
            this.$refs.display?.focus()
        },

        clear() {
            this.set('')
            this.hide()
            this.$refs.display?.focus()
        },

        typed() {
            this.dirty = true
            this.show()

            const parsed = this.parse(this.text)

            this.active = parsed ? this.indexOf(parsed) : -1
        },

        // Arrow keys inside the list preview the option in the field (the
        // combobox list-autocomplete pattern); Enter commits it.
        point(index) {
            if (! this.options.length) return

            this.active = Math.max(0, Math.min(index, this.options.length - 1))
            this.text = this.format(this.options[this.active].value)
            this.dirty = true

            this.$nextTick(() => this.options[this.active]?.el.scrollIntoView({ block: 'nearest' }))
        },

        move(delta) {
            if (this.active === -1) {
                const from = this.nearest(this.currentMinutes())

                // From nothing, Down lands on the nearest option and Up on the one before it.
                return this.point(delta > 0 ? from : from - 1)
            }

            this.point(this.active + delta)
        },

        // With the list closed, the arrows step the value itself by `step`
        // (Flux's behaviour), clamped to the bounds.
        nudge(delta) {
            const minutes = this.toMinutes(this.value)

            if (minutes === null) return this.show()

            this.set(this.toTime(Math.min(Math.max(minutes + delta * this.step, this.min), this.max)))
        },

        keydown(e) {
            if (e.isComposing) return

            switch (e.key) {
                case 'ArrowDown':
                case 'ArrowUp':
                    e.preventDefault()

                    if (e.altKey) return this.open ? this.hide() : this.show()

                    if (this.open) return this.move(e.key === 'ArrowDown' ? 1 : -1)

                    return this.nudge(e.key === 'ArrowDown' ? 1 : -1)

                case 'Home':
                case 'End':
                    if (! this.open) return

                    e.preventDefault()

                    return this.point(e.key === 'Home' ? 0 : this.options.length - 1)

                case 'Enter':
                    if (! this.open && ! this.dirty) return

                    e.preventDefault()
                    this.commitText()

                    return this.hide()

                case 'Escape':
                    if (this.open) {
                        e.preventDefault()
                        e.stopPropagation()
                    }

                    this.revert()

                    return this.hide()

                case 'Tab':
                    return this.hide()
            }
        },

        blur() {
            this.commitText()
            this.hide()
        },

        optionId(index) {
            return this.options[index]?.el.id ?? null
        },

        isActive(el) {
            return this.active !== -1 && this.options[this.active]?.el === el
        },

        isSelected(el) {
            return this.value !== '' && el.dataset.value === this.value
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerTimePicker(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerTimePicker(window.Alpine))
}
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative w-full', 'opacity-50' => $disabled]) }}
        @if ($disabled) inert aria-disabled="true" data-disabled @endif
        x-id="['mds-time-picker-listbox', 'mds-time-picker-option']"
        x-data="mdsTimePicker({
            value: @js($value),
            step: @js($step),
            min: @js($minMinutes),
            max: @js($maxMinutes),
            hours: @js($hours),
            fa: @js((bool) $fa),
            am: @js($am),
            pm: @js($pm),
        })"
        x-on:click.outside="blur()"
        data-mds-time-picker
    >
        <input
            type="hidden"
            x-ref="input"
            value="{{ $value }}"
            @if ($name) name="{{ $name }}" @endif
            {{ $attributes->whereStartsWith('wire:model') }}
        >

        <div @class([
            'flex items-center gap-2 rounded-lg border bg-white ps-3 pe-2 shadow-xs dark:bg-white/10',
            'has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-accent/40',
            'border-red-500 dark:border-red-400' => $invalid,
            'border-zinc-200 border-b-zinc-300/80 dark:border-white/10' => ! $invalid,
        ]) data-mds-time-picker-control>
            @if ($icon)
                <mds:icon :icon="$icon" variant="micro" class="size-4 shrink-0 text-zinc-400 dark:text-zinc-500" />
            @endif

            {{-- Display only: shows «۱۴:۳۰» or "2:30 PM", the hidden input above carries HH:MM. --}}
            <input
                type="text"
                dir="ltr"
                data-ltr
                class="{{ $inputClasses }} w-full flex-1 min-w-0 bg-transparent tabular-nums text-zinc-800 outline-none placeholder:text-zinc-400 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-zinc-500"
                value="{{ $text }}"
                placeholder="{{ $placeholder }}"
                autocomplete="off"
                spellcheck="false"
                @if ($hours === 24) inputmode="numeric" @endif
                @if ($disabled) disabled @endif
                @if ($invalid) aria-invalid="true" @endif
                role="combobox"
                aria-autocomplete="list"
                aria-haspopup="listbox"
                x-ref="display"
                x-model="text"
                x-bind:aria-expanded="open ? 'true' : 'false'"
                x-bind:aria-controls="$id('mds-time-picker-listbox')"
                x-bind:aria-activedescendant="open && active !== -1 ? optionId(active) : null"
                x-on:input="typed()"
                x-on:focus="show()"
                x-on:click="show()"
                x-on:keydown="keydown($event)"
                x-on:blur="blur()"
                data-flux-control
                data-mds-time-picker-input
            >

            @if ($clearable)
                <button
                    type="button"
                    class="shrink-0 rounded p-1 text-zinc-400 hover:text-zinc-600 focus-visible:outline-2 focus-visible:outline-accent dark:hover:text-zinc-200"
                    x-show="value !== ''"
                    x-cloak
                    x-on:mousedown.prevent
                    x-on:click="clear()"
                    aria-label="{{ $fa ? 'پاک کردن' : 'Clear' }}"
                    data-mds-time-picker-clear
                >
                    <mds:icon icon="x-mark" variant="micro" class="size-4" />
                </button>
            @endif
        </div>

        {{-- The list reads LTR like the field; rtl:text-end lines it up with the end-aligned input in an RTL form. --}}
        <ul
            class="absolute start-0 top-full z-50 mt-1 max-h-60 w-full overflow-y-auto rounded-lg border border-zinc-200 bg-white p-1 shadow-lg tabular-nums rtl:text-end dark:border-white/10 dark:bg-zinc-800"
            dir="ltr"
            role="listbox"
            @if ($label) aria-label="{{ $label }}" @endif
            x-ref="listbox"
            x-bind:id="$id('mds-time-picker-listbox')"
            x-show="open"
            x-cloak
            x-on:mousedown.prevent
            data-mds-time-picker-listbox
        >
            @foreach ($options as [$optionValue, $optionText])
                <li
                    class="cursor-pointer rounded-md px-2.5 py-1.5 text-sm text-zinc-700 dark:text-zinc-200"
                    role="option"
                    data-value="{{ $optionValue }}"
                    aria-selected="{{ $optionValue === $value ? 'true' : 'false' }}"
                    x-bind:aria-selected="isSelected($el) ? 'true' : 'false'"
                    x-bind:class="{ 'bg-zinc-100 dark:bg-white/10': isActive($el), 'font-semibold text-accent-content': isSelected($el) }"
                    x-on:mouseenter="active = options.findIndex(o => o.el === $el)"
                    x-on:click="pick($el.dataset.value)"
                    data-mds-time-picker-option
                >{{ $optionText }}</li>
            @endforeach
        </ul>
    </div>

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</flux:field>
