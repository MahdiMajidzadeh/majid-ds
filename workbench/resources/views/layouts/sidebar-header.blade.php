@extends('layouts.base')

@section('title', $layout['title'])

@section('layout')
    {{--
        Header first → the grid keeps "header header header", so the header spans
        the whole width and the sidebar starts underneath it.
    --}}
    <flux:header sticky container class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @include('layouts.partials.brand')

        <flux:navbar class="-mb-px max-lg:hidden">
            @include('layouts.partials.navbar-items')
        </flux:navbar>

        <flux:spacer />

        <div class="flex items-center gap-2">
            @include('layouts.partials.header-actions')
        </div>
    </flux:header>

    <flux:sidebar sticky class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
        @include('layouts.partials.sidebar-nav')
    </flux:sidebar>

    <flux:main class="space-y-8 pb-24">
        @include('layouts.partials.notes')
        @include('layouts.partials.content')
    </flux:main>

    <flux:footer>
        @include('layouts.partials.footer-content')
    </flux:footer>
@endsection
