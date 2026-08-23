@extends('layouts.base')

@section('title', __($layout['title']))

@section('layout')
    {{--
        "sticky" on the header, sidebar and aside pins each area and caps its
        height to the viewport, so only main scrolls. The content below repeats
        on purpose — there needs to be something to scroll.
    --}}
    <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            @include('layouts.partials.sidebar-brand')

            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" aria-label="{{ __('بستن منو') }}" />
        </flux:sidebar.header>

        @include('layouts.partials.sidebar-nav')
    </flux:sidebar>

    <flux:header sticky class="border-b border-zinc-200 bg-white/80 backdrop-blur-sm dark:border-zinc-700 dark:bg-zinc-900/80">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" aria-label="{{ __('نمایش منو') }}" />

        <flux:breadcrumbs class="max-lg:hidden">
            <flux:breadcrumbs.item :href="$mdsUrl('/layouts')">{{ __('چیدمان‌ها') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __($layout['title']) }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:spacer />

        <div class="flex items-center gap-2">
            <flux:badge size="sm" color="lime" icon="arrow-down">{{ __('اسکرول کنید') }}</flux:badge>

            @include('layouts.partials.header-actions')
        </div>
    </flux:header>

    <flux:main class="space-y-8 pb-24">
        @include('layouts.partials.notes')
        @include('layouts.partials.content', ['sections' => 4, 'showTable' => true])
    </flux:main>

    <flux:aside sticky class="w-72 border-s border-zinc-200 bg-zinc-50 max-xl:hidden dark:border-zinc-700 dark:bg-zinc-900">
        <div class="space-y-4 p-6">
            <flux:heading>{{ __('در این صفحه') }}</flux:heading>

            <flux:navlist>
                <flux:navlist.item href="#" icon="chart-bar">{{ __('آمار امروز') }}</flux:navlist.item>
                <flux:navlist.item href="#" icon="table-cells">{{ __('آخرین سفارش‌ها') }}</flux:navlist.item>
                <flux:navlist.item href="#" icon="fire">{{ __('پیشنهاد شگفت‌انگیز') }}</flux:navlist.item>
                <flux:navlist.item href="#" icon="squares-2x2">{{ __('ادامه پیشنهادها') }}</flux:navlist.item>
            </flux:navlist>

            <flux:separator />

            <flux:text class="text-xs">
                {{ __('این ستون با prop «sticky» چسبیده است؛ ارتفاعش به ارتفاع دید محدود می‌شود و در صورت نیاز خودش اسکرول می‌گیرد.') }}
            </flux:text>
        </div>
    </flux:aside>

    <flux:footer>
        @include('layouts.partials.footer-content')
    </flux:footer>
@endsection
