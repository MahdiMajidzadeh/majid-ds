<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use MajidDs\Tests\TestCase;

/**
 * <mds:accordion> — the open version of Flux Pro's flux:accordion, built on
 * native details/summary. The browser owns toggling, focus and the expanded
 * state; the markup contract asserted here is what Alpine layers on top and
 * what has to be right before Alpine boots.
 */
class AccordionTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    private const COMPOSED = <<<'BLADE'
        <mds:accordion exclusive transition>
            <mds:accordion.item expanded>
                <mds:accordion.heading>Shipping</mds:accordion.heading>
                <mds:accordion.content>Orders ship within two working days.</mds:accordion.content>
            </mds:accordion.item>
            <mds:accordion.item heading="Returns">Fourteen days, no questions asked.</mds:accordion.item>
            <mds:accordion.item heading="Warranty" disabled>Two years on every device.</mds:accordion.item>
        </mds:accordion>
        BLADE;

    public function test_it_renders_a_details_list_with_the_kit_markers(): void
    {
        $html = $this->render(self::COMPOSED);

        $this->assertMatchesRegularExpression('/<div[^>]*divide-y divide-zinc-200 dark:divide-white\/10[^>]*data-mds-accordion\s*>/', $html);
        $this->assertSame(3, substr_count($html, '<details'), 'One details element per item.');
        $this->assertSame(3, preg_match_all('/<details[^>]*\sdata-mds-accordion-item\s*>/', $html));
        $this->assertSame(3, substr_count($html, '<summary'));
        $this->assertSame(3, preg_match_all('/<summary[^>]*\sdata-mds-accordion-heading\s*>/', $html));
        $this->assertSame(3, preg_match_all('/<div[^>]*\sdata-mds-accordion-content\s*>/', $html));
        $this->assertStringContainsString('Orders ship within two working days.', $html);
    }

    public function test_the_heading_is_a_native_summary_that_acts_as_the_toggle(): void
    {
        $html = $this->render('<mds:accordion><mds:accordion.item heading="Shipping">x</mds:accordion.item></mds:accordion>');

        $this->assertMatchesRegularExpression('/<summary[^>]*\srole="button"/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\stext-start\s/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\sfont-medium\s/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\sx-on:click="toggle\(\$event\)"/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\sx-bind:aria-expanded="expanded \? \'true\' : \'false\'"/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*focus-visible:outline-accent/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\[&amp;::-webkit-details-marker\]:hidden/', $html, 'Safari still draws the disclosure triangle unless the marker pseudo is hidden.');
        $this->assertMatchesRegularExpression('/<span class="min-w-0 flex-1">Shipping<\/span>/', $html);
    }

    public function test_the_chevron_is_a_decorative_icon_that_rotates_with_the_state(): void
    {
        $closed = $this->render('<mds:accordion><mds:accordion.item heading="A">x</mds:accordion.item></mds:accordion>');
        $open = $this->render('<mds:accordion><mds:accordion.item heading="A" expanded>x</mds:accordion.item></mds:accordion>');

        foreach ([$closed, $open] as $html) {
            $this->assertMatchesRegularExpression('/<svg[^>]*data-mds-accordion-chevron/', $html);
            $this->assertMatchesRegularExpression('/<svg[^>]*aria-hidden="true"[^>]*data-mds-accordion-chevron/', $html);
            $this->assertMatchesRegularExpression('/<svg[^>]*motion-safe:transition-transform[^>]*data-mds-accordion-chevron/', $html);
            $this->assertMatchesRegularExpression('/<svg[^>]*x-bind:class="\{ &#039;rotate-180&#039;: expanded \}"/', $html);
        }

        $this->assertStringNotContainsString('rotate-180 size-4', $closed);
        $this->assertStringContainsString('rotate-180 size-4', $open);
    }

    public function test_expanded_renders_open_and_mirrors_the_state_for_assistive_tech(): void
    {
        $html = $this->render('<mds:accordion><mds:accordion.item heading="A" expanded>x</mds:accordion.item></mds:accordion>');

        $this->assertMatchesRegularExpression('/<details[^>]*\sopen\s/', $html);
        $this->assertMatchesRegularExpression('/<details[^>]*\sdata-expanded\s/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\saria-expanded="true"/', $html);
        $this->assertStringContainsString('expanded: true,', $html);
    }

    public function test_items_are_closed_by_default(): void
    {
        $html = $this->render('<mds:accordion><mds:accordion.item heading="A">x</mds:accordion.item></mds:accordion>');

        $this->assertDoesNotMatchRegularExpression('/<details[^>]*\sopen[\s>]/', $html);
        $this->assertDoesNotMatchRegularExpression('/<details[^>]*\sdata-expanded[\s>]/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\saria-expanded="false"/', $html);
        $this->assertStringContainsString('expanded: false,', $html);
        $this->assertMatchesRegularExpression('/<details[^>]*\sx-bind:data-expanded="expanded \? \'\' : false"/', $html, 'Alpine keeps data-expanded in step with the toggle event.');
        $this->assertMatchesRegularExpression('/<details[^>]*\sx-on:toggle="onToggle\(\)"/', $html);
    }

    public function test_a_disabled_item_cannot_be_reached_or_toggled(): void
    {
        $html = $this->render('<mds:accordion><mds:accordion.item heading="A" disabled>x</mds:accordion.item></mds:accordion>');

        $this->assertMatchesRegularExpression('/<details[^>]*\sdata-disabled\s/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\saria-disabled="true"/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\stabindex="-1"/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\spointer-events-none\s/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\stext-zinc-400\s/', $html);
        $this->assertDoesNotMatchRegularExpression('/<summary[^>]*cursor-pointer/', $html);
        $this->assertStringContainsString('disabled: true,', $html);
    }

    public function test_an_enabled_item_is_interactive(): void
    {
        $html = $this->render('<mds:accordion><mds:accordion.item heading="A">x</mds:accordion.item></mds:accordion>');

        $this->assertStringNotContainsString('data-disabled', $html);
        $this->assertStringNotContainsString('aria-disabled', $html);
        $this->assertStringNotContainsString('tabindex="-1"', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*\scursor-pointer\s/', $html);
        $this->assertStringContainsString('disabled: false,', $html);
    }

    public function test_the_heading_shortcut_renders_heading_and_content_and_escapes(): void
    {
        $html = $this->render('<mds:accordion><mds:accordion.item heading="<b>Returns</b> & more">Fourteen days.</mds:accordion.item></mds:accordion>');

        $this->assertStringContainsString('&lt;b&gt;Returns&lt;/b&gt; &amp; more</span>', $html);
        $this->assertStringNotContainsString('<b>Returns</b>', $html);
        $this->assertMatchesRegularExpression('/data-mds-accordion-content\s*>\s*<div class="pb-4">\s*Fourteen days\.\s*<\/div>/', $html);
    }

    public function test_the_composed_form_and_the_shortcut_produce_the_same_structure(): void
    {
        $shortcut = $this->render('<mds:accordion><mds:accordion.item heading="Shipping" expanded>Body</mds:accordion.item></mds:accordion>');
        $composed = $this->render('<mds:accordion><mds:accordion.item expanded><mds:accordion.heading>Shipping</mds:accordion.heading><mds:accordion.content>Body</mds:accordion.content></mds:accordion.item></mds:accordion>');

        $normalise = fn (string $html) => preg_replace('/\s+/', ' ', $html);

        $this->assertSame($normalise($shortcut), $normalise($composed));
    }

    public function test_exclusive_groups_every_details_under_one_name(): void
    {
        $html = $this->render(self::COMPOSED);

        $this->assertSame(3, preg_match_all('/<details[^>]*\sname="mds-accordion"/', $html));
        $this->assertMatchesRegularExpression('/<div[^>]*\sx-data x-id="\[\'mds-accordion\'\]"[^>]*data-mds-accordion\s*>/', $html, 'Unnamed groups get a per-accordion $id at runtime.');
        $this->assertMatchesRegularExpression('/<div[^>]*\sdata-mds-accordion-exclusive[^>]*data-mds-accordion\s*>/', $html);
        $this->assertDoesNotMatchRegularExpression('/<div[^>]*data-mds-accordion-name/', $html);
        $this->assertSame(3, substr_count($html, 'exclusive: true,'));
    }

    public function test_an_explicit_name_wins_and_needs_no_runtime_id(): void
    {
        $html = $this->render('<mds:accordion exclusive name="faq"><mds:accordion.item heading="A">x</mds:accordion.item><mds:accordion.item heading="B">y</mds:accordion.item></mds:accordion>');

        $this->assertSame(2, preg_match_all('/<details[^>]*\sname="faq"/', $html));
        $this->assertMatchesRegularExpression('/<div[^>]*\sdata-mds-accordion-name="faq"[^>]*data-mds-accordion\s*>/', $html);
        $this->assertStringNotContainsString('x-id', $html);
        $this->assertStringNotContainsString('name="mds-accordion"', $html);
    }

    public function test_a_non_exclusive_accordion_has_no_group(): void
    {
        $html = $this->render('<mds:accordion name="ignored"><mds:accordion.item heading="A">x</mds:accordion.item></mds:accordion>');

        $this->assertDoesNotMatchRegularExpression('/<details[^>]*\sname=/', $html);
        $this->assertStringNotContainsString('x-id', $html);
        $this->assertStringNotContainsString('data-mds-accordion-exclusive', $html);
        $this->assertDoesNotMatchRegularExpression('/<div[^>]*data-mds-accordion-name/', $html);
        $this->assertStringContainsString('exclusive: false,', $html);
    }

    public function test_transition_marks_the_content_for_the_height_animation(): void
    {
        $html = $this->render(self::COMPOSED);

        $this->assertMatchesRegularExpression('/<div[^>]*\sdata-mds-accordion-transition[^>]*data-mds-accordion\s*>/', $html);
        $this->assertSame(3, preg_match_all('/<div[^>]*\sx-ref="content"[^>]*data-mds-accordion-content\s*>/', $html));
        $this->assertSame(3, substr_count($html, 'transition: true,'));
    }

    public function test_without_transition_the_browser_toggles_on_its_own(): void
    {
        $html = $this->render('<mds:accordion><mds:accordion.item heading="A">x</mds:accordion.item></mds:accordion>');

        $this->assertStringNotContainsString('x-ref="content"', $html);
        $this->assertStringNotContainsString('data-mds-accordion-transition', $html);
        $this->assertStringContainsString('transition: false,', $html);
    }

    public function test_the_script_is_emitted_once_per_page_and_registers_against_a_running_alpine(): void
    {
        $html = $this->render(self::COMPOSED.'<mds:accordion><mds:accordion.item heading="B">y</mds:accordion.item></mds:accordion>');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsAccordionItem'"));
        $this->assertSame(1, substr_count($html, '<script'));
        $this->assertStringContainsString('window.mds.registerAccordion = (Alpine)', $html);
        $this->assertStringContainsString("if (window.Alpine) {\n    window.mds.registerAccordion(window.Alpine)", $html);
        $this->assertStringContainsString("document.addEventListener('alpine:init'", $html);
        $this->assertStringContainsString("matchMedia('(prefers-reduced-motion: reduce)')", $html);
    }

    public function test_the_script_stays_out_of_an_accordion_without_items(): void
    {
        $html = $this->render('<mds:accordion></mds:accordion>');

        $this->assertMatchesRegularExpression('/<div[^>]*data-mds-accordion\s*>\s*<\/div>/', $html);
        $this->assertStringNotContainsString('<script', $html);
    }

    public function test_attributes_land_on_the_root_of_each_part(): void
    {
        $html = $this->render('<mds:accordion class="max-w-md" id="faq"><mds:accordion.item class="px-2" data-test="i"><mds:accordion.heading class="text-base">H</mds:accordion.heading><mds:accordion.content class="prose">C</mds:accordion.content></mds:accordion.item></mds:accordion>');

        $this->assertMatchesRegularExpression('/<div[^>]*\sclass="divide-y [^"]* max-w-md"[^>]*data-mds-accordion\s*>/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*\sid="faq"[^>]*data-mds-accordion\s*>/', $html);
        $this->assertMatchesRegularExpression('/<details[^>]*class="group\/mds-accordion px-2"[^>]*data-test="i"/', $html);
        $this->assertMatchesRegularExpression('/<summary[^>]*class="flex [^"]* text-base"/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*class="text-sm [^"]* prose"/', $html);
    }

    public function test_an_item_with_an_empty_body_still_renders_its_content_box(): void
    {
        $html = $this->render('<mds:accordion><mds:accordion.item heading="Empty"></mds:accordion.item></mds:accordion>');

        $this->assertMatchesRegularExpression('/data-mds-accordion-content\s*>\s*<div class="pb-4">\s*<\/div>/', $html);
    }

    public function test_fa_is_accepted_everywhere_and_changes_nothing_visible(): void
    {
        // The accordion renders no digits and no microcopy; fa exists for
        // parity with the kit and flows to the parts via @aware. Both values
        // therefore render identically — the point is that neither throws.
        $on = $this->render('<mds:accordion :fa="true"><mds:accordion.item heading="A" :fa="true"><mds:accordion.heading :fa="true">H</mds:accordion.heading><mds:accordion.content :fa="true">C</mds:accordion.content></mds:accordion.item></mds:accordion>');
        $off = $this->render('<mds:accordion :fa="false"><mds:accordion.item heading="A" :fa="false"><mds:accordion.heading :fa="false">H</mds:accordion.heading><mds:accordion.content :fa="false">C</mds:accordion.content></mds:accordion.item></mds:accordion>');

        $this->assertSame($on, $off);
        $this->assertStringNotContainsString('fa=', $on, 'fa is a prop, not an attribute that leaks into the markup.');
    }

    public function test_nothing_in_the_script_looks_like_a_component_tag(): void
    {
        // The kit's tag compiler compiles angle-bracket tags anywhere in the
        // view, script included; the view would break with a parse error.
        foreach (['index', 'item', 'heading', 'content'] as $view) {
            $source = (string) file_get_contents(dirname(__DIR__, 2)."/resources/views/mds/accordion/{$view}.blade.php");

            if (! preg_match('/<script.*?<\/script>/s', $source, $script)) {
                continue;
            }

            $this->assertDoesNotMatchRegularExpression('/<(mds|flux):/', $script[0]);
            $this->assertStringNotContainsString('۰۱۲۳۴۵۶۷۸۹', $source);
        }
    }
}
