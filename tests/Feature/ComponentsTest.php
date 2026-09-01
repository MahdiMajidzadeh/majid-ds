<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Mds;
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
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['photos' => ['هر فایل باید کمتر از ۱۰ مگابایت باشد.']]));

        // ShareErrorsFromSession shares the bag view-wide; mirror that here...
        View::share('errors', $bag);

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

        // wire:model must land on the textarea itself, not the wrapper...
        $this->assertMatchesRegularExpression('/<textarea\s[^>]*wire:model="prompt"/', $html);
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
        $html = $this->render('<mds:composer variant="input" />');

        $this->assertStringContainsString('rounded-lg', $html);
        $this->assertStringNotContainsString('rounded-2xl', $html);
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
        foreach (['days', 'hours', 'min', 'sec'] as $label) {
            $this->assertStringContainsString($label, $labeled);
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

    public function test_microcopy_follows_the_persian_digits_config(): void
    {
        config(['mds.persian_digits' => false]);

        try {
            $this->assertStringContainsString('No results found.',
                $this->render('<mds:command><mds:command.items>x</mds:command.items></mds:command>'));

            $this->assertStringContainsString('Drop a file here or click to browse',
                $this->render('<mds:file-upload><mds:file-upload.dropzone /></mds:file-upload>'));

            $this->assertStringContainsString('aria-label="Increase quantity"',
                $this->render('<mds:quantity :value="1" />'));

            $this->assertStringContainsString('aria-label="Rating"',
                $this->render('<mds:rating.input name="score" />'));

            $this->assertStringContainsString('aria-label="Steps"',
                $this->render('<mds:stepper :steps="[\'Cart\', \'Payment\']" :current="1" />'));

            $this->assertStringContainsString('aria-label="Remove file"',
                $this->render('<mds:file-item.remove />'));

            $this->assertStringContainsString('aria-label="Pick a color"',
                $this->render('<mds:color-picker type="button" />'));

            $this->assertStringContainsString('aria-label="Original price"',
                $this->render('<mds:price :amount="800" :original="1000" currency="none" />'));

            $this->assertStringContainsString('Toman',
                $this->render('<mds:price :amount="800" currency="toman" />'));

            $this->assertStringContainsString('aria-label="3"',
                $this->render('<mds:rating.input name="score" :value="3" />'));
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }
}
