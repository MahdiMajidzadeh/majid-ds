<!DOCTYPE html>
<html lang="{{ $mdsLocale }}" dir="{{ $mdsDir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('مجید — کیت رابط کاربری') }}</title>

    @mdsFonts

    <style>{!! file_get_contents(\Orchestra\Testbench\workbench_path('public/demo.css')) !!}</style>

    @livewireStyles
    @fluxAppearance
</head>

<body class="min-h-screen bg-zinc-50 font-sans text-zinc-800 antialiased dark:bg-zinc-900 dark:text-white">

    <div class="mx-auto max-w-5xl space-y-10 px-6 py-10" x-data>

        {{-- ============================== Header ============================== --}}
        <header class="flex items-center justify-between gap-4">
            <flux:brand href="#" :name="__('مجید دیزاین سیستم')">
                <x-slot name="logo">
                    <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-accent text-sm font-bold text-accent-foreground">{{ __('م') }}</div>
                </x-slot>
            </flux:brand>

            <div class="flex items-center gap-3">
                <flux:button :href="$mdsDocsHref" size="sm" variant="ghost" icon="book-open">{{ __('مستندات') }}</flux:button>

                <flux:button :href="$mdsUrl('/layouts')" size="sm" variant="filled" icon="squares-2x2">{{ __('چیدمان‌های صفحه') }}</flux:button>

                <flux:button :href="$mdsAlt('/demo')" size="sm" variant="ghost" icon="language">{{ $mdsAltLabel }}</flux:button>

                <flux:profile avatar="https://picsum.photos/seed/user/64/64" :name="__('مهدی مجیدزاده')" />

                <flux:tooltip :content="__('حالت تاریک / روشن')">
                    <flux:button variant="subtle" icon="moon" aria-label="{{ __('حالت تاریک') }}" x-on:click="$flux.dark = ! $flux.dark" />
                </flux:tooltip>
            </div>
        </header>

        <flux:text>{{ __('نمایشگاه کامل اجزا: همه اجزای رایگان Flux UI به‌علاوه لایه راست‌چین و فارسی‌محور mds.') }}</flux:text>

        @include('demo.cards')

        <footer class="pb-8 text-center">
            <flux:text class="text-sm">{{ __('مجید دیزاین سیستم — ساخته‌شده روی Flux UI و Tailwind CSS') }}</flux:text>
        </footer>
    </div>

    <flux:toast />

    @livewireScripts
    @fluxScripts
</body>
</html>
