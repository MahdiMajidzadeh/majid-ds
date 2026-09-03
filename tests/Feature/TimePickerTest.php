<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;

class TimePickerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // flux:field and flux:label read the shared bag, as a real request would provide.
        View::share('errors', new ViewErrorBag);
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
     * Render with Persian output switched off, restoring the default afterwards.
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

    /**
     * The option values in document order.
     *
     * @return list<string>
     */
    protected function optionValues(string $html): array
    {
        preg_match_all('/<li[^>]*role="option"[^>]*data-value="([^"]*)"/', $html, $m);

        return $m[1];
    }

    public function test_it_renders_the_combobox_structure(): void
    {
        $html = $this->render('<mds:time-picker name="at" value="14:30" label="ساعت" />');

        $this->assertMatchesRegularExpression('/<div[^>]*\sdata-mds-time-picker\s*>/', $html);
        $this->assertStringContainsString('data-mds-time-picker-control>', $html);
        $this->assertStringContainsString('data-mds-time-picker-input', $html);
        $this->assertStringContainsString('data-mds-time-picker-listbox', $html);
        $this->assertStringContainsString('x-data="mdsTimePicker({', $html);

        // The visible input is the combobox, reads LTR, and is display-only (no name).
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\sdir="ltr"[^>]*\sdata-ltr[\s>]/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\sinputmode="numeric"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\srole="combobox"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\saria-autocomplete="list"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\saria-haspopup="listbox"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\sdata-flux-control/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*type="text"[^>]*\sname=/', $html);
        $this->assertStringContainsString('x-bind:aria-expanded="open ? \'true\' : \'false\'"', $html);
        $this->assertStringContainsString('x-bind:aria-controls="$id(\'mds-time-picker-listbox\')"', $html);
        $this->assertStringContainsString('x-bind:aria-activedescendant=', $html);

        // The hidden input carries the machine value and the name.
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\svalue="14:30"[^>]*\sname="at"/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*type="hidden"[^>]*data-flux-control/', $html);

        // The listbox: LTR, end-aligned in RTL forms, named by the label.
        $this->assertMatchesRegularExpression('/<ul[^>]*\sdir="ltr"[^>]*\srole="listbox"[^>]*\saria-label="ساعت"/', $html);
        $this->assertStringContainsString('rtl:text-end', $html);
        $this->assertMatchesRegularExpression('/<[a-z-]+[^>]*data-flux-label[^>]*>\s*ساعت\s*</', $html);
    }

    public function test_it_displays_persian_digits_and_keeps_the_machine_value_latin(): void
    {
        $html = $this->render('<mds:time-picker value="14:30" />');

        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\svalue="۱۴:۳۰"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\svalue="14:30"/', $html);
        $this->assertStringContainsString('data-value="14:30"', $html);
        $this->assertStringContainsString('>۱۴:۳۰</li>', $html);
        $this->assertStringNotContainsString('data-value="۱۴:۳۰"', $html);
        $this->assertStringNotContainsString('>14:30</li>', $html);
    }

    public function test_it_displays_latin_digits_when_persian_output_is_off(): void
    {
        $html = $this->renderEnglish('<mds:time-picker value="14:30" />');

        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\svalue="14:30"/', $html);
        $this->assertStringContainsString('>14:30</li>', $html);
        $this->assertStringNotContainsString('۱۴:۳۰', $html);
    }

    public function test_the_fa_prop_overrides_the_config(): void
    {
        $html = $this->render('<mds:time-picker value="14:30" :fa="false" />');

        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\svalue="14:30"/', $html);
        $this->assertStringNotContainsString('۱۴:۳۰', $html);
        $this->assertStringContainsString('fa: false', $html);
    }

    public function test_twelve_hour_display_in_persian(): void
    {
        $html = $this->render('<mds:time-picker value="14:30" hours="12" />');

        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\svalue="۲:۳۰ ب.ظ"/', $html);
        $this->assertStringContainsString('>۱۲:۰۰ ق.ظ</li>', $html);
        $this->assertStringContainsString('>۱۲:۰۰ ب.ظ</li>', $html);
        $this->assertStringContainsString('>۱۱:۳۰ ب.ظ</li>', $html);
        $this->assertStringContainsString("am: 'ق.ظ'", $this->jsDecoded($html));
        $this->assertStringContainsString("pm: 'ب.ظ'", $this->jsDecoded($html));

        // The machine value stays 24-hour.
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\svalue="14:30"/', $html);
        $this->assertStringContainsString('data-value="14:30"', $html);
        $this->assertStringContainsString('data-value="00:00"', $html);

        // Typing "pm" needs letters, so the numeric keyboard is not requested here.
        $this->assertStringNotContainsString('inputmode="numeric"', $html);
        $this->assertStringContainsString('hours: 12', $html);
    }

    public function test_twelve_hour_display_in_english(): void
    {
        $html = $this->renderEnglish('<mds:time-picker value="14:30" hours="12" />');

        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\svalue="2:30 PM"/', $html);
        $this->assertStringContainsString('>12:00 AM</li>', $html);
        $this->assertStringContainsString('>12:00 PM</li>', $html);
        $this->assertStringContainsString("am: 'AM'", $html);
        $this->assertStringContainsString("pm: 'PM'", $html);
        $this->assertStringNotContainsString("pm: 'ب.ظ'", $html);
    }

    public function test_the_list_follows_step_and_bounds(): void
    {
        $html = $this->render('<mds:time-picker step="30" min="09:00" max="11:00" />');

        $this->assertSame(['09:00', '09:30', '10:00', '10:30', '11:00'], $this->optionValues($html));
        $this->assertStringContainsString('min: 540', $html);
        $this->assertStringContainsString('max: 660', $html);
        $this->assertStringContainsString('step: 30', $html);
    }

    public function test_the_default_list_covers_the_day_every_thirty_minutes(): void
    {
        $values = $this->optionValues($this->render('<mds:time-picker />'));

        $this->assertCount(48, $values);
        $this->assertSame('00:00', $values[0]);
        $this->assertSame('23:30', $values[47]);
    }

    public function test_a_fifteen_minute_step_within_business_hours(): void
    {
        $values = $this->optionValues($this->render('<mds:time-picker step="15" min="09:00" max="17:00" />'));

        $this->assertCount(33, $values);
        $this->assertSame('09:00', $values[0]);
        $this->assertSame('09:15', $values[1]);
        $this->assertSame('17:00', $values[32]);
    }

    public function test_a_max_that_the_step_skips_is_not_listed(): void
    {
        $values = $this->optionValues($this->render('<mds:time-picker step="30" min="09:00" max="10:15" />'));

        $this->assertSame(['09:00', '09:30', '10:00'], $values);
    }

    public function test_bounds_that_cross_collapse_to_the_minimum(): void
    {
        $values = $this->optionValues($this->render('<mds:time-picker min="12:00" max="09:00" />'));

        $this->assertSame(['12:00'], $values);
    }

    public function test_a_zero_step_falls_back_to_one_minute(): void
    {
        $values = $this->optionValues($this->render('<mds:time-picker step="0" min="09:00" max="09:02" />'));

        $this->assertSame(['09:00', '09:01', '09:02'], $values);
    }

    public function test_the_selected_option_is_marked_and_the_others_are_not(): void
    {
        $html = $this->render('<mds:time-picker value="10:00" step="30" min="09:00" max="11:00" />');

        $this->assertSame(1, substr_count($html, 'aria-selected="true"'));
        $this->assertSame(4, substr_count($html, 'aria-selected="false"'));
        $this->assertMatchesRegularExpression('/<li[^>]*data-value="10:00"[^>]*\saria-selected="true"/', $html);

        // Runtime selection follows Alpine state.
        $this->assertStringContainsString('x-bind:aria-selected="isSelected($el) ? \'true\' : \'false\'"', $html);
    }

    public function test_a_value_off_the_step_is_still_shown_and_bound(): void
    {
        $html = $this->render('<mds:time-picker value="10:07" step="30" min="09:00" max="11:00" />');

        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\svalue="10:07"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\svalue="۱۰:۰۷"/', $html);
        $this->assertStringNotContainsString('aria-selected="true"', $html);
    }

    public function test_it_normalises_the_incoming_value(): void
    {
        $short = $this->render('<mds:time-picker value="9:05" />');
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\svalue="09:05"/', $short);

        $seconds = $this->render('<mds:time-picker value="09:05:33" />');
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\svalue="09:05"/', $seconds);

        $persian = $this->render('<mds:time-picker value="۱۴:۳۰" />');
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\svalue="14:30"/', $persian);
        $this->assertStringContainsString("value: '14:30'", $persian);
    }

    public function test_a_value_that_is_not_a_time_renders_empty(): void
    {
        foreach (['25:00', '10:60', 'noon', ''] as $bad) {
            $html = $this->render('<mds:time-picker value="'.$bad.'" />');

            $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\svalue=""/', $html, "[{$bad}] should not bind");
            $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\svalue=""/', $html, "[{$bad}] should not display");
            $this->assertStringNotContainsString('aria-selected="true"', $html);
        }
    }

    public function test_it_forwards_wire_model_to_the_hidden_input_only(): void
    {
        $html = $this->render('<mds:time-picker wire:model.live="time" name="time" value="14:30" class="max-w-xs" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model.live="time"');
        $this->assertMatchesRegularExpression('/<div[^>]*class="[^"]*max-w-xs[^"]*"[^>]*data-mds-time-picker\s*>/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\sx-ref="input"/', $html);

        // Morph re-sync: the hidden input is observed.
        $this->assertStringContainsString("attributeFilter: ['value']", $html);
    }

    public function test_the_name_lands_on_the_hidden_input_alone(): void
    {
        $html = $this->render('<mds:time-picker name="opens_at" />');

        $this->assertSame(1, substr_count($html, 'name="opens_at"'));
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*\sname="opens_at"/', $html);
    }

    public function test_placeholder_description_and_size(): void
    {
        $html = $this->render('<mds:time-picker />');
        $this->assertStringContainsString('placeholder="--:--"', $html);
        $this->assertStringContainsString('h-10', $html);
        $this->assertStringNotContainsString('h-8 ', $html);

        $custom = $this->render('<mds:time-picker placeholder="ساعت تحویل" description="به وقت تهران" size="sm" />');
        $this->assertStringContainsString('placeholder="ساعت تحویل"', $custom);
        $this->assertStringContainsString('به وقت تهران', $custom);
        $this->assertStringContainsString('h-8 ', $custom);
        $this->assertStringNotContainsString('h-10', $custom);
    }

    public function test_the_clock_icon_is_the_default_and_can_be_swapped_or_removed(): void
    {
        $default = $this->render('<mds:time-picker />');
        $this->assertSame(1, substr_count($default, 'data-mds-icon'));

        $swapped = $this->render('<mds:time-picker icon="calendar" />');
        $this->assertSame(1, substr_count($swapped, 'data-mds-icon'));
        $this->assertNotSame(
            substr($default, strpos($default, '<svg')),
            substr($swapped, strpos($swapped, '<svg')),
            'A different icon should render a different svg.',
        );

        $none = $this->render('<mds:time-picker :icon="false" />');
        $this->assertStringNotContainsString('data-mds-icon', $none);
    }

    public function test_clearable_adds_a_labelled_button_in_both_languages(): void
    {
        $plain = $this->render('<mds:time-picker value="14:30" />');
        $this->assertStringNotContainsString('data-mds-time-picker-clear', $plain);

        $persian = $this->render('<mds:time-picker value="14:30" clearable />');
        $this->assertMatchesRegularExpression('/<button[^>]*\saria-label="پاک کردن"[^>]*\sdata-mds-time-picker-clear/', $persian);
        $this->assertStringContainsString('x-on:click="clear()"', $persian);
        $this->assertStringContainsString('x-show="value !== \'\'"', $persian);

        $english = $this->renderEnglish('<mds:time-picker value="14:30" clearable />');
        $this->assertMatchesRegularExpression('/<button[^>]*\saria-label="Clear"[^>]*\sdata-mds-time-picker-clear/', $english);
        $this->assertStringNotContainsString('پاک کردن', $english);
    }

    public function test_disabled_freezes_the_whole_control(): void
    {
        $html = $this->render('<mds:time-picker value="14:30" clearable disabled />');

        $this->assertMatchesRegularExpression('/<div[^>]*\sinert aria-disabled="true" data-disabled[^>]*data-mds-time-picker\s*>/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\sdisabled[\s>]/', $html);
        $this->assertStringContainsString('class="relative w-full opacity-50', $html);

        $enabled = $this->render('<mds:time-picker value="14:30" />');
        $this->assertStringNotContainsString('aria-disabled', $enabled);
        $this->assertStringNotContainsString('class="relative w-full opacity-50', $enabled);
    }

    public function test_an_explicit_error_renders_the_message_and_invalid_state(): void
    {
        $html = $this->render('<mds:time-picker name="at" error="ساعت را انتخاب کنید." />');

        $this->assertStringContainsString('data-flux-error', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('ساعت را انتخاب کنید.', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\saria-invalid="true"/', $html);
        $this->assertStringContainsString('border-red-500', $html);
    }

    public function test_the_invalid_prop_marks_the_state_without_a_message(): void
    {
        $html = $this->render('<mds:time-picker invalid />');

        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\saria-invalid="true"/', $html);
        $this->assertStringContainsString('border-red-500', $html);
        $this->assertStringNotContainsString('data-flux-error', $html);

        $clean = $this->render('<mds:time-picker />');
        $this->assertStringNotContainsString('aria-invalid', $clean);
        $this->assertStringNotContainsString('border-red-500', $clean);
        $this->assertStringNotContainsString('data-flux-error', $clean);
    }

    public function test_it_falls_back_to_the_validation_error_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['at' => ['ساعت خارج از بازه است.']]));

        View::share('errors', $bag);

        $html = $this->render('<mds:time-picker name="at" />');
        $this->assertStringContainsString('ساعت خارج از بازه است.', $html);
        $this->assertStringContainsString('data-flux-error', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="text"[^>]*\saria-invalid="true"/', $html);

        // Another name stays clean...
        $other = $this->render('<mds:time-picker name="other" />');
        $this->assertStringNotContainsString('data-flux-error', $other);

        // ...and an explicit message wins over the bag.
        $explicit = $this->render('<mds:time-picker name="at" error="پیام صریح" />');
        $this->assertStringContainsString('پیام صریح', $explicit);
        $this->assertStringNotContainsString('ساعت خارج از بازه است.', $explicit);
    }

    public function test_keyboard_and_pointer_bindings_are_present(): void
    {
        $html = $this->render('<mds:time-picker />');

        $this->assertStringContainsString('x-on:keydown="keydown($event)"', $html);
        $this->assertStringContainsString('x-on:blur="blur()"', $html);
        $this->assertStringContainsString('x-on:input="typed()"', $html);
        $this->assertStringContainsString('x-on:focus="show()"', $html);
        $this->assertStringContainsString('x-on:click.outside="blur()"', $html);

        foreach (["case 'ArrowDown':", "case 'ArrowUp':", "case 'Home':", "case 'End':", "case 'Enter':", "case 'Escape':", "case 'Tab':"] as $key) {
            $this->assertStringContainsString($key, $html);
        }

        // Options are picked with the mouse without stealing focus from the combobox.
        $this->assertMatchesRegularExpression('/<ul[^>]*\sx-on:mousedown\.prevent[\s>]/', $html);
        $this->assertStringContainsString('x-on:click="pick($el.dataset.value)"', $html);

        // Typed digits of any script are read back through the shared map.
        $this->assertStringContainsString('window.mds.latinDigits(String(raw', $html);
    }

    public function test_the_script_registers_once_per_page(): void
    {
        $html = $this->render('<mds:time-picker /><mds:time-picker hours="12" />');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsTimePicker'"));
        $this->assertSame(1, substr_count($html, 'window.mds.registerTimePicker ='));
        $this->assertSame(1, substr_count($html, 'window.mds.digits ='));
        $this->assertSame(2, preg_match_all('/\sdata-mds-time-picker\s*>/', $html));

        // Both the digits partial and the component script take the nonce directive.
        $this->assertSame(0, substr_count($html, 'nonce='), 'No nonce is registered, so none should print.');
        $this->assertStringContainsString('registerTimePicker(window.Alpine)', $html);
    }
}
