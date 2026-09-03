@props([
    'label' => null,
    'autoplay' => false,
    'interval' => 5000,
    'loop' => true,
    'indicators' => true,
    'controls' => true,
    'perView' => 1,
    'gap' => null,
    'start' => 0,
    'aspect' => null,
    'fa' => null,
])

@php
use MajidDs\Support\Persian;

// fa picks the built-in strings' language along with the digits.
$fa ??= config('mds.persian_digits', true);

$label ??= $fa ? 'اسلایدشو' : 'Slideshow';

$perView = max(1, (int) $perView);
$interval = max(500, (int) $interval);
$gap ??= 'gap-0';

// The slot is already rendered, so the slides can be counted from it: one
// root marker per item. Nested carousels would inflate the count, and Alpine
// re-reads the real DOM at init anyway — the server number only seeds the
// dots and the first status line.
$total = substr_count((string) $slot, 'data-mds-carousel-item');

// One page per possible start slide: with three slides per view and five
// slides there are three positions, not five.
$pages = max(1, $total - $perView + 1);
$start = max(0, min((int) $start, $pages - 1));

// The item width takes the track gap into account, so the gap class has to
// become a length. Tailwind's spacing scale and arbitrary values cover what
// callers actually write; anything else is corrected by Alpine at init from
// the computed style, and only the first paint is a few pixels off.
$gapLength = match (true) {
    (bool) preg_match('/(?:^|\s)gap-(?:x-)?(\d+(?:\.\d+)?)(?:\s|$)/', $gap, $m) => (float) $m[1] === 0.0 ? '0px' : 'calc(var(--spacing) * '.$m[1].')',
    (bool) preg_match('/(?:^|\s)gap-(?:x-)?px(?:\s|$)/', $gap) => '1px',
    (bool) preg_match('/(?:^|\s)gap-(?:x-)?\[([^\]]+)\](?:\s|$)/', $gap, $m) => $m[1],
    default => '0px',
};

$labels = $fa
    ? ['status' => 'اسلاید {n} از {total}', 'item' => '{n} از {total}', 'dot' => 'رفتن به اسلاید {n}', 'pause' => 'توقف', 'play' => 'پخش']
    : ['status' => 'Slide {n} of {total}', 'item' => '{n} of {total}', 'dot' => 'Go to slide {n}', 'pause' => 'Pause', 'play' => 'Play'];

$digits = fn (int $n) => $fa ? Persian::digits($n) : (string) $n;
$format = fn (string $template, int $n) => str_replace(['{n}', '{total}'], [$digits($n), $digits($total)], $template);

$button = 'absolute top-1/2 z-10 flex size-9 -translate-y-1/2 items-center justify-center rounded-full bg-white/80 text-zinc-800 shadow-sm backdrop-blur transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent disabled:cursor-not-allowed disabled:opacity-40 dark:bg-zinc-900/70 dark:text-white dark:hover:bg-zinc-900';
@endphp

@include('mds::partials.digits')

@once('mds-carousel')
<script @mdsNonce>
window.mds = window.mds || {}

