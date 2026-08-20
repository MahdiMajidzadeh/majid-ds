<?php

namespace MajidDs\Support;

use BladeUI\Icons\Exceptions\SvgNotFound;
use BladeUI\Icons\Factory;
use BladeUI\Icons\Svg;

class Icons
{
    /**
     * The blade-icons set that ships the free Stroke Rounded icons
     * (registered by afatmustafa/blade-hugeicons).
     */
    public const FREE_SET = 'hugeicons';

    /**
     * Prefix for the Pro style sets registered from config('mds.icons.sets').
     */
    public const PRO_PREFIX = 'mds-hugeicons';

    /**
     * The nine Hugeicons styles. Only Stroke Rounded is free; the rest need a
     * Pro export registered under config('mds.icons.sets').
     */
    public const STYLES = [
        'stroke-rounded',
        'solid-rounded',
        'twotone-rounded',
        'duotone-rounded',
        'bulk-rounded',
        'stroke-standard',
        'solid-standard',
        'stroke-sharp',
        'solid-sharp',
    ];

    /**
     * Flux's icon variants mapped onto Hugeicons styles. Flux's mini/micro are
     * size variants, which Hugeicons doesn't have — they collapse to stroke.
     */
    public const VARIANTS = [
        'outline' => 'stroke-rounded',
        'mini' => 'stroke-rounded',
        'micro' => 'stroke-rounded',
        'solid' => 'solid-rounded',
    ];

    /**
     * Heroicon names with no Hugeicons icon of the same name, mapped to their
     * closest free equivalent. Consulted AFTER the literal lookup, so a real
     * Hugeicons name always wins. Anything missing here falls back to
     * flux:icon, which still renders heroicons.
     */
    public const ALIASES = [
        // Controls and navigation...
        'magnifying-glass'               => 'search-01',
        'x-mark'                         => 'cancel-01',
        'x-circle'                       => 'cancel-circle',
        'plus'                           => 'add-01',
        'minus'                          => 'remove-01',
        'arrow-path'                     => 'refresh',
        'arrow-up-tray'                  => 'upload-01',
        'arrow-down-tray'                => 'download-01',
        'arrow-up-circle'                => 'circle-arrow-up-02',
        'arrow-right-start-on-rectangle' => 'logout-01',
        'arrow-left-start-on-rectangle'  => 'login-01',
        'bars-3'                         => 'menu-01',
        'ellipsis-horizontal'            => 'more-horizontal',
        'adjustments-horizontal'         => 'preference-horizontal',
        'cog-6-tooth'                    => 'settings-02',
        'squares-2x2'                    => 'dashboard-square-01',
        'eye-dropper'                    => 'dropper',
        'cursor-arrow-rays'              => 'cursor-01',
        // Feedback...
        'exclamation-triangle' => 'alert-02',
        'exclamation-circle'   => 'alert-circle',
        'question-mark-circle' => 'help-circle',
        // Files and media...
        'document'         => 'file-01',
        'document-text'    => 'file-02',
        'paper-clip'       => 'attachment-01',
        'photo'            => 'image-01',
        'cloud-arrow-up'   => 'cloud-upload',
        'cloud-arrow-down' => 'cloud-download',
        'trash'            => 'delete-02',
        'pencil-square'    => 'pencil-edit-01',
        'eye-slash'        => 'view-off',
        'archive-box'      => 'archive-02',
        'chart-bar'        => 'chart-01',
        // People and messaging...
        'users'                  => 'user-multiple',
        'chat-bubble-left-right' => 'bubble-chat',
        'chat-bubble-oval-left'  => 'message-01',
        'envelope'               => 'mail-01',
        'phone'                  => 'call',
        'device-phone-mobile'    => 'smart-phone-01',
        'lock-closed'            => 'square-lock-01',
        // Commerce...
        'banknotes'           => 'banknote',
        'currency-dollar'     => 'dollar-circle',
        'receipt-percent'     => 'discount-tag-01',
        'building-storefront' => 'store-01',
        'rocket-launch'       => 'rocket-01',
        'percent-badge'       => 'percent-circle',
        // Odds and ends...
        'language' => 'translate',
        // Everything else...
        'check-circle' => 'checkmark-circle-02',
    ];

    /**
     * Heroicon names that Hugeicons ALSO has — but drawing something else.
     * Hugeicons' arrow-* are chevrons, its moon is full rather than crescent,
     * and its map-pin is a pin on a map instead of the bare teardrop. These
     * are consulted BEFORE the literal lookup so the heroicon meaning wins.
     */
    public const OVERRIDES = [
        'arrow-up'    => 'arrow-up-02',
        'arrow-down'  => 'arrow-down-02',
        'arrow-left'  => 'arrow-left-02',
        'arrow-right' => 'arrow-right-02',
        'map-pin'     => 'location-01',
        'moon'        => 'moon-02',
    ];

    /**
     * Is a Hugeicons source available at all? False in apps that skipped the
     * blade-hugeicons dependency and configured no Pro sets.
     */
    public static function available(): bool
    {
        return class_exists(Factory::class);
    }

    /**
     * Normalize a variant/style prop into a Hugeicons style.
     */
    public static function style(?string $variant): string
    {
        if ($variant === null) {
            return config('mds.icons.style', 'stroke-rounded');
        }

        if (in_array($variant, static::STYLES, true)) {
            return $variant;
        }

        return static::VARIANTS[$variant] ?? config('mds.icons.style', 'stroke-rounded');
    }

    /**
     * The blade-icons prefixes to try for a style, most specific first. The
     * bundled free set only holds Stroke Rounded, so it is the last resort.
     */
    public static function prefixes(string $style): array
    {
        $prefixes = [];

        if (array_key_exists($style, (array) config('mds.icons.sets', []))) {
            $prefixes[] = static::PRO_PREFIX.'-'.$style;
        }

        if ($style === 'stroke-rounded' || config('mds.icons.fallback_style', true)) {
            $prefixes[] = static::FREE_SET;
        }

        return $prefixes;
    }

    /**
     * Resolve an icon to a renderable Svg, or null when no source has it.
     */
    public static function svg(string $name, ?string $variant = null, array $attributes = []): ?Svg
    {
        if (! static::available()) {
            return null;
        }

        $factory = app(Factory::class);
        $sets = $factory->all();

        foreach (static::prefixes(static::style($variant)) as $prefix) {
            if (! isset($sets[$prefix])) {
                continue;
            }

            $candidates = array_unique(array_filter([
                static::OVERRIDES[$name] ?? null,
                $name,
                static::ALIASES[$name] ?? null,
            ]));

            foreach ($candidates as $candidate) {
                try {
                    return $factory->svg($prefix.'-'.$candidate, '', $attributes);
                } catch (SvgNotFound) {
                    continue;
                }
            }
        }

        return null;
    }
}
