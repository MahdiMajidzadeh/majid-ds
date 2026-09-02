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
}
