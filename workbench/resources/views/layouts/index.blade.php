<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>چیدمان‌ها — مجید دیزاین سیستم</title>

    @mdsFonts

    <style>{!! file_get_contents(\Orchestra\Testbench\workbench_path('public/demo.css')) !!}</style>

    @livewireStyles
    @fluxAppearance
</head>

{{-- No flux:main here, so the body stays a normal flow container, not the layout grid. --}}
<body class="min-h-dvh bg-zinc-50 font-sans text-zinc-800 antialiased dark:bg-zinc-900 dark:text-white">

    <div class="mx-auto max-w-6xl space-y-10 px-6 py-12">

        <header class="flex flex-wrap items-center justify-between gap-4">
            <flux:brand href="/layouts" name="مجید دیزاین سیستم">
                <x-slot name="logo">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-accent text-sm font-bold text-accent-foreground">م</div>
                </x-slot>
            </flux:brand>

            <div class="flex items-center gap-2">
                <flux:button href="/demo" size="sm" variant="filled" icon="swatch">نمایشگاه اجزا</flux:button>

                <flux:tooltip content="حالت تاریک / روشن">
                    <flux:button variant="subtle" icon="moon" square aria-label="حالت تاریک" x-data x-on:click="$flux.dark = ! $flux.dark" />
                </flux:tooltip>
            </div>
        </header>

        <div class="space-y-3">
            <flux:heading size="xl">چیدمان‌های صفحه</flux:heading>
            <flux:text>
                همه‌ی حالت‌هایی که شبکه‌ی چیدمان Flux پشتیبانی می‌کند، راست‌چین و با ناوبری فارسی. عنصری که مستقیماً
                <code dir="ltr">flux:main</code> را در بر می‌گیرد به یک شبکه‌ی ۳×۳ با نواحی
                <code dir="ltr">header / sidebar / main / aside / footer</code> تبدیل می‌شود؛ ترتیب فرزندان است که
                چیدمان را تعیین می‌کند.
            </flux:text>
        </div>

        <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($mdsLayouts as $item)
                <a href="{{ $item['url'] }}" class="group block">
                    <flux:card class="h-full space-y-4 transition group-hover:border-accent/50 group-hover:shadow-md">
                        @include('layouts.partials.preview', ['grid' => $item['grid'], 'height' => 'h-32'])

                        <div class="space-y-1">
                            <div class="flex items-center gap-2">
                                <flux:icon :icon="$item['icon']" class="size-4 text-zinc-400" />
                                <flux:heading>{{ $item['title'] }}</flux:heading>
                            </div>

                            <flux:text class="text-sm">{{ $item['tagline'] }}</flux:text>
                        </div>

                        <div class="flex items-center justify-between">
                            <flux:badge size="sm" color="zinc" dir="ltr" class="font-mono">{{ $item['url'] }}</flux:badge>
                            <flux:icon.arrow-left class="size-4 text-zinc-400 transition group-hover:-translate-x-1" />
                        </div>
                    </flux:card>
                </a>
            @endforeach
        </div>

        <flux:card class="space-y-4">
            <flux:heading size="lg">قاعده‌ی کار</flux:heading>

            <div class="grid gap-4 lg:grid-cols-2">
                <flux:text class="text-sm">
                    نواحی شبکه از خود <code dir="ltr">flux.css</code> می‌آیند و با ترتیب عناصر عوض می‌شوند: اگر
                    <code dir="ltr">flux:sidebar</code> پیش از <code dir="ltr">flux:header</code> بیاید، سایدبار تمام
                    ارتفاع را می‌گیرد؛ اگر هدر اول بیاید، تمام عرض را می‌گیرد. در حالت راست‌چین هم سایدبار به‌طور خودکار
                    سمت راست می‌نشیند، چون Flux از ویژگی‌های منطقی
                    (<code dir="ltr">start</code> / <code dir="ltr">end</code>) استفاده می‌کند.
                </flux:text>

                <pre dir="ltr" class="overflow-x-auto rounded-xl bg-zinc-900 p-4 text-left font-mono text-xs leading-6 text-zinc-100 dark:bg-black/40"><code>grid-template-areas:
    "header  header  header"
    "sidebar main    aside"
    "sidebar footer  aside";

/* sidebar before header: */
    "sidebar header  header"
    "sidebar main    aside"
    "sidebar footer  aside";</code></pre>
            </div>
        </flux:card>

        <footer class="pb-4 text-center">
            <flux:text class="text-sm">مجید دیزاین سیستم — ساخته‌شده روی Flux UI و Tailwind CSS</flux:text>
        </footer>
    </div>

    @livewireScripts
    @fluxScripts
</body>
</html>
