{{-- Footer body (flux:footer). --}}
<div class="flex flex-wrap items-center justify-between gap-4 border-t border-zinc-200 pt-6 dark:border-zinc-700">
    <flux:text class="text-sm">{{ __('مجید دیزاین سیستم — ساخته‌شده روی Flux UI و Tailwind CSS') }}</flux:text>

    <div class="flex items-center gap-4">
        <flux:link href="#" class="text-sm">{{ __('قوانین و مقررات') }}</flux:link>
        <flux:link href="#" class="text-sm">{{ __('حریم خصوصی') }}</flux:link>
        <flux:link :href="$mdsUrl('/demo')" class="text-sm">{{ __('نمایشگاه اجزا') }}</flux:link>
    </div>
</div>
