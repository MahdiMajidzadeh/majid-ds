{{-- Top navigation (flux:navbar) — used by the header-only and full-width-header layouts. --}}
<flux:navbar.item :href="$mdsUrl('/layouts/header')" icon="home">{{ __('خانه') }}</flux:navbar.item>
<flux:navbar.item href="#" icon="fire">{{ __('پیشنهاد شگفت‌انگیز') }}</flux:navbar.item>
<flux:navbar.item href="#" icon="squares-2x2">{{ __('دسته‌بندی‌ها') }}</flux:navbar.item>
<flux:navbar.item href="#" icon="shopping-bag" :badge="$mdsNum(3)">{{ __('سفارش‌ها') }}</flux:navbar.item>
<flux:navbar.item href="#" icon="chat-bubble-left-right">{{ __('پشتیبانی') }}</flux:navbar.item>
