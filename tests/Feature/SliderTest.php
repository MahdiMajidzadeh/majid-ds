<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;

class SliderTest extends TestCase
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

    public function test_single_slider_is_one_native_range_input(): void
    {
        $html = $this->render('<mds:slider :value="25" name="volume" />');

        $this->assertStringContainsString('data-mds-slider', $html);
        $this->assertStringNotContainsString('data-mds-slider-range', $html);
        $this->assertStringContainsString('data-mds-slider-track', $html);
        $this->assertStringContainsString('data-mds-slider-fill', $html);
        $this->assertSame(1, substr_count($html, 'type="range"'));
        $this->assertStringContainsString('data-mds-slider-input="value"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="range"[^>]*\smin="0"[^>]*\smax="100"[^>]*\sstep="1"[^>]*\svalue="25"/', $html);
        $this->assertStringContainsString('name="volume"', $html);
        $this->assertStringNotContainsString('name="volume[]"', $html);
        // The fill runs from min to the value...
        $this->assertStringContainsString('--mds-slider-a: 0%; --mds-slider-b: 25%', $html);
        // ...and the shared Alpine component is registered, not only referenced.
        $this->assertStringContainsString("Alpine.data('mdsSlider'", $html);
        $this->assertStringContainsString('x-data="mdsSlider(', $html);
        $this->assertStringContainsString('window.mds.registerSlider', $html);
    }

    public function test_range_slider_has_two_thumbs_and_posts_as_an_array(): void
    {
        $html = $this->render('<mds:slider range :value="[10, 40]" name="price" />');

        $this->assertStringContainsString('data-mds-slider-range', $html);
        $this->assertSame(2, substr_count($html, 'type="range"'));
        $this->assertMatchesRegularExpression('/<input[^>]*type="range"[^>]*\svalue="10"[^>]*data-mds-slider-input="low"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="range"[^>]*\svalue="40"[^>]*data-mds-slider-input="high"/', $html);
        $this->assertSame(2, substr_count($html, 'name="price[]"'));
        $this->assertStringContainsString('--mds-slider-a: 10%; --mds-slider-b: 40%', $html);
        // Track presses move the nearer thumb — only a range needs that.
        $this->assertStringContainsString('x-on:pointerdown="trackDown($event)"', $html);
    }

    public function test_range_without_a_value_spans_the_whole_track(): void
    {
        $html = $this->render('<mds:slider range :min="0" :max="50" />');

        $this->assertMatchesRegularExpression('/value="0"[^>]*data-mds-slider-input="low"/', $html);
        $this->assertMatchesRegularExpression('/value="50"[^>]*data-mds-slider-input="high"/', $html);
        $this->assertStringContainsString('--mds-slider-a: 0%; --mds-slider-b: 100%', $html);
    }

    public function test_wire_model_reaches_the_single_range_input(): void
    {
        // A modifier has to survive too — the view filters on the prefix.
        $html = $this->render('<mds:slider wire:model.live="volume" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="range"', 'wire:model.live="volume"');
    }

    public function test_wire_model_on_a_range_binds_the_dotted_pair(): void
    {
        $html = $this->render('<mds:slider range wire:model.live="price" />');

        $this->assertBindingReachesControl($html, 'input[^>]*data-mds-slider-input="low"', 'wire:model.live="price.0"');
        $this->assertBindingReachesControl($html, 'input[^>]*data-mds-slider-input="high"', 'wire:model.live="price.1"');
        // The bare path is gone: nothing else carries the binding.
        $this->assertSame(2, substr_count($html, 'wire:model.live="price'));
    }

    public function test_bounds_and_step_reach_the_inputs_including_fractions(): void
    {
        $html = $this->render('<mds:slider :min="-1" :max="1" :step="0.25" :value="0.5" />');

        $this->assertMatchesRegularExpression('/<input[^>]*\smin="-1"[^>]*\smax="1"[^>]*\sstep="0.25"[^>]*\svalue="0.5"/', $html);
        $this->assertStringContainsString('--mds-slider-a: 0%; --mds-slider-b: 75%', $html);
        $this->assertStringContainsString('min: -1,', $html);
        $this->assertStringContainsString('step: 0.25,', $html);
    }

    public function test_values_are_clamped_and_ordered_on_the_server(): void
    {
        $single = $this->render('<mds:slider :value="500" :max="100" />');
        $this->assertMatchesRegularExpression('/type="range"[^>]*\svalue="100"/', $single);

        $below = $this->render('<mds:slider :value="-5" :min="0" />');
        $this->assertMatchesRegularExpression('/type="range"[^>]*\svalue="0"/', $below);

        // An inverted pair is put back in order rather than rendering a
        // crossed range.
        $range = $this->render('<mds:slider range :value="[80, 20]" />');
        $this->assertMatchesRegularExpression('/value="20"[^>]*data-mds-slider-input="low"/', $range);
        $this->assertMatchesRegularExpression('/value="80"[^>]*data-mds-slider-input="high"/', $range);
    }

    public function test_disabled_reaches_every_thumb(): void
    {
        $html = $this->render('<mds:slider range disabled />');

        $this->assertSame(2, preg_match_all('/<input[^>]*type="range"[^>]*\sdisabled[\s>]/', $html));
        $this->assertStringContainsString('data-disabled', $html);
        $this->assertStringContainsString('opacity-50', $html);
        $this->assertStringContainsString('disabled: true,', $html);

        $this->assertStringNotContainsString('data-disabled', $this->render('<mds:slider />'));
    }

    public function test_show_value_renders_a_live_readout_in_persian_then_english(): void
    {
        $persian = $this->render('<mds:slider range :value="[10, 40]" show-value />');

        $this->assertMatchesRegularExpression('/<span[^>]*aria-live="polite"[^>]*data-mds-slider-value\s*>۱۰ – ۴۰<\/span>/', $persian);
        $this->assertStringContainsString('x-text="readout"', $persian);
        $this->assertStringContainsString('fa: true,', $persian);

        $single = $this->render('<mds:slider :value="7" show-value />');
        $this->assertMatchesRegularExpression('/data-mds-slider-value\s*>۷<\/span>/', $single);

        $english = $this->renderEnglish('<mds:slider range :value="[10, 40]" show-value />');
        $this->assertMatchesRegularExpression('/data-mds-slider-value\s*>10 – 40<\/span>/', $english);
        $this->assertStringContainsString('fa: false,', $english);

        // Without show-value there is no readout at all.
        $this->assertStringNotContainsString('data-mds-slider-value', $this->render('<mds:slider :value="7" />'));
    }

    public function test_aria_valuetext_follows_the_digits_and_the_format(): void
    {
        $persian = $this->render('<mds:slider :value="25" format="{value}٪" show-value />');

        $this->assertStringContainsString('aria-valuetext="۲۵٪"', $persian);
        $this->assertMatchesRegularExpression('/data-mds-slider-value\s*>۲۵٪<\/span>/', $persian);
        $this->assertStringContainsString('x-bind:aria-valuetext="display(low)"', $persian);
        $this->assertStringContainsString("format: '{value}٪',", $persian);

        $english = $this->renderEnglish('<mds:slider :value="25" format="{value}%" show-value />');
        $this->assertStringContainsString('aria-valuetext="25%"', $english);

        $money = $this->render('<mds:slider range :min="0" :max="5000000" :step="50000" :value="[50000, 2000000]" format="{value} تومان" show-value />');
        $this->assertStringContainsString('aria-valuetext="۵۰۰۰۰ تومان"', $money);
        $this->assertStringContainsString('aria-valuetext="۲۰۰۰۰۰۰ تومان"', $money);
        $this->assertMatchesRegularExpression('/data-mds-slider-value\s*>۵۰۰۰۰ تومان – ۲۰۰۰۰۰۰ تومان<\/span>/', $money);
    }

    public function test_ticks_follow_the_steps_or_an_explicit_count(): void
    {
        // `ticks` alone: one per step while there are at most 20 steps...
        $perStep = $this->render('<mds:slider ticks :step="10" />');
        $this->assertStringContainsString('data-mds-slider-ticks', $perStep);
        $this->assertSame(11, substr_count($perStep, 'data-mds-slider-tick>'));

        // ...and none once the steps would crowd the track.
        $crowded = $this->render('<mds:slider ticks />');
        $this->assertStringNotContainsString('data-mds-slider-ticks', $crowded);

        $counted = $this->render('<mds:slider :ticks="5" />');
        $this->assertSame(5, substr_count($counted, 'data-mds-slider-tick>'));

        $this->assertStringNotContainsString('data-mds-slider-tick', $this->render('<mds:slider />'));
    }

    public function test_explicit_error_renders_the_message_and_marks_every_thumb(): void
    {
        $html = $this->render('<mds:slider range name="price" error="بازه نامعتبر است." />');

        $this->assertMatchesRegularExpression('/<div[^>]*role="alert"[^>]*data-flux-error>\s*<svg/', $html);
        $this->assertStringContainsString('بازه نامعتبر است.', $html);
        $this->assertSame(2, preg_match_all('/<input[^>]*type="range"[^>]*\saria-invalid="true"/', $html));
        $this->assertStringContainsString('data-invalid', $html);
        $this->assertStringContainsString('bg-red-500', $html);
        // The message is wired to the thumbs, not only placed under them.
        $this->assertMatchesRegularExpression('/aria-describedby="(mds-slider-[0-9a-f]{8})-error"[\s\S]*id="\1-error"/', $html);
    }

    public function test_invalid_alone_marks_the_state_without_a_message(): void
    {
        $html = $this->render('<mds:slider invalid />');

        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringNotContainsString('data-flux-error', $html);

        $clean = $this->render('<mds:slider />');
        $this->assertStringNotContainsString('aria-invalid', $clean);
        $this->assertStringNotContainsString('bg-red-500', $clean);
    }

    public function test_falls_back_to_the_validation_error_bag_by_name(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag([
            'volume' => ['صدا را کم کنید.'],
            'price.1' => ['سقف قیمت بیش از حد است.'],
        ]));

        // ShareErrorsFromSession shares the bag view-wide; mirror that here...
        View::share('errors', $bag);

        $single = $this->render('<mds:slider name="volume" />');
        $this->assertStringContainsString('صدا را کم کنید.', $single);
        $this->assertStringContainsString('aria-invalid="true"', $single);

        // A range posts as price[] and its rules report against price.0/1.
        $range = $this->render('<mds:slider range name="price" />');
        $this->assertStringContainsString('سقف قیمت بیش از حد است.', $range);

        // A single slider does not read the wildcard key...
        $this->assertStringNotContainsString('سقف قیمت بیش از حد است.', $this->render('<mds:slider name="price" />'));

        // ...and an explicit error still wins over the bag.
        $explicit = $this->render('<mds:slider name="volume" error="پیام صریح" />');
        $this->assertStringContainsString('پیام صریح', $explicit);
        $this->assertStringNotContainsString('صدا را کم کنید.', $explicit);
    }

    public function test_thumbs_are_labelled_with_the_field_label_and_their_end(): void
    {
        $persian = $this->render('<mds:slider range label="قیمت" />');
        $this->assertStringContainsString('aria-label="قیمت — حداقل"', $persian);
        $this->assertStringContainsString('aria-label="قیمت — حداکثر"', $persian);

        $english = $this->renderEnglish('<mds:slider range label="Price" />');
        $this->assertStringContainsString('aria-label="Price — minimum"', $english);
        $this->assertStringContainsString('aria-label="Price — maximum"', $english);
        $this->assertStringNotContainsString('حداقل', $english);

        // One thumb takes the label as is...
        $single = $this->render('<mds:slider label="صدا" />');
        $this->assertStringContainsString('aria-label="صدا"', $single);
        $this->assertStringNotContainsString('حداقل', $single);

        // ...and an unlabelled slider still has a name for its thumb.
        $this->assertStringContainsString('aria-label="مقدار"', $this->render('<mds:slider />'));
        $this->assertStringContainsString('aria-label="Value"', $this->renderEnglish('<mds:slider />'));
        $this->assertStringContainsString('aria-label="مقدار — حداقل"', $this->render('<mds:slider range />'));
    }

    public function test_label_and_description_render_as_field_chrome(): void
    {
        $html = $this->render('<mds:slider label="روشنایی" description="از صفر تا صد." />');

        $this->assertMatchesRegularExpression('/<div[^>]*data-mds-slider-header>\s*<ui-label[^>]*data-flux-label>\s*روشنایی/', $html);
        $this->assertMatchesRegularExpression('/<ui-description[^>]*id="(mds-slider-[0-9a-f]{8})-description"[^>]*data-flux-description>\s*از صفر تا صد\./', $html);
        $this->assertMatchesRegularExpression('/aria-describedby="mds-slider-[0-9a-f]{8}-description"/', $html);

        $bare = $this->render('<mds:slider />');
        $this->assertStringNotContainsString('data-mds-slider-header', $bare);
        $this->assertStringNotContainsString('aria-describedby', $bare);
    }

    public function test_size_sm_marks_the_root(): void
    {
        $this->assertStringContainsString('data-mds-slider-size="sm"', $this->render('<mds:slider size="sm" />'));
        $this->assertStringNotContainsString('data-mds-slider-size', $this->render('<mds:slider />'));
    }

    public function test_wrapper_attributes_land_on_the_root_not_the_control(): void
    {
        $html = $this->render('<mds:slider class="mt-4" id="loudness" />');

        $this->assertMatchesRegularExpression('/<div[^>]*class="[^"]*mt-4[^"]*"[^>]*data-mds-slider\s*>/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*id="loudness"[^>]*data-mds-slider\s*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<input[^>]*mt-4/', $html);
    }

    public function test_script_and_digit_helper_ship_once_per_page(): void
    {
        $html = $this->render('<mds:slider /><mds:slider range /><mds:quantity />');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsSlider'"));
        $this->assertSame(1, substr_count($html, 'window.mds.digits ='));
        $this->assertSame(3, substr_count($html, 'type="range"'));
        $this->assertStringNotContainsString('۰۱۲۳۴۵۶۷۸۹', (string) file_get_contents(dirname(__DIR__, 2).'/resources/views/mds/slider.blade.php'));
    }
}
