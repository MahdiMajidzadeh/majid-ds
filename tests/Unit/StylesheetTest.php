<?php

namespace MajidDs\Tests\Unit;

use Illuminate\Support\Facades\Blade;
use MajidDs\Tests\TestCase;

class StylesheetTest extends TestCase
{
    /**
     * Unlayered CSS outranks every Tailwind utility, so a component rule
     * written outside @layer cannot be overridden by a class — the caller has
     * no way back. [x-cloak] is the deliberate exception: it has to win over
     * whatever the component sets until Alpine boots.
     */
    public function test_every_component_rule_lives_in_a_layer(): void
    {
        $css = (string) file_get_contents(dirname(__DIR__, 2).'/resources/css/mds.css');

        // Strip comments, then walk the top level collecting block headers.
        $css = (string) preg_replace('#/\*.*?\*/#s', '', $css);

        $headers = [];
        $depth = 0;
        $header = '';

        foreach (str_split($css) as $char) {
            if ($char === '{') {
                if ($depth === 0) {
                    $headers[] = trim(preg_replace('/\s+/', ' ', $header) ?? '');
                }

                $depth++;
                $header = '';
            } elseif ($char === '}') {
                $depth--;
            } elseif ($depth === 0) {
                $header .= $char;
            }
        }

        $this->assertNotEmpty($headers, 'Could not parse mds.css.');

        $allowed = ['@theme', '[x-cloak]'];

        foreach ($headers as $found) {
            $this->assertTrue(
                str_starts_with($found, '@layer ') || in_array($found, $allowed, true),
                "[{$found}] sits outside @layer, so no utility class can override it. Move it into @layer components.",
            );
        }
    }

    /**
     * The kit ships no font and imposes none. It used to set --font-sans to
     * Vazirmatn and register an @mdsFonts directive that hot-linked Google
     * Fonts — the wrong default for an audience that often cannot reach it.
     * Typography belongs to the app; the docs guide says how.
     */
    public function test_the_kit_ships_no_font(): void
    {
        // Comments are stripped first: the header is allowed to *mention* --font-sans
        // to point readers at the theming guide. Only rules count as shipping a font.
        $css = (string) preg_replace('#/\*.*?\*/#s', '', (string) file_get_contents(dirname(__DIR__, 2).'/resources/css/mds.css'));

        foreach (['--font-sans', '@font-face', 'fonts.googleapis', 'Vazirmatn\'', 'Vazirmatn"'] as $needle) {
            $this->assertStringNotContainsString($needle, $css, "mds.css sets or loads a font ({$needle}); the app owns typography.");
        }

        $this->assertArrayNotHasKey('mdsFonts', Blade::getCustomDirectives(), 'The @mdsFonts directive was removed on purpose.');
    }

    /**
     * Flux pads its input with logical ps-N / pe-N for icons and trailing
     * buttons. An `ltr` input is direction:ltr, so those land on the wrong
     * physical side; mds.css mirrors each one. When Flux adds a padding step,
     * this fails instead of a viewable password quietly growing a gap again.
     */
    public function test_every_flux_input_padding_is_mirrored_for_ltr_inputs(): void
    {
        $flux = (string) file_get_contents(dirname(__DIR__, 2).'/vendor/livewire/flux/stubs/resources/views/flux/input/index.blade.php');
        $css = (string) file_get_contents(dirname(__DIR__, 2).'/resources/css/mds.css');

        preg_match_all("/'(p[se])-(\\d+)'/", $flux, $matches, PREG_SET_ORDER);
        $this->assertNotEmpty($matches, 'Could not find Flux input padding classes.');

        foreach ($matches as [, $side, $step]) {
            $physical = $side === 'ps' ? 'padding-right' : 'padding-left';
            $this->assertStringContainsString(
                "input[data-ltr]).{$side}-{$step} { {$physical}: calc(var(--spacing) * {$step}); }",
                $css,
                "Flux pads inputs with {$side}-{$step}, but mds.css does not mirror it for ltr inputs.",
            );
        }
    }
}