window.mds.registerCarousel = (Alpine) => {
    if (window.mds.carouselRegistered) return
    window.mds.carouselRegistered = true

    Alpine.data('mdsCarousel', (config = {}) => ({
        active: config.start ?? 0,
        total: config.total ?? 0,
        perView: Math.max(1, config.perView ?? 1),
        loop: config.loop ?? true,
        autoplay: config.autoplay ?? false,
        interval: Math.max(500, config.interval ?? 5000),
        fa: config.fa ?? true,
        labels: config.labels ?? {},
        status: '',
        playing: false,
        reduced: false,
        hovered: false,
        focused: false,
        scrolling: false,
        items: [],
        visible: null,
        observer: null,
        watcher: null,
        motion: null,
        timer: null,
        settle: null,

        init() {
            // Reduced motion disables auto-rotation outright (the user can
            // still press play) and makes every programmatic scroll instant.
            this.motion = window.matchMedia('(prefers-reduced-motion: reduce)')
            this.reduced = this.motion.matches
            this.onMotion = () => {
                this.reduced = this.motion.matches

                if (this.reduced) this.playing = false
            }
            this.motion.addEventListener('change', this.onMotion)

            this.playing = this.autoplay && ! this.reduced

            // The server derived the gap from the class name; the computed
            // value is the truth (responsive gaps, unusual classes).
            const gap = getComputedStyle(this.$refs.track).columnGap

            if (gap && gap !== 'normal') this.$refs.track.style.setProperty('--mds-carousel-gap', gap)

            this.refresh()

            // Slides arrive as slot content, and a Livewire morph or an x-for
            // can add or remove them later — re-scan when the track changes.
            this.watcher = new MutationObserver(() => this.refresh())
            this.watcher.observe(this.$refs.track, { childList: true })

            // The scroll position cannot be server-rendered: jump to `start`.
            if (this.active > 0) this.$nextTick(() => this.go(this.active, 'auto'))

            if (this.autoplay) this.timer = setInterval(() => this.tick(), this.interval)
        },

        destroy() {
            clearInterval(this.timer)
            clearTimeout(this.settle)
            this.observer?.disconnect()
            this.watcher?.disconnect()
            this.motion?.removeEventListener('change', this.onMotion)
            this.observer = this.watcher = this.motion = this.timer = this.settle = null
        },

        // Only direct children: a carousel nested inside a slide keeps its own.
        refresh() {
            this.items = [...this.$refs.track.querySelectorAll(':scope > [data-mds-carousel-item]')]
            this.total = this.items.length
            this.active = Math.min(this.active, this.last)

            // Slides cannot know their own position from inside a Blade slot.
            this.items.forEach((el, i) => el.setAttribute('aria-label', this.format(this.labels.item, i + 1)))

            this.announce()

            // Swipes, trackpad scrolls and buttons all end in the same place:
            // the active slide is the first one at least 60% inside the track.
            this.observer?.disconnect()
            this.visible = new Set()
            this.observer = new IntersectionObserver((entries) => this.observe(entries), { root: this.$refs.track, threshold: 0.6 })
            this.items.forEach(el => this.observer.observe(el))
        },

        observe(entries) {
            for (const entry of entries) {
                const i = this.items.indexOf(entry.target)

                if (i === -1) continue

                entry.isIntersecting ? this.visible.add(i) : this.visible.delete(i)
            }

            // Mid-swipe neither neighbour qualifies — keep the last answer.
            if (! this.visible.size) return

            const first = Math.min(...this.visible)

            if (first !== this.active) {
                this.active = first
                this.announce()
            }
        },

        // The last position a view can start from, and how many there are.
        get last() { return Math.max(0, this.total - this.perView) },
        get pages() { return this.last + 1 },
        get atStart() { return ! this.loop && this.active <= 0 },
        get atEnd() { return ! this.loop && this.active >= this.last },

        // scrollIntoView follows the writing direction, so RTL needs no
        // arithmetic: `start` is the right edge there and the left edge in LTR.
        go(i, behavior = null) {
            if (! this.total) return

            i = this.loop
                ? ((i % this.pages) + this.pages) % this.pages
                : Math.max(0, Math.min(i, this.last))

            const el = this.items[i]

            if (! el) return

            this.active = i
            this.announce()

            el.scrollIntoView({
                inline: 'start',
                block: 'nearest',
                behavior: behavior ?? (this.reduced ? 'auto' : 'smooth'),
            })
        },

        next() { this.go(this.active + 1) },
        prev() { this.go(this.active - 1) },

        toggle() {
            this.playing = ! this.playing
        },

        // One interval, many reasons to sit a beat out: paused by the user,
        // a pointer over the slides, focus inside them, a swipe in progress
        // or a hidden tab. A non-looping show stops at the end.
        tick() {
            if (! this.playing || this.hovered || this.focused || this.scrolling || document.hidden) return

            if (! this.loop && this.active >= this.last) {
                this.playing = false

                return
            }

            this.next()
        },

        scrolled() {
            this.scrolling = true
            clearTimeout(this.settle)
            this.settle = setTimeout(() => { this.scrolling = false }, 150)
        },

        // Horizontal arrows follow the VISUAL order: in RTL the next slide
        // sits to the left. Text fields inside a slide keep their arrows.
        keydown(event) {
            if (event.altKey || event.ctrlKey || event.metaKey) return
            if (['INPUT', 'TEXTAREA', 'SELECT'].includes(event.target.tagName) || event.target.isContentEditable) return

            const rtl = getComputedStyle(this.$root).direction === 'rtl'

            const handlers = {
                ArrowRight: () => rtl ? this.prev() : this.next(),
                ArrowLeft: () => rtl ? this.next() : this.prev(),
                Home: () => this.go(0),
                End: () => this.go(this.last),
            }

            if (! handlers[event.key]) return

            event.preventDefault()
            handlers[event.key]()
        },

        format(template, n) {
            return String(template ?? '')
                .replace('{n}', window.mds.digits(n, this.fa))
                .replace('{total}', window.mds.digits(this.total, this.fa))
        },

        // Idempotent: the live region only speaks when the text changes, so
        // go() and the observer agreeing on a slide does not announce twice.
        announce() {
            const text = this.format(this.labels.status, this.active + 1)

            if (text !== this.status) this.status = text
        },
    }))
}

// Alpine may already be running — a wire:navigate visit executes this block
// after alpine:init fired for the page — so register straight away then.
if (window.Alpine) {
    window.mds.registerCarousel(window.Alpine)
} else {
    document.addEventListener('alpine:init', () => window.mds.registerCarousel(window.Alpine))
}
</script>
@endonce

