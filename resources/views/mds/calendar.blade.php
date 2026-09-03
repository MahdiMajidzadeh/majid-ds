@props([
    'value' => null,
    'mode' => 'single',
    'min' => null,
    'max' => null,
    'months' => 1,
    'size' => null,
    'calendar' => 'jalali',
    'weekNumbers' => false,
    'selectableHeader' => false,
    'withToday' => false,
    'fixedWeeks' => false,
    'startDay' => null,
    'unavailable' => [],
    'static' => false,
    'name' => null,
    'label' => null,
    'error' => null,
    'invalid' => false,
    'fa' => null,
])

@php
use Illuminate\View\ComponentAttributeBag;
use MajidDs\Support\Jalali;
use MajidDs\Support\Persian;

// fa picks the digits and the language of every built-in string. The
// calendar SYSTEM is a separate axis: a Gregorian grid in Persian is valid,
// and so is a Jalali grid in English — exactly as mds:jalali-date behaves.
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag...
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

$mode = in_array($mode, ['single', 'multiple', 'range'], true) ? $mode : 'single';
$jalali = $calendar !== 'gregorian';
$months = max(1, min(12, (int) $months));
// Saturday opens the Iranian week, Monday the ISO one.
$startDay = $startDay === null ? ($jalali ? 6 : 1) : (((int) $startDay % 7) + 7) % 7;

// Machine values are ISO Gregorian `Y-m-d`, whatever the grid shows.
$iso = fn ($date) => blank($date) ? null : Persian::toDateTime($date)->format('Y-m-d');

$selected = [];
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
} elseif ($mode === 'multiple') {
    $selected = array_values(array_unique(array_filter(array_map($iso, is_array($value) ? $value : [$value]))));
    sort($selected);
} else {
    $selected = array_values(array_filter([$iso(is_array($value) ? ($value[0] ?? null) : $value)]));
}

$min = $iso($min);
$max = $iso($max);
$unavailable = array_values(array_filter(array_map($iso, (array) $unavailable)));

// now() follows Date::setTestNow — the docs builder pins it, so the
// rendered pages stay byte-identical. The raw clock helpers would not.
$today = now()->format('Y-m-d');

