<?php

namespace MajidDs\Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\MessageBag;
use Illuminate\Support\ViewErrorBag;
use MajidDs\Tests\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

class AutocompleteTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The control is Flux's own input, which reads the shared error bag
        // the way ShareErrorsFromSession provides it in a real request.
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

    private const CITIES = '<mds:autocomplete wire:model.live="city" label="City" placeholder="Start typing…" clearable>
        <mds:autocomplete.item>Tehran</mds:autocomplete.item>
        <mds:autocomplete.item>Isfahan</mds:autocomplete.item>
        <mds:autocomplete.item>Shiraz</mds:autocomplete.item>
        <mds:autocomplete.item value="Mashhad">Mashhad (Khorasan)</mds:autocomplete.item>
    </mds:autocomplete>';

    public function test_renders_root_popup_listbox_and_options(): void
    {
        $html = $this->render(self::CITIES);

        $this->assertMatchesRegularExpression('/<div\s[^>]*x-data="mdsAutocomplete\(\{[^>]*data-mds-autocomplete\s*>/s', $html);
        $this->assertStringContainsString('data-mds-autocomplete-popup', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*role="listbox"[^>]*data-mds-autocomplete-list/', $html);
        $this->assertStringContainsString('class="max-h-60 overflow-auto"', $html);
        $this->assertSame(4, substr_count($html, 'role="option"'));
        $this->assertSame(4, preg_match_all('/data-mds-autocomplete-item\s*>/', $html));
        // Text sits in the label span the highlighter paints into...
        $this->assertMatchesRegularExpression('/<span[^>]*data-mds-autocomplete-label>\s*Tehran\s*<\/span>/', $html);
        // The popup hangs under the control in the tree — no teleport.
        $this->assertStringNotContainsString('x-teleport', $html);
        $this->assertMatchesRegularExpression('/class="absolute start-0 end-0 top-full[^"]*"[^>]*x-show="expanded"/', $html);
    }

    public function test_the_control_is_flux_input_carrying_the_combobox_contract(): void
    {
        $html = $this->render(self::CITIES);

        // One text input, Flux's own (data-flux-control), with the ARIA
        // combobox attributes on that element and nowhere else.
        $this->assertSame(1, substr_count($html, '<input'));
        $this->assertMatchesRegularExpression('/<input\s[^>]*type="text"[^>]*data-flux-control/s', $html);

        foreach ([
            'role="combobox"',
            'aria-autocomplete="list"',
            'autocomplete="off"',
            'x-ref="input"',
            'x-bind:aria-expanded="expanded ? \'true\' : \'false\'"',
            'x-bind:aria-controls="$id(\'mds-autocomplete-listbox\')"',
            'x-bind:aria-activedescendant="activeId"',
            'x-on:focus="show()"',
            'x-on:blur="blur()"',
            'x-on:input="type($event)"',
            'x-on:keydown="keydown($event)"',
            'data-mds-autocomplete-input',
        ] as $needle) {
            $this->assertMatchesRegularExpression('/<input\s[^>]*'.preg_quote($needle, '/').'/s', $html, "Missing on the input: {$needle}");
        }

        // The listbox is the element aria-controls points at.
        $this->assertStringContainsString('x-bind:id="$id(\'mds-autocomplete-listbox\')"', $html);
        $this->assertStringContainsString('x-id="[\'mds-autocomplete-listbox\', \'mds-autocomplete-option\']"', $html);

        // Free text: the digit-normalising directive must not be on it.
        $this->assertStringNotContainsString('x-mds-digits', $this->markup($html));
    }

    public function test_wire_model_reaches_the_text_input_exactly_once(): void
    {
        $html = $this->render(self::CITIES);

        $this->assertBindingReachesControl($html, 'input[^>]*type="text"', 'wire:model.live="city"');

        // The field is named after its binding, as flux:input does.
        $this->assertMatchesRegularExpression('/<input\s[^>]*name="city"/s', $html);
    }

    public function test_label_description_placeholder_value_and_name(): void
    {
        $html = $this->render('<mds:autocomplete name="town" label="Town" description="Where the parcel goes." placeholder="Type a town" value="Karaj" />');

        $this->assertMatchesRegularExpression('/<ui-label[^>]*data-flux-label>\s*Town\s*<\/ui-label>/', $html);
        $this->assertMatchesRegularExpression('/<ui-description[^>]*data-flux-description>\s*Where the parcel goes\.\s*<\/ui-description>/', $html);
        $this->assertMatchesRegularExpression('/<input\s[^>]*placeholder="Type a town"/s', $html);
        $this->assertMatchesRegularExpression('/<input\s[^>]*value="Karaj"/s', $html);
        $this->assertMatchesRegularExpression('/<input\s[^>]*name="town"/s', $html);
        // The label also names the listbox.
        $this->assertMatchesRegularExpression('/role="listbox"[^>]*aria-label="Town"/', $html);

        $bare = $this->render('<mds:autocomplete />');

        $this->assertStringNotContainsString('<ui-label', $bare);
        $this->assertStringNotContainsString('<ui-description', $bare);
        $this->assertStringNotContainsString('placeholder=', $bare);
        $this->assertDoesNotMatchRegularExpression('/role="listbox"[^>]*aria-label=/', $bare);
    }

    public function test_items_carry_their_value_only_when_given(): void
    {
        $html = $this->render(self::CITIES);

        $this->assertMatchesRegularExpression('/<div\s[^>]*role="option"[^>]*data-value="Mashhad"[^>]*data-mds-autocomplete-item\s*>\s*<span[^>]*>\s*Mashhad \(Khorasan\)/s', $html);
        // Exactly one item sets a value; the others fall back to their text at runtime.
        $this->assertSame(1, substr_count($html, 'data-value='));

        // Runtime pieces of the option: filtered by x-show, tracked by the cursor.
        $this->assertSame(4, substr_count($html, 'x-show="matches($el)"'));
        $this->assertSame(4, substr_count($html, 'x-bind:aria-selected="isActive($el) ? \'true\' : \'false\'"'));
        $this->assertSame(4, substr_count($html, 'x-on:click="pick($el)"'));
        $this->assertSame(4, substr_count($html, 'x-on:mouseenter="point($el)"'));
    }

    public function test_disabled_item_is_marked_and_not_clickable(): void
    {
        $html = $this->render('<mds:autocomplete>
            <mds:autocomplete.item disabled>Qom</mds:autocomplete.item>
            <mds:autocomplete.item>Rasht</mds:autocomplete.item>
        </mds:autocomplete>');

        $this->assertMatchesRegularExpression('/<div\s[^>]*aria-disabled="true"\s+data-disabled[^>]*data-mds-autocomplete-item\s*>\s*<span[^>]*>\s*Qom/s', $html);
        $this->assertStringContainsString('cursor-not-allowed opacity-50', $html);
        // One clickable option, one inert one.
        $this->assertSame(1, substr_count($html, 'x-on:click="pick($el)"'));
        $this->assertSame(1, substr_count($html, 'aria-disabled="true"'));
        $this->assertSame(2, substr_count($html, 'role="option"'));
    }

    public function test_clearable_adds_a_labelled_clear_button_in_the_trailing_slot(): void
    {
        $html = $this->render(self::CITIES);

        $this->assertMatchesRegularExpression('/<button\s[^>]*type="button"[^>]*x-on:mousedown\.prevent[^>]*x-on:click="clear\(\)"[^>]*aria-label="پاک کردن"[^>]*data-mds-autocomplete-clear/s', $html);
        // Only shown once something is typed...
        $this->assertMatchesRegularExpression('/<button\s[^>]*x-show="query !== \'\'"[^>]*data-mds-autocomplete-clear/s', $html);
        // ...and Flux made room for it on the trailing edge.
        $this->assertMatchesRegularExpression('/<input\s[^>]*class="[^"]*\spe-10[\s"]/s', $html);

        $plain = $this->render('<mds:autocomplete />');

        $this->assertStringNotContainsString('data-mds-autocomplete-clear', $plain);
        $this->assertMatchesRegularExpression('/<input\s[^>]*class="[^"]*\spe-3[\s"]/s', $plain);

        // A disabled control has nothing to clear.
        $disabled = $this->render('<mds:autocomplete clearable disabled />');

        $this->assertStringNotContainsString('data-mds-autocomplete-clear', $disabled);
    }

    public function test_empty_text_is_off_by_default_built_in_when_asked_for_and_replaceable(): void
    {
        $off = $this->render('<mds:autocomplete><mds:autocomplete.item>x</mds:autocomplete.item></mds:autocomplete>');

        $this->assertStringNotContainsString('data-mds-autocomplete-empty', $off);
        $this->assertStringNotContainsString('role="status"', $off);
        $this->assertStringContainsString('hasEmpty: false', $off);

        $builtIn = $this->render('<mds:autocomplete empty />');

        $this->assertMatchesRegularExpression('/<div\s[^>]*role="status"[^>]*x-show="empty"[^>]*data-mds-autocomplete-empty>\s*موردی یافت نشد\.\s*<\/div>/', $builtIn);
        $this->assertStringContainsString('hasEmpty: true', $builtIn);
        // The status line sits outside the listbox.
        $this->assertMatchesRegularExpression('/role="listbox".*<\/div>\s*<div\s[^>]*role="status"/s', $builtIn);

        $custom = $this->render('<mds:autocomplete empty="شهری با این نام نداریم." />');

        $this->assertMatchesRegularExpression('/data-mds-autocomplete-empty>\s*شهری با این نام نداریم\.\s*</', $custom);
        $this->assertStringNotContainsString('موردی یافت نشد.', $custom);
    }

    public function test_min_chars_strict_and_disabled_reach_the_alpine_config(): void
    {
        $html = $this->render('<mds:autocomplete min-chars="2" strict disabled />');

        $this->assertMatchesRegularExpression('/x-data="mdsAutocomplete\(\{\s*minChars: 2,\s*strict: true,\s*hasEmpty: false,\s*disabled: true,?\s*\}\)"/', $html);
        $this->assertMatchesRegularExpression('/<input\s[^>]*\sdisabled="disabled"/s', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*data-disabled\s[^>]*data-mds-autocomplete\s*>/s', $html);

        $defaults = $this->render('<mds:autocomplete />');

        $this->assertMatchesRegularExpression('/x-data="mdsAutocomplete\(\{\s*minChars: 0,\s*strict: false,\s*hasEmpty: false,\s*disabled: false,?\s*\}\)"/', $defaults);
        $this->assertStringNotContainsString('disabled="disabled"', $defaults);
        $this->assertStringNotContainsString('data-disabled', $defaults);

        // Negative counts clamp to zero.
        $this->assertStringContainsString('minChars: 0,', $this->render('<mds:autocomplete min-chars="-3" />'));
    }

    public function test_leading_icon_and_small_size_pass_through_to_flux(): void
    {
        $withIcon = $this->render('<mds:autocomplete icon="map-pin" />');

        $this->assertStringContainsString('data-flux-icon', $withIcon);
        $this->assertMatchesRegularExpression('/<input\s[^>]*class="[^"]*\sps-10[\s"]/s', $withIcon);

        $plain = $this->render('<mds:autocomplete />');

        $this->assertStringNotContainsString('data-flux-icon', $plain);
        $this->assertMatchesRegularExpression('/<input\s[^>]*class="[^"]*\sps-3[\s"]/s', $plain);

        $this->assertMatchesRegularExpression('/<input\s[^>]*class="[^"]*\sh-8[\s"]/s', $this->render('<mds:autocomplete size="sm" />'));
        $this->assertMatchesRegularExpression('/<input\s[^>]*class="[^"]*\sh-10[\s"]/s', $plain);
    }

    public function test_explicit_error_renders_the_message_and_marks_the_input_invalid(): void
    {
        $html = $this->render('<mds:autocomplete name="city" error="شهر معتبر نیست." />');

        $this->assertMatchesRegularExpression('/<div\s[^>]*role="alert"[^>]*aria-live="polite"[^>]*data-flux-error>.*شهر معتبر نیست\./s', $html);
        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-invalid="true"\s+data-invalid/s', $html);

        $clean = $this->render('<mds:autocomplete name="city" />');

        $this->assertStringNotContainsString('data-flux-error', $clean);
        $this->assertStringNotContainsString('aria-invalid', $clean);
    }

    public function test_invalid_prop_marks_the_input_without_a_message(): void
    {
        $html = $this->render('<mds:autocomplete invalid />');

        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-invalid="true"\s+data-invalid/s', $html);
        $this->assertStringNotContainsString('data-flux-error', $html);
    }

    public function test_falls_back_to_the_validation_error_bag_by_name_or_binding(): void
    {
        $bag = new ViewErrorBag;
        $bag->put('default', new MessageBag(['city' => ['شهر را از فهرست انتخاب کنید.']]));

        View::share('errors', $bag);

        $byName = $this->render('<mds:autocomplete name="city" />');

        $this->assertMatchesRegularExpression('/data-flux-error>.*شهر را از فهرست انتخاب کنید\./s', $byName);
        $this->assertMatchesRegularExpression('/<input\s[^>]*aria-invalid="true"/s', $byName);

        // No name: the binding names the field, like flux:input.
        $byBinding = $this->render('<mds:autocomplete wire:model="city" />');

        $this->assertMatchesRegularExpression('/data-flux-error>.*شهر را از فهرست انتخاب کنید\./s', $byBinding);

        // An explicit message wins over the bag.
        $explicit = $this->render('<mds:autocomplete name="city" error="پیام صریح" />');

        $this->assertStringContainsString('پیام صریح', $explicit);
        $this->assertStringNotContainsString('شهر را از فهرست انتخاب کنید.', $explicit);

        // Another field's error is not ours.
        $other = $this->render('<mds:autocomplete name="street" />');

        $this->assertStringNotContainsString('data-flux-error', $other);
    }

    public function test_fa_false_switches_the_built_in_strings_to_english(): void
    {
        $html = $this->render('<mds:autocomplete :fa="false" clearable empty />');

        $this->assertStringContainsString('aria-label="Clear"', $html);
        $this->assertStringContainsString('No matches.', $html);
        $this->assertStringNotContainsString('پاک کردن', $html);
        $this->assertStringNotContainsString('موردی یافت نشد.', $html);
    }

    public function test_the_alpine_script_ships_once_per_page_with_the_started_alpine_guard(): void
    {
        $html = $this->render('<mds:autocomplete /><mds:autocomplete />');

        $this->assertSame(1, substr_count($html, "Alpine.data('mdsAutocomplete'"));
        $this->assertSame(1, substr_count($html, 'window.mds.registerAutocomplete = '));
        $this->assertStringContainsString('if (window.Alpine) {', $html);
        $this->assertStringContainsString("document.addEventListener('alpine:init', () => window.mds.registerAutocomplete(window.Alpine))", $html);
        // Two components, one behaviour block plus the shared digits partial.
        $this->assertSame(2, substr_count($html, '<script'));
        $this->assertSame(2, preg_match_all('/data-mds-autocomplete\s*>/', $html));
    }

    public function test_keyboard_filtering_and_highlighting_behaviour_is_in_the_script(): void
    {
        $html = $this->render('<mds:autocomplete />');

        // WAI-ARIA combobox keys, handled in one place so Enter/Escape/Home/End
        // only intercept while the list is open.
        foreach (["case 'ArrowDown':", "case 'ArrowUp':", "case 'Enter':", "case 'Escape':", "case 'Home':", "case 'End':"] as $key) {
            $this->assertStringContainsString($key, $html);
        }

        $this->assertStringContainsString('if (! this.expanded) return', $html);
        $this->assertStringContainsString('(this.active + delta + found.length) % found.length', $html);

        // Persian normalisation before matching, on both the query and the items.
        $this->assertStringContainsString("replace(/[يى]/g, 'ی').replace(/ك/g, 'ک')", $html);
        $this->assertStringContainsString('window.mds.latinDigits(', $html);
        $this->assertStringContainsString('el.dataset.mdsHaystack = this.normalize(this.label(el))', $html);

        // Highlight is built from text nodes; nothing typed ever meets innerHTML.
        $this->assertStringContainsString("document.createElement('mark')", $html);
        $this->assertStringContainsString('mark.textContent = label.slice(at, at + needle.length)', $html);
        $this->assertStringNotContainsString('innerHTML', $html);

        // The option cache follows Livewire morphs, and drops its own paint.
        $this->assertStringContainsString('new MutationObserver(', $html);
        $this->assertStringContainsString('this.observer?.takeRecords()', $html);
        $this->assertStringContainsString('this.observer?.disconnect()', $html);

        // Picking writes the input and tells Livewire.
        $this->assertStringContainsString("input.dispatchEvent(new Event('input', { bubbles: true }))", $html);

        // Clicks inside the popup keep focus in the input.
        $this->assertMatchesRegularExpression('/<div\s[^>]*x-on:mousedown\.prevent[^>]*data-mds-autocomplete-popup/s', $html);
    }

    public function test_wrapper_attributes_land_on_the_root_not_the_input(): void
    {
        $html = $this->render('<mds:autocomplete class="max-w-sm" data-testid="city-box" />');

        $this->assertMatchesRegularExpression('/<div\s[^>]*class="[^"]*max-w-sm[^"]*"[^>]*data-testid="city-box"[^>]*data-mds-autocomplete\s*>/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<input\s[^>]*max-w-sm/s', $html);
        $this->assertDoesNotMatchRegularExpression('/<input\s[^>]*data-testid/s', $html);
    }

    public function test_renders_with_no_items(): void
    {
        $html = $this->render('<mds:autocomplete label="Tag" />');

        $this->assertStringNotContainsString('data-mds-autocomplete-item', $this->markup($html));
        $this->assertStringNotContainsString('role="option"', $html);
        $this->assertMatchesRegularExpression('/<div\s[^>]*role="listbox"[^>]*>\s*<\/div>/s', $html);
    }

    /**
     * @return array<string, array{0: string, 1: string, 2: string}>
     */
    public static function microcopy(): array
    {
        return [
            'autocomplete clear' => ['<mds:autocomplete clearable />', 'aria-label="پاک کردن"', 'aria-label="Clear"'],
            'autocomplete empty' => ['<mds:autocomplete empty />', '>موردی یافت نشد.<', '>No matches.<'],
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

    // The behaviour script names the markers and the digits partial names its
    // directive, so a "not in the markup" check has to look past the scripts.
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
