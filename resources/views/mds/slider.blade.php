@props([
    'value' => null,
    'min' => 0,
    'max' => 100,
    'step' => 1,
    'range' => false,
    'label' => null,
    'description' => null,
    'name' => null,
    'size' => null,
    'disabled' => false,
    'showValue' => false,
    'ticks' => false,
    'format' => null,
    'error' => null,
    'invalid' => false,
    'fa' => null,
])

@php
// fa picks the readout's digits and the built-in strings' language.
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag. A range
// posts as `name[]`, so its rules report against `name.0` / `name.1` — the
// `name.*` key is the second half of that fallback.
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: ($range ? $errors->first($name.'.*') : null) ?: null;
}

$invalid = $invalid || filled($error);

// Bounds. Numeric strings arrive from attributes; anything else is ignored
// rather than warned about, and an inverted pair is put back in order.
$number = fn ($n, $default) => is_numeric($n) ? $n + 0 : $default;

$min = $number($min, 0);
$max = $number($max, 100);
$step = $number($step, 1);

if ($max < $min) {
    [$min, $max] = [$max, $min];
}

if ($step <= 0) {
    $step = 1;
}

$span = $max - $min;

// Clamp to the bounds and snap to the step grid, as the browser will anyway
// when it sanitizes the value — so the first paint and the readout agree
// with what the thumb ends up on.
$clamp = function ($n) use ($min, $max, $step, $number) {
    $n = max($min, min($max, $number($n, $min)));

    return max($min, min($max, $min + round(($n - $min) / $step) * $step));
};

// Two thumbs hold [low, high]; one thumb keeps its value in `low`. Both are
// clamped on the server too: Alpine keeps low <= high on input, but the first
// paint and a no-JS post come straight from the props.
if ($range) {
    $pair = is_array($value) ? array_values($value) : [];
    $low = $clamp($pair[0] ?? $min);
    $high = $clamp($pair[1] ?? $max);

    if ($low > $high) {
        [$low, $high] = [$high, $low];
    }
} else {
    $low = $clamp(is_array($value) ? ($value[0] ?? $min) : ($value ?? $min));
    $high = $low;
}

// Machine form for attributes: "50", "0.5" — never "50.0" or "5.0E+6".
$machine = fn ($n) => (string) (is_float($n) && floor($n) == $n && abs($n) < 1e15 ? (int) $n : $n);

// Display form for the readout and aria-valuetext: digits by fa, then the
// caller's `{value}` template — a plain replace, so «{value} تومان» works.
$display = function ($n) use ($machine, $fa, $format) {
    $s = $machine($n);
    $s = $fa ? \MajidDs\Support\Persian::digits($s) : $s;

    return $format === null ? $s : str_replace('{value}', $s, $format);
};

$percent = fn ($n) => $span > 0 ? round(($n - $min) / $span * 100, 4) : 0;

$readout = $range ? $display($low).' – '.$display($high) : $display($low);

// Ticks: `true` draws one per step while that stays readable (≤ 20 steps and
// the steps fit the span exactly), an int draws that many, evenly spaced.
if ($ticks === true) {
    $steps = $span / $step;
    $ticks = $steps >= 1 && $steps <= 20 && abs($steps - round($steps)) < 1e-9 ? (int) round($steps) + 1 : 0;
} elseif ($ticks === false || $ticks === null) {
    $ticks = 0;
} else {
    $ticks = max(0, (int) $ticks);
}

if ($ticks === 1) {
    $ticks = 2;
}

// aria-label for each native input: the field label, and which end it is.
$ariaLabel = $label ?? ($fa ? 'مقدار' : 'Value');
$lowLabel = $range ? $ariaLabel.($fa ? ' — حداقل' : ' — minimum') : $ariaLabel;
$highLabel = $ariaLabel.($fa ? ' — حداکثر' : ' — maximum');

// Deterministic ids for aria-describedby: stable across rebuilds, and a
// same-props collision points at an identical field, which is harmless.
$id = 'mds-slider-'.substr(md5(json_encode([$name, $label, $min, $max, $range])), 0, 8);
$describedBy = trim(($description ? $id.'-description ' : '').(filled($error) ? $id.'-error' : '')) ?: null;

$sm = $size === 'sm';

// wire:model goes to the real control(s). A range binds `base.0` / `base.1`
// with the caller's modifiers kept: wire:model.live="price" -> price.0.
$bindings = $attributes->whereStartsWith('wire:model');
$lowBindings = $range ? new \Illuminate\View\ComponentAttributeBag(array_map(fn ($v) => $v.'.0', $bindings->getAttributes())) : $bindings;
$highBindings = new \Illuminate\View\ComponentAttributeBag(array_map(fn ($v) => $v.'.1', $bindings->getAttributes()));

