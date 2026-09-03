<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use MajidDs\Tests\TestCase;

/**
 * Every interactive component registers itself in an inline <script>. The
 * obvious way to write that is
 *
 *     document.addEventListener('alpine:init', () => Alpine.data('mdsX', …))
 *
 * and it is wrong on any page reached by `wire:navigate`. Livewire swaps the
 * body and re-executes the scripts in it, but `alpine:init` fired once, on the
 * first full page load, and never fires again — so a component whose script
 * first arrives on a navigated-to page registers nothing. Alpine then hits an
 * `x-data="mdsX()"` it has never heard of, and the component renders as inert
 * markup: no error thrown, nothing in the console, just a stepper whose
 * buttons do nothing.
 *
 * The fix each script carries is to register immediately when Alpine is
 * already running and fall back to the event when it is not. These tests
 * sweep for the shape so a new component cannot quietly reintroduce the bug —
 * it is invisible in every server-rendered test, because the markup is
 * identical either way.
 */
class NavigateRegistrationTest extends TestCase
{
    /**
     * The registrations, and the scripts that hold them.
     *
     * @return array<string, string> relative view path => source
     */
    private function scripts(): array
    {
        $root = dirname(__DIR__, 2).'/resources/views/';
        $found = [];

        foreach (glob($root.'{*,*/*,*/*/*}.blade.php', GLOB_BRACE) ?: [] as $path) {
            $source = (string) file_get_contents($path);

            if (preg_match('/Alpine\.(data|directive|magic|store)\(|Livewire\.hook\(/', $source)) {
                $found[substr($path, strlen($root))] = $source;
            }
        }

        return $found;
    }

    public function test_every_registration_survives_an_already_started_alpine(): void
    {
        $offenders = [];
        $checked = 0;

        foreach ($this->scripts() as $view => $source) {
            if (! preg_match('/Alpine\.(data|directive|magic|store)\(/', $source)) {
                continue;
            }

            $checked++;

            // Registering straight away when Alpine is up is the whole fix;
            // the event listener is only the not-yet-started branch.
            if (! str_contains($source, 'if (window.Alpine)')) {
                $offenders[] = $view;
            }
        }

        $this->assertGreaterThanOrEqual(20, $checked, "Expected to find the kit's Alpine registrations.");
        $this->assertSame(
            [],
            $offenders,
            "These register only from alpine:init, so they never register on a wire:navigate visit:\n".implode("\n", $offenders),
        );
    }

    public function test_every_livewire_hook_survives_an_already_started_livewire(): void
    {
        $offenders = [];

        foreach ($this->scripts() as $view => $source) {
            if (! str_contains($source, 'Livewire.hook(')) {
                continue;
            }

            // Same trap, same shape: livewire:init fires once per full load.
            if (! str_contains($source, 'if (window.Livewire)')) {
                $offenders[] = $view;
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These hook Livewire only from livewire:init, so the hook is lost on a wire:navigate visit:\n".implode("\n", $offenders),
        );
    }

    public function test_registering_twice_is_a_no_op(): void
    {
        // Body scripts re-execute on every navigate, so each registration runs
        // again on every visit. Without a flag, Alpine.data() would be called
        // repeatedly for the same name — harmless today, but the flag is what
        // makes that explicit, and it is what lets the immediate branch and the
        // event branch both fire without fighting.
        $offenders = [];

        foreach ($this->scripts() as $view => $source) {
            if (! preg_match('/window\.mds\.register[A-Za-z]+ = /', $source)) {
                continue;
            }

            if (! preg_match('/if \(window\.mds\.[a-zA-Z]+Registered\) return/', $source)) {
                $offenders[] = $view;
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "These registration helpers re-run their body on every navigate:\n".implode("\n", $offenders),
        );
    }

    public function test_the_rendered_markup_still_carries_both_branches(): void
    {
        // A spot check through real Blade, so this cannot pass on source that
        // never reaches a page.
        $html = Blade::render('<mds:quantity :value="2" /><mds:countdown :until="now()->addHour()" />');

        $this->assertStringContainsString('if (window.Alpine) {', $html);
        $this->assertStringContainsString("addEventListener('alpine:init'", $html);
        $this->assertStringContainsString('window.mds.registerQuantity(window.Alpine)', $html);
        $this->assertStringContainsString('window.mds.registerCountdown(window.Alpine)', $html);
    }
}
