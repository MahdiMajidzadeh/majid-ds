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
        'magnifying-glass' => 'search-01',
        'x-mark' => 'cancel-01',
        'x-circle' => 'cancel-circle',
        'plus' => 'add-01',
        'minus' => 'remove-01',
        'arrow-path' => 'refresh',
        'arrow-up-tray' => 'upload-01',
        'arrow-down-tray' => 'download-01',
        'arrow-up-circle' => 'circle-arrow-up-02',
        'arrow-right-start-on-rectangle' => 'logout-01',
        'arrow-left-start-on-rectangle' => 'login-01',
        'bars-3' => 'menu-01',
        'ellipsis-horizontal' => 'more-horizontal',
        'adjustments-horizontal' => 'preference-horizontal',
        'cog-6-tooth' => 'settings-02',
        'squares-2x2' => 'dashboard-square-01',
        'eye-dropper' => 'dropper',
        'cursor-arrow-rays' => 'cursor-01',
        // Feedback...
        'exclamation-triangle' => 'alert-02',
        'exclamation-circle' => 'alert-circle',
        'question-mark-circle' => 'help-circle',
        // Files and media...
        'document' => 'file-01',
        'document-text' => 'file-02',
        'paper-clip' => 'attachment-01',
        'photo' => 'image-01',
        'cloud-arrow-up' => 'cloud-upload',
        'cloud-arrow-down' => 'cloud-download',
        'trash' => 'delete-02',
        'pencil-square' => 'pencil-edit-01',
        'eye-slash' => 'view-off',
        'archive-box' => 'archive-02',
        'chart-bar' => 'chart-01',
        'arrow-trending-up' => 'analytics-up',
        'arrow-trending-down' => 'analytics-down',
        'scale' => 'balance-scale',
        // People and messaging...
        'users' => 'user-multiple',
        'chat-bubble-left-right' => 'bubble-chat',
        'chat-bubble-oval-left' => 'message-01',
        'paper-airplane' => 'sent',
        'microphone' => 'mic-01',
        'envelope' => 'mail-01',
        'phone' => 'call',
        'device-phone-mobile' => 'smart-phone-01',
        'lock-closed' => 'square-lock-01',
        // Commerce...
        'banknotes' => 'banknote',
        'currency-dollar' => 'dollar-circle',
        'receipt-percent' => 'discount-tag-01',
        'building-storefront' => 'store-01',
        'rocket-launch' => 'rocket-01',
        'percent-badge' => 'percent-circle',
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
        'arrow-up' => 'arrow-up-02',
        'arrow-down' => 'arrow-down-02',
        'arrow-left' => 'arrow-left-02',
        'arrow-right' => 'arrow-right-02',
        'map-pin' => 'location-01',
        'moon' => 'moon-02',
    ];

    /**
     * The default set <mds:icon-picker> offers: a hundred-odd everyday icons
     * spanning navigation, people, commerce, files, feedback and devices. Real
     * Hugeicons names, not heroicon aliases, so the picker never depends on
     * the alias table. Pass `:icons` to the picker to use any other set —
     * `names()` gives the whole free set when a form really needs it all.
     */
    public const PICKER = [
        // Navigation and controls...
        'home-01', 'search-01', 'menu-01', 'more-horizontal', 'more-vertical', 'filter', 'grid-view', 'list-view',
        'dashboard-square-01', 'settings-02', 'preference-horizontal', 'refresh', 'add-01', 'remove-01', 'cancel-01',
        'cancel-circle', 'tick-01', 'checkmark-circle-02', 'arrow-up-02', 'arrow-down-02', 'arrow-left-02', 'arrow-right-02',
        'arrow-up-right-01', 'login-01', 'logout-01', 'link-01', 'share-01', 'eye', 'view-off', 'cursor-01',
        // Feedback and status...
        'alert-02', 'alert-circle', 'help-circle', 'information-circle', 'notification-01', 'flag-01', 'star', 'favourite',
        'bookmark-01', 'sparkles', 'fire', 'flash', 'idea', 'magic-wand-01', 'rocket-01', 'award-01', 'champion', 'crown', 'medal-01',
        // People and messaging...
        'user', 'user-add-01', 'user-settings-01', 'user-multiple', 'mail-01', 'inbox', 'sent', 'call', 'message-01',
        'bubble-chat', 'chat-bot', 'ai-brain-01', 'robot-01', 'mic-01',
        // Time and place...
        'calendar-01', 'calendar-03', 'clock-01', 'time-01', 'location-01', 'map-pin', 'pin-location-01', 'global', 'earth',
        'translate', 'language-square',
        // Commerce...
        'shopping-cart-01', 'shopping-bag-01', 'store-01', 'package', 'delivery-truck-01', 'truck', 'credit-card', 'wallet-01',
        'banknote', 'dollar-circle', 'coins-01', 'discount-tag-01', 'percent-circle', 'tag-01', 'gift', 'invoice-01',
        'balance-scale', 'briefcase-01', 'building-01', 'house-01',
        // Files and media...
        'file-01', 'file-02', 'folder-01', 'note-01', 'task-01', 'clipboard', 'copy-01', 'archive-02', 'attachment-01',
        'image-01', 'camera-01', 'video-01', 'music-note-01', 'headphones', 'printer', 'pencil-edit-01', 'edit-02',
        'delete-02', 'download-01', 'upload-01', 'cloud-upload', 'cloud-download', 'cloud',
        // Data and devices...
        'chart-01', 'analytics-up', 'analytics-down', 'pie-chart', 'bar-chart', 'database', 'server-stack-01', 'code',
        'source-code', 'smart-phone-01', 'computer', 'laptop', 'tablet-01', 'wifi-01', 'bluetooth',
        // Safety and the world...
        'square-lock-01', 'square-unlock-01', 'key-01', 'shield-01', 'security-check', 'sun-03', 'moon-02', 'leaf-01',
        'plant-01', 'rain', 'umbrella', 'car-01', 'bus-01', 'airplane-01', 'hospital-01', 'medicine-02', 'book-01', 'school',
        'graduate-male', 'paint-brush-01', 'color-picker', 'layout-01',
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
     * Every icon name in the bundled free set, sorted — the full menu for
     * `<mds:icon-picker :icons="Icons::names()">`. Six thousand names, so a
     * picker fed all of them renders a few megabytes of SVG: fine for an
     * admin screen that needs the lot, wrong as a default.
     *
     * @return list<string>
     */
    public static function names(): array
    {
        if (! static::available()) {
            return [];
        }

        // The directory blade-hugeicons registered, wherever Composer put it
        // — this package may itself be sitting under an app's vendor/.
        $set = app(Factory::class)->all()[static::FREE_SET] ?? [];

        $names = [];

        // Older blade-icons releases keep the registered directory under
        // `path`; newer ones normalise it into `paths`.
        foreach ((array) ($set['paths'] ?? $set['path'] ?? []) as $dir) {
            foreach (glob($dir.'/*.svg') ?: [] as $path) {
                $names[] = basename($path, '.svg');
            }
        }

        $names = array_values(array_unique($names));

        sort($names);

        return $names;
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
     *
     * @return list<string>
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
     *
     * @param  array<string, mixed>  $attributes
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
