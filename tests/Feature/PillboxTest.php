<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class PillboxTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The field chrome is Flux's own, which reads the shared error bag the
        // way ShareErrorsFromSession provides it in a real request.
        View::share('errors', new ViewErrorBag);
    }

    protected function render(string $template, array $data = []): string
    {
        return Blade::render($template, $data);
    }

    /**
     * The kit's Livewire contract: the binding reaches the real form element,
     * and the wrapper keeps no copy of it.
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

    private const TAGS = '<mds:pillbox wire:model.live="tags" name="tags" label="Tags" placeholder="Pick a stack…" :value="[\'php\', \'js\']" clearable>
        <mds:pillbox.option value="php">PHP</mds:pillbox.option>
        <mds:pillbox.option value="js">JavaScript</mds:pillbox.option>
        <mds:pillbox.option value="go">Go</mds:pillbox.option>
        <mds:pillbox.option value="rust" disabled>Rust</mds:pillbox.option>
    </mds:pillbox>';

    public function test_renders_root_control_popup_and_multiselectable_listbox(): void
    {
        $html = $this->render(self::TAGS);

        $this->assertMatchesRegularExpression('/<div\s[^>]*x-data="mdsPillbox\(\{[^>]*data-mds-pillbox\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-on:click="activate\(\)"\s+data-mds-pillbox-control/s', $html);
        $this->assertStringContainsString('data-mds-pillbox-popup', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*role="listbox"[^>]*aria-multiselectable="true"[^>]*data-mds-pillbox-list/s', $html);
        $this->assertStringContainsString('class="max-h-60 overflow-auto"', $html);
        $this->assertSame(4, substr_count($html, 'role="option"'));
        $this->assertSame(4, preg_match_all('/data-mds-pillbox-option\s*>/', $html));
        // The popup hangs under the control in the tree — no teleport.
        $this->assertStringNotContainsString('x-teleport', $html);
        $this->assertMatchesRegularExpression('/class="absolute start-0 end-0 top-full[^"]*"[^>]*x-show="expanded"/', $html);
        // The listbox is the element aria-controls points at.
        $this->assertStringContainsString('x-bind:id="$id(\'mds-pillbox-listbox\')"', $html);
        $this->assertStringContainsString('x-id="[\'mds-pillbox-listbox\', \'mds-pillbox-option\']"', $html);
    }

    public function test_the_machine_value_is_a_visually_hidden_multiple_select(): void
    {
        $html = $this->render(self::TAGS);

        $this->assertMatchesRegularExpression(
            '/<select\s+multiple\s+class="sr-only"\s+tabindex="-1"\s+aria-hidden="true"\s+x-ref="select"/s',
            $html,
        );
        // Livewire reads a multiple select as an array; a plain form post needs the [] name.
        $this->assertMatchesRegularExpression('/<select\s[^>]*\sname="tags\[\]"/s', $html);
        // Its options mirror the chosen values, labelled from the matching rows.
        $this->assertMatchesRegularExpression('/<option value="php" selected>PHP<\/option>/', $html);
        $this->assertMatchesRegularExpression('/<option value="js" selected>JavaScript<\/option>/', $html);
        $this->assertSame(2, substr_count($html, ' selected>'));
        $this->assertStringContainsString('data-mds-pillbox-select', $html);
    }

    public function test_wire_model_reaches_the_hidden_select_exactly_once(): void
    {
        $html = $this->render(self::TAGS);

        $this->assertBindingReachesControl($html, 'select[^>]*multiple', 'wire:model.live="tags"');

        // The wrapper kept none of it, and the text field is not the bound control.
        $this->assertDoesNotMatchRegularExpression('/<div\s[^>]*wire:model/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<input\s[^>]*wire:model/s', $html);
    }

    public function test_the_field_is_named_after_its_binding_when_no_name_is_given(): void
    {
        $html = $this->render('<mds:pillbox wire:model="skills" />');

        $this->assertMatchesRegularExpression('/<select\s[^>]*\sname="skills\[\]"/s', $html);

        // No binding and no name: nothing to post under, so no name attribute.
        $this->assertDoesNotMatchRegularExpression('/<select\s[^>]*\sname=/s', $this->render('<mds:pillbox />'));
    }

    public function test_the_value_prop_takes_an_array_a_comma_string_or_nothing(): void
    {
        $fromString = $this->render('<mds:pillbox name="tags" value="php, js" />');

        $this->assertMatchesRegularExpression('/<option value="php" selected>php<\/option>/', $fromString);
        $this->assertMatchesRegularExpression('/<option value="js" selected>js<\/option>/', $fromString);

        // Duplicates and blanks collapse; the order the caller gave survives.
        $messy = $this->render('<mds:pillbox name="tags" :value="[\'js\', \'php\', \'js\', \'\']" />');

        $this->assertSame(2, substr_count($messy, ' selected>'));
        $this->assertMatchesRegularExpression('/value="js" selected>js<\/option>\s*<option value="php"/s', $messy);

        $none = $this->render('<mds:pillbox name="tags" />');

        $this->assertStringNotContainsString(' selected>', $none);
        $this->assertMatchesRegularExpression('/data-mds-pillbox-initial>\s*<\/span>/s', $none);
        $this->assertStringContainsString('selected: [],', $none);
    }

    public function test_the_first_paint_draws_the_chosen_values_as_pills(): void
    {
        $html = $this->render(self::TAGS);

        // Server pills stand in until Alpine's x-for can take over...
        $this->assertMatchesRegularExpression('/<span class="contents" x-show="! ready" data-mds-pillbox-initial>/', $html);
        // Two server pills, plus the x-for template that replaces them.
        $this->assertSame(3, preg_match_all('/data-mds-pillbox-pill>/', $html));
        $this->assertSame(2, preg_match_all('/data-mds-pillbox-pill-label>[^<]+<\/span>/', $html));
        $this->assertMatchesRegularExpression('/<span class="truncate" data-mds-pillbox-pill-label>PHP<\/span>/', $html);
        $this->assertMatchesRegularExpression('/<span class="truncate" data-mds-pillbox-pill-label>JavaScript<\/span>/', $html);
        // ...each with a named remove button naming the pill it removes.
        $this->assertMatchesRegularExpression('/<button\s[^>]*x-on:click\.stop="remove\(\'php\'\)"[^>]*aria-label="حذف PHP"[^>]*data-mds-pillbox-remove/s', $html);
        $this->assertMatchesRegularExpression('/<button\s[^>]*aria-label="حذف JavaScript"/s', $html);

        // ...and the x-for that replaces them the moment Alpine runs.
        $this->assertMatchesRegularExpression('/<template x-for="pill in pills" :key="pill\.value">/', $html);
        $this->assertStringContainsString('<span class="truncate" x-text="pill.label" data-mds-pillbox-pill-label></span>', $html);
        $this->assertMatchesRegularExpression('/<button\s[^>]*x-on:click\.stop="remove\(pill\.value\)"[^>]*x-bind:aria-label="removeLabel \+ \' \' \+ pill\.label"/s', $html);
        // Clicks on a remove button never open the list, and never blur the
        // field: two server pills, the template, the clear button, the popup.
        $this->assertSame(5, substr_count($html, 'x-on:mousedown.prevent'));
    }

    public function test_the_text_field_carries_the_combobox_contract(): void
    {
        $html = $this->render(self::TAGS);

        $this->assertSame(1, substr_count($html, '<input'));

        foreach ([
            'type="text"',
            'autocomplete="off"',
            'role="combobox"',
            'aria-autocomplete="list"',
            'aria-label="Tags"',
            'x-ref="input"',
            'x-bind:aria-expanded="expanded ? \'true\' : \'false\'"',
            'x-bind:aria-controls="$id(\'mds-pillbox-listbox\')"',
            'x-bind:aria-activedescendant="activeId"',
            'x-on:focus="show()"',
            'x-on:blur="blur()"',
            'x-on:input="type($event)"',
            'x-on:keydown="keydown($event)"',
            'data-mds-pillbox-input',
        ] as $needle) {
            $this->assertMatchesRegularExpression('/<input\s[^>]*'.preg_quote($needle, '/').'/s', $html, "Missing on the input: {$needle}");
        }

        // The placeholder steps aside once there is a pill to read instead.
        $this->assertMatchesRegularExpression('/<input\s[^>]*x-bind:placeholder="pills\.length \? \'\' : \'Pick a stack…\'"/s', $html);
        // Nothing chosen yet: it is on the element from the first paint.
        $this->assertMatchesRegularExpression('/<input\s[^>]*\splaceholder="Pick a stack…"/s', $this->render('<mds:pillbox placeholder="Pick a stack…" />'));
        $this->assertDoesNotMatchRegularExpression('/<input\s[^>]*\splaceholder="Pick a stack…"/s', $html);
    }

    public function test_label_description_and_search_label_name_the_widget(): void
    {
        $html = $this->render('<mds:pillbox name="tags" label="Tags" description="Up to five." />');

        $this->assertMatchesRegularExpression('/<ui-label[^>]*data-flux-label>\s*Tags\s*<\/ui-label>/', $html);
        $this->assertMatchesRegularExpression('/<ui-description[^>]*data-flux-description>\s*Up to five\.\s*<\/ui-description>/', $html);
        // The label names both the field and the listbox.
        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-label="Tags"/s', $html);
        $this->assertMatchesRegularExpression('/role="listbox"[^>]*aria-label="Tags"/', $html);

        // search-label overrides the field's name without printing anything.
        $custom = $this->render('<mds:pillbox label="Tags" search-label="Filter tags" />');

        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-label="Filter tags"/s', $custom);
        $this->assertMatchesRegularExpression('/role="listbox"[^>]*aria-label="Tags"/', $custom);

        // Unlabelled, the field still has a name of its own.
        $bare = $this->render('<mds:pillbox />');

        $this->assertStringNotContainsString('<ui-label', $bare);
        $this->assertStringNotContainsString('<ui-description', $bare);
        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-label="جستجو"/s', $bare);
        $this->assertDoesNotMatchRegularExpression('/role="listbox"[^>]*aria-label=/', $bare);
    }

    public function test_options_carry_their_value_label_and_runtime_bindings(): void
    {
        $html = $this->render(self::TAGS);

        // The value and the flattened label ride along for the server pills
        // and for Alpine's own map.
        $this->assertMatchesRegularExpression('/role="option"\s+aria-selected="false"\s+data-value="go"\s+data-label="Go"/s', $html);
        $this->assertMatchesRegularExpression('/<span class="flex-1 truncate" data-mds-pillbox-label>Go<\/span>/', $html);

        $this->assertSame(4, substr_count($html, 'x-show="matches($el)"'));
        $this->assertSame(4, substr_count($html, 'x-bind:aria-selected="isSelected($el) ? \'true\' : \'false\'"'));
        $this->assertSame(4, substr_count($html, 'x-bind:aria-disabled="isDisabled($el) ? \'true\' : null"'));
        $this->assertSame(4, substr_count($html, 'x-on:click="toggle($el)"'));
        $this->assertSame(4, substr_count($html, 'x-on:mouseenter="point($el)"'));
        // Selection is never told by colour alone: a check rides with aria-selected.
        // Inside the check span, $el is the span — the row is its parent.
        $this->assertSame(4, substr_count($html, 'x-show="isSelected($el.parentElement)"'));
        $this->assertSame(4, substr_count($html, 'data-mds-pillbox-check'));
    }

    public function test_an_option_without_a_value_is_worth_its_own_label(): void
    {
        $html = $this->render('<mds:pillbox name="tags" value="Tehran">
            <mds:pillbox.option>Tehran</mds:pillbox.option>
        </mds:pillbox>');

        $this->assertMatchesRegularExpression('/data-value="Tehran"\s+data-label="Tehran"/', $html);
        // Markup inside the slot still renders; the label is its text.
        $rich = $this->render('<mds:pillbox><mds:pillbox.option value="php"><b>PHP</b> 8</mds:pillbox.option></mds:pillbox>');

        $this->assertMatchesRegularExpression('/data-value="php"\s+data-label="PHP 8"/', $rich);
        $this->assertStringContainsString('<b>PHP</b> 8', $rich);
    }

    public function test_a_disabled_option_is_marked_and_skipped(): void
    {
        $html = $this->render(self::TAGS);

        $this->assertMatchesRegularExpression('/data-label="Rust"\s+data-disabled aria-disabled="true"/s', $html);
        $this->assertSame(1, substr_count($html, 'data-disabled aria-disabled="true"'));
        $this->assertSame(1, substr_count($html, 'cursor-not-allowed opacity-50"'));
        // The cursor skips it at runtime too, and toggle() refuses it.
        $this->assertStringContainsString('return el.dataset.disabled !== undefined || (this.full && ! this.isSelected(el))', $html);
        $this->assertStringContainsString('return this.items().filter(el => ! this.isDisabled(el))', $html);
    }

    public function test_clearable_adds_a_labelled_clear_button(): void
    {
        $html = $this->render(self::TAGS);

        $this->assertMatchesRegularExpression('/<button\s[^>]*x-show="pills\.length > 0"[^>]*x-on:click\.stop="clear\(\)"[^>]*aria-label="پاک کردن"[^>]*data-mds-pillbox-clear/s', $html);

        $plain = $this->render('<mds:pillbox />');

        $this->assertStringNotContainsString('data-mds-pillbox-clear', $plain);

        // A disabled pillbox has nothing to clear and no pill to remove.
        $disabled = $this->render('<mds:pillbox clearable disabled :value="[\'php\']" />');

        $this->assertStringNotContainsString('data-mds-pillbox-clear', $disabled);
        $this->assertStringNotContainsString('data-mds-pillbox-remove', $disabled);
    }

    public function test_disabled_inerts_every_control_and_marks_the_root(): void
    {
        $html = $this->render('<mds:pillbox name="tags" disabled :value="[\'php\']" />');

        $this->assertMatchesRegularExpression('/<div\s[^>]*\sdata-disabled\s[^>]*data-mds-pillbox\s*>/s', $html);
        $this->assertMatchesRegularExpression('/<select\s[^>]*\sdisabled\s/s', $html);
        $this->assertMatchesRegularExpression('/<input\s[^>]*\sdisabled\s/s', $html);
        $this->assertStringContainsString('disabled: true,', $html);
        $this->assertStringContainsString('cursor-not-allowed opacity-50', $html);
        // The pill is still shown — it is the value, it is just not editable.
        $this->assertStringContainsString('data-mds-pillbox-pill', $html);

        $enabled = $this->render('<mds:pillbox name="tags" />');

        $this->assertStringNotContainsString('data-disabled', $enabled);
        $this->assertStringContainsString('disabled: false,', $enabled);
    }

    public function test_max_and_empty_reach_the_alpine_config(): void
    {
        $html = $this->render('<mds:pillbox max="3" />');

        $this->assertMatchesRegularExpression('/x-data="mdsPillbox\(\{\s*selected: \[\],\s*max: 3,\s*hasEmpty: true,\s*disabled: false,/s', $html);
        // The cap only ever locks options that are not already chosen.
        $this->assertStringContainsString('return this.max !== null && this.selected.length >= this.max', $html);

        $uncapped = $this->render('<mds:pillbox />');

        $this->assertStringContainsString('max: null,', $uncapped);
        $this->assertStringContainsString('max: 0,', $this->render('<mds:pillbox max="-2" />'));
    }

    public function test_empty_text_is_built_in_replaceable_and_switchable_off(): void
    {
        $builtIn = $this->render('<mds:pillbox />');

        $this->assertMatchesRegularExpression('/<div\s[^>]*role="status"[^>]*x-show="empty"[^>]*data-mds-pillbox-empty>\s*موردی یافت نشد\.\s*<\/div>/', $builtIn);
        $this->assertStringContainsString('hasEmpty: true,', $builtIn);
        // The status line sits outside the listbox.
        $this->assertMatchesRegularExpression('/data-mds-pillbox-list.*<\/div>\s*<div\s[^>]*data-mds-pillbox-empty/s', $builtIn);

        $custom = $this->render('<mds:pillbox empty="برچسبی با این نام نداریم." />');

        $this->assertMatchesRegularExpression('/data-mds-pillbox-empty>\s*برچسبی با این نام نداریم\.\s*</', $custom);
        $this->assertStringNotContainsString('موردی یافت نشد.', $custom);

        $off = $this->render('<mds:pillbox :empty="false" />');

        $this->assertStringNotContainsString('data-mds-pillbox-empty', $off);
        $this->assertStringContainsString('hasEmpty: false,', $off);
    }

    public function test_the_selection_count_is_announced_politely(): void
    {
        $html = $this->render(self::TAGS);

        $this->assertMatchesRegularExpression(
            '/<span class="sr-only" role="status" aria-live="polite" x-text="status" data-mds-pillbox-status>۲ مورد انتخاب شده<\/span>/',
            $html,
        );
        $this->assertStringContainsString("statusSuffix: ' مورد انتخاب شده',", $html);
        $this->assertStringContainsString('return window.mds.digits(this.selected.length, this.fa) + this.statusSuffix', $html);

        $none = $this->render('<mds:pillbox />');

        $this->assertStringContainsString('data-mds-pillbox-status>۰ مورد انتخاب شده</span>', $none);
    }

    public function test_explicit_error_renders_the_message_and_marks_the_field_invalid(): void
    {
        $html = $this->render('<mds:pillbox name="tags" error="یک برچسب انتخاب کنید." />');

        $this->assertMatchesRegularExpression('/<div\s[^>]*role="alert"[^>]*aria-live="polite"[^>]*data-flux-error>.*یک برچسب انتخاب کنید\./s', $html);
        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-invalid="true"/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*\sdata-invalid\s[^>]*data-mds-pillbox\s*>/s', $html);
        $this->assertStringContainsString('border-red-500 dark:border-red-400', $html);

        // The message is wired to the field, and its id is derived from the name.
        preg_match('/aria-describedby="([^"]+)"/', $html, $m);
        $this->assertMatchesRegularExpression('/^mds-pillbox-error-[0-9a-f]{8}$/', $m[1]);
        $this->assertStringContainsString('<div id="'.$m[1].'" role="alert"', $html);
        $this->assertSame($html, $this->render('<mds:pillbox name="tags" error="یک برچسب انتخاب کنید." />'));

        $clean = $this->render('<mds:pillbox name="tags" />');

        $this->assertStringNotContainsString('data-flux-error', $clean);
        $this->assertStringNotContainsString('aria-invalid', $clean);
        $this->assertStringNotContainsString('aria-describedby', $clean);
        $this->assertStringNotContainsString('data-invalid', $clean);
    }

    public function test_invalid_prop_marks_the_field_without_a_message(): void
    {
        $html = $this->render('<mds:pillbox invalid />');

        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-invalid="true"/s', $html);
        $this->assertStringContainsString('data-invalid', $html);
        $this->assertStringNotContainsString('data-flux-error', $html);
        $this->assertStringNotContainsString('aria-describedby', $html);
    }

    public function test_falls_back_to_the_validation_error_bag_by_name_or_binding(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['tags' => ['حداقل یک برچسب لازم است.']]));

        View::share('errors', $bag);

        $byName = $this->render('<mds:pillbox name="tags" />');

        $this->assertMatchesRegularExpression('/data-flux-error>.*حداقل یک برچسب لازم است\./s', $byName);
        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-invalid="true"/s', $byName);

        // No name: the binding names the field, like flux:input.
        $byBinding = $this->render('<mds:pillbox wire:model="tags" />');

        $this->assertMatchesRegularExpression('/data-flux-error>.*حداقل یک برچسب لازم است\./s', $byBinding);

        // An explicit message wins over the bag.
        $explicit = $this->render('<mds:pillbox name="tags" error="پیام صریح" />');

        $this->assertStringContainsString('پیام صریح', $explicit);
        $this->assertStringNotContainsString('حداقل یک برچسب لازم است.', $explicit);

        // Another field's error is not ours.
        $other = $this->render('<mds:pillbox name="skills" />');

        $this->assertStringNotContainsString('data-flux-error', $other);
    }

    public function test_fa_false_switches_the_built_in_strings_to_english(): void
    {
        $html = $this->render('<mds:pillbox :fa="false" clearable :value="[\'php\']">
            <mds:pillbox.option value="php">PHP</mds:pillbox.option>
        </mds:pillbox>');

        $this->assertStringContainsString('aria-label="Clear"', $html);
        $this->assertStringContainsString('aria-label="Remove PHP"', $html);
        $this->assertStringContainsString('aria-label="Search"', $html);
        $this->assertStringContainsString('No matches.', $html);
        $this->assertStringContainsString('data-mds-pillbox-status>1 selected</span>', $html);
        $this->assertStringContainsString("statusSuffix: ' selected',", $html);
        $this->assertStringContainsString('fa: false,', $html);

        foreach (['پاک کردن', 'حذف', 'جستجو', 'موردی یافت نشد.', 'مورد انتخاب شده'] as $persian) {
            $this->assertStringNotContainsString($persian, $html);
        }
    }

    public function test_the_alpine_script_ships_once_per_page_with_the_started_alpine_guard(): void
    {
        $html = $this->render('<mds:pillbox /><mds:pillbox />');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsPillbox'"));
        $this->assertSame(1, substr_count($html, 'window.mds.registerPillbox = '));
        $this->assertStringContainsString('if (window.mds.pillboxRegistered) return', $html);
        $this->assertStringContainsString('if (window.Alpine) {', $html);
        $this->assertStringContainsString("document.addEventListener('alpine:init', () => window.mds.registerPillbox(window.Alpine))", $html);
        // Two components, one behaviour block plus the shared digits partial.
        $this->assertSame(2, substr_count($html, '<script'));
        $this->assertSame(2, preg_match_all('/data-mds-pillbox\s*>/', $html));
    }

    public function test_keyboard_selection_and_morph_behaviour_is_in_the_script(): void
    {
        $html = $this->render('<mds:pillbox />');

        // WAI-ARIA combobox keys, handled in one place so Enter/Escape/Home/End
        // only intercept while the list is open.
        foreach (["case 'ArrowDown':", "case 'ArrowUp':", "case 'Enter':", "case 'Escape':", "case 'Home':", "case 'End':", "case 'Backspace':", "case 'Tab':"] as $key) {
            $this->assertStringContainsString($key, $html);
        }

        $this->assertStringContainsString('if (! this.expanded) return', $html);
        $this->assertStringContainsString('(this.active + delta + found.length) % found.length', $html);
        // Backspace on an empty query takes the last pill off.
        $this->assertStringContainsString("if (event.target.value !== '' || ! this.selected.length) return", $html);
        $this->assertStringContainsString('this.remove(this.selected[this.selected.length - 1])', $html);
        // Toggling keeps the popup open — picking a second value is the point.
        $this->assertStringContainsString('this.selected = this.isSelected(el)', $html);

        // Persian normalisation before matching, on both the query and the options.
        $this->assertStringContainsString("replace(/[يى]/g, 'ی').replace(/ك/g, 'ک')", $html);
        $this->assertStringContainsString('window.mds.latinDigits(', $html);
        $this->assertStringContainsString('el.dataset.mdsHaystack = this.normalize(this.label(el))', $html);

        // The option cache follows Livewire morphs...
        $this->assertSame(2, substr_count($html, 'new MutationObserver('));
        $this->assertStringContainsString("attributeFilter: ['selected', 'value'],", $html);
        $this->assertStringContainsString('this.observer?.disconnect()', $html);
        $this->assertStringContainsString('this.selectObserver?.disconnect()', $html);
        // ...and so does the selection, when the server rewrites the select.
        $this->assertStringContainsString('.filter(option => option.selected).map(option => option.value)', $html);
        $this->assertStringContainsString('this.selectObserver?.takeRecords()', $html);

        // Committing rebuilds the select and tells both listeners.
        $this->assertStringContainsString("el.dispatchEvent(new Event('input', { bubbles: true }))", $html);
        $this->assertStringContainsString("el.dispatchEvent(new Event('change', { bubbles: true }))", $html);
        $this->assertStringNotContainsString('innerHTML', $html);

        // Clicks inside the popup keep focus in the text field.
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-on:mousedown\.prevent[^>]*data-mds-pillbox-popup/s', $html);
    }

    public function test_wrapper_attributes_land_on_the_root_not_the_controls(): void
    {
        $html = $this->render('<mds:pillbox class="max-w-sm" data-testid="tag-box" />');

        $this->assertMatchesRegularExpression('/<div\s[^>]*class="[^"]*max-w-sm[^"]*"[^>]*data-testid="tag-box"[^>]*data-mds-pillbox\s*>/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<input\s[^>]*max-w-sm/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<select\s[^>]*data-testid/s', $html);
    }

    public function test_renders_with_no_options(): void
    {
        $html = $this->render('<mds:pillbox label="Tags" />');

        $this->assertStringNotContainsString('data-mds-pillbox-option', $this->markup($html));
        $this->assertStringNotContainsString('role="option"', $html);
        $this->assertMatchesRegularExpression('/data-mds-pillbox-list\s*>\s*<\/div>/s', $html);
        // No chosen value, so the first-paint pill list is empty — only the
        // x-for template the runtime draws into is left.
        $this->assertMatchesRegularExpression('/data-mds-pillbox-initial>\s*<\/span>/s', $html);
        $this->assertSame(1, substr_count($html, 'data-mds-pillbox-pill>'));
    }

    public function test_a_value_with_no_matching_option_still_gets_a_pill(): void
    {
        $html = $this->render('<mds:pillbox name="tags" value="ghost">
            <mds:pillbox.option value="php">PHP</mds:pillbox.option>
        </mds:pillbox>');

        // No label to be had: the value speaks for itself, in the pill and in
        // the hidden option alike.
        $this->assertMatchesRegularExpression('/<option value="ghost" selected>ghost<\/option>/', $html);
        $this->assertMatchesRegularExpression('/data-mds-pillbox-pill-label>ghost<\/span>/', $html);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function microcopy(): array
    {
        return [
            'pillbox search' => ['<mds:pillbox />', 'aria-label="جستجو"', 'aria-label="Search"'],
            'pillbox clear' => ['<mds:pillbox clearable />', 'aria-label="پاک کردن"', 'aria-label="Clear"'],
            'pillbox remove' => ['<mds:pillbox value="php"><mds:pillbox.option value="php">PHP</mds:pillbox.option></mds:pillbox>', 'aria-label="حذف PHP"', 'aria-label="Remove PHP"'],
            'pillbox empty' => ['<mds:pillbox />', '-empty>موردی یافت نشد.<', '-empty>No matches.<'],
            'pillbox status' => ['<mds:pillbox />', 'data-mds-pillbox-status>۰ مورد انتخاب شده<', 'data-mds-pillbox-status>0 selected<'],
        ];
    }

    #[DataProvider('microcopy')]
    public function test_microcopy_is_persian_by_default(string $template, string $persian, string $english): void
    {
        $html = $this->collapse($this->render($template));

        $this->assertStringContainsString($persian, $html);
        $this->assertStringNotContainsString($english, $html);
    }

    #[DataProvider('microcopy')]
    public function test_microcopy_switches_to_english(string $template, string $persian, string $english): void
    {
        config(['mds.persian_digits' => false]);

        try {
            $html = $this->collapse($this->render($template));

            $this->assertStringContainsString($english, $html);
            $this->assertStringNotContainsString($persian, $html);
        } finally {
            config(['mds.persian_digits' => true]);
        }
    }

    // The behaviour script names the markers, so a "not in the markup" check
    // has to look past the scripts.
    private function markup(string $html): string
    {
        return (string) preg_replace('/<script\b.*?<\/script>/s', '', $html);
    }

    // Blade leaves the slot text on its own line; fold the whitespace so the
    // needles can anchor on the surrounding tags.
    private function collapse(string $html): string
    {
        return (string) preg_replace_callback('/>\s+|\s+</', fn ($m) => trim($m[0]), $html);
    }
}
