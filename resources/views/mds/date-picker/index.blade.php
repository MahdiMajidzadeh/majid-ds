@props([
    'value' => null,
    'mode' => 'single',
    'min' => null,
    'max' => null,
    'unavailable' => [],
    'months' => 1,
    'calendar' => 'jalali',
    'startDay' => null,
    'weekNumbers' => false,
    'fixedWeeks' => false,
    'selectableHeader' => false,
    'withToday' => false,
    'format' => 'numeric',
    'name' => null,
    'label' => null,
    'description' => null,
    'placeholder' => null,
    'clearable' => false,
    'disabled' => false,
    'readonly' => false,
    'size' => null,
    'icon' => 'calendar',
    'position' => 'bottom',
    'align' => 'start',
    'error' => null,
    'invalid' => false,
    'fa' => null,
])

@php
use Illuminate\View\ComponentAttributeBag;
use MajidDs\Support\Jalali;
use MajidDs\Support\Persian;

// fa picks the digits and the language of every built-in string. The
// calendar SYSTEM is the other axis, exactly as mds:calendar has it: a
// Jalali field in English and a Gregorian field in Persian are both valid.
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag, under
// the same key the field posts.
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

$mode = $mode === 'range' ? 'range' : 'single';
$format = $format === 'long' ? 'long' : 'numeric';
$jalali = $calendar !== 'gregorian';
$position = in_array($position, ['top', 'bottom', 'start', 'end'], true) ? $position : 'bottom';
$align = in_array($align, ['start', 'center', 'end'], true) ? $align : 'start';

