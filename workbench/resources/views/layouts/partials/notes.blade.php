{{-- Explainer card shown at the top of every layout page. Expects $layout. --}}
<flux:card class="space-y-5">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div class="space-y-1">
            <div class="flex items-center gap-2">
                <flux:icon :icon="$layout['icon']" class="size-5 text-zinc-400" />
                <flux:heading size="lg">{{ __($layout['title']) }}</flux:heading>
            </div>
            <flux:subheading>{{ __($layout['tagline']) }}</flux:subheading>
        </div>

        <flux:badge color="zinc" size="sm" class="font-mono" dir="ltr">{{ $mdsUrl($layout['path']) }}</flux:badge>
    </div>

    <flux:separator />

    <div class="grid gap-5 lg:grid-cols-[minmax(0,1fr)_15rem]">
        <pre dir="ltr" class="overflow-x-auto rounded-xl bg-zinc-900 p-4 text-left font-mono text-xs leading-6 text-zinc-100 dark:bg-black/40"><code>{{ $layout['code'] }}</code></pre>

        <div class="space-y-2">
            @include('layouts.partials.preview', ['grid' => $layout['grid']])
            <flux:text class="text-xs">{{ __($layout['note']) }}</flux:text>
        </div>
    </div>
</flux:card>
