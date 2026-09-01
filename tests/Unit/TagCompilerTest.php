<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Tests\TestCase;

class TagCompilerTest extends TestCase
{
    private function compile(string $template): string
    {
        return app('mds.compiler')->compile($template);
    }

    /*
    | Every attribute form Blade understands has to survive the <mds:*> regex.
    | These are the shapes that break silently when the pattern falls behind
    | Laravel or Flux: the tag simply stops matching and ships to the browser
    | as literal text, with no error anywhere.
    */

    public function test_it_compiles_a_self_closing_tag(): void
    {
        $compiled = $this->compile('<mds:price />');

        $this->assertStringContainsString("'mds::price'", $compiled);
        $this->assertStringContainsString('@endComponentClass', $compiled);
    }

    public function test_it_compiles_dotted_subcomponent_names(): void
    {
        $this->assertStringContainsString("'mds::command.item'", $this->compile('<mds:command.item />'));
    }

    public function test_it_compiles_a_matching_pair_of_tags(): void
    {
        $compiled = $this->compile('<mds:command>سلام</mds:command>');

        $this->assertStringContainsString("'mds::command'", $compiled);
        $this->assertStringContainsString('سلام', $compiled);
        $this->assertStringContainsString('@endComponentClass', $compiled);
    }

    public function test_it_compiles_plain_and_bound_attributes(): void
    {
        $this->assertStringContainsString("'currency' => 'toman'", $this->compile('<mds:price currency="toman" />'));
        $this->assertStringContainsString("'amount' => \$total", $this->compile('<mds:price :amount="$total" />'));
    }

    public function test_it_compiles_the_bound_shorthand(): void
    {
        // <mds:price :$amount /> is sugar for :amount="$amount"
        $this->assertStringContainsString("'amount' => \$amount", $this->compile('<mds:price :$amount />'));
    }

    public function test_it_compiles_the_class_and_style_directives(): void
    {
        $this->assertStringContainsString(
            'Illuminate\Support\Arr::toCssClasses',
            $this->compile('<mds:price @class(["on" => $active]) />'),
        );

        $this->assertStringContainsString(
            'Illuminate\Support\Arr::toCssStyles',
            $this->compile('<mds:price @style(["color: red" => $bad]) />'),
        );
    }

    public function test_it_compiles_a_forwarded_attribute_bag(): void
    {
        $this->assertStringContainsString(
            "'attributes' => \$attributes",
            $this->compile('<mds:price {{ $attributes }} />'),
        );
    }

    public function test_it_compiles_an_inline_slot_attribute(): void
    {
        $compiled = $this->compile('<mds:empty-state slot="header" />');

        $this->assertStringContainsString("@slot('header')", $compiled);
        $this->assertStringContainsString('@endslot', $compiled);
    }

    public function test_it_compiles_colon_and_at_bearing_attribute_names(): void
    {
        // wire:model is the package's whole Livewire contract; x-on:click and
        // @click are the Alpine spellings the views hand to callers.
        $this->assertStringContainsString("'wire:model' => 'qty'", $this->compile('<mds:quantity wire:model="qty" />'));
        $this->assertStringContainsString("'x-on:click' => 'go()'", $this->compile('<mds:price x-on:click="go()" />'));
        $this->assertStringContainsString('@click', $this->compile('<mds:price @click="go()" />'));
    }

    public function test_it_leaves_other_namespaces_and_lookalikes_alone(): void
    {
        // Flux's own tags belong to Flux's compiler.
        $this->assertStringNotContainsString('mds::', $this->compile('<flux:button />'));

        // A prefix match is not a namespace match.
        foreach (['<mdsx:price />', '<mds-price />', '<x-mds-price />'] as $template) {
            $this->assertStringNotContainsString('BEGIN-COMPONENT-CLASS', $this->compile($template), $template);
        }
    }

    /*
    | src/MdsTagCompiler.php is a deliberate verbatim fork of Flux's compiler:
    | the three compile*Tags() methods are Flux's, with `flux` swapped for
    | `mds`. They override protected internals of Laravel's
    | ComponentTagCompiler, which carry no backwards-compatibility promise, so
    | when Laravel extends the attribute syntax Flux updates these patterns —
    | and this fork has to follow, or <mds:*> quietly stops parsing whatever
    | was added. This guard turns that into a failing test instead of a bug
    | report.
    */

    public function test_the_fork_still_matches_flux_upstream(): void
    {
        $upstream = dirname(__DIR__, 2).'/vendor/livewire/flux/src/FluxTagCompiler.php';

        $this->assertFileExists($upstream, 'livewire/flux is a hard requirement; without it this guard cannot run.');

        $flux = $this->forkedMethods($upstream, 'flux');
        $mds = $this->forkedMethods(dirname(__DIR__, 2).'/src/MdsTagCompiler.php', 'mds');

        foreach (['compileOpeningTags', 'compileSelfClosingTags', 'compileClosingTags'] as $method) {
            $this->assertArrayHasKey($method, $flux, "Flux no longer defines {$method}() — re-read FluxTagCompiler and re-sync the fork by hand.");
            $this->assertArrayHasKey($method, $mds, "MdsTagCompiler is missing {$method}().");

            $this->assertSame(
                $flux[$method],
                $mds[$method],
                "MdsTagCompiler::{$method}() has drifted from Flux's. Copy Flux's version over, swap `flux` for `mds`, and check the new syntax renders — see the tests above.",
            );
        }
    }

    /**
     * The three forked methods as normalised token streams: comments and
     * whitespace dropped, and the namespace token folded away so `flux` and
     * `mds` compare equal. Tokenising rather than brace-matching is what makes
     * this reliable — the patterns themselves contain `\{\{` and `\}\}`.
     *
     * @return array<string, list<string>>
     */
    private function forkedMethods(string $file, string $namespace): array
    {
        $tokens = token_get_all((string) file_get_contents($file));
        $wanted = ['compileOpeningTags', 'compileSelfClosingTags', 'compileClosingTags'];
        $methods = [];
        $count = count($tokens);

        for ($i = 0; $i < $count; $i++) {
            if (! is_array($tokens[$i]) || $tokens[$i][0] !== T_FUNCTION) {
                continue;
            }

            $j = $i + 1;

            while ($j < $count && is_array($tokens[$j]) && $tokens[$j][0] === T_WHITESPACE) {
                $j++;
            }

            if (! is_array($tokens[$j]) || ! in_array($tokens[$j][1], $wanted, true)) {
                continue;
            }

            $name = $tokens[$j][1];

            // '{' as a bare token is real punctuation — one inside a string
            // literal arrives as part of a single string token instead.
            while ($j < $count && $tokens[$j] !== '{') {
                $j++;
            }

            $depth = 0;
            $body = [];

            for (; $j < $count; $j++) {
                $token = $tokens[$j];

                if ($token === '{') {
                    $depth++;

                    if ($depth === 1) {
                        continue;
                    }
                } elseif ($token === '}') {
                    $depth--;

                    if ($depth === 0) {
                        break;
                    }
                }

                if (is_array($token) && in_array($token[0], [T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
                    continue;
                }

                $body[] = str_replace($namespace, '{namespace}', is_array($token) ? $token[1] : $token);
            }

            $methods[$name] = $body;
        }

        return $methods;
    }
}
