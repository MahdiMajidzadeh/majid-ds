<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use MajidDs\Tests\TestCase;

class CarouselTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * Three slides, the shape every test starts from.
     */
    protected function carousel(string $attributes = '', int $slides = 3): string
    {
        $items = '';

        for ($i = 1; $i <= $slides; $i++) {
            $items .= "<mds:carousel.item><p>slide {$i}</p></mds:carousel.item>";
        }

        return $this->render("<mds:carousel {$attributes}>{$items}</mds:carousel>");
    }

    protected function english(callable $callback): void
    {
        config(['mds.persian_digits' => false]);

        try {
            $callback();
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }

    public function test_root_is_a_labelled_region_with_the_carousel_role_description(): void
    {
        $html = $this->carousel();

        $this->assertMatchesRegularExpression('/<div[^>]*\sdata-mds-carousel[\s>]/', $html);
        $this->assertStringContainsString('role="region"', $html);
        $this->assertStringContainsString('aria-roledescription="اسلایدشو"', $html);
        $this->assertStringContainsString('aria-label="اسلایدشو"', $html);
        $this->assertStringContainsString('x-data="mdsCarousel(', $html);
        $this->assertStringContainsString('data-mds-carousel-track', $html);
        $this->assertStringContainsString('x-on:keydown="keydown($event)"', $html);
    }

    public function test_label_prop_names_the_region(): void
    {
        $html = $this->carousel('label="Featured products"');

        $this->assertStringContainsString('aria-label="Featured products"', $html);
        $this->assertStringNotContainsString('aria-label="اسلایدشو"', $html);
    }

    public function test_items_are_slide_groups(): void
    {
        $html = $this->carousel();

        $this->assertSame(3, preg_match_all('/\sdata-mds-carousel-item\s*>/', $html));
        $this->assertSame(3, substr_count($html, 'role="group"'));
        $this->assertSame(3, substr_count($html, 'aria-roledescription="اسلاید"'));
        $this->assertStringContainsString('snap-start', $html);
        $this->assertStringContainsString('<p>slide 2</p>', $html);

        // The slide count reaches Alpine, which labels each slide "n of total".
        $this->assertStringContainsString('total: 3,', $html);
    }

    public function test_attributes_land_on_the_root_and_on_the_item(): void
    {
        $html = $this->render('<mds:carousel class="mt-4" id="hero"><mds:carousel.item class="bg-red-500">x</mds:carousel.item></mds:carousel>');

        $this->assertMatchesRegularExpression('/<div[^>]*class="relative w-full mt-4"[^>]*id="hero"/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*class="min-w-0 shrink-0 snap-start bg-red-500"[^>]*data-mds-carousel-item/', $html);
    }

    public function test_controls_render_by_default_and_can_be_turned_off(): void
    {
        $html = $this->carousel();

        $this->assertStringContainsString('data-mds-carousel-prev', $html);
        $this->assertStringContainsString('data-mds-carousel-next', $html);
        $this->assertStringContainsString('aria-label="قبلی"', $html);
        $this->assertStringContainsString('aria-label="بعدی"', $html);
        $this->assertSame(2, substr_count($html, 'x-bind:aria-controls="$id(\'mds-carousel-track\')"'));
        $this->assertStringContainsString('x-bind:id="$id(\'mds-carousel-track\')"', $html);

        // Both chevrons on each button, one hidden per direction.
        $this->assertSame(2, substr_count($html, 'rtl:hidden'));
        $this->assertSame(2, substr_count($html, 'ltr:hidden'));

        $off = $this->carousel(':controls="false"');

        $this->assertStringNotContainsString('data-mds-carousel-prev', $off);
        $this->assertStringNotContainsString('data-mds-carousel-next', $off);
        $this->assertStringNotContainsString('aria-label="قبلی"', $off);
    }

    public function test_indicators_render_one_dot_per_slide_and_can_be_turned_off(): void
    {
        $html = $this->carousel();

        $this->assertStringContainsString('data-mds-carousel-indicators', $html);
        $this->assertSame(3, substr_count($html, 'data-mds-carousel-dot'));
        $this->assertStringContainsString('aria-label="رفتن به اسلاید ۱"', $html);
        $this->assertStringContainsString('aria-label="رفتن به اسلاید ۳"', $html);
        $this->assertStringContainsString('x-on:click="go(2)"', $html);

        // Only the starting dot is current.
        $this->assertSame(1, substr_count($html, 'aria-current="true"'));
        $this->assertMatchesRegularExpression('/x-on:click="go\(0\)"[^>]*aria-current="true"/', $html);

        $off = $this->carousel(':indicators="false"');

        $this->assertStringNotContainsString('data-mds-carousel-indicators', $off);
        $this->assertStringNotContainsString('data-mds-carousel-dot', $off);
    }

    public function test_autoplay_configures_the_interval_and_adds_a_pause_button(): void
    {
        $html = $this->carousel('autoplay :interval="3000"');

        $this->assertStringContainsString('autoplay: true,', $html);
        $this->assertStringContainsString('interval: 3000,', $html);
        $this->assertStringContainsString('data-mds-carousel-toggle', $html);
        $this->assertStringContainsString('aria-label="توقف"', $html);
        // Js::from() escapes the quotes, so the label object survives the attribute.
        // Decoded first: this Laravel may or may not escape the Persian
        // inside @js() — see TestCase::jsDecoded().
        $this->assertStringContainsString('"pause":"توقف","play":"پخش"', $this->jsDecoded($html));
        $this->assertStringContainsString('x-bind:aria-label="playing ? labels.pause : labels.play"', $html);

        // Silent while rotating, per the WAI-ARIA carousel guidance.
        $this->assertMatchesRegularExpression('/aria-live="off"[^>]*data-mds-carousel-status/', $html);
        $this->assertStringContainsString('x-bind:aria-live="playing ? \'off\' : \'polite\'"', $html);
    }

    public function test_without_autoplay_there_is_no_pause_button_and_the_status_is_polite(): void
    {
        $html = $this->carousel();

        $this->assertStringContainsString('autoplay: false,', $html);
        $this->assertStringContainsString('interval: 5000,', $html);
        $this->assertStringNotContainsString('data-mds-carousel-toggle', $html);
        $this->assertStringNotContainsString('aria-label="توقف"', $html);
        $this->assertMatchesRegularExpression('/aria-live="polite"[^>]*data-mds-carousel-status/', $html);
    }

    public function test_interval_has_a_floor(): void
    {
        $html = $this->carousel('autoplay :interval="10"');

        $this->assertStringContainsString('interval: 500,', $html);
    }

    public function test_per_view_sets_the_custom_property_and_shrinks_the_dot_count(): void
    {
        $html = $this->carousel('per-view="3"', 5);

        $this->assertStringContainsString('--mds-carousel-per-view: 3;', $html);
        $this->assertStringContainsString('perView: 3,', $html);

        // Five slides, three at a time: three positions to start from.
        $this->assertSame(3, substr_count($html, 'data-mds-carousel-dot'));

        // The item width is a share of the track that accounts for the gaps.
        $this->assertStringContainsString('flex: 0 0 calc((100% - (var(--mds-carousel-per-view, 1) - 1) * var(--mds-carousel-gap, 0px)) / var(--mds-carousel-per-view, 1))', $html);
    }

    public function test_per_view_larger_than_the_slide_count_still_renders_one_dot(): void
    {
        $html = $this->carousel('per-view="4"', 2);

        $this->assertSame(1, substr_count($html, 'data-mds-carousel-dot'));
    }

    public function test_gap_class_lands_on_the_track_and_becomes_a_length(): void
    {
        $default = $this->carousel();

        $this->assertMatchesRegularExpression('/class="[^"]*\sgap-0[\s"][^>]*data-mds-carousel-track/', $default);
        $this->assertStringContainsString('--mds-carousel-gap: 0px"', $default);

        $scale = $this->carousel('gap="gap-4"');

        $this->assertMatchesRegularExpression('/class="[^"]*\sgap-4[\s"][^>]*data-mds-carousel-track/', $scale);
        $this->assertStringContainsString('--mds-carousel-gap: calc(var(--spacing) * 4)"', $scale);

        $arbitrary = $this->carousel('gap="gap-[10px]"');

        $this->assertStringContainsString('--mds-carousel-gap: 10px"', $arbitrary);

        $px = $this->carousel('gap="gap-px"');

        $this->assertStringContainsString('--mds-carousel-gap: 1px"', $px);
    }

    public function test_aspect_class_lands_on_the_track(): void
    {
        $html = $this->carousel('aspect="aspect-video"');

        $this->assertMatchesRegularExpression('/class="[^"]*\saspect-video"[^>]*data-mds-carousel-track/', $html);
    }

    public function test_start_picks_the_initial_slide(): void
    {
        $html = $this->carousel('start="2"');

        $this->assertStringContainsString('start: 2,', $html);
        $this->assertMatchesRegularExpression('/x-on:click="go\(2\)"[^>]*aria-current="true"/', $html);
        $this->assertSame(1, substr_count($html, 'aria-current="true"'));
        $this->assertStringContainsString('data-mds-carousel-status>اسلاید ۳ از ۳</div>', $html);
    }

    public function test_start_is_clamped_to_the_slides(): void
    {
        $html = $this->carousel('start="9"');

        $this->assertStringContainsString('start: 2,', $html);

        $negative = $this->carousel('start="-1"');

        $this->assertStringContainsString('start: 0,', $negative);
    }

    public function test_loop_off_disables_the_edge_buttons_server_side(): void
    {
        $html = $this->carousel(':loop="false"');

        $this->assertStringContainsString('loop: false,', $html);
        $this->assertMatchesRegularExpression('/<button[^>]*\sdisabled\s[^>]*data-mds-carousel-prev/', $html);
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*\sdisabled\s[^>]*data-mds-carousel-next/', $html);

        $end = $this->carousel(':loop="false" start="2"');

        $this->assertDoesNotMatchRegularExpression('/<button[^>]*\sdisabled\s[^>]*data-mds-carousel-prev/', $end);
        $this->assertMatchesRegularExpression('/<button[^>]*\sdisabled\s[^>]*data-mds-carousel-next/', $end);

        $looping = $this->carousel();

        $this->assertStringContainsString('loop: true,', $looping);
        $this->assertDoesNotMatchRegularExpression('/<button[^>]*\sdisabled\s/', $looping);
    }

    public function test_status_line_announces_the_current_slide(): void
    {
        $html = $this->carousel();

        $this->assertStringContainsString('data-mds-carousel-status>اسلاید ۱ از ۳</div>', $html);
        $this->assertStringContainsString('aria-atomic="true"', $html);
        $this->assertStringContainsString('x-text="status"', $html);
    }

    public function test_english_microcopy_when_persian_output_is_off(): void
    {
        $this->english(function () {
            $html = $this->carousel('autoplay');

            $this->assertStringContainsString('aria-roledescription="carousel"', $html);
            $this->assertStringContainsString('aria-label="Slideshow"', $html);
            $this->assertSame(3, substr_count($html, 'aria-roledescription="slide"'));
            $this->assertStringContainsString('aria-label="Previous"', $html);
            $this->assertStringContainsString('aria-label="Next"', $html);
            $this->assertStringContainsString('aria-label="Pause"', $html);
            $this->assertStringContainsString('aria-label="Go to slide 1"', $html);
            $this->assertStringContainsString('data-mds-carousel-status>Slide 1 of 3</div>', $html);
            $this->assertStringContainsString('fa: false,', $html);
            $this->assertStringNotContainsString('اسلاید', $html);
        });
    }

    public function test_fa_prop_reaches_the_items(): void
    {
        $html = $this->carousel(':fa="false"');

        $this->assertStringContainsString('aria-roledescription="carousel"', $html);
        $this->assertSame(3, substr_count($html, 'aria-roledescription="slide"'));
        $this->assertStringContainsString('aria-label="Go to slide 2"', $html);
        $this->assertStringContainsString('fa: false,', $html);

        // An item can still override on its own.
        $mixed = $this->render('<mds:carousel :fa="false"><mds:carousel.item :fa="true">x</mds:carousel.item></mds:carousel>');

        $this->assertStringContainsString('aria-roledescription="اسلاید"', $mixed);
        $this->assertStringContainsString('aria-roledescription="carousel"', $mixed);
    }

    public function test_alpine_component_is_registered_once_per_page(): void
    {
        $html = $this->render('<mds:carousel><mds:carousel.item>a</mds:carousel.item></mds:carousel><mds:carousel><mds:carousel.item>b</mds:carousel.item></mds:carousel>');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsCarousel'"));
        $this->assertSame(2, substr_count($html, 'x-data="mdsCarousel('));

        // The started-Alpine guard, so a wire:navigate page registers too.
        $this->assertStringContainsString('window.mds.registerCarousel = (Alpine) =>', $html);
        $this->assertStringContainsString('if (window.Alpine)', $html);
    }

    public function test_keyboard_autoplay_and_cleanup_behaviour_is_in_the_script(): void
    {
        $html = $this->carousel();

        // Arrows follow the visual direction; Home/End jump to the edges.
        $this->assertStringContainsString("getComputedStyle(this.\$root).direction === 'rtl'", $html);
        $this->assertStringContainsString('ArrowRight: () => rtl ? this.prev() : this.next()', $html);
        $this->assertStringContainsString('Home: () => this.go(0)', $html);
        $this->assertStringContainsString('End: () => this.go(this.last)', $html);

        // Reduced motion, hidden tab, hover and focus all hold the rotation.
        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $html);
        $this->assertStringContainsString('this.hovered || this.focused || this.scrolling || document.hidden', $html);

        // Observers and timers die with the component.
        $this->assertStringContainsString('clearInterval(this.timer)', $html);
        $this->assertStringContainsString('this.observer?.disconnect()', $html);
        $this->assertStringContainsString('this.watcher?.disconnect()', $html);
        $this->assertStringContainsString('threshold: 0.6', $html);
        // The slide is never asked to bring itself into view: scrollIntoView()
        // scrolls every scrollable ancestor, so an autoplaying carousel walked
        // the whole page down to itself every few seconds. Measured on the docs
        // page — 22px to 1832px in under eight seconds with nobody touching it.
        // The track scrolls itself instead, which cannot move anything else.
        // (the call, not the word — the view's comment explains the history)
        $this->assertStringNotContainsString('.scrollIntoView(', $html);
        $this->assertStringContainsString('track.scrollBy({', $html);
        $this->assertStringContainsString('rtl ? e.right - t.right : e.left - t.left', $html);
    }

    public function test_empty_carousel_renders_without_dots_or_status_text(): void
    {
        $html = $this->render('<mds:carousel></mds:carousel>');

        $this->assertStringContainsString('data-mds-carousel', $html);
        $this->assertStringContainsString('total: 0,', $html);
        $this->assertStringNotContainsString('data-mds-carousel-indicators', $html);
        $this->assertStringContainsString('data-mds-carousel-status></div>', $html);
    }

    public function test_view_does_not_inline_its_own_digit_map(): void
    {
        foreach (['index', 'item'] as $view) {
            $source = (string) file_get_contents(dirname(__DIR__, 2)."/resources/views/mds/carousel/{$view}.blade.php");

            $this->assertStringNotContainsString('۰۱۲۳۴۵۶۷۸۹', $source);
        }
    }
}