<div
    {{ $attributes->class('relative w-full') }}
    x-id="['mds-carousel-track']"
    x-data="mdsCarousel({
        start: @js($start),
        total: @js($total),
        perView: @js($perView),
        loop: @js((bool) $loop),
        autoplay: @js((bool) $autoplay),
        interval: @js($interval),
        fa: @js((bool) $fa),
        labels: @js($labels),
    })"
    x-on:keydown="keydown($event)"
    x-on:mouseenter="hovered = true"
    x-on:mouseleave="hovered = false"
    x-on:focusin="focused = true"
    x-on:focusout="focused = false"
    role="region"
    aria-roledescription="{{ $fa ? 'اسلایدشو' : 'carousel' }}"
    aria-label="{{ $label }}"
    data-mds-carousel
>
    <div class="relative">
        <div
            @class([
                'flex snap-x snap-mandatory overflow-x-auto overflow-y-hidden [scrollbar-width:none] [&::-webkit-scrollbar]:hidden motion-safe:scroll-smooth focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent',
                $gap,
                $aspect,
            ])
            style="--mds-carousel-per-view: {{ $perView }}; --mds-carousel-gap: {{ $gapLength }}"
            x-ref="track"
            x-bind:id="$id('mds-carousel-track')"
            x-on:scroll.passive="scrolled()"
            tabindex="0"
            data-mds-carousel-track
        >
            {{ $slot }}
        </div>

        @if ($controls)
            {{-- Logical placement: "previous" sits at the inline start in both
                 directions, and its chevron points there — left in LTR, right in RTL. --}}
            <button
                type="button"
                class="{{ $button }} start-2"
                x-on:click="prev()"
                x-bind:disabled="atStart"
                x-bind:aria-controls="$id('mds-carousel-track')"
                @if (! $loop && $start === 0) disabled @endif
                aria-label="{{ $fa ? 'قبلی' : 'Previous' }}"
                data-mds-carousel-prev
            >
                <mds:icon icon="chevron-left" variant="micro" class="size-5 rtl:hidden" />
                <mds:icon icon="chevron-right" variant="micro" class="size-5 ltr:hidden" />
            </button>

            <button
                type="button"
                class="{{ $button }} end-2"
                x-on:click="next()"
                x-bind:disabled="atEnd"
                x-bind:aria-controls="$id('mds-carousel-track')"
                @if (! $loop && $start >= $pages - 1) disabled @endif
                aria-label="{{ $fa ? 'بعدی' : 'Next' }}"
                data-mds-carousel-next
            >
                <mds:icon icon="chevron-right" variant="micro" class="size-5 rtl:hidden" />
                <mds:icon icon="chevron-left" variant="micro" class="size-5 ltr:hidden" />
            </button>
        @endif

        @if ($autoplay)
            {{-- The label says what pressing does next (Pause while rotating,
                 Play while stopped) — a changing label and aria-pressed must not
                 be combined, so this is not a pressed-state toggle. --}}
            <button
                type="button"
                class="absolute bottom-2 end-2 z-10 flex size-8 items-center justify-center rounded-full bg-white/80 text-zinc-800 shadow-sm backdrop-blur transition hover:bg-white focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent dark:bg-zinc-900/70 dark:text-white dark:hover:bg-zinc-900"
                x-on:click="toggle()"
                x-bind:aria-label="playing ? labels.pause : labels.play"
                aria-label="{{ $labels['pause'] }}"
                data-mds-carousel-toggle
            >
                <span x-show="playing"><mds:icon icon="pause" variant="micro" class="size-4" /></span>
                <span x-show="! playing" x-cloak><mds:icon icon="play" variant="micro" class="size-4" /></span>
            </button>
        @endif
    </div>

    @if ($indicators && $total > 0)
        <div class="mt-3 flex items-center justify-center gap-1.5" data-mds-carousel-indicators>
            @for ($i = 0; $i < $pages; $i++)
                <button
                    type="button"
                    class="flex h-6 min-w-6 items-center justify-center rounded-full px-1 focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-accent"
                    x-on:click="go({{ $i }})"
                    x-bind:aria-current="active === {{ $i }} ? 'true' : null"
                    @if ($i === $start) aria-current="true" @endif
                    aria-label="{{ $format($labels['dot'], $i + 1) }}"
                    data-mds-carousel-dot
                >
                    <span
                        @class([
                            'block h-2 rounded-full transition-all',
                            'w-5 bg-accent' => $i === $start,
                            'w-2 bg-zinc-300 dark:bg-white/30' => $i !== $start,
                        ])
                        x-bind:class="{ 'w-5 bg-accent': active === {{ $i }}, 'w-2 bg-zinc-300 dark:bg-white/30': active !== {{ $i }} }"
                    ></span>
                </button>
            @endfor
        </div>
    @endif

    {{-- Announces the slide the USER moved to. Silent while auto-rotating:
         a live region that speaks every few seconds is noise, not help. --}}
    <div
        class="sr-only"
        aria-live="{{ $autoplay ? 'off' : 'polite' }}"
        aria-atomic="true"
        x-bind:aria-live="playing ? 'off' : 'polite'"
        x-text="status"
        data-mds-carousel-status>{{ $total > 0 ? $format($labels['status'], $start + 1) : '' }}</div>
</div>
