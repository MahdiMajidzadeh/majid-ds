<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;

class KanbanTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * The kit's Livewire contract: the binding reaches the real form element
     * and the wrapper keeps no copy of it.
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
     * Render with the kit switched to English, restoring Persian afterwards.
     */
    protected function renderEnglish(string $template): string
    {
        config(['mds.persian_digits' => false]);

        try {
            return $this->render($template);
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }

    protected function board(string $columns): string
    {
        return '<mds:kanban>'.$columns.'</mds:kanban>';
    }

    public function test_board_is_a_labelled_group_with_a_live_region_and_a_hint(): void
    {
        $html = $this->render('<mds:kanban />');

        $this->assertStringContainsString('data-mds-kanban', $html);
        $this->assertStringContainsString('role="group"', $html);
        $this->assertStringContainsString('aria-label="تخته کانبان"', $html);

        // The announcements go through one polite status region…
        $this->assertStringContainsString('data-mds-kanban-live', $html);
        $this->assertStringContainsString('role="status"', $html);
        $this->assertStringContainsString('aria-live="polite"', $html);
        $this->assertStringContainsString('x-text="announcement"', $html);

        // …and every card is described by the keyboard instructions, wired at
        // runtime so two boards on a page cannot collide on the id.
        $this->assertStringContainsString('data-mds-kanban-hint', $html);
        $this->assertStringContainsString('x-bind:id="$id(\'mds-kanban-hint\')"', $html);
        $this->assertStringContainsString('x-id="[\'mds-kanban-hint\']"', $html);
    }

    public function test_board_label_can_be_overridden(): void
    {
        $html = $this->render('<mds:kanban label="تخته اسپرینت" />');

        $this->assertStringContainsString('aria-label="تخته اسپرینت"', $html);
        $this->assertStringNotContainsString('aria-label="تخته کانبان"', $html);
    }

    public function test_disabled_marks_the_board_and_is_handed_to_alpine(): void
    {
        $html = $this->render('<mds:kanban disabled />');

        $this->assertStringContainsString('data-disabled', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('disabled: true }', $html);

        $enabled = $this->render('<mds:kanban />');

        $this->assertStringNotContainsString('aria-disabled="true"', $enabled);
        $this->assertStringContainsString('disabled: false }', $enabled);
    }

    public function test_column_is_a_labelled_region_carrying_its_card_count(): void
    {
        $html = $this->render($this->board(
            '<mds:kanban.column key="todo" heading="در انتظار">
                <mds:kanban.card key="1">یک</mds:kanban.card>
                <mds:kanban.card key="2">دو</mds:kanban.card>
            </mds:kanban.column>'
        ));

        $this->assertStringContainsString('data-mds-kanban-column="todo"', $html);
        $this->assertStringContainsString('role="region"', $html);
        $this->assertStringContainsString('aria-label="در انتظار — ۲ کارت"', $html);
        $this->assertStringContainsString('>در انتظار</span>', $html);
        $this->assertStringContainsString('>۲ کارت</span>', $html);
        $this->assertStringContainsString('data-mds-kanban-cards', $html);
        $this->assertStringContainsString('role="list"', $html);

        // Alpine owns both after the first paint, from the same templates.
        $this->assertStringContainsString('x-bind:aria-label="regionLabel($el)"', $html);
        $this->assertStringContainsString('data-mds-kanban-count=":n کارت"', $html);
    }

    public function test_a_single_card_takes_the_singular_count(): void
    {
        $one = $this->renderEnglish($this->board(
            '<mds:kanban.column key="todo" heading="Todo"><mds:kanban.card key="1">a</mds:kanban.card></mds:kanban.column>'
        ));

        $this->assertStringContainsString('aria-label="Todo — 1 card"', $one);

        $two = $this->renderEnglish($this->board(
            '<mds:kanban.column key="todo" heading="Todo"><mds:kanban.card key="1">a</mds:kanban.card><mds:kanban.card key="2">b</mds:kanban.card></mds:kanban.column>'
        ));

        $this->assertStringContainsString('aria-label="Todo — 2 cards"', $two);
    }

    public function test_an_empty_column_shows_its_placeholder(): void
    {
        $html = $this->render($this->board('<mds:kanban.column key="todo" heading="در انتظار" />'));

        $this->assertStringContainsString('data-mds-kanban-empty', $html);
        $this->assertStringContainsString('>کارتی نیست</li>', $html);
        $this->assertStringContainsString('aria-label="در انتظار — ۰ کارت"', $html);
        $this->assertDoesNotMatchRegularExpression('/style="display: none"[^>]*data-mds-kanban-empty/', $html);

        $filled = $this->render($this->board(
            '<mds:kanban.column key="todo" heading="در انتظار"><mds:kanban.card key="1">یک</mds:kanban.card></mds:kanban.column>'
        ));

        // Still in the DOM (Alpine shows it again when the column empties),
        // but hidden from the first paint.
        $this->assertStringContainsString('>کارتی نیست</li>', $filled);
        $this->assertMatchesRegularExpression('/style="display: none"[^>]*data-mds-kanban-empty/', $filled);
    }

    public function test_empty_text_can_be_overridden(): void
    {
        $html = $this->render($this->board('<mds:kanban.column key="todo" empty="این ستون خالی است" />'));

        $this->assertStringContainsString('>این ستون خالی است</li>', $html);
        $this->assertStringNotContainsString('کارتی نیست', $html);
    }

    public function test_limit_shows_the_ratio_and_flags_a_column_that_is_over_it(): void
    {
        $within = $this->render($this->board(
            '<mds:kanban.column key="todo" heading="در انتظار" limit="2"><mds:kanban.card key="1">یک</mds:kanban.card></mds:kanban.column>'
        ));

        $this->assertStringContainsString('data-mds-kanban-limit="2"', $within);
        $this->assertStringContainsString('aria-label="در انتظار — ۱ از ۲ کارت"', $within);
        $this->assertStringNotContainsString('data-mds-kanban-over ', $within);

        $over = $this->render($this->board(
            '<mds:kanban.column key="todo" heading="در انتظار" limit="1"><mds:kanban.card key="1">یک</mds:kanban.card><mds:kanban.card key="2">دو</mds:kanban.card></mds:kanban.column>'
        ));

        $this->assertStringContainsString('data-mds-kanban-over ', $over);
        $this->assertStringContainsString('aria-label="در انتظار — ۲ از ۱ کارت — بیش از حد مجاز"', $over);

        // Never colour alone: the badge turns red AND an icon with a word
        // behind it appears.
        $this->assertStringContainsString('data-mds-kanban-warning', $over);
        $this->assertStringContainsString('<span class="sr-only">بیش از حد مجاز</span>', $over);
        $this->assertStringContainsString('bg-red-100', $over);
    }

    public function test_a_nonsense_limit_is_ignored(): void
    {
        $html = $this->render($this->board('<mds:kanban.column key="todo" heading="در انتظار" limit="0" />'));

        $this->assertStringNotContainsString('data-mds-kanban-limit', $html);
        $this->assertStringContainsString('aria-label="در انتظار — ۰ کارت"', $html);
    }

    public function test_cards_are_focusable_list_items_that_announce_their_own_role(): void
    {
        $html = $this->render($this->board(
            '<mds:kanban.column key="todo"><mds:kanban.card key="7" heading="پرداخت">توضیح</mds:kanban.card></mds:kanban.column>'
        ));

        $this->assertStringContainsString('data-mds-kanban-card="7"', $html);
        $this->assertStringContainsString('role="listitem"', $html);
        $this->assertStringContainsString('tabindex="0"', $html);
        $this->assertStringContainsString('aria-roledescription="کارت جابه‌جایی‌پذیر"', $html);
        $this->assertStringContainsString('x-bind:aria-describedby="$id(\'mds-kanban-hint\')"', $html);

        // The heading doubles as the name the announcements read out.
        $this->assertStringContainsString('data-mds-kanban-card-title="پرداخت"', $html);
        $this->assertStringContainsString('data-mds-kanban-card-heading>پرداخت</span>', $html);
        $this->assertStringContainsString('توضیح', $html);

        // The handle is decoration: the card itself is the keyboard target.
        $this->assertStringContainsString('data-mds-kanban-handle', $html);
        $this->assertStringContainsString('touch-none', $html);
        $this->assertStringContainsString('title="دستگیره جابه‌جایی"', $html);
    }

    public function test_a_card_without_a_heading_gets_no_title_attribute(): void
    {
        $html = $this->render($this->board('<mds:kanban.column key="todo"><mds:kanban.card key="7">فقط متن</mds:kanban.card></mds:kanban.column>'));

        $this->assertStringNotContainsString('data-mds-kanban-card-title', $html);
        $this->assertStringNotContainsString('data-mds-kanban-card-heading', $html);
    }

    public function test_a_disabled_card_says_so_and_loses_its_grab_cursor(): void
    {
        $html = $this->render($this->board('<mds:kanban.column key="todo"><mds:kanban.card key="7" disabled>ثابت</mds:kanban.card></mds:kanban.column>'));

        $this->assertMatchesRegularExpression('/<li[^>]*\sdata-disabled\s/', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('cursor-not-allowed', $html);
        $this->assertStringNotContainsString('cursor-grab', $html);

        // …but it is still focusable, so a reader can still read it.
        $this->assertStringContainsString('tabindex="0"', $html);
    }

    public function test_keys_are_derived_deterministically_when_none_are_given(): void
    {
        $one = $this->render($this->board('<mds:kanban.column heading="در انتظار"><mds:kanban.card>خرید</mds:kanban.card></mds:kanban.column>'));
        $two = $this->render($this->board('<mds:kanban.column heading="در انتظار"><mds:kanban.card>خرید</mds:kanban.card></mds:kanban.column>'));

        $this->assertSame($one, $two, 'A rebuilt page must be byte-identical.');

        preg_match('/data-mds-kanban-card="([^"]+)"/', $one, $card);
        preg_match('/data-mds-kanban-column="([^"]+)"/', $one, $column);

        $this->assertMatchesRegularExpression('/^card-[0-9a-f]{8}$/', $card[1]);
        $this->assertMatchesRegularExpression('/^c[0-9a-f]{8}$/', $column[1]);

        // Different content, different id — otherwise two cards would share a
        // slot in the board state.
        $other = $this->render($this->board('<mds:kanban.column heading="در انتظار"><mds:kanban.card>فروش</mds:kanban.card></mds:kanban.column>'));

        preg_match('/data-mds-kanban-card="([^"]+)"/', $other, $second);

        $this->assertNotSame($card[1], $second[1]);
    }

    public function test_the_column_key_falls_back_to_the_name(): void
    {
        $html = $this->render($this->board('<mds:kanban.column name="board[todo]" heading="در انتظار" />'));

        $this->assertStringContainsString('data-mds-kanban-column="board[todo]"', $html);
        $this->assertStringContainsString('data-mds-kanban-state="board[todo]"', $html);
    }

    public function test_the_hidden_select_mirrors_the_card_order(): void
    {
        $html = $this->render($this->board(
            '<mds:kanban.column key="todo" name="board[todo]">
                <mds:kanban.card key="c1">یک</mds:kanban.card>
                <mds:kanban.card key="c2">دو</mds:kanban.card>
                <mds:kanban.card key="c3">سه</mds:kanban.card>
            </mds:kanban.column>'
        ));

        // A multiple select reads back as an ARRAY — that is the whole point
        // of the shape: `board.todo` round-trips as an ordered list of ids.
        $this->assertMatchesRegularExpression('/<select[^>]*\smultiple[\s>]/', $html);
        $this->assertStringContainsString('name="board[todo][]"', $html);
        $this->assertStringContainsString('data-mds-kanban-state="todo"', $html);

        preg_match_all('/<option value="([^"]+)" selected>/', $html, $options);

        $this->assertSame(['c1', 'c2', 'c3'], $options[1]);

        // Hidden, so it is neither a tab stop nor read out.
        $this->assertMatchesRegularExpression('/<select[^>]*\shidden[\s>]/', $html);
        $this->assertMatchesRegularExpression('/<select[^>]*\saria-hidden="true"[\s>]/', $html);
    }

    public function test_a_column_without_a_name_still_tracks_its_order(): void
    {
        $html = $this->render($this->board(
            '<mds:kanban.column key="todo"><mds:kanban.card key="c1">یک</mds:kanban.card></mds:kanban.column>'
        ));

        $this->assertStringContainsString('data-mds-kanban-state="todo"', $html);
        $this->assertStringNotContainsString('name="', $html);
        $this->assertStringContainsString('<option value="c1" selected>', $html);
    }

    public function test_wire_model_reaches_the_hidden_select_and_not_the_wrapper(): void
    {
        // A modifier has to survive too — the view filters on the wire:model
        // prefix, not on the exact attribute name.
        $html = $this->render($this->board(
            '<mds:kanban.column key="todo" wire:model.live="board.todo"><mds:kanban.card key="c1">یک</mds:kanban.card></mds:kanban.column>'
        ));

        $this->assertBindingReachesControl($html, 'select', 'wire:model.live="board.todo"');
    }

    public function test_each_column_binds_its_own_slice_of_the_board(): void
    {
        $html = $this->render($this->board(
            '<mds:kanban.column key="todo" wire:model="board.todo" />
             <mds:kanban.column key="doing" wire:model="board.doing" />'
        ));

        $this->assertBindingReachesControl($html, 'select', 'wire:model="board.todo"');
        $this->assertBindingReachesControl($html, 'select', 'wire:model="board.doing"');
    }

    public function test_column_attributes_land_on_the_region_not_the_control(): void
    {
        $html = $this->render($this->board('<mds:kanban.column key="todo" class="w-96" id="todo-column" />'));

        $this->assertMatchesRegularExpression('/<div[^>]*class="[^"]*w-96[^"]*"[^>]*role="region"/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*id="todo-column"[^>]*role="region"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<select[^>]*w-96/', $html);
    }

    public function test_an_explicit_error_renders_the_message_and_marks_the_column(): void
    {
        $html = $this->render($this->board('<mds:kanban.column key="todo" error="این ستون پر است" />'));

        $this->assertStringContainsString('data-flux-error', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('این ستون پر است', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('border-red-500', $html);
    }

    public function test_invalid_marks_the_column_without_a_message(): void
    {
        $html = $this->render($this->board('<mds:kanban.column key="todo" invalid />'));

        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('border-red-500', $html);
        $this->assertStringNotContainsString('data-flux-error', $html);
    }

    public function test_the_error_falls_back_to_the_validation_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['board.todo' => ['ستون در انتظار پر است']]));

        View::share('errors', $bag);

        try {
            $html = $this->render($this->board('<mds:kanban.column key="todo" name="board.todo" />'));

            $this->assertStringContainsString('ستون در انتظار پر است', $html);
            $this->assertStringContainsString('aria-invalid="true"', $html);
        } finally {
            View::share('errors', new ViewErrorBag);
        }
    }

    public function test_the_error_bag_fallback_also_reads_the_wildcard_key(): void
    {
        // An array rule reports against board.todo.0, board.todo.1, …
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['board.todo.1' => ['کارت نامعتبر است']]));

        View::share('errors', $bag);

        try {
            $html = $this->render($this->board('<mds:kanban.column key="todo" name="board.todo" />'));

            $this->assertStringContainsString('کارت نامعتبر است', $html);
        } finally {
            View::share('errors', new ViewErrorBag);
        }
    }

    public function test_an_explicit_error_wins_over_the_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['board.todo' => ['از کیف']]));

        View::share('errors', $bag);

        try {
            $html = $this->render($this->board('<mds:kanban.column key="todo" name="board.todo" error="صریح" />'));

            $this->assertStringContainsString('صریح', $html);
            $this->assertStringNotContainsString('از کیف', $html);
        } finally {
            View::share('errors', new ViewErrorBag);
        }
    }

    public function test_every_built_in_string_switches_to_english(): void
    {
        $html = $this->renderEnglish(
            '<mds:kanban><mds:kanban.column key="todo" heading="Todo" limit="1">
                <mds:kanban.card key="c1" heading="Card">body</mds:kanban.card>
                <mds:kanban.card key="c2">two</mds:kanban.card>
            </mds:kanban.column><mds:kanban.column key="done" heading="Done" /></mds:kanban>'
        );

        $this->assertStringContainsString('aria-label="Kanban board"', $html);
        $this->assertStringContainsString('Press Space or Enter to pick up a card', $html);
        $this->assertStringContainsString('aria-roledescription="Draggable card"', $html);
        $this->assertStringContainsString('title="Drag handle"', $html);
        $this->assertStringContainsString('>No cards</li>', $html);
        $this->assertStringContainsString('aria-label="Todo — 2 of 1 cards — Over limit"', $html);
        $this->assertStringContainsString('<span class="sr-only">Over limit</span>', $html);
        $this->assertStringContainsString('data-drop="“:card” dropped at position :index of :total in “:column”."', $html);

        // Latin digits everywhere, and not a Persian one in sight. (The
        // shared digits partial carries the map itself, so the sweep looks at
        // the markup rather than at the scripts above it.)
        $markup = substr($html, (int) strrpos($html, '</script>'));

        $this->assertStringContainsString('>2 of 1 cards</span>', $markup);
        $this->assertStringNotContainsString('کارت', $markup);
        $this->assertDoesNotMatchRegularExpression('/[۰-۹]/u', $markup);
    }

    public function test_the_persian_announcement_templates_ride_on_the_live_region(): void
    {
        $html = $this->render('<mds:kanban />');

        $this->assertStringContainsString('data-grab="«:card» برداشته شد. جایگاه :index از :total در ستون «:column»."', $html);
        $this->assertStringContainsString('data-move="«:card» به جایگاه :index از :total در ستون «:column» رفت."', $html);
        $this->assertStringContainsString('data-cancel="جابه‌جایی «:card» لغو شد."', $html);
    }

    public function test_fa_reaches_the_subcomponents(): void
    {
        // Persian is on in config; the board says otherwise and the column and
        // the card inside it have to hear about it.
        $html = $this->render(
            '<mds:kanban :fa="false"><mds:kanban.column key="todo" heading="Todo"><mds:kanban.card key="c1">a</mds:kanban.card></mds:kanban.column></mds:kanban>'
        );

        $this->assertStringContainsString('aria-label="Kanban board"', $html);
        $this->assertStringContainsString('aria-label="Todo — 1 card"', $html);
        $this->assertStringContainsString('aria-roledescription="Draggable card"', $html);
        $this->assertStringContainsString('>No cards</li>', $html);
        $this->assertStringNotContainsString('کارت', $html);
    }

    public function test_a_column_can_override_fa_on_its_own(): void
    {
        $html = $this->render(
            '<mds:kanban><mds:kanban.column key="todo" heading="Todo" :fa="false" /></mds:kanban>'
        );

        $this->assertStringContainsString('aria-label="Todo — 0 cards"', $html);
        // The board around it stays Persian.
        $this->assertStringContainsString('aria-label="تخته کانبان"', $html);
    }

    public function test_the_actions_slot_lands_in_the_column_header(): void
    {
        $html = $this->render($this->board(
            '<mds:kanban.column key="todo"><x-slot name="actions"><button type="button">افزودن</button></x-slot></mds:kanban.column>'
        ));

        $this->assertStringContainsString('data-mds-kanban-actions', $html);
        $this->assertStringContainsString('>افزودن</button>', $html);
    }

    public function test_the_board_wires_the_keyboard_and_the_pointer(): void
    {
        $html = $this->render('<mds:kanban />');

        $this->assertStringContainsString('x-on:keydown="onKey($event)"', $html);
        $this->assertStringContainsString('x-on:focusin="onFocus($event)"', $html);
        $this->assertStringContainsString('x-on:focusout="onBlur($event)"', $html);
        $this->assertStringContainsString('x-on:pointerdown="pointerDown($event)"', $html);
        $this->assertStringContainsString('x-on:pointermove.window="pointerMove($event)"', $html);
        $this->assertStringContainsString('x-on:pointerup.window="pointerUp($event)"', $html);
        $this->assertStringContainsString('x-on:pointercancel.window="pointerCancel($event)"', $html);

        // The horizontal keys are read against the computed direction, so the
        // board follows the VISUAL order on an RTL page.
        $this->assertStringContainsString("getComputedStyle(this.\$root).direction === 'rtl'", $html);
        $this->assertStringContainsString('mds-kanban-moved', $html);
    }

    public function test_script_and_digit_helper_ship_once_per_page(): void
    {
        $html = $this->render('<mds:kanban /><mds:kanban><mds:kanban.column key="a" /></mds:kanban>');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsKanban'"));
        $this->assertSame(1, substr_count($html, 'window.mds.digits ='));
        $this->assertSame(2, substr_count($html, 'data-mds-kanban-live'));

        $views = dirname(__DIR__, 2).'/resources/views/mds/kanban/';

        foreach (['index', 'column', 'card'] as $view) {
            $this->assertStringNotContainsString('۰۱۲۳۴۵۶۷۸۹', (string) file_get_contents($views.$view.'.blade.php'));
        }
    }
}
