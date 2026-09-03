<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;

class EditorTest extends TestCase
{
    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * The kit's Livewire contract, asserted the same way for every control:
     * the binding reaches the real form element, and the wrapper keeps no
     * copy of it.
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
     * Toolbar buttons, counted without matching `data-mds-editor-toolbar` or
     * the selector inside the shared script.
     */
    protected function tools(string $html): int
    {
        return preg_match_all('/data-mds-editor-tool\s*>/', $html);
    }

    // ---------------------------------------------------------------- structure

    public function test_it_renders_the_root_toolbar_surface_and_hidden_input(): void
    {
        $html = $this->render('<mds:editor name="body" />');

        $this->assertStringContainsString('data-mds-editor', $html);
        $this->assertStringContainsString('data-mds-editor-toolbar', $html);
        $this->assertStringContainsString('data-mds-editor-content', $html);
        $this->assertStringContainsString('data-mds-editor-input', $html);
        $this->assertMatchesRegularExpression('/<input[^>]*type="hidden"[^>]*name="body"/', $html);
        $this->assertStringContainsString('x-data="mdsEditor(', $html);
    }

    public function test_the_editing_surface_is_a_contenteditable_textbox(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertMatchesRegularExpression(
            '/<div[^>]*contenteditable="true"[^>]*data-mds-editor-content/s',
            $html,
        );
        $this->assertStringContainsString('role="textbox"', $html);
        $this->assertStringContainsString('aria-multiline="true"', $html);
    }

    /**
     * The surface is filled by Alpine from the sanitised hidden value, so a
     * Livewire morph must be kept off it — morphdom would otherwise diff the
     * server's empty div against the typed content and wipe it.
     */
    public function test_the_surface_is_ignored_by_livewire_morphs(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertMatchesRegularExpression('/wire:ignore[^>]*data-mds-editor-content/s', $html);
    }

    public function test_the_default_toolbar_carries_every_built_in_tool(): void
    {
        $html = $this->render('<mds:editor />');

        foreach (['bold', 'italic', 'underline', 'strike', 'h1', 'h2', 'h3', 'bullet', 'ordered', 'quote', 'code', 'link', 'unlink', 'direction', 'clear'] as $tool) {
            $this->assertStringContainsString('data-mds-editor-command="'.$tool.'"', $html, "The [{$tool}] tool is missing from the default toolbar.");
        }

        $this->assertSame(15, $this->tools($html));
        $this->assertSame(5, substr_count($html, 'data-mds-editor-separator'));
    }

    public function test_the_toolbar_prop_picks_the_tools_and_their_order(): void
    {
        $html = $this->render('<mds:editor toolbar="bold | link" />');

        $this->assertSame(2, $this->tools($html));
        $this->assertSame(1, substr_count($html, 'data-mds-editor-separator'));
        $this->assertStringContainsString('data-mds-editor-command="bold"', $html);
        $this->assertStringContainsString('data-mds-editor-command="link"', $html);
        $this->assertStringNotContainsString('data-mds-editor-command="italic"', $html);
    }

    public function test_the_toolbar_can_be_switched_off_entirely(): void
    {
        $html = $this->render('<mds:editor :toolbar="false" />');

        $this->assertStringNotContainsString('data-mds-editor-toolbar', $html);
        $this->assertStringContainsString('data-mds-editor-content', $html);
    }

    public function test_a_tool_name_can_be_spelled_the_long_way(): void
    {
        $html = $this->render('<mds:editor toolbar="strikethrough ordered-list blockquote code-block clear-format" />');

        foreach (['strike', 'ordered', 'quote', 'code', 'clear'] as $tool) {
            $this->assertStringContainsString('data-mds-editor-command="'.$tool.'"', $html);
        }
    }

    public function test_the_default_slot_replaces_the_built_in_composition(): void
    {
        $html = $this->render(<<<'BLADE'
        <mds:editor>
            <mds:editor.toolbar tools="bold">
                <mds:editor.button command="bold" />
                <mds:editor.button label="Emoji">:-)</mds:editor.button>
            </mds:editor.toolbar>
            <mds:editor.content placeholder="Say something" />
        </mds:editor>
        BLADE);