// The calendar system, on the PHP side. The Alpine block mirrors these
// through window.mds.jalali (mds::partials.jalali) so the grid it redraws
// is the one the server drew.
$gregorianNames = $fa
    ? [1 => 'ژانویه', 'فوریه', 'مارس', 'آوریل', 'مه', 'ژوئن', 'ژوئیه', 'اوت', 'سپتامبر', 'اکتبر', 'نوامبر', 'دسامبر']
    : [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

$monthNames = $jalali ? ($fa ? Jalali::MONTHS : Jalali::MONTHS_LATIN) : $gregorianNames;
$weekdayNames = $fa ? Jalali::WEEKDAYS : Jalali::WEEKDAYS_LATIN;
$initials = $fa ? ['ی', 'د', 'س', 'چ', 'پ', 'ج', 'ش'] : ['S', 'M', 'T', 'W', 'T', 'F', 'S'];
$digits = fn ($value) => $fa ? Persian::digits($value) : (string) $value;

$split = fn (string $date) => array_map('intval', explode('-', $date));
$toCal = fn (string $date) => $jalali ? Jalali::fromGregorian(...$split($date)) : $split($date);
$firstOf = function (int $y, int $m) use ($jalali): DateTimeImmutable {
    [$gy, $gm, $gd] = $jalali ? Jalali::toGregorian($y, $m, 1) : [$y, $m, 1];

    return new DateTimeImmutable(sprintf('%04d-%02d-%02d', $gy, $gm, $gd));
};
$daysIn = fn (int $y, int $m) => $jalali
    ? ($m <= 6 ? 31 : ($m <= 11 ? 30 : (Jalali::isLeapYear($y) ? 30 : 29)))
    : (int) $firstOf($y, $m)->format('t');
$addMonths = fn (int $y, int $m, int $n) => [$y + intdiv($m - 1 + $n, 12), (($m - 1 + $n) % 12) + 1];

// Week numbers count from the week holding the 1st of the calendar's own
// year (Farvardin 1 or January 1), so the column means the same thing in
// both systems and for any start-day.
$weekNumber = function (string $date) use ($toCal, $firstOf, $startDay): int {
    [$cy] = $toCal($date);
    $yearStart = $firstOf($cy, 1);
    $offset = (((int) $yearStart->format('w')) - $startDay + 7) % 7;
    $dayOfYear = (int) $yearStart->diff(new DateTimeImmutable($date))->days;

    return intdiv($dayOfYear + $offset, 7) + 1;
};

$isDisabled = fn (string $date) => ($min !== null && $date < $min) || ($max !== null && $date > $max) || in_array($date, $unavailable, true);
$isSelected = fn (string $date) => $mode === 'range' ? ($date === $start || $date === $end) : in_array($date, $selected, true);
$inRange = fn (string $date) => $mode === 'range' && $start !== null && $end !== null && $date >= $start && $date <= $end;

// The first month shown: the selection's month, else today's.
$anchor = $mode === 'range' ? ($start ?? $end ?? $today) : ($selected[0] ?? $today);
[$viewYear, $viewMonth] = $toCal($anchor);

$views = [];

foreach (range(0, $months - 1) as $i) {
    [$y, $m] = $addMonths($viewYear, $viewMonth, $i);
    $first = $firstOf($y, $m);
    $count = $daysIn($y, $m);
    $offset = (((int) $first->format('w')) - $startDay + 7) % 7;
    $rows = $fixedWeeks ? 6 : (int) ceil(($offset + $count) / 7);
    $cursor = $first->modify("-{$offset} days");
    $weeks = [];

    for ($r = 0; $r < $rows; $r++) {
        $days = [];

        for ($c = 0; $c < 7; $c++) {
            $date = $cursor->format('Y-m-d');
            [$cy, $cm, $cd] = $toCal($date);

            $days[] = [
                'iso' => $date,
                'day' => $cd,
                'outside' => $cy !== $y || $cm !== $m,
                'label' => $weekdayNames[(int) $cursor->format('w')].' '.$digits($cd).' '.$monthNames[$cm].' '.$digits($cy),
            ];

            $cursor = $cursor->modify('+1 day');
        }

        $weeks[] = ['number' => $weekNumber($days[0]['iso']), 'days' => $days];
    }

    $views[] = ['y' => $y, 'm' => $m, 'title' => $monthNames[$m].' '.$digits($y), 'weeks' => $weeks];
}

// Roving tabindex: one cell is reachable by Tab — the selection when it is
// on screen, else today, else the 1st of the first month.
$inView = function (?string $date) use ($views): bool {
    if ($date === null) {
        return false;
    }

    foreach ($views as $view) {
        foreach ($view['weeks'] as $week) {
            foreach ($week['days'] as $day) {
                if ($day['iso'] === $date && ! $day['outside']) {
                    return true;
                }
            }
        }
    }

    return false;
};

$focus = collect($mode === 'range' ? [$start, $end] : $selected)
    ->push($today)
    ->first($inView) ?? $firstOf($viewYear, $viewMonth)->format('Y-m-d');

$canPrev = $min === null || $firstOf($viewYear, $viewMonth)->modify('-1 day')->format('Y-m-d') >= $min;
$canNext = $max === null || $firstOf(...$addMonths($viewYear, $viewMonth, $months))->format('Y-m-d') <= $max;

$weekdays = [];

foreach (range(0, 6) as $i) {
    $w = ($startDay + $i) % 7;
    $weekdays[] = ['name' => $weekdayNames[$w], 'initial' => $initials[$w]];
}

// The selectable header's year list: the bounds when given, a wide window
// otherwise, and always the month on screen.
$yearFrom = min($min !== null ? $toCal($min)[0] : $viewYear - 100, $viewYear);
$yearTo = max($max !== null ? $toCal($max)[0] : $viewYear + 20, $viewYear);

$label ??= $fa ? 'تقویم' : 'Calendar';
$prevLabel = $fa ? 'ماه قبل' : 'Previous month';
$nextLabel = $fa ? 'ماه بعد' : 'Next month';
$todayLabel = $fa ? 'امروز' : 'Today';
$monthLabel = $fa ? 'ماه' : 'Month';
$yearLabel = $fa ? 'سال' : 'Year';
$weekLabel = $fa ? 'هفته' : 'Week';

// Multi-month grids each carry the month in their name; a single grid is
// just `label`, and its title sits in the header beside it.
$gridLabel = fn (array $view) => $months > 1 ? $label.' — '.$view['title'] : $label;

// Deterministic id for aria-describedby: stable across rebuilds.
$errorId = 'mds-calendar-error-'.substr(md5((string) $name), 0, 8);

// The range's two hidden inputs bind to dotted paths under the caller's
// property — wire:model.live="trip" becomes trip.start / trip.end.
$wireModel = $attributes->whereStartsWith('wire:model');
$wireFor = fn (string $key) => new ComponentAttributeBag(
    collect($wireModel->getAttributes())->map(fn ($property) => $property.'.'.$key)->all(),
);

$cellSize = match ($size) {
    'sm' => 'size-7 text-xs',
    'lg' => 'size-10 text-base',
    default => 'size-8 text-sm',
};

$navSize = match ($size) {
    'sm' => 'size-7',
    'lg' => 'size-9',
    default => 'size-8',
};

// Every cell class lives here, in the one file Tailwind scans, and feeds both
// the server @class and the Alpine x-bind:class below. The branches are
// exclusive on purpose: two hover: utilities on one element would leave the
// winner to stylesheet order.
$cellBase = $cellSize.' flex items-center justify-center font-medium tabular-nums transition-colors motion-reduce:transition-none focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent';
$cellIdle = 'rounded-lg text-zinc-800 hover:bg-zinc-100 dark:text-white dark:hover:bg-white/10';
$cellOutside = 'rounded-lg text-zinc-400 hover:bg-zinc-100 dark:text-zinc-500 dark:hover:bg-white/10';
$cellDisabled = 'rounded-lg cursor-not-allowed text-zinc-300 line-through dark:text-zinc-600';
$cellSelected = 'rounded-lg bg-accent text-accent-foreground';
$cellInRange = 'rounded-none bg-accent/10 text-zinc-800 dark:text-white';
$cellRangeStart = 'rounded-e-none';
$cellRangeEnd = 'rounded-s-none';
$cellToday = 'ring-1 ring-inset ring-accent';

$cellClasses = fn (array $day) => [
    $cellBase,
    $cellDisabled => $isDisabled($day['iso']),
    $cellSelected => ! $isDisabled($day['iso']) && $isSelected($day['iso']),
    $cellInRange => ! $isDisabled($day['iso']) && ! $isSelected($day['iso']) && $inRange($day['iso']),
    $cellOutside => ! $isDisabled($day['iso']) && ! $isSelected($day['iso']) && ! $inRange($day['iso']) && $day['outside'],
    $cellIdle => ! $isDisabled($day['iso']) && ! $isSelected($day['iso']) && ! $inRange($day['iso']) && ! $day['outside'],
    $cellRangeStart => $mode === 'range' && $day['iso'] === $start && $end !== null && $end !== $start,
    $cellRangeEnd => $mode === 'range' && $day['iso'] === $end && $start !== null && $end !== $start,
    $cellToday => $day['iso'] === $today,
];

$headClasses = $cellSize.' h-8 text-center font-normal text-zinc-500 dark:text-zinc-400';
$weekClasses = $cellSize.' text-center font-normal text-zinc-400 dark:text-zinc-500';
$navClasses = $navSize.' flex shrink-0 items-center justify-center rounded-lg text-zinc-500 transition-colors motion-reduce:transition-none hover:bg-zinc-100 hover:text-zinc-800 disabled:cursor-not-allowed disabled:opacity-40 disabled:hover:bg-transparent focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent dark:text-zinc-400 dark:hover:bg-white/10 dark:hover:text-white';
$selectClasses = 'rounded-md border border-zinc-200 bg-white py-1 ps-2 pe-7 text-sm text-zinc-800 shadow-xs focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent dark:border-white/10 dark:bg-zinc-800 dark:text-white';

$config = [
    'mode' => $mode,
    'jalali' => $jalali,
    'fa' => (bool) $fa,
    'months' => $months,
    'startDay' => $startDay,
    'fixedWeeks' => (bool) $fixedWeeks,
    'static' => (bool) $static,
    'min' => $min,
    'max' => $max,
    'unavailable' => $unavailable,
    'today' => $today,
    'year' => $viewYear,
    'month' => $viewMonth,
    'selected' => $selected,
    'start' => $start,
    'end' => $end,
    'monthNames' => array_values($monthNames),
    'weekdayNames' => array_values($weekdayNames),
];
@endphp

@include('mds::partials.digits')
@include('mds::partials.jalali')

@once('mds-calendar')
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerCalendar = (Alpine) => {
    if (window.mds.calendarRegistered) return
    window.mds.calendarRegistered = true

    Alpine.data('mdsCalendar', (config = {}) => ({
        ready: false,
        mode: config.mode ?? 'single',
        jalali: config.jalali ?? true,
        fa: config.fa ?? true,
        months: config.months ?? 1,
        startDay: config.startDay ?? 6,
        fixedWeeks: !! config.fixedWeeks,
        static: !! config.static,
        min: config.min ?? null,
        max: config.max ?? null,
        unavailable: config.unavailable ?? [],
        today: config.today,
        year: config.year,
        month: config.month,
        selected: config.selected ?? [],
        start: config.start ?? null,
        end: config.end ?? null,
        monthNames: config.monthNames ?? [],
        weekdayNames: config.weekdayNames ?? [],
        focused: null,
        hover: null,
        observers: [],
        focusTimer: null,

        init() {
            // The server grid served the first paint; from here the x-for
            // grid below takes over and the server one is hidden (x-show).
            this.ready = true

            // Morph re-sync: when the server changes the bound value,
            // Livewire patches the hidden control's attributes, and Alpine
            // state does not follow by itself.
            const watch = (el, options) => {
                if (! el) return

                const observer = new MutationObserver(() => this.resync())

                observer.observe(el, options)
                this.observers.push(observer)
            }

            watch(this.$refs.input, { attributes: true, attributeFilter: ['value'] })
            watch(this.$refs.start, { attributes: true, attributeFilter: ['value'] })
            watch(this.$refs.end, { attributes: true, attributeFilter: ['value'] })
            watch(this.$refs.select, { childList: true, subtree: true, attributes: true, attributeFilter: ['selected'] })
        },

        destroy() {
            this.observers.forEach(observer => observer.disconnect())
            this.observers = []
            clearTimeout(this.focusTimer)
        },

        resync() {
            const attr = (el) => el?.getAttribute('value') || null

            if (this.mode === 'single') {
                const value = attr(this.$refs.input)
                const next = value ? [value] : []

                if (next.join() !== this.selected.join()) this.selected = next
            } else if (this.mode === 'range') {
                const start = attr(this.$refs.start), end = attr(this.$refs.end)

                if (start !== this.start) this.start = start
                if (end !== this.end) this.end = end
            } else {
                const next = [...(this.$refs.select?.options ?? [])].filter(o => o.selected).map(o => o.value).sort()

                if (next.join() !== this.selected.join()) this.selected = next
            }
        },

        // --- Dates: ISO strings outward, day serials inward ---------------

        digits(value) {
            return window.mds.digits(value, this.fa)
        },

        parse(iso) {
            return iso.split('-').map(Number)
        },

        isoOf(gy, gm, gd) {
            return gy + '-' + String(gm).padStart(2, '0') + '-' + String(gd).padStart(2, '0')
        },

        serial(iso) {
            return window.mds.jalali.toDays(...this.parse(iso))
        },

        fromSerial(n) {
            return this.isoOf(...window.mds.jalali.fromDays(n))
        },

        // 0 = Sunday, like PHP's `w`. Day 0 (1970-01-01) was a Thursday.
        weekday(n) {
            return (((n + 4) % 7) + 7) % 7
        },

        // --- The calendar system ------------------------------------------

        toCal(iso) {
            const [gy, gm, gd] = this.parse(iso)

            return this.jalali ? window.mds.jalali.toJalali(gy, gm, gd) : [gy, gm, gd]
        },

        fromCal(y, m, d) {
            return this.isoOf(...(this.jalali ? window.mds.jalali.toGregorian(y, m, d) : [y, m, d]))
        },

        daysInMonth(y, m) {
            if (this.jalali) return window.mds.jalali.daysInMonth(y, m)

            const leap = (y % 4 === 0 && y % 100 !== 0) || y % 400 === 0

            return [31, leap ? 29 : 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31][m - 1]
        },

        addMonths(y, m, n) {
            const t = m - 1 + n

            return [y + Math.floor(t / 12), ((t % 12) + 12) % 12 + 1]
        },

        weekNumber(n) {
            const [cy] = this.toCal(this.fromSerial(n))
            const yearStart = this.serial(this.fromCal(cy, 1, 1))
            const offset = (this.weekday(yearStart) - this.startDay + 7) % 7

            return Math.floor((n - yearStart + offset) / 7) + 1
        },

        // --- State queries ------------------------------------------------

        isDisabled(iso) {
            return (this.min !== null && iso < this.min)
                || (this.max !== null && iso > this.max)
                || this.unavailable.includes(iso)
        },

        isSelected(iso) {
            return this.mode === 'range' ? (iso === this.start || iso === this.end) : this.selected.includes(iso)
        },

        // A committed range, or the one being previewed under the pointer.
        isInRange(iso) {
            if (this.mode !== 'range' || this.start === null) return false

            const other = this.end ?? (this.static ? null : this.hover)

            if (other === null) return false

            const [lo, hi] = other < this.start ? [other, this.start] : [this.start, other]

            return iso >= lo && iso <= hi
        },

        get title() {
            return this.views.map(view => view.title).join(' – ')
        },

        get canPrev() {
            return this.min === null || this.fromSerial(this.serial(this.fromCal(this.year, this.month, 1)) - 1) >= this.min
        },

        get canNext() {
            return this.max === null || this.fromCal(...this.addMonths(this.year, this.month, this.months), 1) <= this.max
        },

        // --- The grid -----------------------------------------------------

        get views() {
            const views = []

            for (let i = 0; i < this.months; i++) {
                views.push(this.buildMonth(...this.addMonths(this.year, this.month, i)))
            }

            const focus = this.resolveFocus(views)

            views.forEach(view => view.weeks.forEach(week => week.days.forEach(day => {
                day.tabindex = day.iso === focus && ! day.outside ? 0 : -1
            })))

            return views
        },

        buildMonth(y, m) {
            const first = this.serial(this.fromCal(y, m, 1))
            const count = this.daysInMonth(y, m)
            const offset = (this.weekday(first) - this.startDay + 7) % 7
            const rows = this.fixedWeeks ? 6 : Math.ceil((offset + count) / 7)
            const weeks = []

            for (let r = 0; r < rows; r++) {
                const from = first - offset + r * 7
                const days = []

                for (let c = 0; c < 7; c++) {
                    days.push(this.cell(from + c, y, m))
                }

                weeks.push({ key: days[0].iso, number: this.digits(this.weekNumber(from)), days })
            }

            return { key: y + '-' + m, title: this.monthNames[m - 1] + ' ' + this.digits(y), weeks }
        },

        cell(n, y, m) {
            const iso = this.fromSerial(n)
            const [cy, cm, cd] = this.toCal(iso)
            const disabled = this.isDisabled(iso)
            const selected = this.isSelected(iso)
            const inRange = this.isInRange(iso)
            const outside = cy !== y || cm !== m
            const start = this.mode === 'range' && iso === this.start
            const end = this.mode === 'range' && iso === this.end
            const other = start ? (this.end ?? this.hover) : (end ? this.start : null)

            return {
                iso,
                day: this.digits(cd),
                label: this.weekdayNames[this.weekday(n)] + ' ' + this.digits(cd) + ' ' + this.monthNames[cm - 1] + ' ' + this.digits(cy),
                outside,
                disabled,
                selected,
                inRange,
                today: iso === this.today,
                rangeStart: start,
                rangeEnd: end,
                // The flat edge faces the rest of the range, once there is one.
                flatEnd: ! disabled && start && other !== null && other > iso,
                flatStart: ! disabled && (end || (start && other !== null && other < iso)) && other !== iso,
                tabindex: -1,
            }
        },

        // The selection when it is on screen, else today, else the 1st of
        // the first month — the same rule the server applied.
        resolveFocus(views) {
            const shown = (iso) => iso !== null && views.some(view => view.weeks.some(week => week.days.some(day => day.iso === iso && ! day.outside)))

            const candidates = [this.focused, ...(this.mode === 'range' ? [this.start, this.end] : this.selected), this.today]

            return candidates.find(shown) ?? this.fromCal(this.year, this.month, 1)
        },

        get focusDate() {
            const views = []

            for (let i = 0; i < this.months; i++) {
                views.push(this.buildMonth(...this.addMonths(this.year, this.month, i)))
            }

            return this.resolveFocus(views)
        },

        // --- Navigation ---------------------------------------------------

        shift(n) {
            ;[this.year, this.month] = this.addMonths(this.year, this.month, n)
        },

        // Bring a date's month on screen with the least movement: as the
        // first month when it lies before the view, the last when after.
        reveal(iso) {
            const [y, m] = this.toCal(iso)
            const index = y * 12 + m - 1
            const low = this.year * 12 + this.month - 1
            const high = low + this.months - 1

            if (index < low) {
                ;[this.year, this.month] = [y, m]
            } else if (index > high) {
                ;[this.year, this.month] = this.addMonths(y, m, 1 - this.months)
            }
        },

        focusOn(iso) {
            this.focused = iso
            this.reveal(iso)

            // $root is read HERE, synchronously. A keydown is evaluated in the
            // scope of the cell it happened on, and once the redraw destroys
            // that cell the scope no longer answers for $root — read from the
            // deferred callback below it is undefined, and the whole focus
            // move used to disappear into a swallowed TypeError.
            const root = this.$root

            // Scoped to the live grid: the server-rendered one is still in the
            // DOM (x-show only hid it) and comes first in document order, and
            // focus() on a display:none button does nothing.
            const land = () => root.querySelector('[data-mds-calendar-live] [data-mds-calendar-day][data-date="' + iso + '"]:not([data-outside])')

            // Not $nextTick, for the same reason: a tick registered from the
            // dying cell's scope never lands. A timer belongs to the document
            // rather than to the element being replaced, and runs after the
            // reactive flush. Two hops, because a month that was off screen is
            // built on the first.
            clearTimeout(this.focusTimer)

            this.focusTimer = setTimeout(() => {
                const el = land()

                if (el) return el.focus()

                this.focusTimer = setTimeout(() => land()?.focus())
            })
        },

        shiftMonths(iso, n) {
            const [y, m, d] = this.toCal(iso)
            const [ny, nm] = this.addMonths(y, m, n)

            return this.fromCal(ny, nm, Math.min(d, this.daysInMonth(ny, nm)))
        },

        goToday() {
            this.focusOn(this.today)

            if (this.mode === 'single' && ! this.static && ! this.isDisabled(this.today)) {
                this.selected = [this.today]
                this.commitSingle()
            }
        },

        // The WAI-ARIA grid pattern. Left/Right follow the VISUAL order —
        // the page's direction is read at keydown, so an RTL island inside an
        // LTR page (or the reverse) still moves the way the arrow points.
        keydown(event) {
            if (event.altKey || event.ctrlKey || event.metaKey) return

            const rtl = getComputedStyle(this.$root).direction === 'rtl'
            const iso = this.focusDate
            const n = this.serial(iso)
            const weekOffset = (this.weekday(n) - this.startDay + 7) % 7
            let target

            switch (event.key) {
                case 'ArrowLeft': target = this.fromSerial(n + (rtl ? 1 : -1)); break
                case 'ArrowRight': target = this.fromSerial(n + (rtl ? -1 : 1)); break
                case 'ArrowUp': target = this.fromSerial(n - 7); break
                case 'ArrowDown': target = this.fromSerial(n + 7); break
                case 'Home': target = this.fromSerial(n - weekOffset); break
                case 'End': target = this.fromSerial(n + 6 - weekOffset); break
                case 'PageUp': target = this.shiftMonths(iso, event.shiftKey ? -12 : -1); break
                case 'PageDown': target = this.shiftMonths(iso, event.shiftKey ? 12 : 1); break
                default: return
            }

            event.preventDefault()
            this.focusOn(target)
        },

        // --- Selection ----------------------------------------------------

        pick(day) {
            if (this.static || day.disabled) return

            this.focused = day.iso

            // Picking a muted day from the neighbouring month brings that
            // month on screen, so the selection is never off-grid.
            if (day.outside) this.reveal(day.iso)

            if (this.mode === 'single') {
                this.selected = [day.iso]
                this.commitSingle()
            } else if (this.mode === 'multiple') {
                this.selected = this.selected.includes(day.iso)
                    ? this.selected.filter(iso => iso !== day.iso)
                    : [...this.selected, day.iso].sort()
                this.commitMultiple()
            } else {
                this.pickRange(day.iso)
            }
        },

        // First click opens a range, the second closes it — in either order.
        pickRange(iso) {
            if (this.start === null || this.end !== null) {
                this.start = iso
                this.end = null
            } else if (iso < this.start) {
                this.end = this.start
                this.start = iso
            } else {
                this.end = iso
            }

            this.hover = null
            this.commitRange()
        },

        emit(el) {
            el.dispatchEvent(new Event('input', { bubbles: true }))
            el.dispatchEvent(new Event('change', { bubbles: true }))
        },

        commitSingle() {
            const el = this.$refs.input

            if (! el) return

            el.value = this.selected[0] ?? ''
            this.emit(el)
        },

        commitRange() {
            for (const [ref, value] of [['start', this.start], ['end', this.end]]) {
                const el = this.$refs[ref]

                if (! el) continue

                el.value = value ?? ''
                this.emit(el)
            }
        },

        // Livewire reads a multiple select as an array — rebuild its options
        // from the selection, all selected.
        commitMultiple() {
            const el = this.$refs.select

            if (! el) return

            el.replaceChildren(...this.selected.map(iso => {
                const option = document.createElement('option')

                option.value = iso
                option.textContent = iso
                option.selected = true

                return option
            }))

            this.emit(el)
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerCalendar(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerCalendar(window.Alpine))
}
</script>
@endonce

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([
        'inline-block rounded-xl border bg-white p-3 shadow-xs dark:bg-zinc-800',
        'border-red-500 dark:border-red-400' => $invalid,
        'border-zinc-200 dark:border-white/10' => ! $invalid,
    ]) }}
    x-data="mdsCalendar(@js($config))"
    @if ($invalid) data-invalid @endif
    @if ($static) data-static @endif
    data-mds-calendar
    data-mds-calendar-mode="{{ $mode }}"
    data-mds-calendar-system="{{ $jalali ? 'jalali' : 'gregorian' }}"
>
    {{-- Header: previous · title (or month/year selects) · next. The buttons
         sit at inline-start and inline-end, so in RTL "previous" is on the
         right — where the eye expects "back" to be — and the chevrons turn
         with the direction (rtl:rotate-180) to keep pointing outward. --}}
    <div class="mb-2 flex items-center justify-between gap-2" data-mds-calendar-header>
        <button
            type="button"
            class="{{ $navClasses }}"
            x-on:click="shift(-1)"
            x-bind:disabled="! canPrev"
            @unless ($canPrev) disabled @endunless
            aria-label="{{ $prevLabel }}"
            data-mds-calendar-prev
        >
            <mds:icon icon="chevron-left" variant="micro" class="size-4 rtl:rotate-180" />
        </button>

        @if ($selectableHeader)
            <div class="flex items-center gap-1.5" data-mds-calendar-selects>
                <select class="{{ $selectClasses }}" x-model.number="month" aria-label="{{ $monthLabel }}" data-mds-calendar-month-select>
                    @foreach ($monthNames as $i => $monthName)
                        <option value="{{ $i }}" @selected($i === $viewMonth)>{{ $monthName }}</option>
                    @endforeach
                </select>

                <select class="{{ $selectClasses }} tabular-nums" x-model.number="year" aria-label="{{ $yearLabel }}" data-mds-calendar-year-select>
                    @foreach (range($yearFrom, $yearTo) as $y)
                        <option value="{{ $y }}" @selected($y === $viewYear)>{{ $digits($y) }}</option>
                    @endforeach
                </select>

                <span class="sr-only" aria-live="polite" aria-atomic="true" x-text="title">{{ collect($views)->pluck('title')->implode(' – ') }}</span>
            </div>
        @else
            <div
                @class(['text-sm font-semibold text-zinc-800 dark:text-white', 'sr-only' => $months > 1])
                aria-live="polite"
                aria-atomic="true"
                x-text="title"
                data-mds-calendar-title
            >{{ collect($views)->pluck('title')->implode(' – ') }}</div>
        @endif

        <button
            type="button"
            class="{{ $navClasses }}"
            x-on:click="shift(1)"
            x-bind:disabled="! canNext"
            @unless ($canNext) disabled @endunless
            aria-label="{{ $nextLabel }}"
            data-mds-calendar-next
        >
            <mds:icon icon="chevron-right" variant="micro" class="size-4 rtl:rotate-180" />
        </button>
    </div>

    {{-- The server-rendered grid: complete on first paint, including today,
         the selection and the disabled days, so the page reads right before
         Alpine boots (and without it). Hidden once the live grid is up. --}}
    <div class="flex flex-wrap gap-6" x-show="! ready" data-mds-calendar-months>
        @foreach ($views as $view)
            <div data-mds-calendar-month>
                <table
                    class="border-separate border-spacing-x-0 border-spacing-y-0.5"
                    role="grid"
                    aria-label="{{ $gridLabel($view) }}"
                    @if ($static) aria-readonly="true" @endif
                    @if (filled($error)) aria-describedby="{{ $errorId }}" @endif
                >
                    @if ($months > 1)
                        <caption class="pb-2 text-sm font-semibold text-zinc-800 dark:text-white">{{ $view['title'] }}</caption>
                    @endif

                    <thead>
                        <tr>
                            @if ($weekNumbers)
                                <th scope="col" class="{{ $headClasses }}" abbr="{{ $weekLabel }}"><span aria-hidden="true">#</span><span class="sr-only">{{ $weekLabel }}</span></th>
                            @endif

                            @foreach ($weekdays as $weekday)
                                <th scope="col" class="{{ $headClasses }}" abbr="{{ $weekday['name'] }}"><span aria-hidden="true">{{ $weekday['initial'] }}</span><span class="sr-only">{{ $weekday['name'] }}</span></th>
                            @endforeach
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($view['weeks'] as $week)
                            <tr role="row">
                                @if ($weekNumbers)
                                    <th scope="row" class="{{ $weekClasses }}" data-mds-calendar-week>{{ $digits($week['number']) }}</th>
                                @endif

                                @foreach ($week['days'] as $day)
                                    <td role="gridcell" class="p-0" aria-selected="{{ $isSelected($day['iso']) ? 'true' : 'false' }}">
                                        <button
                                            type="button"
                                            @class($cellClasses($day))
                                            tabindex="{{ $day['iso'] === $focus && ! $day['outside'] ? 0 : -1 }}"
                                            aria-label="{{ $day['label'] }}"
                                            @if ($isDisabled($day['iso'])) aria-disabled="true" data-disabled @endif
                                            @if ($day['iso'] === $today) aria-current="date" data-today @endif
                                            @if ($day['outside']) data-outside @endif
                                            @if ($isSelected($day['iso'])) data-selected @endif
                                            @if ($inRange($day['iso'])) data-in-range @endif
                                            @if ($mode === 'range' && $day['iso'] === $start) data-range-start @endif
                                            @if ($mode === 'range' && $day['iso'] === $end) data-range-end @endif
                                            data-date="{{ $day['iso'] }}"
                                            data-mds-calendar-day
                                        >{{ $digits($day['day']) }}</button>
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    {{-- The live grid: the same shape, redrawn by Alpine from window.mds.jalali. --}}
    <template x-if="ready">
        <div class="flex flex-wrap gap-6" data-mds-calendar-months data-mds-calendar-live>
            <template x-for="view in views" :key="view.key">
                <div data-mds-calendar-month>
                    <table
                        class="border-separate border-spacing-x-0 border-spacing-y-0.5"
                        role="grid"
                        @if ($months > 1)
                            x-bind:aria-label="@js($label.' — ') + view.title"
                        @else
                            x-bind:aria-label="@js($label)"
                        @endif
                        @if ($static) aria-readonly="true" @endif
                        @if (filled($error)) aria-describedby="{{ $errorId }}" @endif
                        x-on:keydown="keydown($event)"
                        x-on:mouseleave="hover = null"
                    >
                        @if ($months > 1)
                            <caption class="pb-2 text-sm font-semibold text-zinc-800 dark:text-white" x-text="view.title"></caption>
                        @endif

                        <thead>
                            <tr>
                                @if ($weekNumbers)
                                    <th scope="col" class="{{ $headClasses }}" abbr="{{ $weekLabel }}"><span aria-hidden="true">#</span><span class="sr-only">{{ $weekLabel }}</span></th>
                                @endif

                                @foreach ($weekdays as $weekday)
                                    <th scope="col" class="{{ $headClasses }}" abbr="{{ $weekday['name'] }}"><span aria-hidden="true">{{ $weekday['initial'] }}</span><span class="sr-only">{{ $weekday['name'] }}</span></th>
                                @endforeach
                            </tr>
                        </thead>

                        <tbody>
                            <template x-for="week in view.weeks" :key="week.key">
                                <tr role="row">
                                    @if ($weekNumbers)
                                        <th scope="row" class="{{ $weekClasses }}" x-text="week.number" data-mds-calendar-week></th>
                                    @endif

                                    <template x-for="day in week.days" :key="day.iso">
                                        <td role="gridcell" class="p-0" x-bind:aria-selected="day.selected ? 'true' : 'false'">
                                            <button
                                                type="button"
                                                class="{{ $cellBase }}"
                                                x-bind:class="{
                                                    @js($cellDisabled): day.disabled,
                                                    @js($cellSelected): ! day.disabled && day.selected,
                                                    @js($cellInRange): ! day.disabled && ! day.selected && day.inRange,
                                                    @js($cellOutside): ! day.disabled && ! day.selected && ! day.inRange && day.outside,
                                                    @js($cellIdle): ! day.disabled && ! day.selected && ! day.inRange && ! day.outside,
                                                    @js($cellRangeStart): day.flatEnd,
                                                    @js($cellRangeEnd): day.flatStart,
                                                    @js($cellToday): day.today,
                                                }"
                                                x-bind:tabindex="day.tabindex"
                                                x-bind:aria-label="day.label"
                                                x-bind:aria-disabled="day.disabled ? 'true' : null"
                                                x-bind:aria-current="day.today ? 'date' : null"
                                                x-bind:data-disabled="day.disabled ? '' : null"
                                                x-bind:data-today="day.today ? '' : null"
                                                x-bind:data-outside="day.outside ? '' : null"
                                                x-bind:data-selected="day.selected ? '' : null"
                                                x-bind:data-in-range="day.inRange ? '' : null"
                                                x-bind:data-range-start="day.rangeStart ? '' : null"
                                                x-bind:data-range-end="day.rangeEnd ? '' : null"
                                                x-bind:data-date="day.iso"
                                                x-on:click="pick(day)"
                                                x-on:focus="focused = day.iso"
                                                x-on:mouseenter="hover = day.iso"
                                                x-text="day.day"
                                                data-mds-calendar-day
                                            ></button>
                                        </td>
                                    </template>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </template>
        </div>
    </template>

    @if ($withToday)
        <div class="mt-2 flex justify-center">
            <button
                type="button"
                class="rounded-md px-2 py-1 text-sm font-medium text-accent-content hover:underline focus-visible:outline-2 focus-visible:outline-offset-1 focus-visible:outline-accent"
                x-on:click="goToday()"
                data-mds-calendar-today
            >{{ $todayLabel }}</button>
        </div>
    @endif

    {{-- The machine values: ISO Gregorian, Latin digits, whatever the grid shows. --}}
    @if ($mode === 'single')
        <input
            type="hidden"
            x-ref="input"
            value="{{ $selected[0] ?? '' }}"
            @if ($name) name="{{ $name }}" @endif
            {{ $wireModel }}
        >
    @elseif ($mode === 'range')
        <input
            type="hidden"
            x-ref="start"
            value="{{ $start ?? '' }}"
            @if ($name) name="{{ $name }}[start]" @endif
            {{ $wireFor('start') }}
            data-mds-calendar-start
        >
        <input
            type="hidden"
            x-ref="end"
            value="{{ $end ?? '' }}"
            @if ($name) name="{{ $name }}[end]" @endif
            {{ $wireFor('end') }}
            data-mds-calendar-end
        >
    @else
        <select
            multiple
            class="sr-only"
            tabindex="-1"
            aria-hidden="true"
            x-ref="select"
            @if ($name) name="{{ $name }}[]" @endif
            {{ $wireModel }}
            data-mds-calendar-select
        >
            @foreach ($selected as $date)
                <option value="{{ $date }}" selected>{{ $date }}</option>
            @endforeach
        </select>
    @endif

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div id="{{ $errorId }}" role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</div>
