@extends('layouts.base')

@section('title', __($layout['title']))

@section('layout')
    {{--
        collapsible="mobile" keeps the sidebar in the grid on desktop and turns it
        into an off-canvas panel below lg. flux:sidebar.toggle opens it; the
        backdrop is rendered by flux:sidebar automatically.
    --}}
    <flux:sidebar sticky collapsible="mobile" class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            @include('layouts.partials.sidebar-brand')

            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" aria-label="{{ __('بستن منو') }}" />
        </flux:sidebar.header>

        @include('layouts.partials.sidebar-nav')
    </flux:sidebar>

    <flux:header sticky class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.toggle class="lg:hidden" icon="bars-2" aria-label="{{ __('نمایش منو') }}" />

        <flux:breadcrumbs class="max-lg:hidden">
            <flux:breadcrumbs.item :href="$mdsUrl('/layouts')">{{ __('چیدمان‌ها') }}</flux:breadcrumbs.item>
            <flux:breadcrumbs.item>{{ __($layout['title']) }}</flux:breadcrumbs.item>
        </flux:breadcrumbs>

        <flux:spacer />

        <div class="flex items-center gap-2">
            @include('layouts.partials.header-actions')
        </div>
    </flux:header>

    <flux:main class="space-y-8 pb-24">
        @include('layouts.partials.notes')

        <flux:callout icon="device-phone-mobile" :heading="__('امتحان کنید')" variant="secondary">
            <flux:callout.text>
                {{ __('پنجره را باریک‌تر از ۱۰۲۴ پیکسل کنید تا سایدبار پنهان شود، سپس دکمه‌ی همبرگری هدر را بزنید؛ پنل از سمت راست باز می‌شود.') }}
            </flux:callout.text>
        </flux:callout>

        @include('layouts.partials.content')
    </flux:main>

    <flux:footer>
        @include('layouts.partials.footer-content')
    </flux:footer>
@endsection
