@extends('layouts.base')

@section('title', __($layout['title']))

@section('layout')
    {{--
        Width control. The "container" prop on header / main / footer centres the
        contents at max-w-7xl, and flux:container does the same for any full-bleed
        section you build by hand.
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

    <flux:main container class="space-y-8 pb-24">
        @include('layouts.partials.notes')

        <div class="grid gap-4 lg:grid-cols-3">
            <flux:card class="space-y-2">
                <flux:badge size="sm" color="lime" dir="ltr" class="font-mono">container</flux:badge>
                <flux:heading>{{ __('ناحیه محدودشده') }}</flux:heading>
                <flux:text class="text-sm">
                {!! __('prop روی <code dir="ltr">flux:header</code>، <code dir="ltr">flux:main</code> و <code dir="ltr">flux:footer</code>؛ محتوا را در <code dir="ltr">max-w-7xl</code> وسط‌چین می‌کند. همین صفحه از آن استفاده می‌کند.') !!}
                </flux:text>
            </flux:card>

            <flux:card class="space-y-2">
                <flux:badge size="sm" color="blue" dir="ltr" class="font-mono">flux:container</flux:badge>
                <flux:heading>{{ __('محدودسازی دستی') }}</flux:heading>
                <flux:text class="text-sm">
                {{ __('برای بخش‌هایی که پس‌زمینه‌شان تمام‌عرض است ولی محتوایشان باید وسط بماند — مثل نوار سبز پایین همین صفحه.') }}
                </flux:text>
            </flux:card>

            <flux:card class="space-y-2">
                <flux:badge size="sm" color="zinc" dir="ltr" class="font-mono">—</flux:badge>
                <flux:heading>{{ __('تمام‌عرض') }}</flux:heading>
                <flux:text class="text-sm">
                {{ __('حالت پیش‌فرض: ناحیه فقط padding می‌گیرد و تا لبه‌های صفحه کشیده می‌شود؛ همان چیزی که چیدمان‌های سایدباری استفاده می‌کنند.') }}
                </flux:text>
            </flux:card>
        </div>

        @include('layouts.partials.content', ['showTable' => false])
    </flux:main>

    {{--
        The footer keeps its own padding off (p-0!) so the band inside it can run
        edge to edge while flux:container re-centres both blocks of content.
    --}}
    <flux:footer class="p-0!">
        <div class="border-y border-lime-200 bg-lime-50 py-10 dark:border-lime-400/30 dark:bg-lime-400/10">
            <flux:container class="flex flex-wrap items-center justify-between gap-6">
                <div class="space-y-1">
                    <flux:heading size="lg">{{ __('عضویت در خبرنامه') }}</flux:heading>
                    <flux:subheading>{!! __('این نوار تمام‌عرض است، ولی محتوایش با <code dir="ltr">flux:container</code> وسط نگه داشته شده.') !!}</flux:subheading>
                </div>

                <div class="flex w-full max-w-sm items-center gap-2">
                    <flux:input :placeholder="__('ایمیل شما')" icon="envelope" class="flex-1" />
                    <flux:button variant="primary">{{ __('عضویت') }}</flux:button>
                </div>
            </flux:container>
        </div>

        <flux:container class="py-6">
            @include('layouts.partials.footer-content')
        </flux:container>
    </flux:footer>
@endsection
