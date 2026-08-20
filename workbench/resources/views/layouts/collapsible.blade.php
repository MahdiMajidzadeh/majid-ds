@extends('layouts.base')

@section('title', $layout['title'])

@section('layout')
    {{--
        collapsible (without "mobile") makes the sidebar collapse to an icon rail
        on desktop. flux:sidebar.collapse is the toggle; sidebar items fall back
        to their tooltips once the labels are hidden.
    --}}
    <flux:sidebar sticky collapsible class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            @include('layouts.partials.sidebar-brand')

            <flux:sidebar.collapse tooltip="جمع/باز کردن سایدبار" />
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
        </div>
    </flux:header>

    <flux:main class="space-y-8 pb-24">
        @include('layouts.partials.notes')

        <flux:callout icon="cursor-arrow-rays" heading="امتحان کنید">
            <flux:callout.text>
                دکمه‌ی جمع‌کردن کنار لوگو را بزنید: سایدبار به یک ریل آیکونی تبدیل می‌شود و برچسب‌ها جای خود را به تولتیپ می‌دهند.
            </flux:callout.text>
        </flux:callout>

        @include('layouts.partials.content')
    </flux:main>

    <flux:footer>
        @include('layouts.partials.footer-content')
    </flux:footer>
@endsection
