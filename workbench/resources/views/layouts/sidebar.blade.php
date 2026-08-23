@extends('layouts.base')

@section('title', __($layout['title']))

@section('layout')
    {{--
        Sidebar first → grid areas become "sidebar header header", so the sidebar
        owns the full height and the header starts next to it.
    --}}
    <flux:sidebar sticky class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        <flux:sidebar.header>
            @include('layouts.partials.sidebar-brand')
        </flux:sidebar.header>

        @include('layouts.partials.sidebar-nav')
    </flux:sidebar>

    <flux:header sticky class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        <flux:breadcrumbs>
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
        @include('layouts.partials.content')
    </flux:main>

    <flux:footer>
        @include('layouts.partials.footer-content')
    </flux:footer>
@endsection