$inputName = $name ? ($range ? $name.'[]' : $name) : null;

$inputClasses = implode(' ', [
    'mds-slider-input w-full appearance-none bg-transparent',
    $sm ? 'h-4' : 'h-5',
    $disabled ? 'cursor-not-allowed' : 'cursor-pointer',
    $range ? 'absolute inset-0' : 'relative',
]);
@endphp

@include('mds::partials.digits')

@once('mds-slider')
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerSlider = (Alpine) => {
    if (window.mds.sliderRegistered) return
    window.mds.sliderRegistered = true

    Alpine.data('mdsSlider', (config = {}) => ({
        min: config.min ?? 0,
        max: config.max ?? 100,
        step: config.step ?? 1,
        range: config.range ?? false,
        disabled: config.disabled ?? false,
        fa: config.fa ?? true,
        format: config.format ?? null,
        low: config.low ?? 0,
        high: config.high ?? 0,
        effects: [],

        init() {
            // The native inputs ARE the controls, so this state is a mirror of
            // them, never the other way round: no x-bind:value, no x-model.
            // Children are not initialised yet when init() runs, so the
            // wiring waits a tick.
            this.$nextTick(() => {
                this.follow('low')
                if (this.range) this.follow('high')
            })
        },

        destroy() {
            this.effects.forEach(effect => Alpine.release(effect))
            this.effects = []
        },

        // Livewire's wire:model is an x-model bound to $wire, and Alpine
        // leaves its accessor on the element as _x_model. Reading it inside an
        // effect makes the readout and fill follow a server-side change of the
        // bound property (x-model sets el.value without an input event, so a
        // listener would not see it; the attribute never changes, so neither
        // would a MutationObserver). Without a binding, the input's own value
        // is the source — it may differ from the config after a form restore.
        follow(which) {
            const el = this.$refs[which]

            if (! el) return

            const model = el._x_model

            if (! model) {
                this.read(which)

                return
            }

            this.effects.push(Alpine.effect(() => {
                const value = model.get()

                if (value === undefined || value === null || value === '') return

                const n = Number(value)

                if (Number.isFinite(n)) this[which] = n
            }))
        },

        read(which) {
            const n = Number(this.$refs[which].value)

            if (Number.isFinite(n)) this[which] = n
        },

        // The thumbs may not cross. A crossing value is pushed back to the
        // other thumb and re-announced, so a wire:model listener that already
        // took the raw value gets the clamped one in the same tick.
        input(which) {
            const el = this.$refs[which]
            const n = Number(el.value)

            if (! Number.isFinite(n)) return

            if (this.range && which === 'low' && n > this.high) return this.commit(el, this.high)
            if (this.range && which === 'high' && n < this.low) return this.commit(el, this.low)

            this[which] = n
        },

        commit(el, value) {
            el.value = value
            el.dispatchEvent(new Event('input', { bubbles: true }))
            el.dispatchEvent(new Event('change', { bubbles: true }))
        },

        // With two thumbs the inputs take no pointer events (only their thumbs
        // do), so a press on the bare track moves the nearer thumb there.
        trackDown(e) {
            if (! this.range || this.disabled || e.button !== 0) return
            if (e.target.matches('input')) return

            const rect = this.$refs.track.getBoundingClientRect()

            if (! rect.width) return

            let fraction = (e.clientX - rect.left) / rect.width

            if (getComputedStyle(this.$root).direction === 'rtl') fraction = 1 - fraction

            const value = this.min + Math.min(Math.max(fraction, 0), 1) * (this.max - this.min)
            const which = Math.abs(value - this.low) <= Math.abs(value - this.high) ? 'low' : 'high'

            // Setting .value snaps to the step and clamps to the bounds natively.
            this.commit(this.$refs[which], value)
            this.$refs[which].focus()
        },

        percent(n) {
            const span = this.max - this.min

            return span > 0 ? Math.min(Math.max((n - this.min) / span * 100, 0), 100) : 0
        },

        get fill() {
            return {
                '--mds-slider-a': this.percent(this.range ? this.low : this.min) + '%',
                '--mds-slider-b': this.percent(this.range ? this.high : this.low) + '%',
            }
        },

        display(n) {
            let s = window.mds.digits(String(Number(Number(n).toFixed(10))), this.fa)

            return this.format === null ? s : this.format.split('{value}').join(s)
        },

        get readout() {
            return this.range ? this.display(this.low) + ' – ' + this.display(this.high) : this.display(this.low)
        },
    }))
}

