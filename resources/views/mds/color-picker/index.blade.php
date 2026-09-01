@props([
    'value' => null,
    'name' => null,
    'format' => 'hex',
    'type' => 'input',
    'placeholder' => null,
    'label' => null,
    'description' => null,
    'swatches' => null,
    'dropper' => false,
    'clearable' => false,
    'size' => null,
    'disabled' => false,
    'invalid' => false,
    'error' => null,
    'fa' => null,
])

@php
// fa picks the built-in labels' language.
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag...
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: null;
}

$invalid = $invalid || filled($error);

// Default palette (Tailwind 500s + neutrals), used when no :swatches given.
// Pass :swatches="false" to hide the grid entirely...
$defaultSwatches = [
    '#ef4444', '#f97316', '#f59e0b', '#eab308', '#84cc16', '#22c55e', '#10b981', '#14b8a6', '#06b6d4', '#0ea5e9',
    '#3b82f6', '#6366f1', '#8b5cf6', '#a855f7', '#d946ef', '#ec4899', '#f43f5e', '#71717a', '#000000', '#ffffff',
];

if ($swatches === null) {
    $swatches = $defaultSwatches;
} elseif ($swatches === false) {
    $swatches = [];
}

$swatches = collect($swatches)
    ->map(fn ($swatch) => is_array($swatch) ? $swatch : [$swatch, null])
    ->values();

