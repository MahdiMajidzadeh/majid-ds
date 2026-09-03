<?php

namespace MajidDs\Tests\Feature;

use DateTimeImmutable;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Support\Jalali;
use MajidDs\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * mds:calendar — the open, Jalali answer to flux:calendar.
 *
 * The grid assertions deliberately do NOT re-implement the view's own month
 * arithmetic. They walk what was rendered and check it back against
 * MajidDs\Support\Jalali: the cells are contiguous days, seven to a row, and
 * the run of non-outside cells is exactly 1..N of the Jalali month the view
 * claims to be showing. A drift in either converter shows up as a failure.
 */
class CalendarTest extends TestCase
{
    /**
     * The clock is pinned because the view anchors on `today` and marks it
     * with aria-current — an unpinned run would drift into another month and
     * quietly change which assertions mean anything.
     *
     * 2026-08-24 is Shahrivar 2, 1405 — two days past a month boundary, so
     * "today" and "the anchor month" are never accidentally the same thing.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Date::setTestNow('2026-08-24 10:00:00');
    }

    protected function tearDown(): void
    {
        Date::setTestNow();

        parent::tearDown();
    }

    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * The kit's Livewire contract: the binding reaches the real control, and
     * the wrapper keeps no copy of it.
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
     * The ISO dates of the server-rendered grid, in document order. Only the
     * server grid has literal data-date attributes; the live Alpine grid binds
     * them with x-bind:data-date.
     *
     * @return list<string>
     */
    protected function dates(string $html): array
    {
        preg_match_all('/\sdata-date="(\d{4}-\d{2}-\d{2})"/', $html, $matches);

        return $matches[1];
    }

    /**
     * The ISO dates that are NOT greyed-out neighbours.
     *
     * @return list<string>
     */
    protected function ownDates(string $html): array
    {
        preg_match_all('/<button\b(?![^>]*\bdata-outside\b)[^>]*\sdata-date="(\d{4}-\d{2}-\d{2})"[^>]*>/s', $html, $matches);

        return $matches[1];
    }

    /**
     * Just the server-rendered grid. The live Alpine grid repeats every marker
     * as an x-bind, and both are in the markup at once — counting across the
     * whole render would double every number.
     */
    protected function serverGrid(string $html): string
    {
        $from = strpos($html, 'x-show="! ready"');
        $to = strpos($html, '<template x-if="ready">');

        $this->assertNotFalse($from, 'no server-rendered grid found');
        $this->assertNotFalse($to, 'no live grid found');

        return substr($html, $from, $to - $from);
    }

    /**
     * Everything after the once-per-page script blocks. The Jalali partial
     * emits both name tables as JSON, so "no Persian anywhere" can only be
     * asserted about the markup the component actually rendered.
     */
    protected function body(string $html): string
    {
        return substr($html, strrpos($html, '</script>') + 9);
    }

    /**
     * The x-data config the server handed Alpine, decoded.
     *
     * @return array<string, mixed>
     */
    protected function config(string $html): array
    {
        preg_match('/x-data="mdsCalendar\(JSON\.parse\(\'(.*?)\'\)\)"/s', $html, $m);

        $this->assertNotEmpty($m, 'no mdsCalendar() config found on the root');

        return json_decode((string) json_decode('"'.$m[1].'"'), true);
    }

