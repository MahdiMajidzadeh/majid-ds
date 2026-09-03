@props([
    'command' => null,
    'icon' => null,
    'label' => null,
    'toggle' => null,
    'fa' => null,
])

@aware(['fa' => null])

@php
// fa picks the built-in labels' language.
$fa ??= config('mds.persian_digits', true);

// Spellings a caller may reasonably reach for, folded onto one command name.
$aliases = [
    'strikethrough' => 'strike',
    'strike-through' => 'strike',
    'heading1' => 'h1',
    'heading2' => 'h2',
    'heading3' => 'h3',
    'p' => 'paragraph',
    'ul' => 'bullet',
    'bullet-list' => 'bullet',
    'ol' => 'ordered',
    'ordered-list' => 'ordered',
    'numbered' => 'ordered',
    'blockquote' => 'quote',
    'pre' => 'code',
    'code-block' => 'code',
    'dir' => 'direction',
    'rtl' => 'direction',
    'clear-format' => 'clear',
    'remove-format' => 'clear',
];

$command = $aliases[$command] ?? $command;

$digit = fn (int $n) => $fa ? \MajidDs\Support\Persian::digits($n) : (string) $n;

// [icon, Persian label, English label, is it a toggle?]. The icons come from
// the Hugeicons text-editing set. A `left-to-right-...` there is the icon's
// own name, not a CSS direction — the set ships a mirrored twin of each list
// icon, and choosing between them would need a page direction the server does
// not know, so the LTR name is rendered and CSS mirrors it on an RTL page.
$tools = [
    'bold' => ['text-bold', 'پررنگ', 'Bold', true],
    'italic' => ['text-italic', 'مورب', 'Italic', true],
    'underline' => ['text-underline', 'زیرخط', 'Underline', true],
    'strike' => ['text-strikethrough', 'خط‌خورده', 'Strikethrough', true],
    'h1' => ['heading-01', 'عنوان '.$digit(1), 'Heading '.$digit(1), true],
    'h2' => ['heading-02', 'عنوان '.$digit(2), 'Heading '.$digit(2), true],
    'h3' => ['heading-03', 'عنوان '.$digit(3), 'Heading '.$digit(3), true],
    'paragraph' => ['paragraph', 'پاراگراف', 'Paragraph', true],
    'bullet' => ['left-to-right-list-bullet', 'فهرست نقطه‌ای', 'Bulleted list', true], // icon name, not a CSS utility
    'ordered' => ['left-to-right-list-number', 'فهرست شماره‌دار', 'Numbered list', true], // icon name, not a CSS utility
    'quote' => ['left-to-right-block-quote', 'نقل‌قول', 'Quote', true], // icon name, not a CSS utility
    'code' => ['source-code', 'بلوک کد', 'Code block', true],
    'link' => ['link-01', 'پیوند', 'Link', false],
    'unlink' => ['unlink-01', 'حذف پیوند', 'Remove link', false],
    'direction' => ['arrow-left-right', 'راست‌به‌چپ', 'Right to left', true], // icon name, not a CSS utility
    'clear' => ['text-clear', 'پاک کردن قالب‌بندی', 'Clear formatting', false],
];

[$defaultIcon, $persian, $english, $isToggle] = $tools[$command] ?? [null, null, null, false];

// The list and quote glyphs point the way the text runs.
$mirror = in_array($command, ['bullet', 'ordered', 'quote'], true) && $icon === null;

$icon ??= $defaultIcon;
$label ??= $fa ? $persian : $english;
$toggle ??= $isToggle;
@endphp

<button
    type="button"
    {{ $attributes->class([
        'flex size-8 shrink-0 cursor-pointer items-center justify-center rounded-md text-zinc-600 transition-colors',
        'hover:bg-zinc-200/70 dark:text-zinc-300 dark:hover:bg-white/10',
        'focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
        'aria-pressed:bg-accent/10 aria-pressed:text-accent-content dark:aria-pressed:bg-accent/20',
    ]) }}
    tabindex="-1"
    @if ($command) x-on:click="run(@js($command))" @endif
    x-on:mousedown.prevent
    x-on:focus="roving($el)"
    @if ($toggle && $command)
        x-bind:aria-pressed="active(@js($command)) ? 'true' : 'false'"
        aria-pressed="false"
    @endif
    @if ($label) aria-label="{{ $label }}" @endif
    @if ($command) data-mds-editor-command="{{ $command }}" @endif
    data-mds-editor-tool
>
    @if ($slot->isNotEmpty())
        {{ $slot }}
    @elseif ($icon)
        <mds:icon :icon="$icon" variant="micro" @class(['size-4', 'rtl:-scale-x-100' => $mirror]) />
    @endif
</button>
