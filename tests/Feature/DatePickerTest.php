<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;

class DatePickerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // flux:field / flux:label read the session error bag; nothing shares
        // one outside a request.
        View::share('errors', new ViewErrorBag);
    }

    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * The kit's Livewire contract, asserted the same way for every control:
     * the binding reaches the real form element, and the wrapper keeps no
     * copy of it.
     *
     * @param  string  $control  regex fragment matching the control's opening tag
     */
    protected function assertBindingReachesControl(string $html, string $control, string $binding): void
    {
        $this->assertMatchesRegularExpression(
            '/<'.$control.'[^>]*\s'.preg_quote($binding, '/').'[\s>]/',
            $html,
            "[{$binding}] never reached the control it is supposed to bind.",
        );

        $this->assertSame(
            1,
            substr_count($html, $binding),
            "[{$binding}] appears more than once — the wrapper kept a copy it should have dropped.",
        );
    }

    /**
     * The `value` attribute of one of the picker's own controls, read off the
     * element carrying the marker — so a needle can never match the calendar's
     * hidden input, or the day cell of the same date, by accident.
     */
    protected function attributeOf(string $html, string $marker, string $attribute = 'value'): ?string
    {
        if (! preg_match('/<[a-z]+[^>]*\bdata-mds-date-picker-'.$marker.'\b[^>]*>/', $html, $tag)) {
            return null;
        }

        return preg_match('/\s'.$attribute.'="([^"]*)"/', $tag[0], $found) ? $found[1] : null;
    }

    protected function machine(string $html, string $part = 'value'): ?string
    {
        return $this->attributeOf($html, $part);
    }

    protected function text(string $html): ?string
    {
        return $this->attributeOf($html, 'input');
    }

    /** Render with Latin digits and English strings, then put the config back. */
    protected function english(string $template, array $data = []): string
    {
        try {
            config(['mds.persian_digits' => false]);

            return $this->render($template, $data);
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }

    // --- Structure ---------------------------------------------------------

    public function test_it_renders_a_field_a_control_and_a_calendar_in_a_popover(): void
    {
        $html = $this->render('<mds:date-picker name="deliver_at" />');

        $this->assertStringContainsString('data-mds-date-picker', $html);
        $this->assertStringContainsString('data-mds-date-picker-mode="single"', $html);
        $this->assertStringContainsString('data-mds-date-picker-system="jalali"', $html);
        $this->assertStringContainsString('data-mds-date-picker-control', $html);
        $this->assertStringContainsString('data-mds-date-picker-input', $html);
        $this->assertStringContainsString('data-mds-date-picker-trigger', $html);

        // The popover is the shell, the calendar is the grid — both really there.
        $this->assertStringContainsString('data-mds-popover-trigger', $html);
        $this->assertStringContainsString('data-mds-popover-content', $html);
        $this->assertStringContainsString('data-mds-calendar', $html);
        $this->assertStringContainsString('x-data="mdsDatePicker(', $html);
    }

    public function test_the_grid_is_teleported_out_with_the_popover_panel(): void
    {
        $html = $this->render('<mds:date-picker />');

        // The panel — and the calendar in it — sit inside the teleport template.
        $this->assertMatchesRegularExpression(
            '/<template x-teleport="body">.*data-mds-calendar\b/s',
            $html,
        );
    }

    public function test_the_gregorian_calendar_is_one_prop_away(): void
    {
        $html = $this->render('<mds:date-picker calendar="gregorian" value="2026-08-20" />');

        $this->assertStringContainsString('data-mds-date-picker-system="gregorian"', $html);
        $this->assertStringContainsString('data-mds-calendar-system="gregorian"', $html);
        $this->assertSame('۲۰۲۶/۰۸/۲۰', $this->text($html));
        $this->assertSame('2026-08-20', $this->machine($html));
    }

    // --- Reading a written date -------------------------------------------

    public function test_persian_digits_in_a_jalali_date_come_out_as_iso_gregorian(): void
    {
        $html = $this->render('<mds:date-picker value="۱۴۰۵/۰۵/۲۹" />');

        $this->assertSame('2026-08-20', $this->machine($html));
        $this->assertSame('۱۴۰۵/۰۵/۲۹', $this->text($html));
    }

    public function test_it_reads_arabic_indic_digits_and_a_one_digit_month(): void
    {
        $html = $this->render('<mds:date-picker value="١٤٠٥/٥/٢٩" />');

        $this->assertSame('2026-08-20', $this->machine($html));
    }

    public function test_it_reads_a_compact_eight_digit_date(): void
    {
        $html = $this->render('<mds:date-picker value="14050529" />');

        $this->assertSame('2026-08-20', $this->machine($html));
    }

    public function test_a_dashed_date_below_1700_is_jalali_and_above_it_is_the_machine_form(): void
    {
        // The rule that keeps «۱۴۰۵-۰۵-۲۹» from becoming a date in 1405 AD.
        $jalaliDashes = $this->render('<mds:date-picker value="1405-05-29" />');
        $this->assertSame('2026-08-20', $this->machine($jalaliDashes));

        $machine = $this->render('<mds:date-picker value="2026-08-20" />');
        $this->assertSame('2026-08-20', $this->machine($machine));
        $this->assertSame('۱۴۰۵/۰۵/۲۹', $this->text($machine));
    }

    public function test_a_gregorian_picker_reads_every_separator_as_gregorian(): void
    {
        $slashes = $this->render('<mds:date-picker calendar="gregorian" value="2026/08/20" />');
        $this->assertSame('2026-08-20', $this->machine($slashes));

        $dashes = $this->render('<mds:date-picker calendar="gregorian" value="2026-08-20" />');
        $this->assertSame('2026-08-20', $this->machine($dashes));
    }

    public function test_a_timestamped_iso_string_keeps_only_the_date(): void
    {
        $html = $this->render('<mds:date-picker value="2026-08-20 13:45:00" />');

        $this->assertSame('2026-08-20', $this->machine($html));
    }

    public function test_it_accepts_a_date_object(): void
    {
        $html = $this->render('<mds:date-picker :value="$date" />', [
            'date' => new \DateTimeImmutable('2026-08-20 09:00:00'),
        ]);

        $this->assertSame('2026-08-20', $this->machine($html));
        $this->assertSame('۱۴۰۵/۰۵/۲۹', $this->text($html));
    }

    /**
     * The one thing a date field must never do: turn nonsense into a date
     * that looks plausible. Every one of these has a shape the parser
     * recognises and a value it must refuse.
     */
    public function test_an_impossible_or_unreadable_date_becomes_nothing_at_all(): void
    {
        $cases = [
            'not a date at all' => 'hello',
            'a month past twelve' => '1405/13/01',
            'a day past the month' => '1405/07/31',
            'the 30th of a common Esfand' => '1404/12/30',
            'a two digit year' => '05/05/29',
            'a Gregorian day past the month' => '2026-02-30',
            'digits without a shape' => '140505',
            'an empty string' => '',
        ];

        foreach ($cases as $why => $value) {
            $html = $this->render('<mds:date-picker :value="$v" />', ['v' => $value]);

            $this->assertSame('', $this->machine($html), "[{$why}] should not have produced a date.");
            $this->assertSame('', $this->text($html), "[{$why}] should not have produced a date.");
        }
    }

    public function test_esfand_30_reads_in_a_leap_year_and_not_otherwise(): void
    {
        // 1403 is a leap Jalali year, 1404 is not.
        $leap = $this->render('<mds:date-picker value="1403/12/30" />');
        $this->assertSame('2025-03-20', $this->machine($leap));

        $common = $this->render('<mds:date-picker value="1404/12/30" />');
        $this->assertSame('', $this->machine($common));
    }

    // --- Writing it back out ----------------------------------------------

    public function test_the_long_format_spells_the_month_out(): void
    {
        $html = $this->render('<mds:date-picker format="long" value="2026-08-20" />');

        $this->assertSame('۲۹ مرداد ۱۴۰۵', $this->text($html));
        $this->assertSame('2026-08-20', $this->machine($html));
    }

    public function test_the_long_format_names_gregorian_months_too(): void
    {
        $html = $this->english('<mds:date-picker format="long" calendar="gregorian" value="2026-08-20" />');

        $this->assertSame('20 August 2026', $this->text($html));
    }

    public function test_a_numeric_field_reads_left_to_right_and_a_long_one_does_not(): void
    {
        $numeric = $this->render('<mds:date-picker />');
        $this->assertMatchesRegularExpression('/<input[^>]*dir="ltr"[^>]*data-mds-date-picker-input/s', $numeric);
        $this->assertMatchesRegularExpression('/<input[^>]*inputmode="numeric"[^>]*data-mds-date-picker-input/s', $numeric);

        $long = $this->render('<mds:date-picker format="long" />');
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*dir="ltr"[^>]*data-mds-date-picker-input/s', $long);
    }

    // --- Ranges -------------------------------------------------------------

    public function test_a_range_renders_two_machine_values_and_one_written_span(): void
    {
        $html = $this->render('<mds:date-picker mode="range" :value="[\'2026-08-20\', \'2026-08-26\']" />');

        $this->assertStringContainsString('data-mds-date-picker-mode="range"', $html);
        $this->assertSame('2026-08-20', $this->machine($html, 'start'));
        $this->assertSame('2026-08-26', $this->machine($html, 'end'));
        $this->assertSame('۱۴۰۵/۰۵/۲۹ تا ۱۴۰۵/۰۶/۰۴', $this->text($html));
        $this->assertNull($this->machine($html), 'A range must not also render the single hidden input.');
    }

    public function test_a_range_takes_keys_as_well_as_a_list_and_straightens_a_backwards_pair(): void
    {
        $keyed = $this->render('<mds:date-picker mode="range" :value="[\'start\' => \'2026-08-26\', \'end\' => \'2026-08-20\']" />');

        $this->assertSame('2026-08-20', $this->machine($keyed, 'start'));
        $this->assertSame('2026-08-26', $this->machine($keyed, 'end'));
    }

    public function test_a_half_open_range_shows_only_the_day_it_has(): void
    {
        $html = $this->render('<mds:date-picker mode="range" :value="[\'2026-08-20\', null]" />');

        $this->assertSame('۱۴۰۵/۰۵/۲۹', $this->text($html));
        $this->assertSame('', $this->machine($html, 'end'));
    }

    public function test_the_range_separator_switches_language(): void
    {
        $english = $this->english('<mds:date-picker mode="range" :value="[\'2026-08-20\', \'2026-08-26\']" />');

        $this->assertSame('1405/05/29 – 1405/06/04', $this->text($english));
    }

    public function test_an_unknown_mode_falls_back_to_single(): void
    {
        $html = $this->render('<mds:date-picker mode="multiple" />');

        $this->assertStringContainsString('data-mds-date-picker-mode="single"', $html);
    }

    // --- The Livewire contract ----------------------------------------------

    public function test_a_single_binding_reaches_the_hidden_input(): void
    {
        $html = $this->render('<mds:date-picker wire:model.live="deliverAt" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model.live="deliverAt"');
    }

    public function test_a_range_binds_two_dotted_paths_and_keeps_the_modifiers(): void
    {
        $html = $this->render('<mds:date-picker mode="range" wire:model.live="trip" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model.live="trip.start"');
        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model.live="trip.end"');

        // The undotted property must never reach the DOM on its own.
        $this->assertStringNotContainsString('wire:model.live="trip"', $html);
    }

    public function test_the_binding_never_lands_on_the_wrapper(): void
    {
        $html = $this->render('<mds:date-picker wire:model="deliverAt" />');

        $this->assertDoesNotMatchRegularExpression(
            '/<div[^>]*wire:model="deliverAt"[^>]*data-mds-date-picker\b/s',
            $html,
        );
    }

    public function test_a_name_posts_the_plain_way(): void
    {
        $single = $this->render('<mds:date-picker name="deliver_at" value="2026-08-20" />');
        $this->assertSame('deliver_at', $this->attributeOf($single, 'value', 'name'));

        $range = $this->render('<mds:date-picker mode="range" name="trip" />');
        $this->assertSame('trip[start]', $this->attributeOf($range, 'start', 'name'));
        $this->assertSame('trip[end]', $this->attributeOf($range, 'end', 'name'));
    }

    public function test_a_nameless_picker_posts_nothing(): void
    {
        $html = $this->render('<mds:date-picker />');

        $this->assertDoesNotMatchRegularExpression(
            '/<input[^>]*\sname="[^"]*"[^>]*data-mds-date-picker-value/s',
            $html,
        );
    }

    // --- Validation ----------------------------------------------------------

    public function test_an_explicit_error_renders_the_message_and_marks_the_field(): void
    {
        $html = $this->render('<mds:date-picker name="deliver_at" error="Pick a delivery day." />');

        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('Pick a delivery day.', $html);
        $this->assertStringContainsString('data-invalid', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*aria-invalid="true"[^>]*data-mds-date-picker-input/s', $html);

        // The message is pointed at from the field it belongs to.
        preg_match('/<div id="(mds-date-picker-error-[0-9a-f]{8})"/', $html, $found);
        $this->assertNotEmpty($found, 'The error block should carry a deterministic id.');
        $this->assertStringContainsString($found[1], (string) $this->attributeOf($html, 'input', 'aria-describedby'));
    }

    public function test_the_error_falls_back_to_the_bag_for_name(): void
    {
        View::share('errors', new ViewErrorBag)->put('default', new MessageBag([
            'deliver_at' => ['A delivery day is required.'],
        ]));

        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['deliver_at' => ['A delivery day is required.']]));
        View::share('errors', $bag);

        $html = $this->render('<mds:date-picker name="deliver_at" />');

        $this->assertStringContainsString('A delivery day is required.', $html);
        $this->assertStringContainsString('data-invalid', $html);
    }

    public function test_an_explicit_error_wins_over_the_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['deliver_at' => ['From the bag.']]));
        View::share('errors', $bag);

        $html = $this->render('<mds:date-picker name="deliver_at" error="From the prop." />');

        $this->assertStringContainsString('From the prop.', $html);
        $this->assertStringNotContainsString('From the bag.', $html);
    }

    public function test_invalid_alone_styles_the_field_without_a_message(): void
    {
        $html = $this->render('<mds:date-picker invalid />');

        $this->assertStringContainsString('data-invalid', $html);
        $this->assertStringNotContainsString('role="alert"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*aria-invalid="true"[^>]*data-mds-date-picker-input/s', $html);
    }

    // --- ARIA ----------------------------------------------------------------

    public function test_the_field_is_described_by_a_format_hint(): void
    {
        $html = $this->render('<mds:date-picker />');

        $hintId = $this->attributeOf($html, 'hint', 'id');

        $this->assertNotNull($hintId);
        $this->assertSame($hintId, $this->attributeOf($html, 'input', 'aria-describedby'));
        $this->assertStringContainsString('>قالب تاریخ: سال/ماه/روز</span>', $html);
    }

    public function test_the_hint_explains_the_range_shape(): void
    {
        $html = $this->render('<mds:date-picker mode="range" />');

        $this->assertStringContainsString('دو تاریخ با «تا»', $html);

        $english = $this->english('<mds:date-picker mode="range" />');
        $this->assertStringContainsString('two dates separated by &quot;to&quot;', $english);
    }

    public function test_the_selection_is_announced_in_a_live_region(): void
    {
        $empty = $this->render('<mds:date-picker />');
        $this->assertMatchesRegularExpression(
            '/<span[^>]*role="status"[^>]*aria-live="polite"[^>]*data-mds-date-picker-status>تاریخی انتخاب نشده است<\/span>/',
            $empty,
        );

        $chosen = $this->render('<mds:date-picker value="2026-08-20" />');
        $this->assertStringContainsString('data-mds-date-picker-status>انتخاب‌شده: ۱۴۰۵/۰۵/۲۹</span>', $chosen);
    }

    public function test_the_panel_is_a_named_dialog_and_the_grid_carries_the_same_name(): void
    {
        $html = $this->render('<mds:date-picker label="روز تحویل" />');

        $this->assertMatchesRegularExpression('/<div\\b(?=[^>]*role="dialog")(?=[^>]*aria-label="روز تحویل")[^>]*>/s', $html);
        $this->assertMatchesRegularExpression('/<table[^>]*role="grid"[^>]*aria-label="روز تحویل"/s', $html);
    }

    public function test_the_dialog_falls_back_to_the_calendars_own_name(): void
    {
        $html = $this->render('<mds:date-picker />');
        $this->assertStringContainsString('aria-label="تقویم"', $html);

        $english = $this->english('<mds:date-picker />');
        $this->assertStringContainsString('aria-label="Calendar"', $english);
    }

    public function test_the_trigger_is_the_control_the_popover_wires_its_state_onto(): void
    {
        $html = $this->render('<mds:date-picker />');

        // The button sits inside the popover trigger wrapper — that is what
        // gets aria-haspopup / aria-expanded / aria-controls at runtime.
        $this->assertMatchesRegularExpression(
            '/data-mds-popover-trigger[^>]*>\s*<button[^>]*aria-label="انتخاب تاریخ"/s',
            $html,
        );

        // ...and the typed field is NOT inside it, so clicking it does not toggle.
        $this->assertDoesNotMatchRegularExpression(
            '/data-mds-popover-trigger[^>]*>(?:(?!<\/span>).)*data-mds-date-picker-input/s',
            $html,
        );
    }

    public function test_the_field_opens_the_grid_with_the_down_arrow(): void
    {
        $html = $this->render('<mds:date-picker />');

        $this->assertStringContainsString('x-on:keydown.arrow-down.prevent="show({ focus: false }); focusDay(contentEl)"', $html);
        $this->assertStringContainsString('x-on:keydown.escape="revert()"', $html);
        $this->assertStringContainsString('x-on:keydown.enter.prevent.stop="commit()"', $html);
        $this->assertStringContainsString('x-on:blur="commit()"', $html);
    }

    public function test_the_grid_reports_its_picks_back_and_closes_the_panel(): void
    {
        $html = $this->render('<mds:date-picker />');

        $this->assertStringContainsString('x-on:input="if (fromCalendar($event)) close()"', $html);
        $this->assertStringContainsString('x-init="calendarEl = $el"', $html);
    }

    // --- Everything the calendar owns ----------------------------------------

    public function test_it_forwards_the_grid_props(): void
    {
        $html = $this->render(<<<'BLADE'
        <mds:date-picker
            :months="2"
            week-numbers
            fixed-weeks
            selectable-header
            with-today
            size="sm"
            :start-day="0"
            value="2026-08-20"
        />
        BLADE);

        $this->assertStringContainsString('data-mds-calendar-week', $html);
        $this->assertStringContainsString('data-mds-calendar-selects', $html);
        $this->assertStringContainsString('data-mds-calendar-today', $html);
        // Two months side by side, each with its own caption.
        $this->assertStringContainsString('مرداد ۱۴۰۵', $html);
        $this->assertStringContainsString('شهریور ۱۴۰۵', $html);
        // start-day 0 puts Sunday first, so the initial column reads یکشنبه.
        $this->assertMatchesRegularExpression('/<thead>\s*<tr>\s*<th[^>]*abbr="هفته"/s', $html);
    }

    public function test_bounds_and_days_off_reach_the_grid(): void
    {
        $html = $this->render('<mds:date-picker min="2026-08-16" max="2026-09-12" :unavailable="[\'2026-08-28\']" value="2026-08-20" />');

        $this->assertMatchesRegularExpression('/data-disabled[^>]*data-date="2026-08-28"/s', $html);
        $this->assertMatchesRegularExpression('/data-disabled[^>]*data-date="2026-08-15"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/data-disabled[^>]*data-date="2026-08-20"/s', $html);
    }

    public function test_bounds_may_be_written_in_the_fields_own_calendar(): void
    {
        $html = $this->render('<mds:date-picker min="۱۴۰۵/۰۵/۲۵" value="2026-08-20" />');

        $this->assertMatchesRegularExpression('/data-disabled[^>]*data-date="2026-08-15"/s', $html);
    }

    public function test_the_selection_reaches_the_grid(): void
    {
        $html = $this->render('<mds:date-picker value="2026-08-20" />');

        $this->assertMatchesRegularExpression('/data-selected[^>]*data-date="2026-08-20"/s', $html);
    }

    public function test_a_range_selection_reaches_the_grid_as_a_span(): void
    {
        $html = $this->render('<mds:date-picker mode="range" :value="[\'2026-08-20\', \'2026-08-26\']" />');

        $this->assertStringContainsString('data-mds-calendar-mode="range"', $html);
        $this->assertMatchesRegularExpression('/data-range-start[^>]*data-date="2026-08-20"/s', $html);
        $this->assertMatchesRegularExpression('/data-range-end[^>]*data-date="2026-08-26"/s', $html);
    }

    public function test_placement_reaches_the_popover(): void
    {
        $html = $this->render('<mds:date-picker position="top" align="end" />');

        $this->assertStringContainsString('mdsPopover({ position: \'top\', align: \'end\'', $html);
    }

    // --- Both languages -------------------------------------------------------

    public function test_every_built_in_string_switches_to_english(): void
    {
        $html = $this->english('<mds:date-picker clearable value="2026-08-20" />');

        $this->assertStringContainsString('placeholder="YYYY/MM/DD"', $html);
        $this->assertStringContainsString('aria-label="Choose date"', $html);
        $this->assertStringContainsString('aria-label="Clear"', $html);
        $this->assertStringContainsString('aria-label="Calendar"', $html);
        $this->assertStringContainsString('>Date format: YYYY/MM/DD</span>', $html);
        $this->assertStringContainsString('data-mds-date-picker-status>Selected: 1405/05/29</span>', $html);
        $this->assertSame('1405/05/29', $this->text($html));

        // The machine value never changes language.
        $this->assertSame('2026-08-20', $this->machine($html));
    }

    public function test_every_built_in_string_is_persian_by_default(): void
    {
        $html = $this->render('<mds:date-picker clearable />');

        $this->assertStringContainsString('placeholder="سال/ماه/روز"', $html);
        $this->assertStringContainsString('aria-label="انتخاب تاریخ"', $html);
        $this->assertStringContainsString('aria-label="پاک کردن"', $html);
        $this->assertStringContainsString('aria-label="تقویم"', $html);
    }

    public function test_fa_overrides_the_config_for_one_picker(): void
    {
        $latin = $this->render('<mds:date-picker :fa="false" value="2026-08-20" />');
        $this->assertSame('1405/05/29', $this->text($latin));
        // ...and it reaches the grid, whose month name is transliterated.
        $this->assertStringContainsString('Mordad', $latin);

        $persian = $this->english('<mds:date-picker :fa="true" value="2026-08-20" />');
        $this->assertSame('۱۴۰۵/۰۵/۲۹', $this->text($persian));
        $this->assertStringContainsString('مرداد', $persian);
    }

    public function test_a_caller_placeholder_wins(): void
    {
        $html = $this->render('<mds:date-picker placeholder="کی می‌رسد؟" />');

        $this->assertStringContainsString('placeholder="کی می‌رسد؟"', $html);
        $this->assertStringNotContainsString('placeholder="سال/ماه/روز"', $html);
    }

    // --- The rest of the field --------------------------------------------------

    public function test_label_and_description_render(): void
    {
        $html = $this->render('<mds:date-picker label="روز تحویل" description="در ساعات اداری" />');

        $this->assertStringContainsString('روز تحویل', $html);
        $this->assertStringContainsString('در ساعات اداری', $html);
    }

    public function test_clearable_is_opt_in(): void
    {
        $this->assertStringNotContainsString('data-mds-date-picker-clear', $this->render('<mds:date-picker />'));

        $html = $this->render('<mds:date-picker clearable />');
        $this->assertMatchesRegularExpression(
            '/<button[^>]*x-show="value !== \'\'"[^>]*data-mds-date-picker-clear/s',
            $html,
        );
    }

    public function test_disabled_takes_the_whole_field_out(): void
    {
        $html = $this->render('<mds:date-picker disabled />');

        $this->assertMatchesRegularExpression('/<div[^>]*\binert\b[^>]*data-mds-date-picker\b/s', $html);
        $this->assertStringContainsString('data-disabled', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\bdisabled\b[^>]*data-mds-date-picker-input/s', $html);
    }

    public function test_readonly_keeps_the_grid_but_stops_the_typing(): void
    {
        $html = $this->render('<mds:date-picker readonly />');

        $this->assertMatchesRegularExpression('/<input[^>]*\breadonly\b[^>]*data-mds-date-picker-input/s', $html);
        $this->assertStringContainsString('data-mds-date-picker-trigger', $html);
    }

    public function test_the_leading_icon_can_be_replaced_or_dropped(): void
    {
        $default = $this->render('<mds:date-picker />');
        $this->assertMatchesRegularExpression('/data-mds-date-picker-control[^>]*>\s*<svg/s', $default);

        $none = $this->render('<mds:date-picker :icon="false" />');
        $this->assertDoesNotMatchRegularExpression('/data-mds-date-picker-control[^>]*>\s*<svg/s', $none);
    }

    public function test_the_caller_class_lands_on_the_root(): void
    {
        $html = $this->render('<mds:date-picker class="max-w-xs" />');

        $this->assertMatchesRegularExpression('/<div[^>]*class="[^"]*max-w-xs[^"]*"[^>]*data-mds-date-picker\b/s', $html);
    }

    // --- Once per page ------------------------------------------------------------

    public function test_the_script_is_registered_once_however_many_pickers(): void
    {
        $html = $this->render('<div><mds:date-picker /><mds:date-picker mode="range" /></div>');

        $this->assertSame(1, substr_count($html, 'window.mds.registerDatePicker ='));
        $this->assertSame(1, substr_count($html, "Alpine.data('mdsDatePicker'"));
    }

    /**
     * The calendar lives inside a teleported <template>, whose content is
     * inert to the parser. If the shared digit and Jalali blocks were first
     * claimed from in there, their scripts would never run — so this view
     * claims both before the popover opens.
     */
    public function test_the_shared_scripts_are_emitted_outside_the_teleport_template(): void
    {
        $html = $this->render('<mds:date-picker />');

        $template = strpos($html, '<template x-teleport="body">');

        $this->assertIsInt($template);

        foreach (['window.mds.digits =', 'window.mds.jalali = {', 'window.mds.registerDatePicker ='] as $needle) {
            $at = strpos($html, $needle);

            $this->assertIsInt($at, "[{$needle}] was not emitted at all.");
            $this->assertLessThan($template, $at, "[{$needle}] was emitted inside the teleport template.");
        }
    }

    public function test_two_pickers_do_not_share_one_hint_id(): void
    {
        $html = $this->render('<div><mds:date-picker name="from" /><mds:date-picker name="to" /></div>');

        preg_match_all('/id="(mds-date-picker-hint-[0-9a-f]{8})"/', $html, $found);

        $this->assertCount(2, $found[1]);
        $this->assertNotSame($found[1][0], $found[1][1]);
    }
}
