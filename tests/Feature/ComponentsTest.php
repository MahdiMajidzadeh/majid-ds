<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Mds;
use MajidDs\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class ComponentsTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * The kit's Livewire contract, asserted the same way for every control:
     * the binding reaches the real form element, and the wrapper keeps no
     * copy of it.
     *
     * Each view spells this as two independent expressions — the wrapper's
     * whereDoesntStartWith('wire:model') and the control's whereStartsWith —
     * so losing the second still leaves the binding in the markup, sitting on
     * the wrapper where Livewire ignores it. A substring assertion cannot see
     * that; matching the control's own tag and counting occurrences can.
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

    public function test_rating_renders_stars_and_persian_value(): void
    {
        $html = $this->render('<mds:rating :value="4.3" :count="126" />');

        $this->assertStringContainsString('data-mds-rating', $html);
        $this->assertStringContainsString('۴٫۳', $html);
        $this->assertStringContainsString('(۱۲۶)', $html);
        $this->assertStringContainsString('width: 86.0000%', $html);
    }

    public function test_rating_supports_latin_digits(): void
    {
        $html = $this->render('<mds:rating :value="4" :fa="false" />');

        $this->assertStringContainsString('>4</span>', $html);
    }

    public function test_rating_input_renders_radiogroup(): void
    {
        $html = $this->render('<mds:rating.input name="score" :value="3" />');

        $this->assertStringContainsString('data-mds-rating-input', $html);
        $this->assertStringContainsString('role="radiogroup"', $html);
        $this->assertStringContainsString('name="score"', $html);
    }

    public function test_rating_input_forwards_wire_model_to_the_hidden_input(): void
    {
        // A modifier has to survive too — the views filter on the wire:model
        // prefix, not on the exact attribute name.
        $html = $this->render('<mds:rating.input name="score" wire:model.live="score" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model.live="score"');
    }

    public function test_price_renders_amount_original_and_discount(): void
    {
        $html = $this->render('<mds:price :amount="2500000" :original="3200000" />');

        $this->assertStringContainsString('data-mds-price', $html);
        $this->assertStringContainsString('۲٬۵۰۰٬۰۰۰', $html);
        $this->assertStringContainsString('۳٬۲۰۰٬۰۰۰', $html);
        $this->assertStringContainsString('تومان', $html);
        // 1 - 2500000/3200000 = 21.875% => 22%...
        $this->assertStringContainsString('۲۲٪', $html);
    }

    public function test_price_respects_currency_and_latin_digits(): void
    {
        $html = $this->render('<mds:price :amount="1000" currency="rial" :fa="false" />');

        $this->assertStringContainsString('1,000', $html);
        $this->assertStringContainsString('Rial', $html);

        $persian = $this->render('<mds:price :amount="1000" currency="rial" :fa="true" />');

        $this->assertStringContainsString('ریال', $persian);
    }

    public function test_discount_badge_computes_percent_from_amounts(): void
    {
        $html = $this->render('<mds:discount-badge :amount="80" :original="100" />');

        $this->assertStringContainsString('۲۰٪', $html);
    }

    public function test_quantity_renders_buttons_and_hidden_input(): void
    {
        $html = $this->render('<mds:quantity :value="2" :min="1" :max="5" name="qty" />');

        $this->assertStringContainsString('data-mds-quantity', $html);
        $this->assertStringContainsString('type="hidden"', $html);
        $this->assertStringContainsString('name="qty"', $html);
        $this->assertStringContainsString('افزایش تعداد', $html);

        // Anchored to the value element: a bare '۲' also matches the Persian
        // digit map inlined in the Alpine block, so it can never fail.
        $this->assertStringContainsString('x-text="display()">۲</span>', $html);
    }

    public function test_quantity_forwards_wire_model_to_hidden_input(): void
    {
        $html = $this->render('<mds:quantity wire:model="qty" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model="qty"');
    }

    public function test_stepper_marks_completed_current_and_upcoming_steps(): void
    {
        $html = $this->render('<mds:stepper :steps="[\'سبد خرید\', \'آدرس\', \'پرداخت\']" :current="2" />');

        $this->assertStringContainsString('data-mds-stepper', $html);
        $this->assertStringContainsString('data-mds-stepper-step="completed"', $html);
        $this->assertStringContainsString('data-mds-stepper-step="current"', $html);
        $this->assertStringContainsString('data-mds-stepper-step="upcoming"', $html);
        $this->assertStringContainsString('aria-current="step"', $html);
        $this->assertStringContainsString('سبد خرید', $html);
    }

    public function test_product_card_composes_rating_price_and_badge(): void
    {
        $html = $this->render('<mds:product-card title="گوشی موبایل" :amount="2500000" :original="3200000" :rating="4.5" :reviews="80" badge="ارسال امروز" />');

        $this->assertStringContainsString('data-mds-product-card', $html);
        $this->assertStringContainsString('data-flux-card', $html);
        $this->assertStringContainsString('گوشی موبایل', $html);
        $this->assertStringContainsString('data-mds-rating', $html);
        $this->assertStringContainsString('data-mds-price', $html);
        $this->assertStringContainsString('ارسال امروز', $html);
    }

    public function test_product_card_unavailable_state(): void
    {
        $html = $this->render('<mds:product-card title="کالا" unavailable />');

        $this->assertStringContainsString('ناموجود', $html);
    }

    public function test_countdown_renders_initial_segments(): void
    {
        // Frozen so the two now() calls — the deadline here and the remaining
        // total in the view — cannot straddle a second boundary.
        $this->travelTo('2026-08-24 10:00:00');

        $html = $this->render('<mds:countdown :until="now()->addHours(2)" :days="false" />');

        $this->assertStringContainsString('data-mds-countdown', $html);

        // Anchored to each segment: a bare '۰۱' also matches the Persian digit
        // map inlined in the Alpine block, so it can never fail.
        $this->assertStringContainsString('x-text="seg(h)">۰۲</span>', $html);
        $this->assertStringContainsString('x-text="seg(m)">۰۰</span>', $html);
        $this->assertStringContainsString('x-text="seg(s)">۰۰</span>', $html);
    }

    public function test_countdown_ticks_through_a_shared_alpine_component_that_clears_its_interval(): void
    {
        $html = $this->render('<mds:countdown :until="now()->addHours(2)" />');

        // The interval lives in Alpine.data so destroy() can clear it — an
        // inline setInterval survives Livewire morphs and wire:navigate.
        $this->assertMatchesRegularExpression('/x-data="mdsCountdown\(/', $html);
        $this->assertStringContainsString("Alpine.data('mdsCountdown'", $html);
        $this->assertStringContainsString('clearInterval(this.timer)', $html);
        $this->assertStringNotContainsString('x-init="setInterval', $html);
    }

    public function test_countdown_plain_variant_is_ltr_but_labeled_variant_inherits_rtl(): void
    {
        // A wall-clock style time (HH:MM:SS) is always written left-to-right...
        $plain = $this->render('<mds:countdown :until="now()->addHour()" :days="false" />');
        $this->assertStringContainsString('dir="ltr"', $plain);

        // Labeled boxes follow the Iranian convention: days on the right...
        $labeled = $this->render('<mds:countdown :until="now()->addHour()" labels />');
        $this->assertStringNotContainsString('dir="ltr"', $labeled);
    }

    public function test_countdown_shows_expired_text(): void
    {
        $html = $this->render('<mds:countdown :until="now()->subMinute()" expired-text="تمام شد" />');

        $this->assertStringContainsString('تمام شد', $html);
    }

    public function test_command_renders_input_items_headings_and_kbd(): void
    {
        $html = $this->render('<mds:command>
            <mds:command.input placeholder="جستجوی فرمان..." clearable />
            <mds:command.items>
                <mds:command.heading>ناوبری</mds:command.heading>
                <mds:command.item icon="shopping-bag" kbd="⌘O">سفارش‌های من</mds:command.item>
                <mds:command.item href="/support">پشتیبانی</mds:command.item>
            </mds:command.items>
        </mds:command>');

        $this->assertStringContainsString('data-mds-command', $html);
        $this->assertStringContainsString('data-mds-command-input', $html);
        $this->assertStringContainsString('placeholder="جستجوی فرمان..."', $html);
        $this->assertStringContainsString('data-mds-command-heading', $html);
        $this->assertStringContainsString('data-mds-command-item', $html);
        $this->assertStringContainsString('⌘O', $html);
        $this->assertStringContainsString('سفارش‌های من', $html);
        // An item with href renders as a link, others as buttons. Anchored to
        // the item itself: the input's clear button also carries type="button",
        // so a bare needle matched even when every item was a link.
        $this->assertMatchesRegularExpression('/<a\s[^>]*href="\/support"[^>]*data-mds-command-item/', $html);
        $this->assertMatchesRegularExpression('/<button\s[^>]*type="button"[^>]*data-mds-command-item/', $html);
        // Default empty-state message ships with the items container...
        $this->assertStringContainsString('نتیجه‌ای یافت نشد.', $html);
    }

    public function test_command_input_close_button_and_custom_empty_text(): void
    {
        $html = $this->render('<mds:command>
            <mds:command.input closable />
            <mds:command.items empty="چیزی پیدا نشد!"></mds:command.items>
        </mds:command>');

        $this->assertStringContainsString('closest(\'dialog\')', $html);
        $this->assertStringContainsString('چیزی پیدا نشد!', $html);
    }

    public function test_color_picker_renders_trigger_panel_and_default_swatches(): void
    {
        $html = $this->render('<mds:color-picker label="رنگ اصلی" value="#e11d48" name="brand_color" clearable dropper />');

        $this->assertStringContainsString('data-mds-color-picker', $html);
        $this->assertStringContainsString('data-mds-color-picker-trigger', $html);
        $this->assertStringContainsString('data-mds-color-picker-panel', $html);
        $this->assertStringContainsString('data-mds-color-picker-area', $html);
        $this->assertStringContainsString('data-mds-color-picker-slider="hue"', $html);
        $this->assertStringContainsString('data-mds-color-picker-dropper', $html);
        $this->assertStringContainsString('name="brand_color"', $html);
        $this->assertStringContainsString('value="#e11d48"', $html);
        $this->assertStringContainsString('رنگ اصلی', $html);
        // The default palette ships 20 swatches...
        $this->assertSame(20, substr_count($html, 'data-mds-color-picker-swatch') - substr_count($html, 'data-mds-color-picker-swatches'));
        // The shared Alpine component is registered, not only referenced: the
        // x-data usage alone satisfied a bare 'mdsColorPicker' needle even with
        // the @once registration gone.
        $this->assertStringContainsString("Alpine.data('mdsColorPicker'", $html);
        $this->assertStringContainsString('x-data="mdsColorPicker(', $html);
    }

    public function test_color_picker_custom_swatches_alpha_format_and_button_type(): void
    {
        $html = $this->render('<mds:color-picker type="button" format="rgba" :swatches="[[\'#ef4444\', \'قرمز\'], \'#3b82f6\']" wire:model="color" />');

        $this->assertStringContainsString('aria-label="قرمز"', $html);
        $this->assertStringContainsString('aria-label="#3b82f6"', $html);
        $this->assertSame(2, substr_count($html, 'data-mds-color-picker-swatch') - substr_count($html, 'data-mds-color-picker-swatches'));
        $this->assertStringContainsString("format: 'rgba'", $html);
    }

    public function test_color_picker_forwards_wire_model_to_the_hidden_input(): void
    {
        $html = $this->render('<mds:color-picker wire:model="color" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model="color"');
    }

    public function test_color_picker_swatches_can_be_hidden(): void
    {
        $html = $this->render('<mds:color-picker :swatches="false" />');

        $this->assertStringNotContainsString('data-mds-color-picker-swatches', $html);
    }

    public function test_empty_state_renders_icon_title_description_and_actions(): void
    {
        $html = $this->render('<mds:empty-state icon="shopping-cart" title="سبد خرید شما خالی است" description="کالاها را اضافه کنید."><button>شروع خرید</button></mds:empty-state>');

        $this->assertStringContainsString('data-mds-empty-state', $html);
        $this->assertStringContainsString('سبد خرید شما خالی است', $html);
        $this->assertStringContainsString('شروع خرید', $html);
    }

    public function test_jalali_date_renders_time_element(): void
    {
        $html = $this->render('<mds:jalali-date date="2026-08-20" />');

        $this->assertStringContainsString('data-mds-jalali-date', $html);
        $this->assertStringContainsString('datetime="2026-08-20T00:00:00', $html);
        $this->assertStringContainsString('۲۹ مرداد ۱۴۰۵', $html);
    }

    public function test_jalali_date_relative_mode(): void
    {
        $html = $this->render('<mds:jalali-date :date="now()->subHours(3)" ago />');

        $this->assertStringContainsString('۳ ساعت پیش', $html);
    }

    public function test_file_upload_renders_field_and_file_input(): void
    {
        $html = $this->render('<mds:file-upload name="photos" label="بارگذاری تصاویر" description="حداکثر ۱۰ مگابایت" multiple accept="image/*"><mds:file-upload.dropzone /></mds:file-upload>');

        $this->assertStringContainsString('data-mds-file-upload', $html);
        $this->assertStringContainsString('data-mds-file-upload-dropzone', $html);
        $this->assertStringContainsString('type="file"', $html);
        $this->assertStringContainsString('name="photos[]"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="file"[^>]*\smultiple[\s>]/', $html);
        $this->assertStringContainsString('accept="image/*"', $html);
        $this->assertStringContainsString('بارگذاری تصاویر', $html);
        $this->assertStringContainsString('حداکثر ۱۰ مگابایت', $html);
    }

    public function test_file_upload_single_does_not_append_array_brackets(): void
    {
        $html = $this->render('<mds:file-upload name="photo" />');

        $this->assertStringContainsString('name="photo"', $html);
        $this->assertStringNotContainsString('name="photo[]"', $html);
    }

    public function test_file_upload_forwards_wire_model_to_the_file_input(): void
    {
        $html = $this->render('<mds:file-upload wire:model="photos" multiple />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="file"', 'wire:model="photos"');
    }

    public function test_file_upload_disabled_state(): void
    {
        $html = $this->render('<mds:file-upload disabled />');

        // Anchored to the input: every render carries Tailwind's `disabled:`
        // variant classes, so a bare 'disabled' matches with or without it.
        $this->assertMatchesRegularExpression('/<input[^>]*type="file"[^>]*\sdisabled[\s>]/', $html);
        $this->assertStringContainsString('cursor-not-allowed', $html);

        $plain = $this->render('<mds:file-upload />');

        $this->assertDoesNotMatchRegularExpression('/<input[^>]*type="file"[^>]*\sdisabled[\s>]/', $plain);
        $this->assertStringNotContainsString('cursor-not-allowed', $plain);
    }

    public function test_file_upload_renders_error_message(): void
    {
        $html = $this->render('<mds:file-upload error="حجم فایل بیش از حد مجاز است." />');

        $this->assertStringContainsString('حجم فایل بیش از حد مجاز است.', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
    }

    public function test_file_upload_falls_back_to_the_validation_error_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['photos' => ['هر فایل باید کمتر از ۱۰ مگابایت باشد.']]));

        // ShareErrorsFromSession shares the bag view-wide; mirror that here...
        View::share('errors', $bag);

        $html = $this->render('<mds:file-upload name="photos" multiple />');

        $this->assertStringContainsString('هر فایل باید کمتر از ۱۰ مگابایت باشد.', $html);
    }

    public function test_file_upload_falls_back_to_per_file_validation_errors(): void
    {
        // A `photos.*` rule reports against photos.0, photos.1 … and never
        // against a plain `photos` key. That is the ordinary shape for a
        // multiple upload, and the half of the fallback that exists for it.
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag([
            'photos.0' => ['تصویر اول باید کمتر از ۱۰ مگابایت باشد.'],
            'photos.1' => ['تصویر دوم باید یک تصویر باشد.'],
        ]));

        View::share('errors', $bag);

        $html = $this->render('<mds:file-upload name="photos" multiple />');

        $this->assertStringContainsString('تصویر اول باید کمتر از ۱۰ مگابایت باشد.', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
    }

    public function test_dropzone_inline_and_progress_variants(): void
    {
        $html = $this->render('<mds:file-upload><mds:file-upload.dropzone heading="رها کنید" text="JPG تا ۱۰ مگابایت" inline with-progress /></mds:file-upload>');

        $this->assertStringContainsString('رها کنید', $html);
        $this->assertStringContainsString('JPG تا ۱۰ مگابایت', $html);
        $this->assertStringContainsString('role="progressbar"', $html);
    }

    public function test_file_item_formats_size_in_persian(): void
    {
        $html = $this->render('<mds:file-item heading="Profile_pic.jpg" :size="162400" />');

        $this->assertStringContainsString('data-mds-file-item', $html);
        $this->assertStringContainsString('Profile_pic.jpg', $html);
        $this->assertStringContainsString('۱۵۹ کیلوبایت', $html);
    }

    public function test_file_item_renders_image_preview_and_actions_slot(): void
    {
        $html = $this->render('<mds:file-item heading="banner.jpg" image="/img/banner.jpg"><x-slot name="actions"><mds:file-item.remove /></x-slot></mds:file-item>');

        $this->assertStringContainsString('src="/img/banner.jpg"', $html);
        $this->assertStringContainsString('data-mds-file-item-remove', $html);
        $this->assertStringContainsString('aria-label="حذف فایل"', $html);
    }

    public function test_file_item_remove_forwards_wire_click_and_custom_label(): void
    {
        $html = $this->render('<mds:file-item.remove wire:click="removePhoto(1)" label="حذف banner.jpg" />');

        $this->assertStringContainsString('wire:click="removePhoto(1)"', $html);
        $this->assertStringContainsString('aria-label="حذف banner.jpg"', $html);
    }

    public function test_composer_renders_a_textarea_with_label_and_rows(): void
    {
        $html = $this->render('<mds:composer name="prompt" label="پیام" description="کوتاه بنویسید" placeholder="چطور کمک کنم؟" rows="3" />');

        $this->assertStringContainsString('data-mds-composer', $html);
        $this->assertStringContainsString('data-mds-composer-input', $html);
        $this->assertStringContainsString('rows="3"', $html);
        $this->assertStringContainsString('name="prompt"', $html);
        $this->assertStringContainsString('placeholder="چطور کمک کنم؟"', $html);
        $this->assertStringContainsString('پیام', $html);
        $this->assertStringContainsString('کوتاه بنویسید', $html);
    }

    public function test_composer_forwards_wire_model_to_the_textarea(): void
    {
        $html = $this->render('<mds:composer wire:model="prompt" />');

        $this->assertBindingReachesControl($html, 'textarea', 'wire:model="prompt"');
    }

    public function test_composer_renders_the_named_slots_in_grid_order(): void
    {
        $html = $this->render('<mds:composer><x-slot name="header">HEADER</x-slot><x-slot name="footer">FOOTER</x-slot><x-slot name="actionsLeading">LEADING</x-slot><x-slot name="actionsTrailing">TRAILING</x-slot></mds:composer>');

        foreach (['HEADER', 'LEADING', 'TRAILING', 'FOOTER'] as $content) {
            $this->assertStringContainsString($content, $html);
        }

        // Header above the input, actions below it, footer last...
        $this->assertLessThan(strpos($html, '<textarea'), strpos($html, 'HEADER'));
        $this->assertGreaterThan(strpos($html, '<textarea'), strpos($html, 'LEADING'));
        $this->assertGreaterThan(strpos($html, 'LEADING'), strpos($html, 'TRAILING'));
        $this->assertGreaterThan(strpos($html, 'TRAILING'), strpos($html, 'FOOTER'));
    }

    public function test_composer_input_slot_replaces_the_textarea(): void
    {
        $html = $this->render('<mds:composer><x-slot name="input"><div id="editor"></div></x-slot></mds:composer>');

        $this->assertStringContainsString('id="editor"', $html);
        $this->assertStringNotContainsString('<textarea', $html);
    }

    public function test_composer_inline_puts_the_actions_on_the_input_row(): void
    {
        $html = $this->render('<mds:composer inline rows="1"><x-slot name="actionsLeading">L</x-slot><x-slot name="actionsTrailing">T</x-slot></mds:composer>');

        $this->assertStringContainsString('col-start-1 row-start-1', $html);
        $this->assertStringContainsString('col-start-4 row-start-1', $html);
        $this->assertStringContainsString('col-span-2 col-start-2 row-start-1', $html);
    }

    public function test_composer_inline_with_a_header_moves_the_input_row_down(): void
    {
        $html = $this->render('<mds:composer inline><x-slot name="header">H</x-slot><x-slot name="actionsTrailing">T</x-slot></mds:composer>');

        $this->assertStringContainsString('row-start-1', $html);   // the header
        $this->assertStringContainsString('col-start-4 row-start-2', $html);
    }

    public function test_composer_input_variant_swaps_the_corner_radius(): void
    {
        // The default keeps the card radius and only *prefixes* rounded-lg onto
        // its buttons ([&_[data-flux-button]]:rounded-lg), so a bare needle
        // matched both variants and could never fail. Only the input variant
        // carries an unprefixed rounded-lg on the root.
        $bare = '/(?<![:\]])\brounded-lg\b/';

        $input = $this->render('<mds:composer variant="input" />');
        $this->assertMatchesRegularExpression($bare, $input);
        $this->assertStringNotContainsString('rounded-2xl', $input);

        $default = $this->render('<mds:composer />');
        $this->assertStringContainsString('rounded-2xl', $default);
        $this->assertDoesNotMatchRegularExpression($bare, $default);
    }

    public function test_composer_submit_prop_only_accepts_the_two_known_modes(): void
    {
        $this->assertStringContainsString("submit: 'enter'", $this->render('<mds:composer submit="enter" />'));
        $this->assertStringContainsString("submit: 'cmd+enter'", $this->render('<mds:composer />'));
        $this->assertStringContainsString("submit: 'cmd+enter'", $this->render('<mds:composer submit="whatever" />'));
    }

    public function test_composer_counter_counts_characters_in_persian(): void
    {
        $html = $this->render('<mds:composer :maxlength="280" counter value="سلام" />');

        $this->assertStringContainsString('maxlength="280"', $html);
        // «سلام» is 4 characters, not 8 bytes...
        $this->assertStringContainsString('۴ / ۲۸۰', $html);
        $this->assertStringContainsString('dir="ltr"', $html);
    }

    public function test_composer_counter_supports_latin_digits_and_no_limit(): void
    {
        $html = $this->render('<mds:composer counter :fa="false" value="hello" />');

        $this->assertStringContainsString('>5</span>', $html);
    }

    public function test_composer_max_rows_never_drops_below_rows(): void
    {
        $html = $this->render('<mds:composer rows="4" max-rows="2" />');

        $this->assertStringContainsString('maxRows: 4', $html);
    }

    public function test_composer_takes_its_initial_text_from_the_default_slot(): void
    {
        $html = $this->render('<mds:composer>سلام دنیا</mds:composer>');

        $this->assertStringContainsString('>سلام دنیا</textarea>', $html);
    }

    public function test_composer_label_sr_only_hides_the_label_visually(): void
    {
        $html = $this->render('<mds:composer label="پیام" label:sr-only description="راهنما" description:sr-only />');

        $this->assertStringContainsString('sr-only', $html);
        // Flux's colon syntax must not leak through as a stray attribute...
        $this->assertStringNotContainsString('label:sr-only="', $html);
        $this->assertStringNotContainsString('description:sr-only="', $html);
    }

    public function test_composer_disabled_state_is_inert(): void
    {
        $html = $this->render('<mds:composer disabled />');

        $this->assertStringContainsString('inert', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertMatchesRegularExpression('/<textarea\s[^>]*disabled/', $html);
    }

    public function test_composer_renders_error_message_and_marks_the_input_invalid(): void
    {
        $html = $this->render('<mds:composer error="نوشتن پیام الزامی است." />');

        $this->assertStringContainsString('نوشتن پیام الزامی است.', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('border-red-500', $html);
    }

    public function test_composer_falls_back_to_the_validation_error_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['prompt' => ['پیام نمی‌تواند خالی باشد.']]));

        View::share('errors', $bag);

        $html = $this->render('<mds:composer name="prompt" />');

        $this->assertStringContainsString('پیام نمی‌تواند خالی باشد.', $html);
    }

    public function test_preview_card_renders_a_real_link_with_hidden_content(): void
    {
        $html = $this->render('<mds:preview-card><mds:preview-card.trigger href="/profile">@majid</mds:preview-card.trigger><mds:preview-card.content>پروفایل</mds:preview-card.content></mds:preview-card>');

        $this->assertStringContainsString('data-mds-preview-card', $html);
        $this->assertMatchesRegularExpression('/<a\s[^>]*href="\/profile"[^>]*data-mds-preview-card-trigger/s', $html);
        $this->assertStringContainsString('data-mds-preview-card-content', $html);
        // Teleported so a block popup can sit inside a <p> without the parser
        // reparenting it out of the Alpine scope...
        $this->assertStringContainsString('x-teleport="body"', $html);
        $this->assertStringContainsString('x-cloak', $html);
        $this->assertStringContainsString('data-side="bottom"', $html);
        $this->assertStringContainsString('data-align="center"', $html);
        $this->assertStringContainsString('data-side-offset="10"', $html);
        $this->assertStringContainsString('data-mds-preview-card-arrow', $html);
    }

    public function test_preview_card_content_accepts_side_align_and_offset(): void
    {
        $html = $this->render('<mds:preview-card><mds:preview-card.content side="end" align="start" side-offset="24" /></mds:preview-card>');

        $this->assertStringContainsString('data-side="end"', $html);
        $this->assertStringContainsString('data-align="start"', $html);
        $this->assertStringContainsString('data-side-offset="24"', $html);
    }

    public function test_preview_card_rejects_unknown_placements(): void
    {
        $html = $this->render('<mds:preview-card><mds:preview-card.content side="diagonal" align="middle" /></mds:preview-card>');

        $this->assertStringContainsString('data-side="bottom"', $html);
        $this->assertStringContainsString('data-align="center"', $html);
    }

    public function test_preview_card_without_arrow_sits_closer(): void
    {
        $html = $this->render('<mds:preview-card><mds:preview-card.content :arrow="false" /></mds:preview-card>');

        $this->assertStringNotContainsString('data-mds-preview-card-arrow', $html);
        $this->assertStringContainsString('data-side-offset="6"', $html);
    }

    public function test_preview_card_delays_flow_into_alpine(): void
    {
        $html = $this->render('<mds:preview-card delay="100" close-delay="50" />');

        $this->assertStringContainsString('delay: 100', $html);
        $this->assertStringContainsString('closeDelay: 50', $html);
    }

    public function test_timeline_renders_an_ordered_list_with_state_attributes(): void
    {
        $html = $this->render('<mds:timeline horizontal size="lg" align="start"><mds:timeline.item /></mds:timeline>');

        $this->assertStringContainsString('<ol', $html);
        $this->assertStringContainsString('data-mds-timeline', $html);
        $this->assertStringContainsString('data-mds-timeline-horizontal', $html);
        $this->assertStringContainsString('data-mds-timeline-size="lg"', $html);
        $this->assertStringContainsString('data-mds-timeline-align="start"', $html);
    }

    public function test_timeline_item_inherits_align_from_the_timeline(): void
    {
        $html = $this->render('<mds:timeline align="end"><mds:timeline.item /></mds:timeline>');

        // The <li> carries the resolved alignment, not just the <ol>...
        $this->assertMatchesRegularExpression('/<li\s[^>]*data-mds-timeline-align="end"/', $html);
    }

    public function test_timeline_item_align_overrides_the_timeline(): void
    {
        $html = $this->render('<mds:timeline align="end"><mds:timeline.item align="baseline" /></mds:timeline>');

        $this->assertMatchesRegularExpression('/<li\s[^>]*data-mds-timeline-align="baseline"/', $html);
    }

    public function test_timeline_item_defaults_to_center_without_a_parent(): void
    {
        $html = $this->render('<mds:timeline.item />');

        $this->assertStringContainsString('data-mds-timeline-align="center"', $html);
    }

    public function test_timeline_item_renders_status_and_connector_lines(): void
    {
        $html = $this->render('<mds:timeline><mds:timeline.item status="complete" size="lg" /></mds:timeline>');

        $this->assertStringContainsString('data-mds-timeline-item', $html);
        $this->assertStringContainsString('data-mds-timeline-status="complete"', $html);
        $this->assertStringContainsString('data-mds-timeline-size="lg"', $html);
        $this->assertStringContainsString('data-mds-timeline-line="leading"', $html);
        $this->assertStringContainsString('data-mds-timeline-line="trailing"', $html);
    }

    public function test_timeline_indicator_applies_a_color(): void
    {
        $html = $this->render('<mds:timeline.indicator color="green">OK</mds:timeline.indicator>');

        $this->assertStringContainsString('data-mds-timeline-indicator', $html);
        $this->assertStringContainsString('bg-green-500', $html);
        $this->assertStringContainsString('rounded-full', $html);
    }

    public function test_timeline_indicator_bare_variant_drops_its_shell(): void
    {
        $html = $this->render('<mds:timeline.indicator variant="bare" color="green" />');

        $this->assertStringContainsString('data-mds-timeline-bare', $html);
        $this->assertStringNotContainsString('rounded-full', $html);
        $this->assertStringNotContainsString('bg-green-500', $html);
    }

    public function test_timeline_indicator_can_override_the_item_status(): void
    {
        $html = $this->render('<mds:timeline.indicator status="current" />');

        $this->assertMatchesRegularExpression('/data-mds-timeline-status="current"[^>]*data-mds-timeline-indicator/', $html);
    }

    public function test_timeline_content_block_and_subgrid_render(): void
    {
        $html = $this->render(<<<'BLADE'
            <mds:timeline>
                <mds:timeline.item>
                    <mds:timeline.indicator>۱</mds:timeline.indicator>
                    <mds:timeline.content>سفارش ثبت شد</mds:timeline.content>
                </mds:timeline.item>
                <mds:timeline.item>
                    <mds:timeline.block>
                        <mds:timeline.subgrid>پاسخ پشتیبانی</mds:timeline.subgrid>
                    </mds:timeline.block>
                </mds:timeline.item>
            </mds:timeline>
            BLADE);

        $this->assertStringContainsString('data-mds-timeline-content', $html);
        $this->assertStringContainsString('سفارش ثبت شد', $html);
        $this->assertStringContainsString('data-mds-timeline-block', $html);
        $this->assertStringContainsString('data-mds-timeline-subgrid', $html);
        $this->assertStringContainsString('پاسخ پشتیبانی', $html);
    }

    public function test_icon_renders_a_hugeicons_svg(): void
    {
        $html = $this->render('<mds:icon icon="search-01" />');

        $this->assertStringContainsString('data-mds-icon', $html);
        $this->assertStringContainsString('viewBox="0 0 24 24"', $html);
        $this->assertStringContainsString('stroke="currentColor"', $html);
        $this->assertStringContainsString('aria-hidden="true"', $html);
    }

    public function test_icon_escapes_caller_attribute_values(): void
    {
        // blade-icons prints attributes verbatim, so a quote in a caller's
        // value must be escaped here or it breaks out of the attribute.
        $html = $this->render(
            '<mds:icon icon="search-01" :data-note="$note" label="گفت «سلام» & رفت" />',
            ['note' => 'a" onmouseover="alert(1)'],
        );

        $this->assertStringContainsString('data-note="a&quot; onmouseover=&quot;alert(1)"', $html);
        $this->assertStringNotContainsString('data-note="a" onmouseover=', $html);

        // merge() already escapes the label — the pass must not double-encode it.
        $this->assertStringContainsString('aria-label="گفت «سلام» &amp; رفت"', $html);
        $this->assertStringNotContainsString('&amp;amp;', $html);
    }

    public function test_icon_resolves_heroicon_names_through_the_alias_map(): void
    {
        // search-01 opens its path with the magnifier handle...
        $expected = $this->render('<mds:icon icon="search-01" />');

        $this->assertSame($expected, $this->render('<mds:icon icon="magnifying-glass" />'));
    }

    public function test_icon_overrides_beat_a_same_named_hugeicon(): void
    {
        // Hugeicons' own "arrow-up" is a chevron, so the override must win...
        $this->assertSame(
            $this->render('<mds:icon icon="arrow-up-02" />'),
            $this->render('<mds:icon icon="arrow-up" />'),
        );

        $this->assertNotSame(
            $this->render('<mds:icon icon="arrow-up-01" />'),
            $this->render('<mds:icon icon="arrow-up" />'),
        );
    }

    public function test_icon_prefers_a_literal_hugeicons_name_over_an_alias(): void
    {
        // "heart" exists in Hugeicons, so it must not be redirected...
        $this->assertNotSame(
            $this->render('<mds:icon icon="favourite" />'),
            $this->render('<mds:icon icon="heart" />'),
        );
    }

    public function test_icon_falls_back_to_flux_for_unmapped_names(): void
    {
        $html = $this->render('<mds:icon icon="arrow-trending-up" />');

        $this->assertStringContainsString('data-flux-icon', $html);
    }

    public function test_icon_strict_mode_suppresses_the_flux_fallback(): void
    {
        config(['mds.icons.strict' => true]);

        $html = $this->render('<mds:icon icon="arrow-trending-up" />');

        $this->assertStringNotContainsString('data-flux-icon', $html);
        $this->assertStringNotContainsString('<svg', $html);
    }

    public function test_icon_honours_the_flux_driver(): void
    {
        config(['mds.icons.default' => 'flux']);

        $html = $this->render('<mds:icon icon="magnifying-glass" />');

        $this->assertStringContainsString('data-flux-icon', $html);
    }

    public function test_icon_stroke_override_is_opt_in(): void
    {
        $plain = $this->render('<mds:icon icon="user" />');
        $thick = $this->render('<mds:icon icon="user" :stroke="2" />');

        $this->assertStringNotContainsString('data-mds-icon-stroke', $plain);
        $this->assertStringNotContainsString('--mds-icon-stroke', $plain);

        $this->assertStringContainsString('data-mds-icon-stroke', $thick);
        $this->assertStringContainsString('--mds-icon-stroke:2', $thick);
    }

    public function test_icon_label_promotes_it_to_an_image_role(): void
    {
        $html = $this->render('<mds:icon icon="user" label="حساب کاربری" />');

        $this->assertStringContainsString('role="img"', $html);
        $this->assertStringContainsString('aria-label="حساب کاربری"', $html);
        $this->assertStringNotContainsString('aria-hidden', $html);
    }

    public function test_icon_passes_classes_through(): void
    {
        $html = $this->render('<mds:icon icon="user" class="size-4 text-zinc-400" />');

        $this->assertStringContainsString('class="size-4 text-zinc-400"', $html);
    }

    public function test_icon_accepts_a_name_prop_like_flux(): void
    {
        $this->assertSame(
            $this->render('<mds:icon icon="user" />'),
            $this->render('<mds:icon name="user" />'),
        );
    }

    public function test_mds_components_render_hugeicons(): void
    {
        $html = $this->render('<mds:empty-state icon="shopping-cart" title="خالی" />');

        $this->assertStringContainsString('data-mds-icon', $html);
        $this->assertStringNotContainsString('data-flux-icon', $html);
    }

    public function test_mds_components_follow_the_flux_driver(): void
    {
        config(['mds.icons.default' => 'flux']);

        $html = $this->render('<mds:empty-state icon="shopping-cart" title="خالی" />');

        $this->assertStringContainsString('data-flux-icon', $html);
    }

    public function test_x_mds_namespace_also_works(): void
    {
        $html = $this->render('<x-mds::rating :value="3" />');

        $this->assertStringContainsString('data-mds-rating', $html);
    }

    public function test_blade_directives(): void
    {
        $this->assertSame('۱۲۳', $this->render('@fa(123)'));
        $this->assertSame('۱٬۰۰۰ تومان', $this->render('@toman(1000)'));
        $this->assertSame('۲۹ مرداد ۱۴۰۵', $this->render("@jalali('2026-08-20')"));
    }

    public function test_mds_facade_is_reachable_as_a_global_alias(): void
    {
        // Registered via composer.json's extra.laravel.aliases — an app sees
        // it at the root namespace without importing anything.
        $this->assertTrue(class_exists('Mds'));
        $this->assertSame('۱۲۳', \Mds::fa(123));
    }

    public function test_mds_facade_ago_speaks_both_languages(): void
    {
        $date = new \DateTimeImmutable('-3 hours');

        $this->assertSame('۳ ساعت پیش', Mds::ago($date));
        $this->assertSame('3 hours ago', Mds::ago($date, false));
    }

    /*
    | fa=false (per prop or config) switches every built-in string to English —
    | digits and microcopy travel together, so an English app never ships a
    | stray Persian word it cannot override.
    */

    public function test_countdown_english_labels_and_expiry(): void
    {
        $labeled = $this->render('<mds:countdown :until="now()->addDays(2)" labels :fa="false" />');
        // Anchored to the label element: a bare 'min' also matches every
        // min-w-* class in the render, so it could never fail.
        foreach (['days', 'hours', 'min', 'sec'] as $label) {
            $this->assertStringContainsString(">{$label}</span>", $labeled);
        }

        $expired = $this->render('<mds:countdown :until="now()->subMinute()" :fa="false" />');
        $this->assertStringContainsString('Expired', $expired);
        $this->assertStringNotContainsString('به پایان رسید', $expired);
    }

    public function test_discount_badge_english_sign_and_label(): void
    {
        $html = $this->render('<mds:discount-badge :percent="25" :fa="false" />');

        $this->assertStringContainsString('>25%<', $html);
        $this->assertStringContainsString('aria-label="25% off"', $html);
        $this->assertStringNotContainsString('٪', $html);
    }

    public function test_product_card_english_unavailable_state(): void
    {
        $html = $this->render('<mds:product-card title="Widget" unavailable :fa="false" />');

        $this->assertStringContainsString('Out of stock', $html);
        $this->assertStringNotContainsString('ناموجود', $html);
    }

    public function test_jalali_date_english_output_keeps_the_jalali_calendar(): void
    {
        $formatted = $this->render('<mds:jalali-date date="2026-08-20" :fa="false" />');
        $this->assertStringContainsString('29 Mordad 1405', $formatted);

        $ago = $this->render('<mds:jalali-date :date="now()->subDays(2)" ago :fa="false" />');
        $this->assertStringContainsString('2 days ago', $ago);
    }

    public function test_file_item_derives_an_english_size(): void
    {
        $html = $this->render('<mds:file-item heading="report.pdf" :size="162400" :fa="false" />');

        $this->assertStringContainsString('159 KB', $html);
    }

    public function test_chart_card_renders_persian_stat_delta_and_footer(): void
    {
        $html = $this->render('<mds:chart label="فروش ماهانه" badge="تومان" :value="48920" unit="هزار" delta="+14.2%" footer-start="شش ماه اخیر" footer-end="اوج در مرداد">stage</mds:chart>');

        $this->assertStringContainsString('data-mds-chart', $html);
        $this->assertStringContainsString('۴۸٬۹۲۰', $html);
        $this->assertStringContainsString('+۱۴.۲%', $html);
        $this->assertStringContainsString('data-mds-chart-delta', $html);
        $this->assertStringContainsString('اوج در مرداد', $html);
    }

    public function test_chart_card_marks_a_negative_delta_as_a_drop(): void
    {
        $html = $this->render('<mds:chart :value="10" delta="-3%" :fa="false">stage</mds:chart>');

        $this->assertStringContainsString('data-mds-chart-delta-down', $html);
        $this->assertStringContainsString('-3%', $html);
    }

    public function test_chart_line_draws_a_spline_with_dots_and_persian_ticks(): void
    {
        $html = $this->render('<mds:chart.line :data="[24, 45, 38, 65, 52, 84]" :labels="[\'فروردین\', \'اردیبهشت\', \'خرداد\', \'تیر\', \'مرداد\', \'شهریور\']" />');

        $this->assertStringContainsString('data-mds-chart-line', $html);
        $this->assertStringContainsString('data-mds-chart-stage', $html);
        // The monotone spline renders cubic segments, not straight lines.
        $this->assertMatchesRegularExpression('/<path d="M[^"]* C[^"]*" stroke="currentColor" stroke-width="3"/', $html);
        $this->assertSame(6, substr_count($html, 'data-mds-chart-dot'));
        // 84 peaks on a 0..100 axis with Persian tick digits.
        $this->assertStringContainsString('>۱۰۰</text>', $html);
        $this->assertStringContainsString('فروردین', $html);
    }

    public function test_chart_line_supports_area_baseline_and_latin_digits(): void
    {
        $html = $this->render('<mds:chart.line :data="[24, 45, 84]" :baseline="[18, 32, 62]" area :fa="false" />');

        $this->assertStringContainsString('<linearGradient', $html);
        $this->assertStringContainsString('stroke-dasharray="4 4"', $html);
        $this->assertStringContainsString('>100</text>', $html);
        $this->assertStringNotContainsString('۱۰۰', $html);
    }

    public function test_chart_bars_stacks_layers_with_outer_end_rounding(): void
    {
        $html = $this->render('<mds:chart.bars :data="[[30, 25, 20], [45, 35, 25]]" :labels="[\'Q1\', \'Q2\']" :fa="false" />');

        $this->assertStringContainsString('data-mds-chart-bars', $html);
        // Two stacks of three tones: solid base, half, fifth.
        $this->assertSame(2, substr_count($html, 'fill-opacity="1"'));
        $this->assertSame(2, substr_count($html, 'fill-opacity="0.5"'));
        $this->assertSame(2, substr_count($html, 'fill-opacity="0.2"'));
    }

    public function test_chart_bars_horizontal_renders_direction_aware_rows(): void
    {
        $html = $this->render('<mds:chart.bars horizontal :data="[100, 68, 42, 24]" :labels="[\'بازدید\', \'ثبت‌نام\', \'فعال\', \'خرید\']" />');

        $this->assertStringContainsString('data-mds-chart-bars-rows', $html);
        $this->assertStringContainsString('inline-size: 100%', $html);
        $this->assertStringContainsString('inline-size: 68%', $html);
        $this->assertStringContainsString('ثبت‌نام', $html);
        $this->assertStringContainsString('۱۰۰', $html);
    }

    public function test_chart_svgs_are_exposed_to_assistive_tech(): void
    {
        // role="img" makes an SVG's inner <text> presentational, so donut and
        // gauge carry their value in the accessible name; the decorative
        // sparkline is the only stage that stays aria-hidden.
        $gauge = $this->render('<mds:chart.gauge :value="72" label="رضایت" />');
        $this->assertStringContainsString('aria-label="رضایت، ۷۲"', $gauge);
        $this->assertStringNotContainsString('aria-hidden', $gauge);

        $donut = $this->render('<mds:chart.donut :data="[\'الف\' => 60, \'ب\' => 40]" label="Split" :fa="false" />');
        $this->assertStringContainsString('aria-label="Split, 100"', $donut);

        $line = $this->render('<mds:chart.line :data="[1, 2]" :fa="false" />');
        $this->assertStringContainsString('aria-label="Line chart"', $line);

        $bars = $this->render('<mds:chart.bars :data="[1, 2]" />');
        $this->assertStringContainsString('aria-label="نمودار ستونی"', $bars);

        $sparkline = $this->render('<mds:chart.sparkline :data="[1, 2]" />');
        $this->assertStringContainsString('aria-hidden="true"', $sparkline);
        $this->assertStringNotContainsString('role="img"', $sparkline);
    }

    public function test_chart_bars_and_line_survive_a_zero_max(): void
    {
        // A caller-supplied ceiling of zero must clamp, not divide by zero —
        // the same guard gauge, bullet, and radar already carry.
        $bars = $this->render('<mds:chart.bars :data="[3, 7]" :max="0" />');
        $this->assertStringContainsString('data-mds-chart-bars', $bars);

        $line = $this->render('<mds:chart.line :data="[3, 7]" :max="0" />');
        $this->assertStringContainsString('data-mds-chart-line', $line);
    }

    public function test_chart_donut_draws_rounded_segments_and_a_legend(): void
    {
        $html = $this->render('<mds:chart.donut :data="[\'هسته\' => 45, \'رابط\' => 30, \'دارایی\' => 15, \'دیگر\' => 10]" value="100%" label="تخصیص" />');

        $this->assertStringContainsString('data-mds-chart-donut', $html);
        $this->assertSame(4, substr_count($html, 'stroke-linecap="round"'));
        $this->assertStringContainsString('stroke-opacity="0.7"', $html);
        $this->assertStringContainsString('۱۰۰%', $html);
        $this->assertStringContainsString('data-mds-chart-legend', $html);
        $this->assertStringContainsString('دارایی', $html);
    }

    public function test_chart_gauge_dials_the_value_over_a_faint_track(): void
    {
        $html = $this->render('<mds:chart.gauge :value="84" label="Target met" :fa="false" />');

        $this->assertStringContainsString('data-mds-chart-gauge', $html);
        $this->assertStringContainsString('>84</text>', $html);
        $this->assertStringContainsString('stroke-opacity="0.1"', $html);
        $this->assertStringContainsString('Target met', $html);
    }

    public function test_chart_radar_webs_the_axes(): void
    {
        $html = $this->render('<mds:chart.radar :data="[\'سرعت\' => 90, \'حافظه\' => 75, \'مقیاس\' => 85, \'تاخیر\' => 95, \'دقت\' => 80]" />');

        $this->assertStringContainsString('data-mds-chart-radar', $html);
        // Four grid rings plus the data shape.
        $this->assertSame(5, substr_count($html, '<polygon'));
        $this->assertStringContainsString('fill-opacity="0.15"', $html);
        $this->assertStringContainsString('حافظه', $html);
    }

    public function test_chart_bullet_places_the_target_marker(): void
    {
        $html = $this->render('<mds:chart.bullet :items="[[\'label\' => \'گذردهی\', \'value\' => 82, \'target\' => 75]]" />');

        $this->assertStringContainsString('data-mds-chart-bullet', $html);
        $this->assertStringContainsString('inline-size: 82%', $html);
        $this->assertStringContainsString('inset-inline-start: 75%', $html);
        $this->assertStringContainsString('۸۲% / ۷۵%', $html);
    }

    public function test_chart_heatmap_grades_cells_and_speaks_persian(): void
    {
        $html = $this->render('<mds:chart.heatmap :data="[0, 1, 3, 6, 12]" :labels="[\'فروردین\']" />');

        $this->assertStringContainsString('data-mds-chart-heatmap', $html);
        $this->assertSame(5, substr_count($html, 'data-mds-chart-cell'));
        $this->assertStringContainsString('data-level="0"', $html);
        $this->assertStringContainsString('data-level="4"', $html);
        $this->assertStringContainsString('۱۲ مورد', $html);
        $this->assertStringContainsString('برای جزئیات روی خانه‌ها بروید', $html);
    }

    public function test_chart_heatmap_supports_english_and_the_accent_ladder(): void
    {
        $html = $this->render('<mds:chart.heatmap :data="[2, 4]" color="accent" :fa="false" />');

        $this->assertStringContainsString('data-mds-chart-heatmap-color="accent"', $html);
        $this->assertStringContainsString('4 items', $html);
        $this->assertStringContainsString('Hover tiles for details', $html);
    }

    public function test_chart_sparkline_is_a_bare_stretchable_svg(): void
    {
        $html = $this->render('<mds:chart.sparkline :data="[30, 45, 35, 60, 50, 85]" area class="h-10" />');

        $this->assertStringContainsString('data-mds-chart-sparkline', $html);
        $this->assertStringContainsString('preserveAspectRatio="none"', $html);
        $this->assertStringContainsString('vector-effect="non-scaling-stroke"', $html);
        $this->assertStringContainsString('<linearGradient', $html);
        $this->assertMatchesRegularExpression('/<svg\s[^>]*class="h-10"/', $html);
    }

    public function test_chart_stages_inherit_fa_from_the_card(): void
    {
        $html = $this->render('<mds:chart :fa="false"><mds:chart.line :data="[10, 20]" /></mds:chart>');

        $this->assertStringContainsString('>20</text>', $html);
        $this->assertStringNotContainsString('۲۰', $html);
    }

    public function test_chart_stage_fa_overrides_the_card(): void
    {
        $html = $this->render('<mds:chart :fa="false"><mds:chart.gauge :value="84" :fa="true" /></mds:chart>');

        $this->assertStringContainsString('۸۴', $html);
    }

    public function test_components_hold_up_at_their_edges(): void
    {
        // quantity clamped in Alpine but not on the server, so the first paint
        // — and the value that posts without JS — could sit outside its bounds.
        $below = $this->render('<mds:quantity :value="0" :min="1" :max="5" />');
        $this->assertStringContainsString('x-text="display()">۱</span>', $below);
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*value="1"/', $below);

        $above = $this->render('<mds:quantity :value="99" :min="1" :max="5" />');
        $this->assertStringContainsString('x-text="display()">۵</span>', $above);

        // `original > amount` was true for original=0 with a negative amount,
        // which then divided by it.
        $free = $this->render('<mds:price :amount="-10" :original="0" />');
        $this->assertStringNotContainsString('٪', $free);

        $card = $this->render('<mds:product-card title="x" :amount="-10" :original="0" />');
        $this->assertStringNotContainsString('data-mds-discount-badge', $card);

        // A card with no amount used to pass null through to mds:price, which
        // defeated its default and advertised the product at «۰ تومان».
        $unpriced = $this->render('<mds:product-card title="x" />');
        $this->assertStringNotContainsString('data-mds-price', $unpriced);
        $this->assertStringNotContainsString('تومان', $unpriced);

        // Out of stock still says so without an amount…
        $this->assertStringContainsString('ناموجود', $this->render('<mds:product-card title="x" unavailable />'));

        // …and a priced card still prices.
        $this->assertStringContainsString('data-mds-price', $this->render('<mds:product-card title="x" :amount="1000" />'));

        // A badge with nothing to say used to render «۰٪».
        $this->assertSame('', trim($this->render('<mds:discount-badge />')));
        $this->assertStringContainsString('۲۰٪', $this->render('<mds:discount-badge :percent="20" />'));

        // The one chart label that ignored fa.
        $bullet = $this->render('<mds:chart.bullet :items="[[\'label\' => \'Q1 2026\', \'value\' => 5, \'target\' => 8]]" />');
        $this->assertStringContainsString('Q۱ ۲۰۲۶', $bullet);
    }

    public function test_the_digit_helper_ships_once_for_the_whole_page(): void
    {
        // Five views used to inline the same Latin -> Persian replace().
        $page = $this->render('
            <mds:quantity :value="2" />
            <mds:countdown :until="now()->addHour()" />
            <mds:composer counter />
            <mds:file-upload />
            <mds:command><mds:command.items>x</mds:command.items></mds:command>
        ');

        $this->assertSame(1, substr_count($page, 'window.mds.digits ='));
        $this->assertSame(1, substr_count($page, 'window.mds.latinDigits ='));

        // And the map itself lives only in that partial — no component view
        // may grow a private copy again.
        $views = glob(dirname(__DIR__, 2).'/resources/views/mds/{*.blade.php,*/*.blade.php}', GLOB_BRACE);

        $this->assertNotEmpty($views);

        foreach ($views as $view) {
            $this->assertStringNotContainsString(
                '۰۱۲۳۴۵۶۷۸۹',
                (string) file_get_contents($view),
                basename(dirname($view)).'/'.basename($view).' inlines the digit map; include mds::partials.digits instead.',
            );
        }
    }

    public function test_the_remaining_controls_honour_the_error_contract(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag([
            'shade' => ['یک رنگ انتخاب کنید.'],
            'qty' => ['تعداد نامعتبر است.'],
            'score' => ['امتیاز لازم است.'],
        ]));

        View::share('errors', $bag);

        // color-picker sits in a field of its own, so it renders the message.
        $picker = $this->render('<mds:color-picker name="shade" />');
        $this->assertStringContainsString('یک رنگ انتخاب کنید.', $picker);
        $this->assertStringContainsString('data-flux-error', $picker);

        // quantity and rating.input are primitives placed inside the app's own
        // field, which already renders the message — a second one would double
        // up, so the bag only drives their invalid state.
        $quantity = $this->render('<mds:quantity name="qty" />');
        $this->assertStringContainsString('aria-invalid="true"', $quantity);
        $this->assertStringContainsString('border-red-500', $quantity);
        $this->assertStringNotContainsString('تعداد نامعتبر است.', $quantity);

        $rating = $this->render('<mds:rating.input name="score" />');
        $this->assertStringContainsString('aria-invalid="true"', $rating);

        // An explicit :error still wins over the bag.
        $explicit = $this->render('<mds:color-picker name="shade" error="پیام دستی" />');
        $this->assertStringContainsString('پیام دستی', $explicit);
        $this->assertStringNotContainsString('یک رنگ انتخاب کنید.', $explicit);

        // And a name with nothing in the bag stays clean.
        $this->assertStringNotContainsString('aria-invalid', $this->render('<mds:quantity name="other" />'));
    }

    public function test_command_registers_a_shared_alpine_component(): void
    {
        $html = $this->render('<mds:command><mds:command.items>x</mds:command.items></mds:command>');

        $this->assertStringContainsString('x-data="mdsCommand()"', $html);
        $this->assertStringContainsString("Alpine.data('mdsCommand'", $html);

        // The option list is cached in `options` with each item's normalised
        // text, so a keystroke no longer re-queries the DOM and re-normalises
        // every option for every other option.
        $this->assertStringContainsString('this.options = [', $html);
        $this->assertStringContainsString('el.dataset.mdsHaystack', $html);

        // …but the cache follows the DOM: a MutationObserver on the root
        // re-scans when a Livewire morph or an x-for adds, removes or relabels
        // items, and is disconnected on teardown. Once, items added after init
        // were unsearchable and unselectable.
        $this->assertStringContainsString('new MutationObserver(', $html);
        $this->assertStringContainsString('childList: true, subtree: true, characterData: true', $html);
        $this->assertStringContainsString('this.observer?.disconnect()', $html);
        $this->assertStringContainsString('refresh() {', $html);

        // Ids come from a running serial, not the array index — an item
        // inserted mid-list must not collide with a neighbour's id.
        $this->assertStringContainsString('this.$id(\'mds-command-option\', this.serial++)', $html);
    }

    public function test_command_input_is_wired_as_a_combobox(): void
    {
        $html = $this->render('<mds:command>
            <mds:command.input />
            <mds:command.items>
                <mds:command.heading>ناوبری</mds:command.heading>
                <mds:command.item>سفارش‌ها</mds:command.item>
            </mds:command.items>
        </mds:command>');

        // Without this pairing, arrowing the list moves a highlight that a
        // screen reader never hears about.
        $this->assertMatchesRegularExpression('/<input[^>]*role="combobox"/', $html);
        $this->assertStringContainsString('x-bind:aria-activedescendant="activeId"', $html);
        $this->assertStringContainsString("x-bind:aria-controls=\"\$id('mds-command-listbox')\"", $html);
        $this->assertStringContainsString("x-bind:id=\"\$id('mds-command-listbox')\"", $html);

        // A listbox takes options: the heading is a label and the empty
        // message is a status, so neither may sit inside it as a child.
        $this->assertMatchesRegularExpression('/data-mds-command-heading[^>]*role="presentation"|role="presentation"[^>]*data-mds-command-heading/', $html);
        $this->assertMatchesRegularExpression('/data-mds-command-empty|role="status"/', $html);
        $this->assertStringContainsString('role="status"', $html);
    }

    public function test_colour_area_exposes_an_axis_per_value(): void
    {
        $html = $this->render('<mds:color-picker type="button" />');

        // Two values cannot be one slider, and dragging is pointer-only — a
        // native range per axis gives both a name and keyboard control.
        $this->assertStringNotContainsString('role="slider"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="range"[^>]*data-mds-color-picker-axis="saturation"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="range"[^>]*data-mds-color-picker-axis="brightness"/', $html);
    }

    public function test_colour_picker_panel_is_a_dialog_that_manages_focus(): void
    {
        $html = $this->render('<mds:color-picker type="button" />');

        $this->assertMatchesRegularExpression('/data-mds-color-picker-panel|role="dialog"/', $html);
        $this->assertStringContainsString('role="dialog"', $html);
        $this->assertStringContainsString('aria-haspopup="dialog"', $html);
        $this->assertStringContainsString("x-bind:aria-controls=\"\$id('mds-color-picker-panel')\"", $html);

        // Escape has to return focus to the trigger, not drop it at the top
        // of the document.
        $this->assertStringContainsString('x-on:keydown.escape.window="toggle(false)"', $html);
        $this->assertStringContainsString('this.trigger?.focus()', $html);
    }

    public function test_colour_picker_disabled_is_not_merely_cosmetic(): void
    {
        $html = $this->render('<mds:color-picker disabled />');

        // The wrapper used to carry `pointer-events-none opacity-50`, which
        // only stops the mouse: every control stayed in the tab order and
        // fully operable by keyboard. (The area's thumb keeps its own
        // pointer-events-none, which is unrelated.)
        $this->assertStringNotContainsString('pointer-events-none opacity-50', $html);
        $this->assertStringContainsString('inert', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);

        $this->assertStringNotContainsString('inert', $this->render('<mds:color-picker />'));
    }

    public function test_rating_input_is_one_tab_stop_with_arrow_keys(): void
    {
        $html = $this->render('<mds:rating.input name="score" :value="3" />');

        // A radiogroup is a single tab stop: the checked star holds it and the
        // rest are skipped, rather than five stops in a row.
        $this->assertStringContainsString('x-bind:tabindex="tabindex(1)"', $html);
        $this->assertStringContainsString('x-on:keydown.home.prevent="focus(1)"', $html);

        // Down/Right = next, Up/Left = previous — the ARIA radio-group pattern.
        // Up/Down were once wired the other way round.
        $this->assertStringContainsString('x-on:keydown.down.prevent="step(1)"', $html);
        $this->assertStringContainsString('x-on:keydown.up.prevent="step(-1)"', $html);
        $this->assertStringContainsString('x-on:keydown.right.prevent="horizontal(1)"', $html);
        $this->assertStringContainsString('x-on:keydown.left.prevent="horizontal(-1)"', $html);

        // Arrows map the same way in RTL and LTR: nothing is read off the page
        // direction. Callers opt into a flipped horizontal pair explicitly.
        $this->assertStringNotContainsString('getComputedStyle', $html);
        $this->assertStringContainsString('reverse: false', $html);
        $this->assertStringContainsString('reverse: true', $this->render('<mds:rating.input name="score" reverse />'));
    }

    public function test_heatmap_cells_are_reachable_and_announce(): void
    {
        $html = $this->render('<mds:chart.heatmap :data="[1, 2, 3]" />');

        // Roving tabindex — a year of cells must not become 365 tab stops.
        $this->assertStringContainsString('x-bind:tabindex="active === 0 ? 0 : -1"', $html);
        $this->assertStringContainsString('x-on:keydown.down.prevent="move(1)"', $html);
        $this->assertMatchesRegularExpression('/<span[^>]*aria-label="۱ مورد"/', $html);
        $this->assertMatchesRegularExpression('/data-mds-chart-heatmap-callout[^>]*aria-live="polite"/', $html);
    }

    public function test_quantity_announces_the_value_as_it_changes(): void
    {
        $html = $this->render('<mds:quantity :value="2" />');

        $this->assertMatchesRegularExpression('/<span[^>]*aria-live="polite"[^>]*x-text="display\(\)"/', $html);
    }

    public function test_countdown_names_itself_and_its_units(): void
    {
        // Without visible labels the boxes would read as a bare run of digits.
        $plain = $this->render('<mds:countdown :until="now()->addHours(2)" />');

        $this->assertStringContainsString('role="timer"', $plain);
        $this->assertStringContainsString('<span class="sr-only">ساعت</span>', $plain);
        $this->assertStringContainsString('<span class="sr-only">دقیقه</span>', $plain);

        // With them, the unit is already on screen — no duplicate for AT.
        $labeled = $this->render('<mds:countdown :until="now()->addHours(2)" labels />');

        $this->assertStringNotContainsString('<span class="sr-only">ساعت</span>', $labeled);
    }

    public function test_stepper_says_which_step_of_how_many(): void
    {
        $html = $this->render('<mds:stepper :steps="[\'سبد\', \'آدرس\', \'پرداخت\']" :current="2" />');

        $this->assertStringContainsString('<span class="sr-only">مرحله ۱ از ۳</span>', $html);
        $this->assertStringContainsString('<span class="sr-only">مرحله ۳ از ۳</span>', $html);
    }

    public function test_composer_counter_describes_the_textarea(): void
    {
        $html = $this->render('<mds:composer counter :maxlength="500" />');

        $this->assertMatchesRegularExpression('/<textarea[^>]*aria-describedby="(mds-composer-counter-[a-f0-9]{8})"/', $html, 'textarea should point at its counter');

        preg_match('/aria-describedby="(mds-composer-counter-[a-f0-9]{8})"/', $html, $m);

        $this->assertStringContainsString('id="'.$m[1].'"', $html, 'the id it points at should exist');

        // No counter, nothing to describe.
        $this->assertStringNotContainsString('aria-describedby', $this->render('<mds:composer />'));
    }

    /**
     * A subcomponent that reads the config directly cannot see its parent's
     * choice, so `:fa="false"` on the wrapper left Persian strings in the
     * children. @aware is what carries it down.
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function faInheritance(): array
    {
        return [
            'file-upload to dropzone' => ['<mds:file-upload :fa="false"><mds:file-upload.dropzone /></mds:file-upload>', 'Drop a file here or click to browse', 'فایل را اینجا رها کنید یا برای انتخاب کلیک کنید'],
            'command to items' => ['<mds:command :fa="false"><mds:command.items>x</mds:command.items></mds:command>', 'No results found.', 'نتیجه‌ای یافت نشد.'],
            'command to input' => ['<mds:command :fa="false"><mds:command.input clearable closable /></mds:command>', 'aria-label="Clear"', 'aria-label="پاک کردن"'],
            'colour picker to area' => ['<mds:color-picker type="button" :fa="false" />', 'aria-label="Saturation"', 'aria-label="اشباع"'],
            'colour picker to hue slider' => ['<mds:color-picker type="button" :fa="false" />', 'aria-label="Hue"', 'aria-label="رنگ"'],
            'colour picker to dropper' => ['<mds:color-picker type="button" dropper :fa="false" />', 'aria-label="Eyedropper"', 'aria-label="قطره‌چکان"'],
            'file-item to remove' => ['<mds:file-item heading="a.pdf" :fa="false"><mds:file-item.remove /></mds:file-item>', 'aria-label="Remove file"', 'aria-label="حذف فایل"'],
            'product-card to price' => ['<mds:product-card title="x" :amount="800" :original="1000" :fa="false" />', 'aria-label="Original price"', 'aria-label="قیمت قبلی"'],
            'product-card to rating' => ['<mds:product-card title="x" :rating="4" :fa="false" />', '>4</span>', '>۴</span>'],
            'rating input' => ['<mds:rating.input name="s" :fa="false" />', 'aria-label="Rating"', 'aria-label="امتیاز"'],
        ];
    }

    #[DataProvider('faInheritance')]
    public function test_fa_reaches_the_subcomponents(string $template, string $english, string $persian): void
    {
        // Persian is the config default here, so English can only appear if
        // the prop travelled down.
        $html = $this->render($template);

        $this->assertStringContainsString($english, $html);
        $this->assertStringNotContainsString($persian, $html);
    }

    /**
     * mds:input is flux:input with the digits directive on the control — so
     * Flux's own field markup has to be there, and the directive has to sit on
     * the <input> itself, not on the wrapper Flux builds around it.
     */
    public function test_input_is_a_flux_input_carrying_the_digits_directive(): void
    {
        View::share('errors', new ViewErrorBag);

        $html = $this->render('<mds:input label="Mobile number" placeholder="0912" />');

        $this->assertStringContainsString('data-flux-input', $html);
        $this->assertStringContainsString('Mobile number', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\sx-mds-digits=""/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\sdata-mds-input=""/', $html);

        // Without the flags, nothing is added beyond the directive.
        $this->assertStringNotContainsString('x-mds-digits.only', $html);
        $this->assertStringNotContainsString('inputmode=', $html);
        $this->assertStringNotContainsString('data-ltr', $html);
    }

    public function test_input_only_keeps_digits_and_asks_for_a_numeric_keyboard(): void
    {
        View::share('errors', new ViewErrorBag);

        $html = $this->render('<mds:input only ltr />');

        $this->assertMatchesRegularExpression('/<input[^>]*\sx-mds-digits\.only=""/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\sinputmode="numeric"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\sdata-ltr=""/', $html);

        // The defaults yield to the caller's own attributes.
        $html = $this->render('<mds:input only inputmode="tel" />');

        $this->assertStringContainsString('inputmode="tel"', $html);
        $this->assertStringNotContainsString('inputmode="numeric"', $html);
    }

    public function test_input_forwards_wire_model_to_the_real_control(): void
    {
        View::share('errors', new ViewErrorBag);

        $html = $this->render('<mds:input wire:model.live="mobile" />');

        $this->assertBindingReachesControl($html, 'input', 'wire:model.live="mobile"');
    }

    public function test_input_inherits_flux_validation_state_from_the_error_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['mobile' => ['The mobile number is required.']]));
        View::share('errors', $bag);

        $html = $this->render('<mds:input name="mobile" />');

        // The invalid state is Flux's, read from the bag by name — the same
        // contract a bare flux:input has. (Flux fills the message block itself;
        // that is its own behaviour, not asserted here.)
        $this->assertMatchesRegularExpression('/<input[^>]*\saria-invalid="true"/', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\sdata-invalid/', $html);
    }

    public function test_input_registers_the_digits_directive_once_per_page(): void
    {
        View::share('errors', new ViewErrorBag);

        $page = $this->render('<mds:input /><mds:input only /><mds:quantity :value="1" />');

        $this->assertSame(1, substr_count($page, "Alpine.directive('mds-digits'"));
        $this->assertSame(1, substr_count($page, 'window.mds.latinDigits ='));

        // Registration must survive Alpine having started already (wire:navigate),
        // so the block registers directly when window.Alpine exists.
        $this->assertStringContainsString('if (window.Alpine) {', $page);
        $this->assertStringContainsString("addEventListener('alpine:init'", $page);
    }

    /**
     * The Iranian variants are attribute presets over mds:input — each has
     * to reach the real control, keep the caller's overrides, and pick the
     * right half of the only/mask trade-off (a mask owns the value's shape).
     *
     * @return array<string, array{0: string, 1: string, 2: list<string>, 3: list<string>}>
     */
    public static function inputVariants(): array
    {
        return [
            'mobile' => ['mobile', 'data-mds-input-mobile', ['x-mds-digits.only=""', 'data-ltr=""', 'type="tel"', 'maxlength="14"', 'autocomplete="tel-national"', 'inputmode="numeric"'], ['x-mask']],
            'national-id' => ['national-id', 'data-mds-input-national-id', ['x-mds-digits.only=""', 'data-ltr=""', 'maxlength="10"', 'autocomplete="off"'], ['x-mask', 'type="tel"']],
            'card' => ['card', 'data-mds-input-card', ['x-mds-digits=""', 'data-ltr=""', 'x-mask="9999 9999 9999 9999"', 'inputmode="numeric"', 'autocomplete="cc-number"'], ['x-mds-digits.only', 'maxlength']],
            'sheba' => ['sheba', 'data-mds-input-sheba', ['x-mds-digits=""', 'data-ltr=""', 'x-mask="IR99 9999 9999 9999 9999 9999 99"', 'inputmode="numeric"'], ['x-mds-digits.only', 'maxlength']],
        ];
    }

    /**
     * @param  list<string>  $present
     * @param  list<string>  $absent
     */
    #[DataProvider('inputVariants')]
    public function test_input_variants_preset_the_control(string $variant, string $marker, array $present, array $absent): void
    {
        View::share('errors', new ViewErrorBag);

        $html = $this->render("<mds:input.{$variant} wire:model=\"value\" label=\"Field\" />");

        preg_match('/<input\b[^>]*>/', $html, $control);

        $this->assertNotEmpty($control, "<mds:input.{$variant}> rendered no <input>.");
        $this->assertStringContainsString($marker.'=""', $control[0]);

        foreach ($present as $needle) {
            $this->assertStringContainsString($needle, $control[0], "<mds:input.{$variant}> lacks {$needle}.");
        }

        foreach ($absent as $needle) {
            $this->assertStringNotContainsString($needle, $control[0], "<mds:input.{$variant}> must not carry {$needle}.");
        }

        $this->assertBindingReachesControl($html, 'input', 'wire:model="value"');

        // The presets are defaults: a caller's own attribute wins.
        $html = $this->render("<mds:input.{$variant} placeholder=\"custom\" autocomplete=\"one-time-code\" />");

        $this->assertStringContainsString('placeholder="custom"', $html);
        $this->assertStringContainsString('autocomplete="one-time-code"', $html);
        $this->assertSame(1, substr_count($html, 'autocomplete='));
    }

    /**
     * Every built-in string ships in both languages, and `fa` — or the
     * `mds.persian_digits` default behind it — picks which. One table feeds
     * both directions, so the languages cannot drift apart the way they did
     * while each was a hand-kept list: Persian is the kit's default and was
     * the less covered of the two.
     *
     * Needles are anchored where a bare word would lie. `items` is inside
     * `items-center`, `min` inside `min-w-8`, and `رنگ` (Hue) inside
     * `انتخاب رنگ` (Pick a color).
     *
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function microcopy(): array
    {
        return [
            'command empty state' => ['<mds:command><mds:command.items>x</mds:command.items></mds:command>', 'نتیجه‌ای یافت نشد.', 'No results found.'],
            'command clear' => ['<mds:command.input clearable />', 'aria-label="پاک کردن"', 'aria-label="Clear"'],
            'command close' => ['<mds:command.input closable />', 'aria-label="بستن"', 'aria-label="Close"'],
            'file upload dropzone' => ['<mds:file-upload><mds:file-upload.dropzone /></mds:file-upload>', 'فایل را اینجا رها کنید یا برای انتخاب کلیک کنید', 'Drop a file here or click to browse'],
            'file item remove' => ['<mds:file-item.remove />', 'aria-label="حذف فایل"', 'aria-label="Remove file"'],
            'quantity increase' => ['<mds:quantity :value="1" />', 'aria-label="افزایش تعداد"', 'aria-label="Increase quantity"'],
            'quantity decrease' => ['<mds:quantity :value="1" />', 'aria-label="کاهش تعداد"', 'aria-label="Decrease quantity"'],
            'rating input' => ['<mds:rating.input name="score" />', 'aria-label="امتیاز"', 'aria-label="Rating"'],
            'rating star digit' => ['<mds:rating.input name="score" :value="3" />', 'aria-label="۳"', 'aria-label="3"'],
            'stepper' => ['<mds:stepper :steps="[\'a\', \'b\']" :current="1" />', 'aria-label="مراحل"', 'aria-label="Steps"'],
            'color picker trigger' => ['<mds:color-picker type="button" />', 'aria-label="انتخاب رنگ"', 'aria-label="Pick a color"'],
            'color picker clear' => ['<mds:color-picker clearable />', 'aria-label="پاک کردن"', 'aria-label="Clear"'],
            'color picker area' => ['<mds:color-picker type="button" />', 'aria-label="اشباع و روشنایی"', 'aria-label="Saturation and brightness"'],
            'color picker hue' => ['<mds:color-picker type="button" />', 'aria-label="رنگ"', 'aria-label="Hue"'],
            'color picker opacity' => ['<mds:color-picker type="button" alpha />', 'aria-label="شفافیت"', 'aria-label="Opacity"'],
            'color picker dropper' => ['<mds:color-picker type="button" dropper />', 'aria-label="قطره‌چکان"', 'aria-label="Eyedropper"'],
            'price original' => ['<mds:price :amount="800" :original="1000" currency="none" />', 'aria-label="قیمت قبلی"', 'aria-label="Original price"'],
            'price currency' => ['<mds:price :amount="800" currency="toman" />', 'تومان', 'Toman'],
            'product card unavailable' => ['<mds:product-card title="x" unavailable />', 'ناموجود', 'Out of stock'],
            'discount badge' => ['<mds:discount-badge :percent="25" />', 'درصد تخفیف', '% off'],
            'countdown expired' => ['<mds:countdown :until="now()->subMinute()" />', 'به پایان رسید', 'Expired'],
            'countdown unit days' => ['<mds:countdown :until="now()->addDays(2)" labels />', '>روز</span>', '>days</span>'],
            'countdown unit hours' => ['<mds:countdown :until="now()->addDays(2)" labels />', '>ساعت</span>', '>hours</span>'],
            'countdown unit min' => ['<mds:countdown :until="now()->addDays(2)" labels />', '>دقیقه</span>', '>min</span>'],
            'countdown unit sec' => ['<mds:countdown :until="now()->addDays(2)" labels />', '>ثانیه</span>', '>sec</span>'],
            'colour area saturation' => ['<mds:color-picker type="button" />', 'aria-label="اشباع"', 'aria-label="Saturation"'],
            'colour area brightness' => ['<mds:color-picker type="button" />', 'aria-label="روشنایی"', 'aria-label="Brightness"'],
            'countdown timer name' => ['<mds:countdown :until="now()->addHour()" />', 'aria-label="زمان باقی‌مانده"', 'aria-label="Time remaining"'],
            'upload progress name' => ['<mds:file-upload><mds:file-upload.dropzone with-progress /></mds:file-upload>', 'aria-label="در حال بارگذاری"', 'aria-label="Uploading"'],
            'stepper step position' => ['<mds:stepper :steps="[\'a\', \'b\']" :current="1" />', 'مرحله ۱ از ۲', 'Step 1 of 2'],
            'chart bars' => ['<mds:chart.bars :data="[1, 2]" />', 'aria-label="نمودار ستونی"', 'aria-label="Bar chart"'],
            'chart line' => ['<mds:chart.line :data="[1, 2]" />', 'aria-label="نمودار خطی"', 'aria-label="Line chart"'],
            'chart radar' => ['<mds:chart.radar :data="[\'a\' => 1, \'b\' => 2, \'c\' => 3]" />', 'aria-label="نمودار راداری"', 'aria-label="Radar chart"'],
            'chart heatmap idle' => ['<mds:chart.heatmap :data="[1, 2, 3]" />', 'برای جزئیات روی خانه‌ها بروید', 'Hover tiles for details'],
            'chart heatmap unit' => ['<mds:chart.heatmap :data="[1, 2, 3]" />', 'مورد"', 'items"'],
            'popover close' => ['<mds:popover><mds:popover.content closable>x</mds:popover.content></mds:popover>', 'aria-label="بستن"', 'aria-label="Close"'],
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
}