    /**
     * Cross-check a rendered grid against MajidDs\Support\Jalali.
     */
    protected function assertJalaliMonthGrid(string $html, int $jy, int $jm, int $expectedDays, int $startDay = 6): void
    {
        $all = $this->dates($html);

        $this->assertNotEmpty($all, 'the server grid rendered no day cells at all');
        $this->assertSame(0, count($all) % 7, 'the grid is not a whole number of seven-day rows');

        // Contiguity: every cell is exactly one day after the previous one.
        foreach ($all as $i => $iso) {
            if ($i === 0) {
                continue;
            }

            $this->assertSame(
                (new DateTimeImmutable($all[$i - 1]))->modify('+1 day')->format('Y-m-d'),
                $iso,
                "cell {$i} does not follow cell ".($i - 1).' by one day',
            );
        }

        // The first column is the configured start of the week (PHP's `w`).
        $this->assertSame(
            $startDay,
            (int) (new DateTimeImmutable($all[0]))->format('w'),
            'the first column is not the configured start day',
        );

        // The month's own days: exactly 1..N of the Jalali month, in order.
        $own = $this->ownDates($html);
        $this->assertCount($expectedDays, $own, "Jalali {$jy}-{$jm} should show {$expectedDays} of its own days");

        foreach ($own as $i => $iso) {
            [$gy, $gm, $gd] = array_map('intval', explode('-', $iso));

            $this->assertSame(
                [$jy, $jm, $i + 1],
                Jalali::fromGregorian($gy, $gm, $gd),
                "own-day {$i} ({$iso}) is not Jalali {$jy}-{$jm}-".($i + 1),
            );
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Structure and the root marker
    |--------------------------------------------------------------------------
    */

    public function test_renders_the_root_marker_and_a_jalali_grid(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertStringContainsString('data-mds-calendar', $html);
        $this->assertStringContainsString('data-mds-calendar-mode="single"', $html);
        $this->assertStringContainsString('data-mds-calendar-system="jalali"', $html);
        $this->assertStringContainsString('data-mds-calendar-header', $html);
        $this->assertStringContainsString('data-mds-calendar-months', $html);
        $this->assertStringContainsString('data-mds-calendar-month', $html);
        $this->assertStringContainsString('data-mds-calendar-day', $html);
        $this->assertStringContainsString('x-data="mdsCalendar(', $html);
    }

    public function test_the_anchor_month_is_the_month_of_the_value(): void
    {
        // 2026-08-20 is 29 Mordad 1405 — a month before today's Shahrivar.
        $html = $this->render('<mds:calendar value="2026-08-20" />');

        $this->assertStringContainsString('data-mds-calendar-title', $html);
        $this->assertStringContainsString('>مرداد ۱۴۰۵</div>', $html);
        $this->assertJalaliMonthGrid($html, 1405, 5, 31);
    }

    public function test_the_anchor_month_is_today_when_there_is_no_value(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertStringContainsString('>شهریور ۱۴۰۵</div>', $html);
        $this->assertJalaliMonthGrid($html, 1405, 6, 31);
    }

    public function test_the_grid_starts_the_week_on_saturday(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-24" />');

        // Shahrivar 1, 1405 is 2026-08-23, a Sunday, so one Mordad day leads.
        $dates = $this->dates($html);
        $this->assertSame('2026-08-22', $dates[0]);
        $this->assertStringContainsString('data-outside', $html);
        $this->assertMatchesRegularExpression('/data-outside[^>]*data-date="2026-08-22"/s', $html);
        $this->assertStringContainsString('aria-label="شنبه ۳۱ مرداد ۱۴۰۵"', $html);
    }

    public function test_a_month_starting_on_saturday_has_no_leading_neighbours(): void
    {
        // Farvardin 1, 1405 = 2026-03-21, itself a Saturday.
        $html = $this->render('<mds:calendar value="2026-03-21" />');

        $this->assertSame('2026-03-21', $this->dates($html)[0]);
        $this->assertJalaliMonthGrid($html, 1405, 1, 31);
    }

    /*
    |--------------------------------------------------------------------------
    | The Jalali calendar itself — leap years and month boundaries
    |--------------------------------------------------------------------------
    */

    public function test_esfand_has_thirty_days_in_a_leap_year(): void
    {
        $this->assertTrue(Jalali::isLeapYear(1403), 'the fixture year is meant to be a leap year');

        // 2025-03-10 is 20 Esfand 1403.
        $html = $this->render('<mds:calendar value="2025-03-10" />');

        $this->assertStringContainsString('>اسفند ۱۴۰۳</div>', $html);
        $this->assertJalaliMonthGrid($html, 1403, 12, 30);

        // The leap day itself: 30 Esfand 1403 = 2025-03-20.
        $this->assertContains('2025-03-20', $this->ownDates($html));
        $this->assertStringContainsString('aria-label="پنجشنبه ۳۰ اسفند ۱۴۰۳"', $html);
        // …and Nowruz belongs to the next year, so it can only be a neighbour.
        $this->assertNotContains('2025-03-21', $this->ownDates($html));
    }

    public function test_a_leap_esfand_grows_the_grid_by_a_row(): void
    {
        // Esfand 1399 opens on a Friday, so its 30th day needs a sixth row —
        // the one month where the leap rule changes the grid's height and not
        // just its last cell. 2021-02-28 is 10 Esfand 1399.
        $this->assertTrue(Jalali::isLeapYear(1399));

        $html = $this->render('<mds:calendar value="2021-02-28" />');

        $this->assertCount(42, $this->dates($html));
        $this->assertJalaliMonthGrid($html, 1399, 12, 30);

        // The common year before it fits in five.
        $this->assertFalse(Jalali::isLeapYear(1398));
        $this->assertCount(35, $this->dates($this->render('<mds:calendar value="2020-03-10" />')));
    }

    public function test_esfand_has_twenty_nine_days_in_a_common_year(): void
    {
        $this->assertFalse(Jalali::isLeapYear(1405), 'the fixture year is meant to be a common year');

        // 2027-03-01 is 10 Esfand 1405.
        $html = $this->render('<mds:calendar value="2027-03-01" />');

        $this->assertStringContainsString('>اسفند ۱۴۰۵</div>', $html);
        $this->assertJalaliMonthGrid($html, 1405, 12, 29);

        $own = $this->ownDates($html);
        $this->assertSame('2027-03-20', end($own));
        $this->assertNotContains('2027-03-21', $own);
    }

    public function test_the_six_month_boundary_keeps_thirty_one_day_months(): void
    {
        // Shahrivar (31 days) then Mehr (30) — the seam of the Jalali year.
        $shahrivar = $this->render('<mds:calendar value="2026-09-22" />');
        $this->assertJalaliMonthGrid($shahrivar, 1405, 6, 31);
        $own = $this->ownDates($shahrivar);
        $this->assertSame('2026-09-22', end($own));

        $mehr = $this->render('<mds:calendar value="2026-09-23" />');
        $this->assertStringContainsString('>مهر ۱۴۰۵</div>', $mehr);
        $this->assertJalaliMonthGrid($mehr, 1405, 7, 30);
        $this->assertSame('2026-09-23', $this->ownDates($mehr)[0]);
    }

    public function test_the_gregorian_calendar_can_be_asked_for_instead(): void
    {
        $html = $this->render('<mds:calendar calendar="gregorian" value="2026-08-20" />');

        $this->assertStringContainsString('data-mds-calendar-system="gregorian"', $html);
        $this->assertStringContainsString('>اوت ۲۰۲۶</div>', $html);
        // The Jalali partial always ships both name tables, so the claim is
        // about the markup the component rendered, not the whole page.
        $this->assertStringNotContainsString('مرداد', $this->body($html));

        // The ISO week opens on Monday when the grid is Gregorian.
        $this->assertSame(1, (int) (new DateTimeImmutable($this->dates($html)[0]))->format('w'));
        $this->assertCount(31, $this->ownDates($html));
        $this->assertSame('2026-08-01', $this->ownDates($html)[0]);
    }

    public function test_start_day_can_be_overridden(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" :start-day="0" />');

        $this->assertSame(0, (int) (new DateTimeImmutable($this->dates($html)[0]))->format('w'));
        $this->assertJalaliMonthGrid($html, 1405, 5, 31, startDay: 0);
        // The header follows: Sunday first.
        $this->assertMatchesRegularExpression('/<thead>.*?abbr="یکشنبه"/s', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Props
    |--------------------------------------------------------------------------
    */

    public function test_months_renders_several_grids_with_captions(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" :months="3" />');

        $this->assertSame(3, substr_count($this->serverGrid($html), 'data-mds-calendar-month>'));
        $this->assertStringContainsString('>مرداد ۱۴۰۵</caption>', $html);
        $this->assertStringContainsString('>شهریور ۱۴۰۵</caption>', $html);
        $this->assertStringContainsString('>مهر ۱۴۰۵</caption>', $html);
        // Each grid names itself, and the shared title moves out of sight.
        $this->assertStringContainsString('aria-label="تقویم — مرداد ۱۴۰۵"', $html);
        $this->assertStringContainsString('aria-label="تقویم — مهر ۱۴۰۵"', $html);
        // …and the shared header title steps out of sight.
        $this->assertStringContainsString('class="text-sm font-semibold text-zinc-800 dark:text-white sr-only"', $html);
    }

    public function test_months_is_clamped_to_a_year(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" :months="99" />');

        $this->assertSame(12, substr_count($this->serverGrid($html), 'data-mds-calendar-month>'));
    }

    public function test_week_numbers_adds_a_row_header_column(): void
    {
        $plain = $this->render('<mds:calendar value="2026-03-21" />');
        $this->assertStringNotContainsString('data-mds-calendar-week', $plain);

        $html = $this->render('<mds:calendar value="2026-03-21" week-numbers />');

        $this->assertStringContainsString('data-mds-calendar-week', $html);
        $this->assertStringContainsString('<span class="sr-only">هفته</span>', $html);
        // Farvardin 1 opens the Jalali year, so its row is week one.
        $this->assertMatchesRegularExpression('/data-mds-calendar-week>۱</', $html);
    }

    public function test_fixed_weeks_always_draws_six_rows(): void
    {
        // Farvardin 1405 fits in five rows; fixed-weeks pads it to six.
        $loose = $this->render('<mds:calendar value="2026-03-21" />');
        $this->assertCount(35, $this->dates($loose));

        $fixed = $this->render('<mds:calendar value="2026-03-21" fixed-weeks />');
        $this->assertCount(42, $this->dates($fixed));
        $this->assertJalaliMonthGrid($fixed, 1405, 1, 31);
    }

    public function test_selectable_header_swaps_the_title_for_two_selects(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" selectable-header />');

        $this->assertStringContainsString('data-mds-calendar-selects', $html);
        $this->assertStringContainsString('data-mds-calendar-month-select', $html);
        $this->assertStringContainsString('data-mds-calendar-year-select', $html);
        $this->assertStringNotContainsString('data-mds-calendar-title', $html);
        $this->assertStringContainsString('x-model.number="month"', $html);
        $this->assertStringContainsString('x-model.number="year"', $html);
        // Month values are 1-based, and the anchor month is preselected.
        $this->assertStringContainsString('<option value="5" selected>مرداد</option>', $html);
        $this->assertStringContainsString('<option value="1405" selected>۱۴۰۵</option>', $html);
    }

    public function test_selectable_header_year_range_follows_min_and_max(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" selectable-header min="2026-01-01" max="2027-12-31" />');

        preg_match_all('/<option value="(1[34]\d\d)"/', $html, $matches);
        $years = array_map('intval', array_unique($matches[1]));

        $this->assertSame(1404, min($years));
        $this->assertSame(1406, max($years));
    }

    public function test_with_today_renders_a_today_shortcut(): void
    {
        $plain = $this->render('<mds:calendar />');
        $this->assertStringNotContainsString('data-mds-calendar-today', $plain);

        $html = $this->render('<mds:calendar with-today />');

        $this->assertStringContainsString('data-mds-calendar-today', $html);
        $this->assertStringContainsString('>امروز</button>', $html);
        $this->assertStringContainsString('x-on:click="goToday()"', $html);
    }

    public function test_size_scales_the_cells(): void
    {
        $this->assertStringContainsString('size-7 text-xs', $this->render('<mds:calendar size="sm" />'));
        $this->assertStringContainsString('size-10 text-base', $this->render('<mds:calendar size="lg" />'));
        $this->assertStringContainsString('size-8 text-sm', $this->render('<mds:calendar />'));
    }

    public function test_min_and_max_disable_days_and_the_navigation(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" min="2026-08-10" max="2026-08-25" />');

        $this->assertMatchesRegularExpression('/aria-disabled="true"[^>]*data-disabled[^>]*data-date="2026-08-09"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/data-disabled[^>]*data-date="2026-08-10"/s', $html);
        $this->assertMatchesRegularExpression('/data-disabled[^>]*data-date="2026-08-26"/s', $html);

        // The Mordad grid spans 2026-07-18 … 2026-08-28 (six rows): 23 cells
        // fall before min and 3 after max.
        $this->assertSame(26, substr_count($this->serverGrid($html), 'data-disabled'));
    }

    public function test_previous_is_disabled_at_the_lower_bound(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" min="2026-08-01" />');

        $this->assertMatchesRegularExpression('/<button[^>]*x-bind:disabled="! canPrev"[^>]*\sdisabled[^>]*data-mds-calendar-prev/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*x-bind:disabled="! canNext"[^>]*\sdisabled[^>]*data-mds-calendar-next/s', $html);
    }

    public function test_next_is_disabled_at_the_upper_bound(): void
    {
        // Shahrivar 1405 opens on 2026-08-23; a max before it closes the door.
        $html = $this->render('<mds:calendar value="2026-08-20" max="2026-08-22" />');

        $this->assertMatchesRegularExpression('/<button[^>]*x-bind:disabled="! canNext"[^>]*\sdisabled[^>]*data-mds-calendar-next/s', $html);
    }

    public function test_unavailable_days_are_disabled_without_moving_the_bounds(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" :unavailable="[\'2026-08-21\', \'2026-08-22\']" />');

        $this->assertMatchesRegularExpression('/data-disabled[^>]*data-date="2026-08-21"/s', $html);
        $this->assertMatchesRegularExpression('/data-disabled[^>]*data-date="2026-08-22"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/data-disabled[^>]*data-date="2026-08-20"/s', $html);
        $this->assertSame(['2026-08-21', '2026-08-22'], $this->config($html)['unavailable']);
    }

    public function test_static_marks_the_grid_read_only(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" static />');

        $this->assertStringContainsString('data-static', $html);
        $this->assertStringContainsString('aria-readonly="true"', $html);
        $this->assertTrue($this->config($html)['static']);
    }

    public function test_label_names_the_grid_and_defaults_to_persian(): void
    {
        $this->assertStringContainsString('aria-label="تقویم"', $this->render('<mds:calendar />'));
        $this->assertStringContainsString('aria-label="سررسید"', $this->render('<mds:calendar label="سررسید" />'));
    }

    public function test_name_lands_on_the_hidden_control(): void
    {
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*name="due"/s', $this->render('<mds:calendar name="due" />'));
        $this->assertMatchesRegularExpression('/<select[^>]*name="days\[\]"/s', $this->render('<mds:calendar mode="multiple" name="days" />'));
        $this->assertMatchesRegularExpression('/<input[^>]*name="trip\[start\]"/s', $this->render('<mds:calendar mode="range" name="trip" />'));
        $this->assertMatchesRegularExpression('/<input[^>]*name="trip\[end\]"/s', $this->render('<mds:calendar mode="range" name="trip" />'));
    }

    /*
    |--------------------------------------------------------------------------
    | Modes
    |--------------------------------------------------------------------------
    */

    public function test_single_mode_marks_the_selection(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" />');

        $this->assertMatchesRegularExpression('/data-selected[^>]*data-date="2026-08-20"/s', $html);
        $this->assertSame(1, substr_count($this->serverGrid($html), 'data-selected'));
        $this->assertMatchesRegularExpression('/<td[^>]*aria-selected="true"/s', $html);
        $this->assertSame(1, substr_count($this->serverGrid($html), 'aria-selected="true"'));
        $this->assertMatchesRegularExpression('/<input\s[^>]*type="hidden"[^>]*value="2026-08-20"/s', $html);
    }

    public function test_multiple_mode_uses_a_hidden_multiple_select(): void
    {
        $html = $this->render('<mds:calendar mode="multiple" :value="[\'2026-08-22\', \'2026-08-20\']" />');

        $this->assertStringContainsString('data-mds-calendar-mode="multiple"', $html);
        $this->assertMatchesRegularExpression('/<select\s[^>]*multiple[^>]*class="sr-only"/s', $html);
        $this->assertStringContainsString('data-mds-calendar-select', $html);
        // Sorted, so the server and Alpine agree on order.
        $this->assertMatchesRegularExpression('/<option value="2026-08-20" selected>.*<option value="2026-08-22" selected>/s', $html);
        $this->assertSame(2, substr_count($this->serverGrid($html), 'data-selected'));
    }

    public function test_multiple_mode_drops_duplicates(): void
    {
        $html = $this->render('<mds:calendar mode="multiple" :value="[\'2026-08-20\', \'2026-08-20\']" />');

        $this->assertSame(1, substr_count($html, '<option value="2026-08-20" selected>'));
    }

    public function test_range_mode_renders_two_hidden_inputs_and_paints_the_span(): void
    {
        $html = $this->render('<mds:calendar mode="range" :value="[\'2026-08-20\', \'2026-08-23\']" />');

        $this->assertStringContainsString('data-mds-calendar-mode="range"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*data-mds-calendar-start[^>]*>/s', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*x-ref="start"[^>]*value="2026-08-20"/s', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*x-ref="end"[^>]*value="2026-08-23"/s', $html);
        $this->assertMatchesRegularExpression('/data-range-start[^>]*data-date="2026-08-20"/s', $html);
        $this->assertMatchesRegularExpression('/data-range-end[^>]*data-date="2026-08-23"/s', $html);
        // The whole span is marked, endpoints included — four days, and not
        // the day either side of them.
        $this->assertSame(4, substr_count($this->serverGrid($html), 'data-in-range'));
        $this->assertMatchesRegularExpression('/data-in-range[^>]*data-date="2026-08-21"/s', $html);
        $this->assertMatchesRegularExpression('/data-in-range[^>]*data-date="2026-08-22"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/data-in-range[^>]*data-date="2026-08-19"/s', $html);
        $this->assertDoesNotMatchRegularExpression('/data-in-range[^>]*data-date="2026-08-24"/s', $html);
        // The interior stays legible: only the endpoints take the accent fill.
        $this->assertSame(2, substr_count($this->serverGrid($html), 'bg-accent text-accent-foreground'));
    }

    public function test_range_mode_accepts_a_keyed_array_and_orders_it(): void
    {
        $keyed = $this->render('<mds:calendar mode="range" :value="[\'start\' => \'2026-08-20\', \'end\' => \'2026-08-23\']" />');
        $this->assertMatchesRegularExpression('/x-ref="start"[^>]*value="2026-08-20"/s', $keyed);

        // Backwards input is straightened out rather than rendered as nothing.
        $backwards = $this->render('<mds:calendar mode="range" :value="[\'2026-08-23\', \'2026-08-20\']" />');
        $this->assertMatchesRegularExpression('/x-ref="start"[^>]*value="2026-08-20"/s', $backwards);
        $this->assertMatchesRegularExpression('/x-ref="end"[^>]*value="2026-08-23"/s', $backwards);
    }

    public function test_an_unknown_mode_falls_back_to_single(): void
    {
        $html = $this->render('<mds:calendar mode="nonsense" />');

        $this->assertStringContainsString('data-mds-calendar-mode="single"', $html);
        $this->assertMatchesRegularExpression('/<input\s[^>]*type="hidden"[^>]*x-ref="input"/s', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | The Livewire contract
    |--------------------------------------------------------------------------
    */

    public function test_wire_model_reaches_the_hidden_input_in_single_mode(): void
    {
        $html = $this->render('<mds:calendar wire:model.live="due" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model.live="due"');
        $this->assertStringNotContainsString('wire:model.live="due"', substr($html, 0, strpos($html, '<input')));
    }

    public function test_wire_model_reaches_the_select_in_multiple_mode(): void
    {
        $html = $this->render('<mds:calendar mode="multiple" wire:model="days" />');

        $this->assertBindingReachesControl($html, 'select[^>]*multiple', 'wire:model="days"');
    }

    public function test_wire_model_becomes_two_dotted_paths_in_range_mode(): void
    {
        $html = $this->render('<mds:calendar mode="range" wire:model.live="trip" />');

        $this->assertBindingReachesControl($html, 'input[^>]*x-ref="start"', 'wire:model.live="trip.start"');
        $this->assertBindingReachesControl($html, 'input[^>]*x-ref="end"', 'wire:model.live="trip.end"');
        // The caller's own property name never survives on its own.
        $this->assertStringNotContainsString('wire:model.live="trip"', $html);
    }

    public function test_the_wrapper_keeps_every_other_attribute(): void
    {
        $html = $this->render('<mds:calendar id="picker" class="shadow-lg" wire:model="due" />');

        $this->assertMatchesRegularExpression('/<div\s[^>]*id="picker"[^>]*data-mds-calendar\b/s', $html);
        $this->assertStringContainsString('shadow-lg', $html);
    }

    public function test_alpine_state_is_re_synced_from_the_hidden_control(): void
    {
        $html = $this->render('<mds:calendar wire:model="due" />');

        // Livewire's morph patches the control's value attribute; without the
        // observer Alpine would keep painting the stale selection.
        $this->assertStringContainsString('new MutationObserver(() => this.resync())', $html);
        $this->assertStringContainsString("attributeFilter: ['value']", $html);
        $this->assertStringContainsString('observer.disconnect()', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Validation
    |--------------------------------------------------------------------------
    */

    public function test_an_explicit_error_renders_and_marks_the_root_invalid(): void
    {
        $html = $this->render('<mds:calendar name="due" error="یک روز را انتخاب کنید." />');

        $this->assertStringContainsString('یک روز را انتخاب کنید.', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('data-flux-error', $html);
        $this->assertStringContainsString('data-invalid', $html);
        $this->assertStringContainsString('border-red-500', $html);
        // The message is wired to the grid it belongs to.
        preg_match('/id="(mds-calendar-error-[0-9a-f]{8})"/', $html, $m);
        $this->assertNotEmpty($m, 'the error message has no id to point at');
        $this->assertStringContainsString('aria-describedby="'.$m[1].'"', $html);
    }

    public function test_invalid_alone_reddens_the_border_without_a_message(): void
    {
        $html = $this->render('<mds:calendar invalid />');

        $this->assertStringContainsString('data-invalid', $html);
        $this->assertStringContainsString('border-red-500', $html);
        $this->assertStringNotContainsString('role="alert"', $html);
    }

    public function test_a_valid_calendar_is_not_marked_invalid(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertStringNotContainsString('data-invalid', $html);
        $this->assertStringNotContainsString('border-red-500', $html);
        $this->assertStringContainsString('border-zinc-200', $html);
    }

    public function test_falls_back_to_the_validation_error_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['due' => ['تاریخ تحویل الزامی است.']]));

        // ShareErrorsFromSession shares the bag view-wide; mirror that here.
        View::share('errors', $bag);

        $html = $this->render('<mds:calendar name="due" />');

        $this->assertStringContainsString('تاریخ تحویل الزامی است.', $html);
        $this->assertStringContainsString('data-invalid', $html);
    }

    public function test_an_explicit_error_beats_the_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['due' => ['از کیف اعتبارسنجی']]));

        View::share('errors', $bag);

        $html = $this->render('<mds:calendar name="due" error="پیام صریح" />');

        $this->assertStringContainsString('پیام صریح', $html);
        $this->assertStringNotContainsString('از کیف اعتبارسنجی', $html);
    }

    public function test_an_unrelated_bag_key_leaves_the_calendar_alone(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['other' => ['یک خطای دیگر']]));

        View::share('errors', $bag);

        $html = $this->render('<mds:calendar name="due" />');

        $this->assertStringNotContainsString('یک خطای دیگر', $html);
        $this->assertStringNotContainsString('data-invalid', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Accessibility
    |--------------------------------------------------------------------------
    */

    public function test_follows_the_wai_aria_grid_pattern(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" week-numbers />');

        $this->assertStringContainsString('role="grid"', $html);
        $this->assertStringContainsString('role="row"', $html);
        $this->assertStringContainsString('role="gridcell"', $html);
        $this->assertStringContainsString('<th scope="col"', $html);
        $this->assertStringContainsString('<th scope="row"', $html);
        $this->assertStringContainsString('aria-selected="false"', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('aria-atomic="true"', $html);
    }

    public function test_exactly_one_cell_is_reachable_by_tab(): void
    {
        // The selection when it is on screen…
        $selected = $this->render('<mds:calendar value="2026-08-20" />');
        $this->assertSame(1, substr_count($selected, 'tabindex="0"'));
        $this->assertMatchesRegularExpression('/tabindex="0"[^>]*data-date="2026-08-20"/s', $selected);

        // …today when it is not…
        $none = $this->render('<mds:calendar />');
        $this->assertSame(1, substr_count($none, 'tabindex="0"'));
        $this->assertMatchesRegularExpression('/tabindex="0"[^>]*data-date="2026-08-24"/s', $none);

        // …and the 1st of the month when neither is.
        $far = $this->render('<mds:calendar value="2020-05-10" />');
        $this->assertSame(1, substr_count($far, 'tabindex="0"'));
        $this->assertMatchesRegularExpression('/tabindex="0"[^>]*data-date="2020-05-10"/s', $far);
    }

    public function test_today_is_marked_with_aria_current(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertMatchesRegularExpression('/aria-current="date"[^>]*data-today[^>]*data-date="2026-08-24"/s', $html);
        $this->assertSame(1, substr_count($html, 'aria-current="date"'));
        $this->assertStringContainsString('ring-1 ring-inset ring-accent', $html);
    }

    public function test_every_day_carries_a_spoken_date(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" />');

        // Weekday, day, month, year — not just the bare digit in the button.
        $this->assertStringContainsString('aria-label="پنجشنبه ۲۹ مرداد ۱۴۰۵"', $html);
        $this->assertStringContainsString('aria-label="یکشنبه ۱ شهریور ۱۴۰۵"', $html);
    }

    public function test_weekday_headers_carry_the_full_name_beside_the_initial(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertStringContainsString('abbr="شنبه"', $html);
        $this->assertStringContainsString('<span aria-hidden="true">ش</span><span class="sr-only">شنبه</span>', $html);
        $this->assertSame(7, substr_count($this->serverGrid($html), '<th scope="col"'));
    }

    public function test_the_keyboard_grid_bindings_are_present(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertStringContainsString('x-on:keydown="keydown($event)"', $html);

        foreach (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home', 'End', 'PageUp', 'PageDown'] as $key) {
            $this->assertStringContainsString("case '{$key}':", $html, "the grid has no handler for {$key}");
        }

        // Horizontal arrows follow the visual order, read at keydown.
        $this->assertStringContainsString('getComputedStyle(this.$root).direction === \'rtl\'', $html);
        $this->assertStringContainsString('event.preventDefault()', $html);
    }

    public function test_the_roving_focus_survives_the_redraw_that_a_key_causes(): void
    {
        $html = $this->render('<mds:calendar />');

        // Two things the browser proved are load-bearing, and neither shows up
        // in the rendered grid, so they are asserted against the script:
        //
        // 1. `$root` is read synchronously. A keydown is evaluated in the scope
        //    of the cell it happened on; once the redraw destroys that cell the
        //    scope stops answering for `$root`, and a deferred read is
        //    undefined — the focus move vanished into a swallowed TypeError.
        // 2. The lookup is scoped to the live grid: the server grid is still in
        //    the DOM (x-show only hid it) and comes first in document order,
        //    and focus() on a display:none button does nothing.
        $this->assertStringContainsString('const root = this.$root', $html);
        $this->assertStringContainsString("const land = () => root.querySelector('[data-mds-calendar-live] [data-mds-calendar-day][data-date=\"' + iso + '\"]:not([data-outside])')", $html);
        $this->assertStringNotContainsString('this.$root.querySelector', $html);
    }

    public function test_the_focus_timer_is_cleared_on_teardown(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertStringContainsString('clearTimeout(this.focusTimer)', $html);
        $this->assertMatchesRegularExpression('/destroy\(\) \{.*?clearTimeout\(this\.focusTimer\).*?\},/s', $html);
    }

    public function test_focus_rings_and_reduced_motion_are_respected(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertStringContainsString('focus-visible:outline-accent', $html);
        $this->assertStringContainsString('motion-reduce:transition-none', $html);
    }

    public function test_uses_only_logical_direction_utilities(): void
    {
        $html = $this->render('<mds:calendar mode="range" :value="[\'2026-08-20\', \'2026-08-23\']" size="lg" week-numbers with-today selectable-header />');

        // The class strings the view owns: no ml-/mr-/pl-/pr-/left-/right-.
        $this->assertDoesNotMatchRegularExpression('/\sclass="[^"]*\b(ml|mr|pl|pr)-\d/', $html);
        $this->assertStringContainsString('rtl:rotate-180', $html);
    }

    /*
    |--------------------------------------------------------------------------
    | Digits and microcopy — Persian by default, English when it is off
    |--------------------------------------------------------------------------
    */

    public function test_digits_are_persian_by_default(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" week-numbers />');

        $this->assertStringContainsString('>۲۹</button>', $html);
        $this->assertStringContainsString('>مرداد ۱۴۰۵</div>', $html);
        // The machine value stays Latin, whatever the grid shows.
        $this->assertMatchesRegularExpression('/type="hidden"[^>]*value="2026-08-20"/s', $html);
    }

    public function test_fa_false_switches_digits_names_and_microcopy_but_keeps_the_jalali_grid(): void
    {
        $html = $this->render('<mds:calendar value="2026-08-20" :fa="false" week-numbers with-today selectable-header />');

        $this->assertStringContainsString('>29</button>', $html);
        $this->assertStringContainsString('>Mordad 1405</span>', $html);
        $this->assertStringContainsString('aria-label="Previous month"', $html);
        $this->assertStringContainsString('aria-label="Next month"', $html);
        $this->assertStringContainsString('>Today</button>', $html);
        $this->assertStringContainsString('aria-label="Thursday 29 Mordad 1405"', $html);
        $this->assertStringNotContainsString('مرداد', $this->body($html));
        $this->assertStringNotContainsString('۱۴۰۵', $this->body($html));
        // Still Jalali — only the language changed.
        $this->assertStringContainsString('data-mds-calendar-system="jalali"', $html);
        $this->assertJalaliMonthGrid($html, 1405, 5, 31);
    }

    public function test_the_config_key_drives_the_language_when_fa_is_not_given(): void
    {
        config(['mds.persian_digits' => false]);

        try {
            $html = $this->render('<mds:calendar value="2026-08-20" with-today />');

            $this->assertStringContainsString('>Mordad 1405</div>', $html);
            $this->assertStringContainsString('>Today</button>', $html);
            $this->assertStringContainsString('aria-label="Calendar"', $html);

            // …and an explicit fa wins over the config, in both directions.
            $forced = $this->render('<mds:calendar value="2026-08-20" :fa="true" />');
            $this->assertStringContainsString('>مرداد ۱۴۰۵</div>', $forced);
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }

    public function test_gregorian_month_names_translate_too(): void
    {
        $this->assertStringContainsString('>اوت ۲۰۲۶</div>', $this->render('<mds:calendar calendar="gregorian" value="2026-08-20" />'));
        $this->assertStringContainsString('>August 2026</div>', $this->render('<mds:calendar calendar="gregorian" value="2026-08-20" :fa="false" />'));
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function microcopy(): array
    {
        return [
            'calendar grid label' => ['<mds:calendar />', 'aria-label="تقویم"', 'aria-label="Calendar"'],
            'calendar previous' => ['<mds:calendar />', 'aria-label="ماه قبل"', 'aria-label="Previous month"'],
            'calendar next' => ['<mds:calendar />', 'aria-label="ماه بعد"', 'aria-label="Next month"'],
            'calendar today' => ['<mds:calendar with-today />', '>امروز</button>', '>Today</button>'],
            'calendar month select' => ['<mds:calendar selectable-header />', 'aria-label="ماه"', 'aria-label="Month"'],
            'calendar year select' => ['<mds:calendar selectable-header />', 'aria-label="سال"', 'aria-label="Year"'],
            'calendar week column' => ['<mds:calendar week-numbers />', '<span class="sr-only">هفته</span>', '<span class="sr-only">Week</span>'],
            'calendar weekday name' => ['<mds:calendar />', 'abbr="شنبه"', 'abbr="Saturday"'],
            'calendar month name' => ['<mds:calendar value="2026-08-20" />', '>مرداد ۱۴۰۵<', '>Mordad 1405<'],
            'calendar spoken date' => ['<mds:calendar value="2026-08-20" />', 'aria-label="پنجشنبه ۲۹ مرداد ۱۴۰۵"', 'aria-label="Thursday 29 Mordad 1405"'],
        ];
    }

    #[DataProvider('microcopy')]
    public function test_microcopy_is_persian_by_default(string $template, string $persian, string $english): void
    {
        $html = $this->render($template);

        $this->assertStringContainsString($persian, $html);
        $this->assertStringNotContainsString($english, $html);
    }

    #[DataProvider('microcopy')]
    public function test_microcopy_switches_to_english(string $template, string $persian, string $english): void
    {
        config(['mds.persian_digits' => false]);

        try {
            $html = $this->render($template);

            $this->assertStringContainsString($english, $html);
            $this->assertStringNotContainsString($persian, $html);
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | The script block
    |--------------------------------------------------------------------------
    */

    public function test_the_script_is_registered_once_per_page(): void
    {
        $html = $this->render('<mds:calendar /><mds:calendar mode="range" /><mds:calendar mode="multiple" />');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsCalendar'"));
        $this->assertSame(1, substr_count($html, 'window.mds.registerCalendar = '));
        // The shared partials come along exactly once too.
        $this->assertSame(1, substr_count($html, 'window.mds.jalali = {'));
        $this->assertSame(1, substr_count($html, 'window.mds.digits = '));
        // …while every instance still gets its own root.
        $this->assertSame(3, substr_count($html, 'data-mds-calendar-mode='));
    }

    public function test_the_script_survives_a_wire_navigate_visit(): void
    {
        $html = $this->render('<mds:calendar />');

        $this->assertStringContainsString('if (window.mds.calendarRegistered) return', $html);
        $this->assertStringContainsString('window.mds.calendarRegistered = true', $html);
        $this->assertStringContainsString('if (window.Alpine) {', $html);
        $this->assertStringContainsString("document.addEventListener('alpine:init'", $html);
    }

    public function test_the_view_carries_no_digit_map_of_its_own(): void
    {
        $source = file_get_contents(__DIR__.'/../../resources/views/mds/calendar.blade.php');

        $this->assertStringNotContainsString('۰۱۲۳۴۵۶۷۸۹', $source);
        $this->assertStringContainsString("@include('mds::partials.digits')", $source);
        $this->assertStringContainsString("@include('mds::partials.jalali')", $source);
    }

    public function test_the_alpine_config_mirrors_the_server_state(): void
    {
        $config = $this->config($this->render('<mds:calendar value="2026-08-20" :months="2" />'));

        $this->assertTrue($config['jalali']);
        $this->assertSame(2, $config['months']);
        $this->assertSame(6, $config['startDay']);
        $this->assertSame(1405, $config['year']);
        $this->assertSame(5, $config['month']);
        $this->assertSame('2026-08-24', $config['today']);
        $this->assertSame(['2026-08-20'], $config['selected']);
        // The names come from the same PHP constants the server grid used, so
        // the Alpine redraw cannot disagree with the first paint.
        $this->assertSame(array_values(Jalali::MONTHS), $config['monthNames']);
        $this->assertSame(array_values(Jalali::WEEKDAYS), $config['weekdayNames']);
    }
}