$gregorianNames = $fa
    ? [1 => 'ژانویه', 'فوریه', 'مارس', 'آوریل', 'مه', 'ژوئن', 'ژوئیه', 'اوت', 'سپتامبر', 'اکتبر', 'نوامبر', 'دسامبر']
    : [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

$monthNames = $jalali ? ($fa ? Jalali::MONTHS : Jalali::MONTHS_LATIN) : $gregorianNames;

$daysInJalali = fn (int $y, int $m) => $m <= 6 ? 31 : ($m <= 11 ? 30 : (Jalali::isLeapYear($y) ? 30 : 29));

/*
| Reading a written date. The JS twin below follows the same three rules, so
| the server and the browser can never disagree about what a string means:
|
|   1. `Y-m-d` with a year of 1700 or more is the MACHINE form — Gregorian,
|      always, whatever calendar the field draws. That is the shape Livewire
|      and the database hand back.
|   2. every other separator (and a bare `YYYYMMDD`) is read in the field's
|      OWN calendar — «۱۴۰۵/۰۵/۲۹» is Mordad, not the year 1405 AD. So is a
|      dashed date whose year is below 1700, which no Gregorian app means.
|   3. anything else, or a day the month does not have, is NOT a date. It
|      returns null, and null never becomes a value — the field reverts.
*/
$parts = function (string $raw) use ($jalali): ?array {
    $s = trim(Persian::latinDigits($raw));

    if ($s === '') {
        return null;
    }

    if (preg_match('/^(\d{4})-(\d{1,2})-(\d{1,2})(?:[T\s].*)?$/', $s, $m)) {
        return [! $jalali || (int) $m[1] >= 1700 ? 'gregorian' : 'own', (int) $m[1], (int) $m[2], (int) $m[3]];
    }

    if (preg_match('/^(\d{4})[^\d]{1,3}(\d{1,2})[^\d]{1,3}(\d{1,2})$/u', $s, $m)) {
        return ['own', (int) $m[1], (int) $m[2], (int) $m[3]];
    }

    if (preg_match('/^(\d{4})(\d{2})(\d{2})$/', $s, $m)) {
        return ['own', (int) $m[1], (int) $m[2], (int) $m[3]];
    }

    return null;
};

$build = function (?array $parsed) use ($jalali, $daysInJalali): ?string {
    if ($parsed === null) {
        return null;
    }

    [$system, $y, $m, $d] = $parsed;

    if ($y < 1 || $m < 1 || $m > 12 || $d < 1) {
        return null;
    }

    if ($system === 'own' && $jalali) {
        if ($d > $daysInJalali($y, $m)) {
            return null;
        }

        [$gy, $gm, $gd] = Jalali::toGregorian($y, $m, $d);

        return sprintf('%04d-%02d-%02d', $gy, $gm, $gd);
    }

    return checkdate($m, $d, $y) ? sprintf('%04d-%02d-%02d', $y, $m, $d) : null;
};

// Anything the caller may hand over: a Carbon, a timestamp, an ISO string,
// or a date written the way the field itself writes them.
$iso = function ($raw) use ($parts, $build): ?string {
    if ($raw === null || $raw === '' || $raw === []) {
        return null;
    }

    if ($raw instanceof DateTimeInterface) {
        return $raw->format('Y-m-d');
    }

    if (is_int($raw)) {
        return Persian::toDateTime($raw)->format('Y-m-d');
    }

    return is_string($raw) ? $build($parts($raw)) : null;
};

$start = null;
$end = null;

if ($mode === 'range') {
    if (is_array($value)) {
        $start = $iso($value['start'] ?? $value[0] ?? null);
        $end = $iso($value['end'] ?? $value[1] ?? null);
    } else {
        $start = $iso($value);
    }

    if ($start && $end && $start > $end) {
        [$start, $end] = [$end, $start];
    }
} else {
    $start = $iso(is_array($value) ? ($value[0] ?? null) : $value);
}

$min = $iso($min);
$max = $iso($max);
$unavailable = array_values(array_filter(array_map($iso, (array) $unavailable)));

// Writing a date back out. `numeric` is the typeable form (۱۴۰۵/۰۵/۲۹);
// `long` spells the month out and is read-only in practice — a caller can
// still type numerals into it, and it re-renders long on commit.
$display = function (?string $date) use ($jalali, $fa, $format, $monthNames): string {
    if ($date === null) {
        return '';
    }

    [$gy, $gm, $gd] = array_map('intval', explode('-', $date));
    [$y, $m, $d] = $jalali ? Jalali::fromGregorian($gy, $gm, $gd) : [$gy, $gm, $gd];

    $text = $format === 'long'
        ? $d.' '.$monthNames[$m].' '.$y
        : sprintf('%04d/%02d/%02d', $y, $m, $d);

    return $fa ? Persian::digits($text) : $text;
};

$separator = $fa ? ' تا ' : ' – ';

$text = $display($start);

if ($mode === 'range' && $text !== '' && $end !== null) {
    $text .= $separator.$display($end);
}

$hint = $fa ? 'سال/ماه/روز' : 'YYYY/MM/DD';
$placeholder ??= $hint;
$triggerLabel = $fa ? 'انتخاب تاریخ' : 'Choose date';
$clearLabel = $fa ? 'پاک کردن' : 'Clear';
$calendarLabel = $label ?: ($fa ? 'تقویم' : 'Calendar');
$selectedLabel = $fa ? 'انتخاب‌شده:' : 'Selected:';
$emptyLabel = $fa ? 'تاریخی انتخاب نشده است' : 'No date selected';

$hintText = ($fa ? 'قالب تاریخ: ' : 'Date format: ').$hint;

if ($mode === 'range') {
    $hintText .= $fa ? ' — دو تاریخ با «تا»' : ' — two dates separated by "to"';
}

$status = $text === '' ? $emptyLabel : $selectedLabel.' '.$text;

// Deterministic ids: stable across rebuilds, and they still work with
// JavaScript off, which an x-id generated one would not.
$key = substr(md5($mode.'|'.$calendar.'|'.(string) $name), 0, 8);
$hintId = 'mds-date-picker-hint-'.$key;
$errorId = 'mds-date-picker-error-'.$key;

$describedBy = trim($hintId.(filled($error) ? ' '.$errorId : ''));

// The range's two hidden inputs bind to dotted paths under the caller's
// property — wire:model.live="trip" becomes trip.start / trip.end. Exactly
// the contract mds:calendar keeps, so the two are interchangeable.
$wireModel = $attributes->whereStartsWith('wire:model');
$wireFor = fn (string $part) => new ComponentAttributeBag(
    collect($wireModel->getAttributes())->map(fn ($property) => $property.'.'.$part)->all(),
);

$inputClasses = match ($size) {
    'sm' => 'h-8 text-sm',
    'lg' => 'h-12 text-base',
    default => 'h-10 text-base sm:text-sm',
};

$config = [
    'mode' => $mode,
    'jalali' => $jalali,
    'fa' => (bool) $fa,
    'format' => $format,
    'separator' => $separator,
    'monthNames' => array_values($monthNames),
    'value' => $start ?? '',
    'end' => $end ?? '',
    'selectedLabel' => $selectedLabel,
    'emptyLabel' => $emptyLabel,
];
@endphp

{{--
    Both shared scripts are claimed HERE, before the popover: the calendar
    lives inside a teleported <template>, and an @once block first reached
    from in there would emit its script tag inside inert template content.
--}}
@include('mds::partials.digits')
@include('mds::partials.jalali')

@once('mds-date-picker')
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerDatePicker = (Alpine) => {
    if (window.mds.datePickerRegistered) return
    window.mds.datePickerRegistered = true

    Alpine.data('mdsDatePicker', (config = {}) => ({
        mode: config.mode ?? 'single',
        jalali: config.jalali ?? true,
        fa: config.fa ?? true,
        format: config.format ?? 'numeric',
        separator: config.separator ?? ' – ',
        monthNames: config.monthNames ?? [],
        selectedLabel: config.selectedLabel ?? 'Selected:',
        emptyLabel: config.emptyLabel ?? 'No date selected',

        // The machine values, ISO Gregorian with Latin digits. `text` is what
        // the reader sees and types; the two only meet in parse()/display().
        value: '',
        end: '',
        text: '',
        dirty: false,

        // Registered from the calendar's own x-init: it is teleported to
        // body with the panel, so the root can never reach it.
        calendarEl: null,

        // The root, captured ONCE. $root is a magic bound to the element the
        // expression runs in, and half of these methods are called from the
        // grid's own scope inside the teleported panel — read there, $root is
        // the calendar, and every lookup below would search the wrong subtree.
        root: null,
        observers: [],

        init() {
            this.root = this.$root
            this.value = config.value || ''
            this.end = config.end || ''
            this.text = this.display()

            // Morph re-sync: Livewire patches the hidden control's value
            // ATTRIBUTE when the server changes the bound property, and
            // Alpine state does not follow by itself. write() below sets the
            // property instead, so this never sees its own writes.
            for (const part of ['value', 'start', 'end']) {
                const el = this.el(part)

                if (! el) continue

                const observer = new MutationObserver(() => this.resync())

                observer.observe(el, { attributes: true, attributeFilter: ['value'] })
                this.observers.push(observer)
            }
        },

        destroy() {
            this.observers.forEach(observer => observer.disconnect())
            this.observers = []
        },

        el(part) {
            return this.root ? this.root.querySelector('[data-mds-date-picker-' + part + ']') : null
        },

        resync() {
            const read = (part) => this.el(part)?.getAttribute('value') || ''

            const value = this.mode === 'range' ? read('start') : read('value')
            const end = this.mode === 'range' ? read('end') : ''

            if (value === this.value && end === this.end) return

            this.value = value
            this.end = end
            this.text = this.display()
            this.dirty = false
            this.push()
        },

        // --- Formatting ----------------------------------------------------

        pad(n, width = 2) {
            return String(n).padStart(width, '0')
        },

        formatOne(iso) {
            if (! iso) return ''

            const [gy, gm, gd] = iso.split('-').map(Number)
            const [y, m, d] = this.jalali ? window.mds.jalali.toJalali(gy, gm, gd) : [gy, gm, gd]

            const text = this.format === 'long'
                ? d + ' ' + (this.monthNames[m - 1] ?? '') + ' ' + y
                : this.pad(y, 4) + '/' + this.pad(m) + '/' + this.pad(d)

            return window.mds.digits(text, this.fa)
        },

        display() {
            const from = this.formatOne(this.value)

            if (this.mode !== 'range' || from === '') return from

            const to = this.formatOne(this.end)

            return to === '' ? from : from + this.separator + to
        },

        get status() {
            const text = this.display()

            return text === '' ? this.emptyLabel : this.selectedLabel + ' ' + text
        },

        // --- Parsing --------------------------------------------------------

        // The JS twin of the PHP rules above: a dashed date with a year of
        // 1700 or more is Gregorian, everything else is read in the field's
        // own calendar, and an impossible day is not a date.
        toIso(system, y, m, d) {
            if (y < 1 || m < 1 || m > 12 || d < 1) return null

            if (system === 'own' && this.jalali) {
                if (d > window.mds.jalali.daysInMonth(y, m)) return null

                const [gy, gm, gd] = window.mds.jalali.toGregorian(y, m, d)

                return this.pad(gy, 4) + '-' + this.pad(gm) + '-' + this.pad(gd)
            }

            const leap = (y % 4 === 0 && y % 100 !== 0) || y % 400 === 0
            const lengths = [31, leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31]

            if (d > lengths[m - 1]) return null

            return this.pad(y, 4) + '-' + this.pad(m) + '-' + this.pad(d)
        },

        // '' for an empty field, null for something that is not a date.
        parseOne(raw) {
            const s = window.mds.latinDigits(String(raw ?? '')).trim()

            if (s === '') return ''

            let m

            if ((m = /^(\d{4})-(\d{1,2})-(\d{1,2})(?:[T ].*)?$/.exec(s))) {
                return this.toIso(! this.jalali || +m[1] >= 1700 ? 'gregorian' : 'own', +m[1], +m[2], +m[3])
            }

            if ((m = /^(\d{4})[^\d]{1,3}(\d{1,2})[^\d]{1,3}(\d{1,2})$/.exec(s))) {
                return this.toIso('own', +m[1], +m[2], +m[3])
            }

            if ((m = /^(\d{4})(\d{2})(\d{2})$/.exec(s))) {
                return this.toIso('own', +m[1], +m[2], +m[3])
            }

            return null
        },

        // { value, end } for something readable, null for anything else. A
        // hyphen only separates a RANGE when it stands between spaces, so it
        // stays free to separate the parts of one ISO date.
        parse(raw) {
            const s = window.mds.latinDigits(String(raw ?? '')).trim()

            if (s === '') return { value: '', end: '' }

            if (this.mode !== 'range') {
                const one = this.parseOne(s)

                return one === null ? null : { value: one, end: '' }
            }

            const pieces = s.split(/\s*(?:–|—|تا|to)\s*|\s*[,،]\s*|\s+-\s+/i).filter(piece => piece !== '')

            if (pieces.length === 1) {
                const one = this.parseOne(pieces[0])

                return one === null || one === '' ? null : { value: one, end: '' }
            }

            if (pieces.length !== 2) return null

            let from = this.parseOne(pieces[0])
            let to = this.parseOne(pieces[1])

            if (! from || ! to) return null

            if (from > to) [from, to] = [to, from]

            return { value: from, end: to }
        },

        // --- Writing ---------------------------------------------------------

        // The hidden control's PROPERTY, plus the events Livewire listens for.
        // Never setAttribute: that is the server's channel, watched above.
        write(part, value) {
            const el = this.el(part)

            if (! el || el.value === value) return

            el.value = value
            el.dispatchEvent(new Event('input', { bubbles: true }))
            el.dispatchEvent(new Event('change', { bubbles: true }))
        },

        set(value, end = '') {
            this.value = value
            this.end = this.mode === 'range' ? end : ''
            this.text = this.display()
            this.dirty = false

            if (this.mode === 'range') {
                this.write('start', this.value)
                this.write('end', this.end)
            } else {
                this.write('value', this.value)
            }

            this.push()
        },

        // State -> grid. The calendar re-reads its hidden control from a
        // MutationObserver of its own, so setting the ATTRIBUTE is the
        // supported way in; reveal() then brings that month on screen.
        push() {
            const cal = this.calendarEl

            if (! cal) return

            const put = (selector, value) => cal.querySelector(selector)?.setAttribute('value', value)

            if (this.mode === 'range') {
                put('[data-mds-calendar-start]', this.value)
                put('[data-mds-calendar-end]', this.end)
            } else {
                put('input[type="hidden"]', this.value)
            }

            const focus = this.value || this.end
            const data = focus && window.Alpine && window.Alpine.$data ? window.Alpine.$data(cal) : null

            if (data && typeof data.reveal === 'function') data.reveal(focus)
        },

        // --- The two directions ------------------------------------------------

        typed() {
            this.dirty = true
        },

        // Text that is not a date reverts to the last value instead of
        // becoming a wrong one.
        commit() {
            if (! this.dirty) return

            const parsed = this.parse(this.text)

            if (parsed === null) return this.revert()

            this.set(parsed.value, parsed.end)
        },

        revert() {
            this.text = this.display()
            this.dirty = false
        },

        clear() {
            this.set('', '')
            this.el('input')?.focus()
        },

        // Grid -> state. Only the hidden controls speak; the selectable
        // header's month and year selects are <select>s and are ignored.
        // Returns true when the picker has what it asked for and may close.
        fromCalendar(event) {
            const target = event.target
            const cal = target.closest ? target.closest('[data-mds-calendar]') : null

            if (! cal || ! target.matches('input[type="hidden"]')) return false

            this.calendarEl = cal

            if (this.mode !== 'range') {
                this.set(target.value || '')

                return this.value !== ''
            }

            // A range commits both ends in order, so only the second event
            // sees a settled pair — reading on the first would close the
            // panel the moment a new range's opening day was picked.
            if (! target.matches('[data-mds-calendar-end]')) return false

            const read = (selector) => cal.querySelector(selector)?.value || ''

            this.set(read('[data-mds-calendar-start]'), read('[data-mds-calendar-end]'))

            return this.value !== '' && this.end !== ''
        },

        // The day the grid would tab to, so opening with the keyboard lands
        // on the selection rather than on the previous-month arrow.
        focusDay(panel) {
            if (! panel) return

            this.$nextTick(() => {
                const day = panel.querySelector('[data-mds-calendar-live] [data-mds-calendar-day][tabindex="0"]')
                    || panel.querySelector('[data-mds-calendar-day][tabindex="0"]')

                day?.focus()
            })
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerDatePicker(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerDatePicker(window.Alpine))
}
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class(['w-full', 'opacity-50' => $disabled]) }}
        x-data="mdsDatePicker(@js($config))"
        @if ($disabled) inert aria-disabled="true" data-disabled @endif
        @if ($invalid) data-invalid @endif
        data-mds-date-picker
        data-mds-date-picker-mode="{{ $mode }}"
        data-mds-date-picker-system="{{ $jalali ? 'jalali' : 'gregorian' }}"
    >
        {{-- The machine values: ISO Gregorian, Latin digits, whatever the field shows. --}}
        @if ($mode === 'range')
            <input
                type="hidden"
                value="{{ $start }}"
                @if ($name) name="{{ $name }}[start]" @endif
                {{ $wireFor('start') }}
                data-mds-date-picker-start
            >
            <input
                type="hidden"
                value="{{ $end }}"
                @if ($name) name="{{ $name }}[end]" @endif
                {{ $wireFor('end') }}
                data-mds-date-picker-end
            >
        @else
            <input
                type="hidden"
                value="{{ $start }}"
                @if ($name) name="{{ $name }}" @endif
                {{ $wireModel }}
                data-mds-date-picker-value
            >
        @endif

        <span class="sr-only" id="{{ $hintId }}" data-mds-date-picker-hint>{{ $hintText }}</span>

        {{-- The grid announces itself; the field announces what came back. --}}
        <span class="sr-only" role="status" aria-live="polite" aria-atomic="true" x-text="status" data-mds-date-picker-status>{{ $status }}</span>

        <mds:popover :position="$position" :align="$align" class="w-full">
            <div @class([
                'flex items-center gap-2 rounded-lg border bg-white ps-3 pe-2 shadow-xs dark:bg-white/10',
                'has-[:focus-visible]:ring-2 has-[:focus-visible]:ring-accent/40',
                'border-red-500 dark:border-red-400' => $invalid,
                'border-zinc-200 border-b-zinc-300/80 dark:border-white/10' => ! $invalid,
            ]) data-mds-date-picker-control>
                @if ($icon)
                    <mds:icon :icon="$icon" variant="micro" class="size-4 shrink-0 text-zinc-400 dark:text-zinc-500" />
                @endif

                {{-- Display only: «۱۴۰۵/۰۵/۲۹» here, 2026-08-20 in the hidden input above. --}}
                <input
                    type="text"
                    @if ($format === 'numeric') dir="ltr" data-ltr inputmode="numeric" @endif
                    class="{{ $inputClasses }} w-full min-w-0 flex-1 bg-transparent text-zinc-800 outline-none placeholder:text-zinc-400 disabled:cursor-not-allowed dark:text-white dark:placeholder:text-zinc-500"
                    value="{{ $text }}"
                    placeholder="{{ $placeholder }}"
                    autocomplete="off"
                    spellcheck="false"
                    @if ($disabled) disabled @endif
                    @if ($readonly) readonly @endif
                    @if ($invalid) aria-invalid="true" @endif
                    aria-describedby="{{ $describedBy }}"
                    x-model="text"
                    x-on:input="typed()"
                    x-on:blur="commit()"
                    x-on:keydown.enter.prevent.stop="commit()"
                    x-on:keydown.escape="revert()"
                    x-on:keydown.arrow-down.prevent="show({ focus: false }); focusDay(contentEl)"
                    data-flux-control
                    data-mds-date-picker-input
                >

                @if ($clearable)
                    <button
                        type="button"
                        class="shrink-0 rounded p-1 text-zinc-400 hover:text-zinc-600 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent dark:hover:text-zinc-200"
                        x-show="value !== ''"
                        x-cloak
                        x-on:mousedown.prevent
                        x-on:click="clear()"
                        aria-label="{{ $clearLabel }}"
                        data-mds-date-picker-clear
                    >
                        <mds:icon icon="x-mark" variant="micro" class="size-4" />
                    </button>
                @endif

                {{-- The one control the popover owns: it carries aria-haspopup,
                     aria-expanded and aria-controls, written on at runtime. --}}
                <mds:popover.trigger class="shrink-0">
                    <button
                        type="button"
                        class="rounded p-1 text-zinc-400 transition-colors motion-reduce:transition-none hover:text-zinc-800 focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent dark:hover:text-white"
                        aria-label="{{ $triggerLabel }}"
                        data-mds-date-picker-trigger
                    >
                        <mds:icon icon="chevron-down" variant="micro" class="size-4" />
                    </button>
                </mds:popover.trigger>
            </div>

            {{-- The panel is a transparent shell: the calendar brings its own
                 surface, so the two borders are not drawn on top of each other. --}}
            <mds:popover.content
                class="border-0! bg-transparent! p-0! shadow-none!"
                aria-label="{{ $calendarLabel }}"
                data-mds-date-picker-panel
            >
                <mds:calendar
                    :mode="$mode"
                    :value="$mode === 'range' ? ['start' => $start, 'end' => $end] : $start"
                    :min="$min"
                    :max="$max"
                    :unavailable="$unavailable"
                    :months="$months"
                    :calendar="$calendar"
                    :start-day="$startDay"
                    :week-numbers="$weekNumbers"
                    :fixed-weeks="$fixedWeeks"
                    :selectable-header="$selectableHeader"
                    :with-today="$withToday"
                    :size="$size"
                    :fa="$fa"
                    :label="$calendarLabel"
                    x-init="calendarEl = $el"
                    x-on:input="if (fromCalendar($event)) close()"
                    data-mds-date-picker-calendar
                />
            </mds:popover.content>
        </mds:popover>
    </div>

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session
             error bag — the caller may have passed the message in by hand. --}}
        <div id="{{ $errorId }}" role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</flux:field>