if (window.Alpine) {
    window.mds.registerSlider(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerSlider(window.Alpine))
}
</script>
@endonce

<div
    {{ $attributes->whereDoesntStartWith('wire:model')->class([
        'block min-w-0',
        'opacity-50' => $disabled,
    ])->style([
        '--mds-slider-a: '.$percent($range ? $low : $min).'%',
        '--mds-slider-b: '.$percent($range ? $high : $low).'%',
    ]) }}
    x-data="mdsSlider({
        min: @js($min),
        max: @js($max),
        step: @js($step),
        range: @js((bool) $range),
        disabled: @js((bool) $disabled),
        fa: @js((bool) $fa),
        format: @js($format),
        low: @js($low),
        high: @js($high),
    })"
    x-bind:style="fill"
    @if ($disabled) data-disabled @endif
    @if ($invalid) data-invalid @endif
    @if ($range) data-mds-slider-range @endif
    @if ($sm) data-mds-slider-size="sm" @endif
    data-mds-slider
>
    @if ($label || $showValue)
        <div class="mb-3 flex items-center justify-between gap-3" data-mds-slider-header>
            @if ($label)
                <flux:label>{{ $label }}</flux:label>
            @else
                <span></span>
            @endif

            @if ($showValue)
                <span
                    class="shrink-0 text-sm tabular-nums text-zinc-500 dark:text-zinc-400"
                    aria-live="polite"
                    aria-atomic="true"
                    x-text="readout"
                    data-mds-slider-value
                >{{ $readout }}</span>
            @endif
        </div>
    @endif

    <div
        @class(['relative flex items-center', $sm ? 'h-4' : 'h-5'])
        @if ($range) x-on:pointerdown="trackDown($event)" @endif
    >
        <div
            @class([
                'absolute rounded-full bg-zinc-200 dark:bg-white/10',
                $sm ? 'h-1 start-1.5 end-1.5' : 'h-1.5 start-2 end-2',
            ])
            x-ref="track"
            aria-hidden="true"
            data-mds-slider-track
        >
            <div
                @class([
                    'absolute inset-y-0 rounded-full',
                    'bg-red-500 dark:bg-red-400' => $invalid,
                    'bg-accent' => ! $invalid,
                ])
                style="inset-inline-start: var(--mds-slider-a); inset-inline-end: calc(100% - var(--mds-slider-b))"
                data-mds-slider-fill
            ></div>
        </div>

        <input
            type="range"
            class="{{ $inputClasses }}"
            @if ($range) x-bind:class="(min + max) / 2 < low ? 'z-20' : 'z-10'" @endif
            x-ref="low"
            x-on:input="input('low')"
            min="{{ $machine($min) }}"
            max="{{ $machine($max) }}"
            step="{{ $machine($step) }}"
            value="{{ $machine($low) }}"
            @if ($inputName) name="{{ $inputName }}" @endif
            @if ($disabled) disabled @endif
            @if ($invalid) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            aria-label="{{ $lowLabel }}"
            aria-valuetext="{{ $display($low) }}"
            x-bind:aria-valuetext="display(low)"
            data-mds-slider-input="{{ $range ? 'low' : 'value' }}"
            {{ $lowBindings }}
        >

        @if ($range)
            <input
                type="range"
                class="{{ $inputClasses }} z-10"
                x-ref="high"
                x-on:input="input('high')"
                min="{{ $machine($min) }}"
                max="{{ $machine($max) }}"
                step="{{ $machine($step) }}"
                value="{{ $machine($high) }}"
                @if ($inputName) name="{{ $inputName }}" @endif
                @if ($disabled) disabled @endif
                @if ($invalid) aria-invalid="true" @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                aria-label="{{ $highLabel }}"
                aria-valuetext="{{ $display($high) }}"
                x-bind:aria-valuetext="display(high)"
                data-mds-slider-input="high"
                {{ $highBindings }}
            >
        @endif
    </div>

    @if ($ticks > 0)
        <div @class(['mt-1 flex justify-between', $sm ? 'mx-1.5' : 'mx-2']) aria-hidden="true" data-mds-slider-ticks>
            @for ($i = 0; $i < $ticks; $i++)
                <span class="h-1 w-px bg-zinc-300 dark:bg-white/20" data-mds-slider-tick></span>
            @endfor
        </div>
    @endif

    @if ($description)
        <flux:description id="{{ $id }}-description" class="mt-3">{{ $description }}</flux:description>
    @endif

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div role="alert" aria-live="polite" aria-atomic="true" id="{{ $id }}-error" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</div>
