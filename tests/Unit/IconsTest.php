<?php

namespace MajidDs\Tests\Unit;

use MajidDs\Support\Icons;
use MajidDs\Tests\TestCase;

class IconsTest extends TestCase
{
    /**
     * The alias map is only useful if it points at icons that exist. This
     * guards it against Hugeicons renaming or dropping an icon on upgrade.
     */
    public function test_every_alias_target_exists_in_the_bundled_free_set(): void
    {
        $dir = dirname(__DIR__, 2).'/vendor/afatmustafa/blade-hugeicons/resources/svg';

        $this->assertDirectoryExists($dir, 'The free Hugeicons set is missing.');

        $missing = [];

        foreach (Icons::ALIASES as $heroicon => $hugeicon) {
            if (! file_exists($dir.'/'.$hugeicon.'.svg')) {
                $missing[] = "{$heroicon} => {$hugeicon}";
            }
        }

        $this->assertSame([], $missing, 'Alias targets not found in the free set.');
    }

    public function test_every_override_target_exists_in_the_bundled_free_set(): void
    {
        $dir = dirname(__DIR__, 2).'/vendor/afatmustafa/blade-hugeicons/resources/svg';

        $missing = [];

        foreach (Icons::OVERRIDES as $heroicon => $hugeicon) {
            if (! file_exists($dir.'/'.$hugeicon.'.svg')) {
                $missing[] = "{$heroicon} => {$hugeicon}";
            }
        }

        $this->assertSame([], $missing, 'Override targets not found in the free set.');
    }

    /**
     * The literal name is tried before ALIASES, so an alias whose key is also
     * a real Hugeicons name can never fire. Such an entry belongs in
     * OVERRIDES (which is consulted first) or nowhere at all.
     */
    public function test_no_alias_is_shadowed_by_a_real_hugeicons_name(): void
    {
        $dir = dirname(__DIR__, 2).'/vendor/afatmustafa/blade-hugeicons/resources/svg';

        $shadowed = [];

        foreach (array_keys(Icons::ALIASES) as $heroicon) {
            if (file_exists($dir.'/'.$heroicon.'.svg')) {
                $shadowed[] = $heroicon;
            }
        }

        $this->assertSame([], $shadowed, 'These aliases are dead — the literal lookup wins first.');
    }

    /**
     * The mirror invariant: an override only earns its keep when Hugeicons has
     * a same-named icon to beat. Otherwise it is just an alias.
     */
    public function test_every_override_actually_overrides_something(): void
    {
        $dir = dirname(__DIR__, 2).'/vendor/afatmustafa/blade-hugeicons/resources/svg';

        $pointless = [];

        foreach (array_keys(Icons::OVERRIDES) as $heroicon) {
            if (! file_exists($dir.'/'.$heroicon.'.svg')) {
                $pointless[] = $heroicon;
            }
        }

        $this->assertSame([], $pointless, 'These overrides have nothing to override — move them to ALIASES.');
    }

    public function test_no_alias_or_override_maps_a_name_to_itself(): void
    {
        foreach ([Icons::ALIASES, Icons::OVERRIDES] as $map) {
            foreach ($map as $heroicon => $hugeicon) {
                $this->assertNotSame($hugeicon, $heroicon, "{$heroicon} maps to itself.");
            }
        }
    }

    public function test_it_maps_flux_variants_onto_hugeicons_styles(): void
    {
        $this->assertSame('stroke-rounded', Icons::style('outline'));
        $this->assertSame('stroke-rounded', Icons::style('micro'));
        $this->assertSame('stroke-rounded', Icons::style('mini'));
        $this->assertSame('solid-rounded', Icons::style('solid'));
    }

    public function test_it_passes_hugeicons_styles_through(): void
    {
        $this->assertSame('solid-sharp', Icons::style('solid-sharp'));
        $this->assertSame('duotone-rounded', Icons::style('duotone-rounded'));
    }

    public function test_it_falls_back_to_the_configured_style(): void
    {
        $this->assertSame('stroke-rounded', Icons::style(null));

        config(['mds.icons.style' => 'bulk-rounded']);

        $this->assertSame('bulk-rounded', Icons::style(null));
        $this->assertSame('bulk-rounded', Icons::style('nonsense'));
    }

    public function test_prefixes_prefer_a_registered_pro_set(): void
    {
        config(['mds.icons.sets' => ['solid-rounded' => '/tmp/whatever']]);

        $this->assertSame(
            [Icons::PRO_PREFIX.'-solid-rounded', Icons::FREE_SET],
            Icons::prefixes('solid-rounded'),
        );
    }

    public function test_prefixes_can_refuse_to_fall_back_to_the_free_style(): void
    {
        config(['mds.icons.fallback_style' => false]);

        $this->assertSame([], Icons::prefixes('solid-sharp'));
        $this->assertSame([Icons::FREE_SET], Icons::prefixes('stroke-rounded'));
    }
}
