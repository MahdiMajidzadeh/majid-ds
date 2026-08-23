{{-- Sidebar brand (flux:sidebar.brand) — collapses to the logo mark on a collapsed sidebar. --}}
<flux:sidebar.brand :href="$mdsUrl('/layouts')" :name="__('مجید دیزاین سیستم')">
    <x-slot name="logo">
        <div class="flex size-7 shrink-0 items-center justify-center rounded-lg bg-accent text-sm font-bold text-accent-foreground">{{ __('م') }}</div>
    </x-slot>
</flux:sidebar.brand>