$triggerSize = match ($size) {
    'sm' => 'py-1.5',
    default => 'py-2',
};
@endphp

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsColorPicker', (config) => ({
        open: false,

        toggle(state = null) {
            const next = state ?? ! this.open

            if (next === this.open) return

            this.open = next

            this.$nextTick(() => {
                const panel = this.$refs.panel

                // Focus follows the panel, and comes back to whatever opened
                // it — otherwise Escape drops the reader at the top of the page.
                if (this.open) {
                    this.trigger = document.activeElement
                    panel?.querySelector('input, button')?.focus()
                } else {
                    this.trigger?.focus()
                    this.trigger = null
                }
            })
        },

        trigger: null,
        h: 0, s: 100, v: 100, a: 1,
        empty: true,
        format: config.format ?? 'hex',

        init() {
            if (config.value) {
                this.setFromString(config.value)

                // Normalize the server-rendered value to the configured format (no event)...
                this.$nextTick(() => { if (this.$refs.input) this.$refs.input.value = this.output })
            }
        },

        hsvToRgb(h, s, v) {
            s /= 100; v /= 100
            const f = (n) => {
                const k = (n + h / 60) % 6
                return v - v * s * Math.max(0, Math.min(k, 4 - k, 1))
            }
            return [f(5), f(3), f(1)].map(x => Math.round(x * 255))
        },

        rgbToHsv(r, g, b) {
            r /= 255; g /= 255; b /= 255
            const max = Math.max(r, g, b), min = Math.min(r, g, b), d = max - min
            let h = 0
            if (d) {
                if (max === r) h = ((g - b) / d) % 6
                else if (max === g) h = (b - r) / d + 2
                else h = (r - g) / d + 4
                h *= 60
                if (h < 0) h += 360
            }
            return [h, max ? (d / max) * 100 : 0, max * 100]
        },

        get rgb() { return this.hsvToRgb(this.h, this.s, this.v) },
        get hex() { return '#' + this.rgb.map(x => x.toString(16).padStart(2, '0')).join('') },
        get hexa() { return this.hex + Math.round(this.a * 255).toString(16).padStart(2, '0') },
        get hsl() {
            const s = this.s / 100, v = this.v / 100
            const l = v * (1 - s / 2)
            const sl = (l === 0 || l === 1) ? 0 : (v - l) / Math.min(l, 1 - l)
            return [Math.round(this.h), Math.round(sl * 100), Math.round(l * 100)]
        },

        get hasAlpha() { return ['hexa', 'rgba', 'hsla'].includes(this.format) },

        get output() {
            if (this.empty) return ''
            const [r, g, b] = this.rgb, [hh, ss, ll] = this.hsl, alpha = +this.a.toFixed(2)
            switch (this.format) {
                case 'hexa': return this.hexa
                case 'rgb': return `rgb(${r}, ${g}, ${b})`
                case 'rgba': return `rgba(${r}, ${g}, ${b}, ${alpha})`
                case 'hsl': return `hsl(${hh}, ${ss}%, ${ll}%)`
                case 'hsla': return `hsla(${hh}, ${ss}%, ${ll}%, ${alpha})`
                default: return this.hex
            }
        },

        get previewCss() {
            if (this.empty) return ''
            const [r, g, b] = this.rgb
            return this.hasAlpha ? `rgba(${r}, ${g}, ${b}, ${+this.a.toFixed(2)})` : this.hex
        },

        setFromString(str) {
            str = String(str ?? '').trim()
            if (! str) { this.empty = true; return }

            let m = str.match(/^#?([0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/i)
            if (m) {
                let hx = m[1]
                if (hx.length === 3) hx = [...hx].map(c => c + c).join('')
                ;[this.h, this.s, this.v] = this.rgbToHsv(parseInt(hx.slice(0, 2), 16), parseInt(hx.slice(2, 4), 16), parseInt(hx.slice(4, 6), 16))
                this.a = hx.length === 8 ? +(parseInt(hx.slice(6, 8), 16) / 255).toFixed(2) : 1
                this.empty = false
                return
            }

            m = str.match(/^rgba?\(\s*(\d+)[,\s]+(\d+)[,\s]+(\d+)(?:[,\s\/]+([\d.]+%?))?\s*\)$/i)
            if (m) {
                ;[this.h, this.s, this.v] = this.rgbToHsv(+m[1], +m[2], +m[3])
                this.a = m[4] !== undefined ? (m[4].endsWith('%') ? parseFloat(m[4]) / 100 : parseFloat(m[4])) : 1
                this.empty = false
                return
            }

            m = str.match(/^hsla?\(\s*([\d.]+)[,\s]+([\d.]+)%[,\s]+([\d.]+)%(?:[,\s\/]+([\d.]+%?))?\s*\)$/i)
            if (m) {
                const l = +m[3] / 100, sl = +m[2] / 100
                const v = l + sl * Math.min(l, 1 - l)
                this.h = +m[1]
                this.s = (v === 0 ? 0 : 2 * (1 - l / v)) * 100
                this.v = v * 100
                this.a = m[4] !== undefined ? (m[4].endsWith('%') ? parseFloat(m[4]) / 100 : parseFloat(m[4])) : 1
                this.empty = false
            }
        },

        commit() {
            const input = this.$refs.input
            if (! input) return
            input.value = this.output
            input.dispatchEvent(new Event('input', { bubbles: true }))
        },

        pick(value) { this.setFromString(value); this.commit() },
        clear() { this.empty = true; this.commit() },

        areaDown(e) {
            const el = e.currentTarget
            const move = (ev) => {
                const rect = el.getBoundingClientRect()
                this.s = Math.min(Math.max((ev.clientX - rect.left) / rect.width, 0), 1) * 100
                this.v = 100 - Math.min(Math.max((ev.clientY - rect.top) / rect.height, 0), 1) * 100
                this.empty = false
                this.commit()
            }
            const up = () => {
                window.removeEventListener('pointermove', move)
                window.removeEventListener('pointerup', up)
            }
            move(e)
            window.addEventListener('pointermove', move)
            window.addEventListener('pointerup', up)
        },

        async eyeDropper() {
            if (! window.EyeDropper) return
            try {
                const result = await new window.EyeDropper().open()
                this.pick(result.sRGBHex)
            } catch (e) {
                // User dismissed the eyedropper...
            }
        },
    }))
})
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class(['relative', 'opacity-50' => $disabled]) }}
        @if ($disabled) inert aria-disabled="true" @endif
        x-id="['mds-color-picker-panel']"
        x-data="mdsColorPicker({ value: @js($value), format: @js($format) })"
        x-on:keydown.escape.window="toggle(false)"
        data-mds-color-picker
    >
        <input
            type="hidden"
            x-ref="input"
            value="{{ $value }}"
            @if ($name) name="{{ $name }}" @endif
            {{ $attributes->whereStartsWith('wire:model') }}
        >

        @if ($type === 'button')
            <button
                type="button"
                class="mds-checker size-9 overflow-hidden rounded-lg border border-zinc-200 shadow-xs dark:border-white/10"
                x-on:click="toggle()"
                x-bind:aria-expanded="open ? 'true' : 'false'"
                aria-haspopup="dialog"
                x-bind:aria-controls="$id('mds-color-picker-panel')"
                aria-label="{{ $label ?? ($fa ? 'انتخاب رنگ' : 'Pick a color') }}"
                data-mds-color-picker-trigger
            >
                <span class="block size-full" x-bind:style="empty ? '' : 'background:' + previewCss"></span>
            </button>
        @else
            <div @class([
                'flex items-center gap-2 rounded-lg border bg-white ps-3 pe-2 shadow-xs dark:bg-white/10',
                'border-red-500 dark:border-red-400' => $invalid,
                'border-zinc-200 dark:border-white/10' => ! $invalid,
            ]) data-mds-color-picker-trigger>
                <button
                    type="button"
                    class="mds-checker size-5 shrink-0 overflow-hidden rounded-md border border-black/10 dark:border-white/20"
                    x-on:click="toggle()"
                    x-bind:aria-expanded="open ? 'true' : 'false'"
                    aria-haspopup="dialog"
                    x-bind:aria-controls="$id('mds-color-picker-panel')"
                    aria-label="{{ $label ?? ($fa ? 'انتخاب رنگ' : 'Pick a color') }}"
                >
                    <span class="block size-full" x-bind:style="empty ? '' : 'background:' + previewCss"></span>
                </button>

                <input
                    type="text"
                    dir="ltr"
                    class="w-full flex-1 bg-transparent {{ $triggerSize }} text-sm text-zinc-800 outline-none placeholder:text-zinc-400 dark:text-white dark:placeholder:text-zinc-500"
                    x-bind:value="output"
                    x-on:change="pick($event.target.value)"
                    x-on:focus="open = true"
                    @if ($placeholder) placeholder="{{ $placeholder }}" @endif
                >

                @if ($clearable)
                    <button
                        type="button"
                        class="shrink-0 rounded p-1 text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200"
                        x-show="! empty"
                        x-cloak
                        x-on:click="clear()"
                        aria-label="{{ $fa ? 'پاک کردن' : 'Clear' }}"
                    >
                        <mds:icon icon="x-mark" variant="micro" class="size-4" />
                    </button>
                @endif
            </div>
        @endif

        <div
            class="absolute start-0 top-full z-50 mt-2 w-64 rounded-xl border border-zinc-200 bg-white p-3 shadow-lg dark:border-white/10 dark:bg-zinc-800"
            x-ref="panel"
            x-bind:id="$id('mds-color-picker-panel')"
            role="dialog"
            aria-label="{{ $label ?? ($fa ? 'انتخاب رنگ' : 'Pick a color') }}"
            x-show="open"
            x-cloak
            x-transition.opacity.duration.100ms
            x-on:click.outside="toggle(false)"
            data-mds-color-picker-panel
        >
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                <div class="flex flex-col gap-3">
                    <mds:color-picker.area />
                    <mds:color-picker.slider channel="hue" />

                    <template x-if="hasAlpha">
                        <div><mds:color-picker.slider channel="alpha" /></div>
                    </template>

                    <div class="flex items-center gap-2">
                        <mds:color-picker.input :placeholder="$placeholder" />

                        @if ($dropper)
                            <mds:color-picker.dropper />
                        @endif
                    </div>

                    @if ($swatches->isNotEmpty())
                        <mds:color-picker.swatches>
                            @foreach ($swatches as [$swatchValue, $swatchLabel])
                                <mds:color-picker.swatch :value="$swatchValue" :label="$swatchLabel" />
                            @endforeach
                        </mds:color-picker.swatches>
                    @endif
                </div>
            @endif
        </div>
    </div>

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif
</flux:field>
