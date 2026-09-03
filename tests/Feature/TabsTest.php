<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;

/**
 * mds:tabs — the open version of Flux Pro's flux:tabs. A group holds the
 * Alpine state, the tablist holds the hidden control Livewire binds to, each
 * tab is a role="tab" button paired with its role="tabpanel" by ids the
 * server derives from the tab's name.
 */
class TabsTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * The kit's Livewire contract: the binding reaches the real control, and
     * the wrapper keeps no copy of it.
     *
     * @param  string  $control  regex fragment matching the control's opening tag
     */
    protected function assertBindingReachesControl(string $html, string $control, string $binding): void
    {
        $this->assertMatchesRegularExpression(
            '/<'.$control.'[^>]*\s'.preg_quote($binding, '/').'[\s>]/',
            $html,
            "[{$binding}] never reached the control it is supposed to bind.",
        );

        $this->assertSame(
            1,
            substr_count($html, $binding),
            "[{$binding}] appears more than once — the wrapper kept a copy it should have dropped.",
        );
    }

    /**
     * The composed example from the docs.
     */
    private function group(string $tabsAttributes = '', string $groupAttributes = ''): string
    {
        return $this->render(<<<BLADE
            <mds:tab.group {$groupAttributes}>
                <mds:tabs {$tabsAttributes}>
                    <mds:tab name="profile" icon="user">Profile</mds:tab>
                    <mds:tab name="account" icon="cog-6-tooth">Account</mds:tab>
                    <mds:tab name="billing" disabled>Billing</mds:tab>
                </mds:tabs>

                <mds:tab.panel name="profile">Profile panel</mds:tab.panel>
                <mds:tab.panel name="account">Account panel</mds:tab.panel>
                <mds:tab.panel name="billing">Billing panel</mds:tab.panel>
            </mds:tab.group>
        BLADE);
    }

    /**
     * The opening tag of the tab or panel named $name. An empty $name asks for
     * a valueless marker — `data-mds-tabs` is a bare attribute, not `=""`.
     */
    private function tag(string $html, string $part, string $name): string
    {
        $marker = $name === ''
            ? 'data-mds-'.$part.'(?=[\s>])'
            : 'data-mds-'.$part.'="'.preg_quote($name, '/').'"';

        $pattern = '/<[a-z]+[^>]*\s'.$marker.'[^>]*>/';

        $this->assertMatchesRegularExpression($pattern, $html);

        preg_match($pattern, $html, $match);

        return $match[0];
    }

    /**
     * Blade wraps whatever a view throws in a ViewException (twice, for a
     * component), so the guard is asserted on the root cause of the chain.
     */
    private function assertRefuses(string $template, string $message): void
    {
        try {
            $this->render($template);
        } catch (\Throwable $e) {
            while ($e->getPrevious() !== null) {
                $e = $e->getPrevious();
            }

            $this->assertInstanceOf(\InvalidArgumentException::class, $e);
            $this->assertStringContainsString($message, $e->getMessage());

            return;
        }

        $this->fail("[{$message}] was never thrown — the guard is gone.");
    }

    public function test_the_group_holds_the_state_and_the_tablist_the_control(): void
    {
        $html = $this->group();

        $this->assertMatchesRegularExpression('/<div[^>]*\sx-data="mdsTabs\(\{ value: \'profile\' \}\)"[^>]*\sdata-mds-tab-group/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*\srole="tablist"[^>]*\saria-orientation="horizontal"[^>]*\sdata-mds-tabs\s/', $html);
        $this->assertStringContainsString('data-mds-tabs-variant="default"', $html);
        $this->assertStringContainsString('data-mds-tabs-size="default"', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\stype="hidden"[^>]*\sx-ref="tabsInput"[^>]*\svalue="profile"[^>]*\sdata-mds-tabs-input/', $html);

        // The keyboard handler sits on the tablist and runs in the group's scope.
        $this->assertMatchesRegularExpression('/<div[^>]*\sx-on:keydown="keydown\(\$event\)"[^>]*\srole="tablist"/', $html);
    }

    public function test_tabs_are_buttons_with_the_tab_role_paired_to_their_panels(): void
    {
        $html = $this->group();

        $tab = $this->tag($html, 'tab', 'account');
        $panel = $this->tag($html, 'tab-panel', 'account');

        $this->assertStringStartsWith('<button', $tab);
        $this->assertStringContainsString('type="button"', $tab);
        $this->assertStringContainsString('role="tab"', $tab);
        $this->assertStringContainsString('id="mds-tab-account"', $tab);
        $this->assertStringContainsString('aria-controls="mds-tabpanel-account"', $tab);

        $this->assertStringStartsWith('<div', $panel);
        $this->assertStringContainsString('role="tabpanel"', $panel);
        $this->assertStringContainsString('id="mds-tabpanel-account"', $panel);
        $this->assertStringContainsString('aria-labelledby="mds-tab-account"', $panel);
        $this->assertStringContainsString('tabindex="0"', $panel);

        $this->assertStringContainsString('>Profile</span>', $html);
        // The panel's slot lands inside the panel it is named for.
        $this->assertMatchesRegularExpression('/data-mds-tab-panel="account"[^>]*>\s*Account panel\s*<\/div>/', $html);
    }

    public function test_the_first_enabled_tab_is_active_by_default(): void
    {
        $html = $this->group();

        $profile = $this->tag($html, 'tab', 'profile');
        $account = $this->tag($html, 'tab', 'account');

        // Roving tabindex: the active tab is the one tab stop.
        $this->assertStringContainsString('aria-selected="true"', $profile);
        $this->assertStringContainsString('tabindex="0"', $profile);
        $this->assertStringContainsString(' data-selected ', $profile);

        $this->assertStringContainsString('aria-selected="false"', $account);
        $this->assertStringContainsString('tabindex="-1"', $account);
        $this->assertStringNotContainsString(' data-selected ', $account);

        // Panels: only the active one is visible before Alpine runs.
        $this->assertStringNotContainsString(' hidden ', $this->tag($html, 'tab-panel', 'profile'));
        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'account'));
        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'billing'));
    }

    public function test_a_disabled_first_tab_is_skipped_for_the_default(): void
    {
        $html = $this->render(<<<'BLADE'
            <mds:tab.group>
                <mds:tabs>
                    <mds:tab name="old" disabled>Old</mds:tab>
                    <mds:tab name="new">New</mds:tab>
                </mds:tabs>
                <mds:tab.panel name="old">Old panel</mds:tab.panel>
                <mds:tab.panel name="new">New panel</mds:tab.panel>
            </mds:tab.group>
        BLADE);

        $this->assertStringContainsString('aria-selected="false"', $this->tag($html, 'tab', 'old'));
        $this->assertStringContainsString('aria-selected="true"', $this->tag($html, 'tab', 'new'));
        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'old'));
        $this->assertStringNotContainsString(' hidden ', $this->tag($html, 'tab-panel', 'new'));
        $this->assertStringContainsString("mdsTabs({ value: 'new' })", $html);
    }

    public function test_the_group_value_picks_the_initial_tab(): void
    {
        $html = $this->group(groupAttributes: 'value="account"');

        $this->assertStringContainsString("mdsTabs({ value: 'account' })", $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\sx-ref="tabsInput"[^>]*\svalue="account"[^>]*\sdata-mds-tabs-input/', $html);
        $this->assertStringContainsString('aria-selected="false"', $this->tag($html, 'tab', 'profile'));
        $this->assertStringContainsString('aria-selected="true"', $this->tag($html, 'tab', 'account'));
        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'profile'));
        $this->assertStringNotContainsString(' hidden ', $this->tag($html, 'tab-panel', 'account'));
    }

    public function test_a_value_naming_a_disabled_or_missing_tab_falls_back_to_the_first_enabled_one(): void
    {
        $html = $this->group(groupAttributes: 'value="billing"');

        $this->assertStringContainsString("mdsTabs({ value: 'profile' })", $html);
        $this->assertStringContainsString('aria-selected="false"', $this->tag($html, 'tab', 'billing'));
        $this->assertStringNotContainsString(' hidden ', $this->tag($html, 'tab-panel', 'profile'));

        $html = $this->group(groupAttributes: 'value="nowhere"');

        $this->assertStringContainsString("mdsTabs({ value: 'profile' })", $html);
        $this->assertNotEmpty($this->tag($html, 'tab-panel', 'profile'));
        $this->assertStringNotContainsString(' hidden ', $this->tag($html, 'tab-panel', 'profile'));
        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'account'));
    }

    public function test_disabled_tabs_are_real_disabled_buttons(): void
    {
        $tab = $this->tag($this->group(), 'tab', 'billing');

        $this->assertMatchesRegularExpression('/\sdisabled\s/', $tab);
        $this->assertStringContainsString('tabindex="-1"', $tab);
        $this->assertStringContainsString('disabled:opacity-50', $tab);
    }

    public function test_two_groups_on_one_page_do_not_leak_into_each_other(): void
    {
        $html = $this->render(<<<'BLADE'
            <mds:tab.group value="b">
                <mds:tabs>
                    <mds:tab name="a">A</mds:tab>
                    <mds:tab name="b">B</mds:tab>
                </mds:tabs>
                <mds:tab.panel name="a">A panel</mds:tab.panel>
                <mds:tab.panel name="b">B panel</mds:tab.panel>
            </mds:tab.group>

            <mds:tab.group>
                <mds:tabs>
                    <mds:tab name="c">C</mds:tab>
                    <mds:tab name="d">D</mds:tab>
                </mds:tabs>
                <mds:tab.panel name="c">C panel</mds:tab.panel>
                <mds:tab.panel name="d">D panel</mds:tab.panel>
            </mds:tab.group>
        BLADE);

        $this->assertStringContainsString("mdsTabs({ value: 'b' })", $html);
        $this->assertStringContainsString("mdsTabs({ value: 'c' })", $html);
        $this->assertStringContainsString('aria-selected="true"', $this->tag($html, 'tab', 'c'));
        $this->assertStringNotContainsString(' hidden ', $this->tag($html, 'tab-panel', 'c'));
        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'd'));

        // The registry the parts talk through is empty again afterwards.
        $this->assertSame(['pending' => [], 'active' => []], app('mds.tabs'));
    }

    public function test_a_group_nested_in_a_panel_keeps_its_own_default(): void
    {
        $html = $this->render(<<<'BLADE'
            <mds:tab.group>
                <mds:tabs>
                    <mds:tab name="outer-1">One</mds:tab>
                    <mds:tab name="outer-2">Two</mds:tab>
                </mds:tabs>
                <mds:tab.panel name="outer-1">
                    <mds:tab.group>
                        <mds:tabs>
                            <mds:tab name="inner-1">Inner one</mds:tab>
                            <mds:tab name="inner-2">Inner two</mds:tab>
                        </mds:tabs>
                        <mds:tab.panel name="inner-1">Inner one panel</mds:tab.panel>
                        <mds:tab.panel name="inner-2">Inner two panel</mds:tab.panel>
                    </mds:tab.group>
                </mds:tab.panel>
                <mds:tab.panel name="outer-2">Two panel</mds:tab.panel>
            </mds:tab.group>
        BLADE);

        $this->assertStringContainsString("mdsTabs({ value: 'outer-1' })", $html);
        $this->assertStringContainsString("mdsTabs({ value: 'inner-1' })", $html);
        $this->assertStringNotContainsString(' hidden ', $this->tag($html, 'tab-panel', 'inner-1'));
        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'inner-2'));
        // The outer panel after the nested group reads the OUTER active tab.
        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'outer-2'));
        $this->assertSame(['pending' => [], 'active' => []], app('mds.tabs'));
    }

    public function test_variants_and_sizes_reach_the_list_and_its_tabs(): void
    {
        $html = $this->group('variant="segmented"');

        $this->assertStringContainsString('data-mds-tabs-variant="segmented"', $html);
        $this->assertStringContainsString('bg-zinc-800/5 p-1', $this->tag($html, 'tabs', ''));
        $this->assertStringContainsString('data-selected:bg-white', $this->tag($html, 'tab', 'profile'));
        $this->assertStringContainsString('px-3 py-1.5 text-sm', $this->tag($html, 'tab', 'profile'));

        $html = $this->group('variant="pills" size="sm"');

        $this->assertStringContainsString('data-mds-tabs-variant="pills"', $html);
        $this->assertStringContainsString('data-mds-tabs-size="sm"', $html);
        $this->assertStringContainsString('rounded-full', $this->tag($html, 'tab', 'profile'));
        $this->assertStringContainsString('data-selected:bg-accent/10', $this->tag($html, 'tab', 'profile'));
        $this->assertStringContainsString('px-2.5 py-1 text-xs', $this->tag($html, 'tab', 'profile'));

        $html = $this->group('size="sm"');

        $this->assertStringContainsString('h-8 text-xs', $this->tag($html, 'tab', 'profile'));
        $this->assertStringContainsString('data-selected:border-accent', $this->tag($html, 'tab', 'profile'));

        // An unknown variant is the default one.
        $this->assertStringContainsString('data-mds-tabs-variant="default"', $this->group('variant="fancy"'));
    }

    public function test_the_tablist_scrolls_instead_of_wrapping(): void
    {
        $list = $this->tag($this->group(), 'tabs', '');

        $this->assertStringContainsString('overflow-x-auto', $list);
        $this->assertStringContainsString('whitespace-nowrap', $this->tag($this->group(), 'tab', 'profile'));
    }

    public function test_icons_render_through_mds_icon_on_either_side(): void
    {
        $html = $this->render(<<<'BLADE'
            <mds:tab.group>
                <mds:tabs>
                    <mds:tab name="a" icon="user">Leading</mds:tab>
                    <mds:tab name="b" icon:trailing="cog-6-tooth">Trailing</mds:tab>
                    <mds:tab name="c" icon-trailing="check">Trailing prop</mds:tab>
                </mds:tabs>
            </mds:tab.group>
        BLADE);

        $this->assertMatchesRegularExpression('/<svg[^>]*data-mds-icon[^>]*>.*?<\/svg>\s*<span>Leading<\/span>/s', $html);
        $this->assertMatchesRegularExpression('/<span>Trailing<\/span>\s*<svg[^>]*data-mds-icon/s', $html);
        $this->assertMatchesRegularExpression('/<span>Trailing prop<\/span>\s*<svg[^>]*data-mds-icon/s', $html);
        $this->assertSame(3, substr_count($html, 'data-mds-icon'));

        // The colon attribute is consumed, not printed.
        $this->assertStringNotContainsString('icon:trailing', $html);
    }

    public function test_click_and_bindings_are_wired_per_tab(): void
    {
        $tab = $this->tag($this->group(), 'tab', 'account');

        $this->assertStringContainsString("x-on:click=\"select('account')\"", $tab);
        $this->assertStringContainsString("x-bind:aria-selected=\"isActive('account') ? 'true' : 'false'\"", $tab);
        $this->assertStringContainsString("x-bind:tabindex=\"isActive('account') ? 0 : -1\"", $tab);
        $this->assertStringContainsString("x-bind:data-selected=\"isActive('account') ? '' : false\"", $tab);

        $panel = $this->tag($this->group(), 'tab-panel', 'account');

        $this->assertStringContainsString("x-bind:hidden=\"! isActive('account')\"", $panel);
    }

    public function test_the_keyboard_pattern_is_in_the_script(): void
    {
        $html = $this->group();

        foreach (['ArrowLeft', 'ArrowRight', 'Home', 'End'] as $key) {
            $this->assertStringContainsString("'{$key}'", $html);
        }

        // Left/Right follow the visual order: the direction is read at keydown.
        $this->assertStringContainsString("getComputedStyle(list).direction === 'rtl'", $html);
        $this->assertStringContainsString("(event.key === 'ArrowRight') !== rtl", $html);
        // Disabled tabs are skipped, and focus follows activation.
        $this->assertStringContainsString('[data-mds-tab]:not([disabled])', $html);
        $this->assertStringContainsString('tabs[next].focus()', $html);
    }

    public function test_wire_model_reaches_the_hidden_input_and_name_posts_it(): void
    {
        $html = $this->group('wire:model="tab" name="tab"');

        $this->assertBindingReachesControl($html, 'input', 'wire:model="tab"');
        $this->assertMatchesRegularExpression('/<input[^>]*\stype="hidden"[^>]*\sname="tab"/', $html);
        // The tablist itself carries neither.
        $this->assertStringNotContainsString('role="tablist" aria-orientation="horizontal" aria-label="زبانه‌ها" name=', $html);
        $this->assertDoesNotMatchRegularExpression('/<div[^>]*\sname="tab"/', $html);

        $html = $this->group('wire:model.live="tab"');

        $this->assertBindingReachesControl($html, 'input', 'wire:model.live="tab"');
    }

    public function test_wire_model_on_the_group_is_refused_rather_than_ignored(): void
    {
        // Flux takes the binding on the group; here it belongs on the list, and
        // on a plain div Livewire would ignore it without a word.
        $this->assertRefuses(
            '<mds:tab.group wire:model="tab"><mds:tabs><mds:tab name="a">A</mds:tab></mds:tabs></mds:tab.group>',
            'mds:tab.group does not take wire:model',
        );

        // A wire:* attribute that is not a binding still passes through.
        $html = $this->render('<mds:tab.group wire:ignore><mds:tabs><mds:tab name="a">A</mds:tab></mds:tabs></mds:tab.group>');

        $this->assertMatchesRegularExpression('/<div[^>]*\swire:ignore[^>]*\sdata-mds-tab-group/', $html);
    }

    public function test_the_script_re_syncs_from_a_livewire_morph_and_dispatches_input(): void
    {
        $html = $this->group();

        $this->assertStringContainsString("attributeFilter: ['value']", $html);
        $this->assertStringContainsString("input.getAttribute('value')", $html);
        $this->assertStringContainsString("new Event('input', { bubbles: true })", $html);
        $this->assertStringContainsString('this.observer?.disconnect()', $html);
    }

    public function test_the_script_ships_once_and_survives_a_started_alpine(): void
    {
        // One page, two groups: @once is per render pass, so the second group
        // must reuse the first one's block.
        $page = $this->render(<<<'BLADE'
            <mds:tab.group>
                <mds:tabs><mds:tab name="a">A</mds:tab></mds:tabs>
                <mds:tab.panel name="a">A panel</mds:tab.panel>
            </mds:tab.group>

            <mds:tab.group>
                <mds:tabs><mds:tab name="b">B</mds:tab></mds:tabs>
                <mds:tab.panel name="b">B panel</mds:tab.panel>
            </mds:tab.group>
        BLADE);

        $this->assertSame(2, substr_count($page, 'x-data="mdsTabs('));
        $this->assertSame(1, substr_count($page, "Alpine.data('mdsTabs'"));
        $this->assertStringContainsString('if (window.Alpine) {', $page);
        $this->assertStringContainsString("addEventListener('alpine:init', () => window.mds.registerTabs(window.Alpine))", $page);
        $this->assertStringContainsString('<script >', $page);
    }

    public function test_the_tablist_label_follows_fa(): void
    {
        $this->assertStringContainsString('aria-label="زبانه‌ها"', $this->group());
        $this->assertStringContainsString('aria-label="Tabs"', $this->group(groupAttributes: ':fa="false"'));
        $this->assertStringContainsString('aria-label="Tabs"', $this->group(':fa="false"'));
        $this->assertStringContainsString('aria-label="Sections"', $this->group('label="Sections"'));

        config(['mds.persian_digits' => false]);

        try {
            $html = $this->group();

            $this->assertStringContainsString('aria-label="Tabs"', $html);
            $this->assertStringNotContainsString('زبانه‌ها', $html);
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }

    public function test_extra_attributes_land_on_the_right_elements(): void
    {
        $html = $this->render(<<<'BLADE'
            <mds:tab.group class="group-class" data-x="g">
                <mds:tabs class="list-class" data-x="l">
                    <mds:tab name="a" class="tab-class" data-x="t">A</mds:tab>
                </mds:tabs>
                <mds:tab.panel name="a" class="panel-class" data-x="p">A panel</mds:tab.panel>
            </mds:tab.group>
        BLADE);

        $this->assertMatchesRegularExpression('/<div[^>]*\sclass="group-class"[^>]*\sdata-x="g"[^>]*\sdata-mds-tab-group/', $html);
        $this->assertMatchesRegularExpression('/<div[^>]*\sdata-x="l"[^>]*\srole="tablist"/', $html);
        $this->assertStringContainsString('list-class', $this->tag($html, 'tabs', ''));
        $this->assertStringContainsString('tab-class', $this->tag($html, 'tab', 'a'));
        $this->assertStringContainsString('data-x="t"', $this->tag($html, 'tab', 'a'));
        $this->assertStringContainsString('panel-class', $this->tag($html, 'tab-panel', 'a'));
        $this->assertStringContainsString('data-x="p"', $this->tag($html, 'tab-panel', 'a'));
    }

    public function test_persian_tab_names_make_valid_ids(): void
    {
        $html = $this->render(<<<'BLADE'
            <mds:tab.group>
                <mds:tabs>
                    <mds:tab name="مشخصات کلی">مشخصات</mds:tab>
                </mds:tabs>
                <mds:tab.panel name="مشخصات کلی">…</mds:tab.panel>
            </mds:tab.group>
        BLADE);

        $this->assertStringContainsString('id="mds-tab-مشخصات-کلی"', $html);
        $this->assertStringContainsString('aria-controls="mds-tabpanel-مشخصات-کلی"', $html);
        $this->assertStringContainsString('id="mds-tabpanel-مشخصات-کلی"', $html);
        $this->assertStringContainsString('aria-labelledby="mds-tab-مشخصات-کلی"', $html);
    }

    public function test_a_tab_without_a_name_is_refused(): void
    {
        $this->assertRefuses(
            '<mds:tab.group><mds:tabs><mds:tab>A</mds:tab></mds:tabs></mds:tab.group>',
            'mds:tab needs a name',
        );
    }

    public function test_a_panel_without_a_name_is_refused(): void
    {
        $this->assertRefuses(
            '<mds:tab.group><mds:tab.panel>A</mds:tab.panel></mds:tab.group>',
            'mds:tab.panel needs a name',
        );
    }

    public function test_an_empty_tablist_renders_without_an_active_tab(): void
    {
        $html = $this->render('<mds:tab.group><mds:tabs /></mds:tab.group>');

        $this->assertStringContainsString('mdsTabs({ value: null })', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*\svalue=""/', $html);
    }

    public function test_a_panel_before_its_tablist_falls_back_to_the_group_value(): void
    {
        $html = $this->render(<<<'BLADE'
            <mds:tab.group value="b">
                <mds:tab.panel name="a">A panel</mds:tab.panel>
                <mds:tab.panel name="b">B panel</mds:tab.panel>
                <mds:tabs>
                    <mds:tab name="a">A</mds:tab>
                    <mds:tab name="b">B</mds:tab>
                </mds:tabs>
            </mds:tab.group>
        BLADE);

        $this->assertStringContainsString(' hidden ', $this->tag($html, 'tab-panel', 'a'));
        $this->assertStringNotContainsString(' hidden ', $this->tag($html, 'tab-panel', 'b'));
        $this->assertStringContainsString('aria-selected="true"', $this->tag($html, 'tab', 'b'));
        $this->assertSame(['pending' => [], 'active' => []], app('mds.tabs'));
    }

    public function test_it_renders_next_to_flux_without_the_session_error_bag(): void
    {
        View::share('errors', new ViewErrorBag);

        $html = $this->render('<flux:card>'.$this->group().'</flux:card>');

        $this->assertStringContainsString('data-mds-tab-group', $html);
    }
}
