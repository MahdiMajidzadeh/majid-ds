@extends('layouts.base')

@section('title', $layout['title'])

@section('layout')
    {{--
        flux:aside claims the third column of the grid, next to main and footer.
        Give it a width with a class; it is hidden below xl here to keep the
        content column readable on narrow screens.
    --}}
    <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            @include('layouts.partials.sidebar-brand')

            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" aria-label="بستن منو" />
        </flux:sidebar.header>

        @include('layouts.partials.sidebar-nav')
    </flux:sidebar>

    <flux:header sticky class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" aria-label="نمایش منو" />

        <flux:breadcrumbs class="max-lg:hidden">
            <flux:breadcrumbs.item href="/layouts">چیدمان‌ها</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ $layout['title'] }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:spacer />

        <div class="flex items-center gap-2">
            @include('layouts.partials.header-actions')

            <flux:modal.trigger name="cart">
                <flux:button size="sm" variant="primary" icon="shopping-cart" class="xl:hidden">سبد خرید</flux:button>
            </flux:modal.trigger>
        </div>
    </flux:header>

    <flux:main class="space-y-8 pb-24">
        @include('layouts.partials.notes')
        @include('layouts.partials.content', ['showStats' => false])
    </flux:main>

    <flux:aside sticky class="w-80 border-s border-zinc-200 bg-zinc-50 max-xl:hidden dark:border-zinc-700 dark:bg-zinc-900">
        @include('layouts.partials.aside-content')
    </flux:aside>

    <flux:footer>
        @include('layouts.partials.footer-content')
    </flux:footer>

    {{-- Below xl the aside is hidden, so the same content moves into a flyout. --}}
    <flux:modal name="cart" variant="flyout" class="w-80">
        @include('layouts.partials.aside-content', ['pad' => ''])
    </flux:modal>
@endsection
