@extends('layouts.base')

@section('title', __($layout['title']))

@section('layout')
    {{--
        Header layout: no sidebar at all. The header is sticky and contained,
        the navbar carries the sections, and a navmenu takes over on mobile.
    --}}
    <flux:header sticky container class="border-b border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900">
        @include('layouts.partials.brand')

        <flux:navbar class="-mb-px max-lg:hidden">
            @include('layouts.partials.navbar-items')
        </flux:navbar>

        <flux:spacer />

        <div class="flex items-center gap-2">
            @include('layouts.partials.header-actions')

            <flux:dropdown position="bottom" align="end" class="lg:hidden">
                <flux:button variant="subtle" icon="bars-2" square aria-label="{{ __('منوی اصلی') }}" />

                <flux:navmenu>
                    @include('layouts.partials.navmenu-items')
                </flux:navmenu>
            </flux:dropdown>
        </div>
    </flux:header>

    <flux:main container class="space-y-8 pb-24">
        @include('layouts.partials.notes')
        @include('layouts.partials.content')
    </flux:main>

    <flux:footer container>
        @include('layouts.partials.footer-content')
    </flux:footer>
@endsection
