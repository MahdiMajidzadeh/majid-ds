<!DOCTYPE html>
<html lang="{{ $mdsLocale }}" dir="{{ $mdsDir }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', __('چیدمان‌ها')) — {{ __('مجید دیزاین سیستم') }}</title>

    @mdsFonts

    <style>{!! file_get_contents(\Orchestra\Testbench\workbench_path('public/demo.css')) !!}</style>

    @livewireStyles
    @fluxAppearance
</head>

{{--
    The element that directly wraps <flux:main> becomes the layout grid, so the
    body itself holds the header / sidebar / main / aside / footer children.
    The floating switcher is position:fixed, so it never becomes a grid item.
--}}
<body class="min-h-dvh bg-white font-sans text-zinc-800 antialiased dark:bg-zinc-900 dark:text-white">

    @yield('layout')

    @include('layouts.partials.switcher')

    <flux:toast />

    @livewireScripts
    @fluxScripts
</body>
</html>
