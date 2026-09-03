<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use MajidDs\Tests\TestCase;

class ContextTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * The composition the docs teach: a card with a Flux menu behind a
     * right-click.
     */
    protected function example(string $rootAttributes = '', string $menuAttributes = ''): string
    {
        return <<<BLADE
        <mds:context {$rootAttributes}>
            <flux:card class="w-64">Right-click this card</flux:card>
            <mds:context.menu {$menuAttributes}>
                <flux:menu>
                    <flux:menu.item icon="pencil-square">Edit</flux:menu.item>
                    <flux:menu.item icon="document-duplicate">Duplicate</flux:menu.item>
                    <flux:menu.separator />
                    <flux:menu.item icon="trash" variant="danger">Delete</flux:menu.item>
                </flux:menu>
            </mds:context.menu>
        </mds:context>
        BLADE;
    }

    public function test_context_renders_the_root_marker_around_the_content(): void
    {
        $html = $this->render($this->example());

        $this->assertMatchesRegularExpression('/<div\s[^>]*x-data="mdsContext\(\{[^"]*\}\)"[^>]*data-mds-context\s*>/s', $html);
        $this->assertStringContainsString('Right-click this card', $html);
        // The content is the first thing inside the root, the panel is teleported away...
        $this->assertMatchesRegularExpression('/data-mds-context\s*>\s*<div[^>]*data-flux-card/s', $html);
    }

    public function test_context_menu_is_a_teleported_hidden_panel_around_the_flux_menu(): void
    {
        $html = $this->render($this->example());

        $this->assertStringContainsString('<template x-teleport="body">', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-show="open"[^>]*data-mds-context-menu\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-cloak[^>]*data-mds-context-menu\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*class="fixed z-50"[^>]*data-mds-context-menu\s*>/s', $html);
        // The panel registers itself into the component scope through the teleport...
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-init="panelEl = \$el"[^>]*data-mds-context-menu\s*>/s', $html);
        // ...and carries no role of its own: the Flux menu is the menu.
        $this->assertDoesNotMatchRegularExpression('/<div\s[^>]*role="[^"]*"[^>]*data-mds-context-menu\s*>/s', $html);
    }

    public function test_the_free_flux_menu_renders_standalone_inside_the_panel(): void
    {
        $html = $this->render($this->example());

        // Flux's menu is a manual popover — the component shows it into the top layer.
        $this->assertMatchesRegularExpression('/data-mds-context-menu\s*>\s*<ui-menu\s[^>]*popover="manual"[^>]*data-flux-menu\s*>/s', $html);
        $this->assertSame(3, substr_count($html, 'data-flux-menu-item="data-flux-menu-item"'), 'Three items expected.');
        $this->assertStringContainsString('data-flux-menu-separator', $html);
        // The heroicon names of the example resolve through flux:icon.
        $this->assertSame(3, preg_match_all('/<svg\s[^>]*data-flux-icon/', $html));
        // Each item's label is the last thing in its button.
        $this->assertMatchesRegularExpression('/Duplicate\s*<\/button>/s', $html);
        $this->assertMatchesRegularExpression('/Delete\s*<\/button>/s', $html);
    }

    public function test_root_binds_the_pointer_keyboard_and_touch_openers(): void
    {
        $html = $this->render($this->example());

        preg_match('/<div\s[^>]*data-mds-context\s*>/s', $html, $root);

        $this->assertStringContainsString('x-on:contextmenu="contextmenu($event)"', $root[0]);
        $this->assertStringContainsString('x-on:keydown="keydown($event)"', $root[0]);
        $this->assertStringContainsString('x-on:pointerdown="pointerdown($event)"', $root[0]);
        $this->assertStringContainsString('x-on:pointermove="pointermove($event)"', $root[0]);
        $this->assertStringContainsString('x-on:pointerup="cancelPress()"', $root[0]);
        $this->assertStringContainsString('x-on:pointercancel="cancelPress()"', $root[0]);
        // The wrapper is not a button: no aria-haspopup, no tab stop by default.
        $this->assertStringNotContainsString('aria-haspopup', $root[0]);
        $this->assertStringNotContainsString('tabindex', $root[0]);
    }

    public function test_panel_binds_the_closers(): void
    {
        $html = $this->render($this->example());

        preg_match('/<div\s[^>]*data-mds-context-menu\s*>/s', $html, $panel);

        $this->assertStringContainsString('x-on:keydown.escape.prevent.stop="close()"', $panel[0]);
        $this->assertStringContainsString('x-on:keydown.tab="close()"', $panel[0]);
        $this->assertStringContainsString('x-on:lofi-close-popovers="close()"', $panel[0]);
        $this->assertStringContainsString('x-on:click="clicked($event)"', $panel[0]);
        // A right-click on the open menu must not summon the native one over it.
        $this->assertStringContainsString('x-on:contextmenu.prevent', $panel[0]);
    }

    public function test_the_script_handles_the_keyboard_gesture_and_the_dismissals(): void
    {
        $html = $this->render($this->example());

        $this->assertStringContainsString("event.key === 'ContextMenu'", $html);
        $this->assertStringContainsString("event.key === 'F10' && event.shiftKey", $html);
        $this->assertStringContainsString("window.addEventListener('scroll', this.onScroll, { capture: true, passive: true })", $html);
        $this->assertStringContainsString("window.addEventListener('blur', this.onBlur)", $html);
        $this->assertStringContainsString("document.addEventListener('pointerdown', this.onPointerDown, { capture: true })", $html);
        // Every listener is torn down again.
        $this->assertSame(4, preg_match_all('/removeEventListener\(/', $html));
        // Focus returns to where it was...
        $this->assertStringContainsString('this.returnFocus = document.activeElement', $html);
        // ...and the keyboard path lands on the first item.
        $this->assertStringContainsString('menu.querySelector(\'[role="menuitem"]:not([disabled])', $html);
        // Long press: 500ms, cancelled past 10px of movement.
        $this->assertStringContainsString('}, 500)', $html);
        $this->assertStringContainsString('> 10) this.cancelPress()', $html);
        // Horizontal placement follows the visual direction, read at open time.
        $this->assertStringContainsString("getComputedStyle(this.\$root).direction === 'rtl'", $html);
        // The menu is measured again on the next frame, because Alpine's
        // x-show write can still be pending when the first pass runs — and
        // the pending frame is cancelled on close and on destroy.
        $this->assertStringContainsString('this.placeFrame = requestAnimationFrame(', $html);
        $this->assertSame(3, preg_match_all('/cancelAnimationFrame\(this\.placeFrame\)/', $html));
    }

    public function test_disabled_renders_no_handlers(): void
    {
        $html = $this->render($this->example('disabled'));

        preg_match('/<div\s[^>]*data-mds-context\s*>/s', $html, $root);

        $this->assertStringContainsString('disabled: true', $root[0]);
        $this->assertStringContainsString('data-disabled', $root[0]);
        $this->assertStringNotContainsString('x-on:', $root[0]);
        // The content still renders, and so does the (dormant) panel.
        $this->assertStringContainsString('Right-click this card', $html);
        $this->assertStringContainsString('data-mds-context-menu', $html);
    }

    public function test_enabled_by_default(): void
    {
        $html = $this->render('<mds:context>x</mds:context>');

        $this->assertStringContainsString('disabled: false', $html);
        $this->assertStringNotContainsString('data-disabled', $html);
    }

    public function test_long_press_flag_flows_into_the_alpine_config(): void
    {
        $this->assertStringContainsString('longPress: true', $this->render('<mds:context>x</mds:context>'));
        $this->assertStringContainsString('longPress: false', $this->render('<mds:context :long-press="false">x</mds:context>'));
    }

    public function test_focusable_adds_a_tab_stop_to_the_wrapper(): void
    {
        $html = $this->render('<mds:context focusable><img src="/a.png" alt=""></mds:context>');

        $this->assertMatchesRegularExpression('/<div\s[^>]*tabindex="0"[^>]*data-mds-context\s*>/s', $html);
        // ...which the default wrapper does not have.
        $this->assertDoesNotMatchRegularExpression('/<div\s[^>]*tabindex="0"[^>]*data-mds-context\s*>/s', $this->render('<mds:context><img src="/a.png" alt=""></mds:context>'));
    }

    public function test_attributes_land_on_the_root_and_on_the_panel(): void
    {
        $html = $this->render($this->example('class="inline-block" id="row-7"', 'class="min-w-56"'));

        $this->assertMatchesRegularExpression('/<div\s[^>]*class="relative inline-block"[^>]*data-mds-context\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*id="row-7"[^>]*data-mds-context\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*class="fixed z-50 min-w-56"[^>]*data-mds-context-menu\s*>/s', $html);
    }

    public function test_the_script_is_emitted_once_per_page_with_the_started_alpine_guard(): void
    {
        $html = $this->render('<mds:context>a</mds:context><mds:context>b</mds:context>');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsContext'"));
        $this->assertSame(1, substr_count($html, 'window.mds.registerContext = (Alpine) =>'));
        $this->assertStringContainsString('if (window.mds.contextRegistered) return', $html);
        $this->assertStringContainsString("document.addEventListener('alpine:init', () => window.mds.registerContext(window.Alpine))", $html);
        $this->assertSame(2, preg_match_all('/data-mds-context\s*>/', $html));
    }

    public function test_the_script_never_spells_a_component_tag_the_compiler_could_eat(): void
    {
        $source = (string) file_get_contents(dirname(__DIR__, 2).'/resources/views/mds/context/index.blade.php');

        preg_match('/<script.*?<\/script>/s', $source, $script);

        $this->assertDoesNotMatchRegularExpression('/<(mds|flux):/', $script[0]);
    }

    public function test_an_empty_context_and_a_menu_without_a_flux_menu_still_render(): void
    {
        $html = $this->render('<mds:context></mds:context>');

        $this->assertMatchesRegularExpression('/data-mds-context\s*>/', $html);

        $html = $this->render('<mds:context><span>row</span><mds:context.menu><div><button type="button">Only</button></div></mds:context.menu></mds:context>');

        $this->assertMatchesRegularExpression('/data-mds-context-menu\s*>\s*<div><button type="button">Only<\/button><\/div>/s', $html);
    }

    public function test_fa_is_accepted_by_both_views(): void
    {
        // No built-in strings today, so the prop is a contract only; both
        // views must still take it without complaint, on or off.
        config(['mds.persian_digits' => false]);

        try {
            $html = $this->render('<mds:context :fa="true"><b>x</b><mds:context.menu :fa="false"><div>m</div></mds:context.menu></mds:context>');

            $this->assertMatchesRegularExpression('/data-mds-context\s*>/', $html);
            $this->assertMatchesRegularExpression('/data-mds-context-menu\s*>/', $html);
            $this->assertStringNotContainsString('fa="', $html);
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }
}
