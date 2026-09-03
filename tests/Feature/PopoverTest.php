<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;

class PopoverTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    protected function composed(string $rootAttrs = '', string $contentAttrs = ''): string
    {
        View::share('errors', new ViewErrorBag);

        return $this->render(<<<BLADE
        <mds:popover {$rootAttrs}>
            <mds:popover.trigger><flux:button icon="bell">Notifications</flux:button></mds:popover.trigger>
            <mds:popover.content {$contentAttrs}>
                <flux:heading size="sm">Notifications</flux:heading>
                <flux:text class="mt-2">Nothing new.</flux:text>
            </mds:popover.content>
        </mds:popover>
        BLADE);
    }

    public function test_renders_root_trigger_and_teleported_dialog(): void
    {
        $html = $this->composed();

        $this->assertMatchesRegularExpression('/<span\s[^>]*x-data="mdsPopover\(\{[^"]*\}\)"[^>]*data-mds-popover\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<span\s[^>]*x-ref="trigger"[^>]*data-mds-popover-trigger\s*>\s*<button/s', $html);
        // The panel is a non-modal dialog, teleported to body and hidden until opened.
        $this->assertStringContainsString('<template x-teleport="body">', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*role="dialog"[^>]*aria-modal="false"[^>]*data-mds-popover-content\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-show="open"[^>]*x-cloak[^>]*data-mds-popover-content\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*tabindex="-1"[^>]*data-mds-popover-content\s*>/s', $html);
        // The slot lands inside the dialog, the Flux pieces rendered.
        $this->assertMatchesRegularExpression('/data-mds-popover-content\s*>.*Nothing new\..*<\/div>\s*<\/template>/s', $html);
        $this->assertStringContainsString('data-flux-button', $html);
    }

    public function test_placement_props_flow_into_alpine_config(): void
    {
        $html = $this->composed('position="top" align="end" offset="12"');

        $this->assertStringContainsString("x-data=\"mdsPopover({ position: 'top', align: 'end', offset: 12, hover: false })\"", $html);
    }

    public function test_placement_defaults_and_unknown_values_fall_back(): void
    {
        $this->assertStringContainsString(
            "mdsPopover({ position: 'bottom', align: 'start', offset: 8, hover: false })",
            $this->composed(),
        );

        $this->assertStringContainsString(
            "mdsPopover({ position: 'bottom', align: 'start', offset: 0, hover: false })",
            $this->composed('position="diagonal" align="middle" offset="-3"'),
        );

        $this->assertStringContainsString("position: 'start', align: 'center'", $this->composed('position="start" align="center"'));
        $this->assertStringContainsString("position: 'end'", $this->composed('position="end"'));
    }

    public function test_hover_flag_reaches_alpine(): void
    {
        $this->assertStringContainsString('hover: true })', $this->composed('hover'));
        $this->assertStringContainsString('hover: false })', $this->composed());
    }

    public function test_arrow_is_off_by_default_and_inherited_from_the_root(): void
    {
        $this->assertStringNotContainsString('data-mds-popover-arrow', $this->composed());

        $html = $this->composed('arrow');

        $this->assertMatchesRegularExpression('/<span\s[^>]*x-init="arrowEl = \$el"[^>]*aria-hidden="true"[^>]*data-mds-popover-arrow\s*>/s', $html);
        // The arrow follows the side place() actually picked.
        $this->assertStringContainsString('in-data-[rendered-side=top]:-bottom-[6px]', $html);
        $this->assertStringContainsString('in-data-[rendered-side=left]:-right-[6px]', $html);
    }

    public function test_content_can_take_the_arrow_itself(): void
    {
        $html = $this->composed('', 'arrow');

        $this->assertStringContainsString('data-mds-popover-arrow', $html);

        $html = $this->composed('arrow', ':arrow="false"');

        $this->assertStringNotContainsString('data-mds-popover-arrow', $html);
    }

    public function test_trigger_owns_the_click_and_the_aria_sync(): void
    {
        $html = $this->composed();

        $this->assertMatchesRegularExpression('/<span\s[^>]*x-on:click="toggle\(\)"[^>]*data-mds-popover-trigger\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<span\s[^>]*x-effect="syncTrigger\(\)"[^>]*data-mds-popover-trigger\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<span\s[^>]*x-bind:id="\$id\(\'mds-popover-trigger\'\)"[^>]*data-mds-popover-trigger\s*>/s', $html);
        // Hover-mode hooks are always wired; the methods no-op when hover is off.
        $this->assertMatchesRegularExpression('/<span\s[^>]*x-on:mouseenter="enter\(\)"[^>]*x-on:mouseleave="leave\(\)"[^>]*x-on:focusin="focusin\(\$event\)"[^>]*data-mds-popover-trigger\s*>/s', $html);
        // ARIA is written onto the real button at runtime, where a reader hears it.
        $this->assertStringContainsString("button.setAttribute('aria-haspopup', 'dialog')", $html);
        $this->assertStringContainsString("button.setAttribute('aria-expanded', this.open ? 'true' : 'false')", $html);
        $this->assertStringContainsString("button.setAttribute('aria-controls', this.\$id('mds-popover'))", $html);
    }

    public function test_dialog_is_named_by_its_trigger_unless_the_caller_names_it(): void
    {
        $html = $this->composed();

        $this->assertMatchesRegularExpression('/<div\s[^>]*x-bind:id="\$id\(\'mds-popover\'\)"[^>]*data-mds-popover-content\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-bind:aria-labelledby="\$id\(\'mds-popover-trigger\'\)"[^>]*data-mds-popover-content\s*>/s', $html);

        $html = $this->composed('', 'aria-label="Inbox"');

        $this->assertStringNotContainsString('x-bind:aria-labelledby', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*aria-label="Inbox"[^>]*data-mds-popover-content\s*>/s', $html);

        $html = $this->composed('', 'aria-labelledby="inbox-heading"');

        $this->assertStringNotContainsString('x-bind:aria-labelledby', $html);
        $this->assertStringContainsString('aria-labelledby="inbox-heading"', $html);
    }

    public function test_escape_outside_click_and_tab_are_wired(): void
    {
        $html = $this->composed();

        $this->assertMatchesRegularExpression('/<span\s[^>]*x-on:keydown\.escape\.window="open && close\(\)"[^>]*data-mds-popover\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-on:click\.outside="outside\(\$event\)"[^>]*data-mds-popover-content\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-on:keydown\.tab="tab\(\$event\)"[^>]*data-mds-popover-content\s*>/s', $html);
        // Clicks on the trigger are "outside" the teleported panel; the script must exempt them.
        $this->assertStringContainsString('if (this.$refs.trigger?.contains(event.target)) return', $html);
        // Public API and the events consumers listen for.
        $this->assertStringContainsString("this.\$root.dispatchEvent(new CustomEvent('mds-popover-open', { bubbles: true }))", $html);
        $this->assertStringContainsString("this.\$root.dispatchEvent(new CustomEvent('mds-popover-close', { bubbles: true }))", $html);
        $this->assertStringContainsString('toggle() {', $html);
        $this->assertStringContainsString('show(options = {}) {', $html);
        $this->assertStringContainsString('close(options = {}) {', $html);
    }

    public function test_positioning_resolves_logical_sides_and_flips(): void
    {
        $html = $this->composed();

        $this->assertStringContainsString("const rtl = getComputedStyle(this.\$root).direction === 'rtl'", $html);
        $this->assertStringContainsString("if (side === 'start') side = rtl ? 'right' : 'left'", $html);
        $this->assertStringContainsString("if (side === 'end') side = rtl ? 'left' : 'right'", $html);
        $this->assertStringContainsString("side = 'top'", $html);
        $this->assertStringContainsString('content.dataset.renderedSide = side', $html);
        // Listeners are attached on open, dropped on close and in destroy().
        $this->assertStringContainsString("window.addEventListener('scroll', this.reposition, { passive: true, capture: true })", $html);
        $this->assertStringContainsString("window.removeEventListener('scroll', this.reposition, { capture: true })", $html);
        $this->assertMatchesRegularExpression('/destroy\(\) \{\s*this\.unlisten\(\)/', $html);
    }

    public function test_transition_is_opacity_only_and_respects_reduced_motion(): void
    {
        $html = $this->composed();

        $this->assertMatchesRegularExpression('/<div\s[^>]*x-transition:enter="transition duration-100 ease-out motion-reduce:transition-none"[^>]*x-transition:enter-start="opacity-0"[^>]*data-mds-popover-content\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-transition:leave="[^"]*motion-reduce:transition-none"[^>]*x-transition:leave-end="opacity-0"[^>]*data-mds-popover-content\s*>/s', $html);
        $this->assertStringNotContainsString('scale-95', $html);
    }

    public function test_panel_carries_the_dropdown_look_and_merges_classes(): void
    {
        $html = $this->composed('', 'class="w-72"');

        $this->assertMatchesRegularExpression('/<div\s[^>]*class="[^"]*\bfixed z-50 min-w-48 rounded-lg border border-zinc-200 bg-white p-4 text-start shadow-sm outline-none dark:border-white\/10 dark:bg-zinc-800\b[^"]*\bw-72\b[^"]*"[^>]*data-mds-popover-content\s*>/s', $html);
        // Extra root attributes (x-on:mds-popover-close, class) land on the root.
        $html = $this->composed('class="ms-2" x-on:mds-popover-close="refresh()"');

        $this->assertMatchesRegularExpression('/<span\s[^>]*class="relative inline-block ms-2"[^>]*x-on:mds-popover-close="refresh\(\)"[^>]*data-mds-popover\s*>/s', $html);
    }

    public function test_closable_renders_a_labelled_close_button_after_the_content(): void
    {
        $html = $this->composed('', 'closable');

        $this->assertMatchesRegularExpression('/Nothing new\..*<button\s[^>]*x-on:click="close\(\)"[^>]*aria-label="بستن"[^>]*data-mds-popover-close\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*class="[^"]*\bpe-10\b[^"]*"[^>]*data-mds-popover-content\s*>/s', $html);

        $html = $this->composed();

        $this->assertStringNotContainsString('data-mds-popover-close', $html);
        $this->assertStringNotContainsString('pe-10', $html);
    }

    public function test_close_label_follows_fa_from_config_root_and_content(): void
    {
        $this->assertStringContainsString('aria-label="بستن"', $this->composed('', 'closable'));
        $this->assertStringContainsString('aria-label="Close"', $this->composed(':fa="false"', 'closable'));
        $this->assertStringContainsString('aria-label="Close"', $this->composed('', 'closable :fa="false"'));
        $this->assertStringContainsString('aria-label="بستن"', $this->composed(':fa="false"', 'closable :fa="true"'));

        config(['mds.persian_digits' => false]);

        try {
            $html = $this->composed('', 'closable');

            $this->assertStringContainsString('aria-label="Close"', $html);
            $this->assertStringNotContainsString('بستن', $html);
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }

    public function test_script_is_emitted_once_per_page_with_the_started_alpine_guard(): void
    {
        $html = $this->render('<mds:popover><mds:popover.trigger>a</mds:popover.trigger><mds:popover.content>x</mds:popover.content></mds:popover><mds:popover><mds:popover.content>y</mds:popover.content></mds:popover>');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsPopover'"));
        $this->assertSame(2, preg_match_all('/data-mds-popover\s*>/', $html));
        $this->assertStringContainsString('window.mds.registerPopover = (Alpine) => {', $html);
        $this->assertStringContainsString('if (window.mds.popoverRegistered) return', $html);
        $this->assertStringContainsString('if (window.Alpine) {', $html);
    }

    public function test_empty_and_bare_slots_still_render(): void
    {
        $html = $this->render('<mds:popover />');

        $this->assertMatchesRegularExpression('/data-mds-popover\s*><\/span>/', $html);
        $this->assertStringNotContainsString('data-mds-popover-content', $html);

        $html = $this->render('<mds:popover><mds:popover.trigger /><mds:popover.content /></mds:popover>');

        $this->assertMatchesRegularExpression('/data-mds-popover-trigger\s*>\s*<\/span>/', $html);
        $this->assertMatchesRegularExpression('/data-mds-popover-content\s*>\s*<\/div>/', $html);
    }

    public function test_keeps_no_persian_digit_map_and_no_livewire_dependency(): void
    {
        $html = $this->composed();

        $this->assertStringNotContainsString('۰۱۲۳۴۵۶۷۸۹', file_get_contents(dirname(__DIR__, 2).'/resources/views/mds/popover/index.blade.php'));
        $this->assertStringNotContainsString('wire:model', $html);
        $this->assertStringNotContainsString('wire:click', $html);
        $this->assertStringNotContainsString('$wire', $html);
    }
}
