<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use MajidDs\Tests\TestCase;

class ComponentsTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
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
        $this->assertStringContainsString('ریال', $html);
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
        $this->assertStringContainsString('۲', $html);
    }

    public function test_quantity_forwards_wire_model_to_hidden_input(): void
    {
        $html = $this->render('<mds:quantity wire:model="qty" />');

        $this->assertStringContainsString('wire:model="qty"', $html);
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
        $html = $this->render('<mds:countdown :until="now()->addHours(2)" :days="false" />');

        $this->assertStringContainsString('data-mds-countdown', $html);
        $this->assertStringContainsString('۰۱', $html);
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
        // An item with href renders as a link, others as buttons...
        $this->assertStringContainsString('href="/support"', $html);
        $this->assertStringContainsString('type="button"', $html);
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
        // The Alpine data component script is emitted...
        $this->assertStringContainsString('mdsColorPicker', $html);
    }

    public function test_color_picker_custom_swatches_alpha_format_and_button_type(): void
    {
        $html = $this->render('<mds:color-picker type="button" format="rgba" :swatches="[[\'#ef4444\', \'قرمز\'], \'#3b82f6\']" wire:model="color" />');

        $this->assertStringContainsString('aria-label="قرمز"', $html);
        $this->assertStringContainsString('aria-label="#3b82f6"', $html);
        $this->assertSame(2, substr_count($html, 'data-mds-color-picker-swatch') - substr_count($html, 'data-mds-color-picker-swatches'));
        $this->assertStringContainsString("format: 'rgba'", $html);
        $this->assertStringContainsString('wire:model="color"', $html);
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
        $this->assertStringContainsString('multiple', $html);
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

        // wire:model must land on the file input itself, not the wrapper...
        $this->assertMatchesRegularExpression('/<input\s[^>]*type="file"[^>]*wire:model="photos"/', $html);
    }

    public function test_file_upload_disabled_state(): void
    {
        $html = $this->render('<mds:file-upload disabled />');

        $this->assertStringContainsString('disabled', $html);
        $this->assertStringContainsString('cursor-not-allowed', $html);
    }

    public function test_file_upload_renders_error_message(): void
    {
        $html = $this->render('<mds:file-upload error="حجم فایل بیش از حد مجاز است." />');

        $this->assertStringContainsString('حجم فایل بیش از حد مجاز است.', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
    }

    public function test_file_upload_falls_back_to_the_validation_error_bag(): void
    {
        $bag = new \Illuminate\Support\ViewErrorBag;
        $bag->put('default', new \Illuminate\Support\MessageBag(['photos' => ['هر فایل باید کمتر از ۱۰ مگابایت باشد.']]));

        // ShareErrorsFromSession shares the bag view-wide; mirror that here...
        \Illuminate\Support\Facades\View::share('errors', $bag);

        $html = $this->render('<mds:file-upload name="photos" multiple />');

        $this->assertStringContainsString('هر فایل باید کمتر از ۱۰ مگابایت باشد.', $html);
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
}