        // The slot's own toolbar, not the default fifteen...
        $this->assertSame(2, $this->tools($html));
        $this->assertStringContainsString('aria-label="Emoji"', $html);
        $this->assertMatchesRegularExpression('/data-mds-editor-tool\s*>\s*:-\)\s*<\/button>/s', $html);
        $this->assertStringContainsString('Say something', $html);
    }

    // ------------------------------------------------------------------ props

    public function test_the_value_reaches_the_hidden_input_and_never_the_page_as_markup(): void
    {
        // The stored HTML is the one thing a rich-text field must not echo:
        // it goes into the hidden input as an escaped attribute and only the
        // client sanitiser ever turns it back into nodes.
        $html = $this->render('<mds:editor :value="$value" />', [
            'value' => '<p>سلام</p><script>alert(1)</script>',
        ]);

        $this->assertStringContainsString('value="&lt;p&gt;سلام&lt;/p&gt;&lt;script&gt;alert(1)&lt;/script&gt;"', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
        $this->assertMatchesRegularExpression('/data-mds-editor-content[^>]*>\s*<\/div>/s', $html);
    }

    public function test_the_placeholder_only_renders_when_one_is_given(): void
    {
        $html = $this->render('<mds:editor placeholder="داستان محصول را بنویسید" />');

        $this->assertStringContainsString('data-mds-editor-placeholder', $html);
        $this->assertStringContainsString('>داستان محصول را بنویسید</div>', $html);
        $this->assertStringContainsString('x-show="empty"', $html);

        $this->assertStringNotContainsString('data-mds-editor-placeholder', $this->render('<mds:editor />'));
    }

    public function test_rows_sets_the_minimum_height_on_the_block_axis(): void
    {
        $this->assertStringContainsString('style="min-block-size: 10.5rem"', $this->render('<mds:editor />'));
        $this->assertStringContainsString('style="min-block-size: 3.5rem"', $this->render('<mds:editor :rows="2" />'));

        // Never zero or negative, whatever the caller passes...
        $this->assertStringContainsString('style="min-block-size: 1.75rem"', $this->render('<mds:editor :rows="0" />'));
    }

    public function test_dir_pins_the_base_direction_of_the_surface(): void
    {
        $this->assertMatchesRegularExpression('/dir="rtl"[^>]*data-mds-editor-content/s', $this->render('<mds:editor dir="rtl" />'));
        $this->assertStringNotContainsString('dir="', $this->render('<mds:editor />'));
    }

    public function test_disabled_makes_the_whole_control_inert_and_uneditable(): void
    {
        $html = $this->render('<mds:editor disabled />');

        $this->assertStringContainsString('inert', $html);
        $this->assertStringContainsString('aria-disabled="true"', $html);
        $this->assertStringContainsString('contenteditable="false"', $html);
        $this->assertStringContainsString('disabled: true,', $html);
    }

    public function test_it_renders_the_flux_field_chrome(): void
    {
        $html = $this->render('<mds:editor label="متن آگهی" description="حداکثر ۵۰۰ کلمه" />');

        $this->assertStringContainsString('متن آگهی', $html);
        $this->assertStringContainsString('حداکثر ۵۰۰ کلمه', $html);

        // The label also names the surface — a contenteditable div cannot be
        // the target of a <label for>...
        $this->assertStringContainsString('aria-label="متن آگهی"', $html);
    }

    public function test_the_label_and_description_can_be_screen_reader_only(): void
    {
        $html = $this->render('<mds:editor label="متن" description="راهنما" label:sr-only description:sr-only />');

        $this->assertSame(2, substr_count($html, 'sr-only'));
        $this->assertStringNotContainsString('label:sr-only', $html);
        $this->assertStringNotContainsString('description:sr-only', $html);
    }

    // ------------------------------------------------------------- validation

    public function test_an_explicit_error_renders_and_marks_the_surface_invalid(): void
    {
        $html = $this->render('<mds:editor error="متن آگهی الزامی است." />');

        $this->assertStringContainsString('متن آگهی الزامی است.', $html);
        $this->assertStringContainsString('role="alert"', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('border-red-500', $html);
    }

    public function test_it_falls_back_to_the_validation_bag_for_its_name(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['body' => ['متن نمی‌تواند خالی باشد.']]));

        // ShareErrorsFromSession shares the bag view-wide; mirror that here...
        View::share('errors', $bag);

        $html = $this->render('<mds:editor name="body" />');

        $this->assertStringContainsString('متن نمی‌تواند خالی باشد.', $html);
        $this->assertStringContainsString('aria-invalid="true"', $html);
    }

    public function test_an_explicit_error_wins_over_the_bag(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['body' => ['از کیسه']]));

        View::share('errors', $bag);

        $html = $this->render('<mds:editor name="body" error="صریح" />');

        $this->assertStringContainsString('صریح', $html);
        $this->assertStringNotContainsString('از کیسه', $html);
    }

    public function test_invalid_marks_the_control_without_a_message(): void
    {
        $html = $this->render('<mds:editor invalid />');

        $this->assertStringContainsString('aria-invalid="true"', $html);
        $this->assertStringContainsString('border-red-500', $html);
        $this->assertStringNotContainsString('role="alert"', $html);
    }

    // --------------------------------------------------------------- Livewire

    public function test_wire_model_reaches_the_hidden_input_and_nothing_else(): void
    {
        $html = $this->render('<mds:editor name="body" wire:model="body" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model="body"');
    }

    public function test_wire_model_keeps_its_modifiers(): void
    {
        $html = $this->render('<mds:editor wire:model.live.debounce.500ms="body" />');

        $this->assertBindingReachesControl($html, 'input[^>]*type="hidden"', 'wire:model.live.debounce.500ms="body"');
    }

    public function test_other_attributes_land_on_the_wrapper(): void
    {
        $html = $this->render('<mds:editor class="max-w-lg" id="story" wire:model="body" />');

        $this->assertMatchesRegularExpression('/<div[^>]*id="story"[^>]*data-mds-editor\b/s', $html);
        $this->assertStringContainsString('max-w-lg', $html);
    }

    public function test_the_alpine_component_re_reads_the_hidden_input_after_a_morph(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertStringContainsString("attributeFilter: ['value']", $html);
        $this->assertStringContainsString('new MutationObserver(() => this.resync())', $html);
        $this->assertStringContainsString('this.observer?.disconnect()', $html);
        $this->assertStringContainsString("removeEventListener('selectionchange'", $html);
    }

    // ------------------------------------------------------------------- ARIA

    public function test_the_toolbar_follows_the_wai_aria_toolbar_pattern(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertStringContainsString('role="toolbar"', $html);
        $this->assertStringContainsString('aria-label="نوار ابزار قالب‌بندی"', $html);
        $this->assertStringContainsString('x-bind:aria-controls="$id(\'mds-editor-surface\')"', $html);
        $this->assertStringContainsString('x-on:keydown="toolbarKeydown($event)"', $html);
        $this->assertStringContainsString('role="separator"', $html);
        $this->assertStringContainsString('aria-orientation="vertical"', $html);

        // One tab stop: every tool starts at -1 and Alpine promotes one.
        $this->assertSame(15, substr_count($html, 'tabindex="-1"'));
        $this->assertStringNotContainsString('tabindex="0"', $html);
    }

    public function test_arrow_keys_follow_the_visual_order_in_rtl(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertStringContainsString("getComputedStyle(event.currentTarget).direction === 'rtl'", $html);
        $this->assertStringContainsString("if (event.key === 'ArrowRight') next = index + (rtl ? -1 : 1)", $html);
        $this->assertStringContainsString("else if (event.key === 'Home') next = 0", $html);
        $this->assertStringContainsString("else if (event.key === 'End') next = tools.length - 1", $html);
    }

    public function test_toggles_carry_aria_pressed_and_one_shot_actions_do_not(): void
    {
        $html = $this->render('<mds:editor toolbar="bold link unlink clear" />');

        $this->assertStringContainsString('x-bind:aria-pressed="active(\'bold\') ? \'true\' : \'false\'"', $html);
        $this->assertSame(1, substr_count($html, 'x-bind:aria-pressed'));

        // link / unlink / clear do something once; they are not toggles.
        $this->assertStringNotContainsString('active(\'link\')', $html);
        $this->assertStringNotContainsString('active(\'clear\')', $html);
    }

    public function test_the_surface_is_named_even_without_a_label(): void
    {
        $this->assertStringContainsString('aria-label="ویرایشگر متن"', $this->render('<mds:editor />'));
    }

    public function test_toolbar_buttons_never_steal_the_selection(): void
    {
        $html = $this->render('<mds:editor toolbar="bold" />');

        $this->assertStringContainsString('x-on:mousedown.prevent', $html);
        $this->assertStringContainsString('focus-visible:outline-accent', $html);
    }

    // -------------------------------------------------------------- behaviour

    public function test_the_sanitiser_allow_list_is_the_documented_one(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertStringContainsString("const ALLOWED = ['p', 'h1', 'h2', 'h3', 'ul', 'ol', 'li', 'blockquote', 'pre', 'strong', 'em', 'u', 's', 'code', 'a', 'br']", $html);
        $this->assertStringContainsString("'script', 'style'", $html);
        $this->assertStringContainsString('document.implementation.createHTMLDocument', $html);

        // Both directions: what is pasted in, and what is handed to Livewire.
        $this->assertStringContainsString('sanitize(this.$refs.surface.innerHTML)', $html);
        $this->assertStringContainsString('this.insert(html ? sanitize(html) : escape(text)', $html);
    }

    public function test_the_sanitiser_guards_the_href_scheme_and_prunes_empty_blocks(): void
    {
        $html = $this->render('<mds:editor />');

        // Only schemes that cannot execute survive; an <a> left without an
        // href is unwrapped rather than kept as a dead link.
        $this->assertStringContainsString('const SAFE_HREF =', $html);
        $this->assertStringContainsString("if (mapped === 'a' && ! el.getAttribute('href')) {", $html);

        // Chrome's insertUnorderedList closes the paragraph it started in and
        // leaves empty shells; a deliberate blank line keeps its <br>.
        $this->assertStringContainsString("const PRUNE = ['p', 'h1', 'h2', 'h3', 'blockquote', 'pre', 'ul', 'ol', 'li']", $html);
        $this->assertStringContainsString("! el.querySelector('br')", $html);
    }

    public function test_the_block_lookup_descends_into_an_element_anchored_selection(): void
    {
        // Ctrl+A anchors the range on the surface itself with an index
        // offset — without the descent every toggle read "no block" and
        // stopped toggling.
        $html = $this->render('<mds:editor />');

        $this->assertStringContainsString('if (node.nodeType === Node.ELEMENT_NODE) node = node.childNodes[range.startOffset] ?? node.lastChild ?? node', $html);
    }

    public function test_a_pressed_tool_is_tinted_the_way_the_kit_marks_a_selection(): void
    {
        $html = $this->render('<mds:editor toolbar="bold" />');

        $this->assertStringContainsString('aria-pressed:bg-accent/10', $html);
        $this->assertStringContainsString('aria-pressed:text-accent-content', $html);
    }

    public function test_a_drop_is_sanitised_like_a_paste(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertStringContainsString('x-on:paste="paste($event)"', $html);
        $this->assertStringContainsString('x-on:drop="drop($event)"', $html);
        $this->assertStringContainsString('this.take(event.dataTransfer)', $html);
    }

    public function test_it_binds_the_common_keyboard_shortcuts(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertStringContainsString('x-on:keydown="keydown($event)"', $html);
        $this->assertStringContainsString("else if (key === 'b') command = 'bold'", $html);
        $this->assertStringContainsString("else if (key === 'i') command = 'italic'", $html);
        $this->assertStringContainsString("else if (key === 'u') command = 'underline'", $html);
        $this->assertStringContainsString("else if (key === 'k') command = 'link'", $html);
        $this->assertStringContainsString("if (key === 'x') command = 'strike'", $html);
    }

    public function test_the_value_committed_to_livewire_is_a_bubbling_input_event(): void
    {
        $html = $this->render('<mds:editor />');

        $this->assertStringContainsString("input.dispatchEvent(new Event('input', { bubbles: true }))", $html);
    }

    public function test_the_script_is_registered_once_per_page_behind_the_alpine_guard(): void
    {
        $html = $this->render('<mds:editor /><mds:editor />');

        $this->assertSame(1, substr_count($html, 'window.mds.registerEditor = '));
        $this->assertSame(1, substr_count($html, '<script'));
        $this->assertStringContainsString('if (window.mds.editorRegistered) return', $html);
        $this->assertStringContainsString('document.addEventListener(\'alpine:init\', () => window.mds.registerEditor(window.Alpine))', $html);

        // Two editors, two independent Alpine scopes...
        $this->assertSame(2, substr_count($html, 'x-data="mdsEditor('));
    }

    // ------------------------------------------------------------------ fa

    public function test_fa_reaches_the_subcomponents(): void
    {
        $html = $this->render('<mds:editor :fa="false" toolbar="bold" />');

        $this->assertStringContainsString('aria-label="Bold"', $html);
        $this->assertStringContainsString('aria-label="Formatting toolbar"', $html);
        $this->assertStringContainsString('aria-label="Rich text editor"', $html);
        $this->assertStringNotContainsString('aria-label="پررنگ"', $html);
    }

    public function test_fa_reaches_subcomponents_written_in_the_slot(): void
    {
        $html = $this->render(<<<'BLADE'
        <mds:editor :fa="false">
            <mds:editor.toolbar tools="bold" />
            <mds:editor.content />
        </mds:editor>
        BLADE);

        $this->assertStringContainsString('aria-label="Bold"', $html);
        $this->assertStringContainsString('aria-label="Formatting toolbar"', $html);
        $this->assertStringContainsString('aria-label="Rich text editor"', $html);
    }

    public function test_heading_labels_number_in_persian_digits(): void
    {
        $this->assertStringContainsString('aria-label="عنوان ۲"', $this->render('<mds:editor toolbar="h2" />'));
        $this->assertStringContainsString('aria-label="Heading 2"', $this->render('<mds:editor toolbar="h2" :fa="false" />'));
    }

    public function test_the_link_prompt_follows_fa(): void
    {
        $this->assertStringContainsString("linkPrompt: 'نشانی پیوند'", $this->jsDecoded($this->render('<mds:editor />')));
        $this->assertStringContainsString("linkPrompt: 'Link URL'", $this->render('<mds:editor :fa="false" />'));
    }

    public function test_labels_can_be_overridden_one_by_one(): void
    {
        $html = $this->render('<mds:editor toolbar-label="ابزارها"><mds:editor.toolbar tools="bold" label="ابزارها"><mds:editor.button command="bold" label="سیاه" /></mds:editor.toolbar></mds:editor>');

        $this->assertStringContainsString('aria-label="ابزارها"', $html);
        $this->assertStringContainsString('aria-label="سیاه"', $html);
        $this->assertStringNotContainsString('aria-label="پررنگ"', $html);
    }

    public function test_a_button_can_carry_a_custom_icon_and_command(): void
    {
        $html = $this->render('<mds:editor.button command="bold" icon="check" label="تأیید" />');

        $this->assertStringContainsString('data-mds-editor-command="bold"', $html);
        $this->assertStringContainsString('aria-label="تأیید"', $html);
        $this->assertStringContainsString('data-mds-icon', $html);
    }

    /**
     * IconsTest only scans literal `icon="..."` attributes in a view, and the
     * button binds `:icon="$icon"` from a table — so nothing else checks that
     * these sixteen names resolve to a real Hugeicon.
     */
    public function test_every_default_tool_icon_resolves(): void
    {
        $tools = ['bold', 'italic', 'underline', 'strike', 'h1', 'h2', 'h3', 'paragraph', 'bullet', 'ordered', 'quote', 'code', 'link', 'unlink', 'direction', 'clear'];

        foreach ($tools as $tool) {
            $html = $this->render('<mds:editor.button command="'.$tool.'" />');

            $this->assertMatchesRegularExpression(
                '/<svg[^>]*data-mds-icon/',
                $html,
                "The [{$tool}] tool renders no icon — its name resolves to no Hugeicon.",
            );
        }
    }
}
