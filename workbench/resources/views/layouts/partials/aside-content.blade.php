{{-- Body of the flux:aside column — an order summary, the classic use for it. --}}
@php
$pad = $pad ?? 'p-6';
@endphp

<div class="space-y-6 {{ $pad }}">
    <div class="space-y-1">
        <flux:heading>{{ __('خلاصه سبد خرید') }}</flux:heading>
        <flux:subheading class="text-xs">{{ __('۳ کالا در سبد شما') }}</flux:subheading>
    </div>

    <mds:stepper :steps="[__('سبد خرید'), __('ارسال'), __('پرداخت')]" :current="2" class="w-full" />

    <flux:separator />

    <div class="space-y-4">
        @foreach ([['seed' => 'phone', 'title' => __('گوشی Galaxy S25'), 'amount' => 42500000], ['seed' => 'headphone', 'title' => __('هدفون AirSound Pro'), 'amount' => 1890000]] as $line)
            <div class="flex items-start gap-3">
                <flux:avatar size="sm" src="https://picsum.photos/seed/{{ $line['seed'] }}/48/48" />

                <div class="min-w-0 flex-1 space-y-1">
                    <flux:text class="truncate text-sm">{{ $line['title'] }}</flux:text>
                    <mds:price :amount="$line['amount']" size="sm" />
                </div>

                <mds:quantity :value="1" :min="1" :max="3" size="sm" />
            </div>
        @endforeach
    </div>

    <flux:separator />

    <div class="space-y-2 text-sm">
        <div class="flex items-center justify-between">
            <flux:text>{{ __('جمع کالاها') }}</flux:text>
            <mds:price :amount="44390000" size="sm" :badge="false" />
        </div>
        <div class="flex items-center justify-between">
            <flux:text>{{ __('تخفیف') }}</flux:text>
            <mds:discount-badge :percent="13" size="sm" />
        </div>
        <div class="flex items-center justify-between">
            <flux:text>{{ __('هزینه ارسال') }}</flux:text>
            <flux:badge size="sm" color="green">{{ __('رایگان') }}</flux:badge>
        </div>
    </div>

    <flux:separator />

    <div class="flex items-center justify-between">
        <flux:text class="font-medium">{{ __('مبلغ قابل پرداخت') }}</flux:text>
        <mds:price :amount="38600000" />
    </div>

    <flux:button variant="primary" class="w-full" icon:trailing="{{ $mdsForward }}">{{ __('تأیید و پرداخت') }}</flux:button>

    <flux:callout icon="shield-check" variant="secondary">
        <flux:callout.text class="text-xs">{{ __('۷ روز ضمانت بازگشت کالا و پرداخت در محل برای سفارش‌های تهران.') }}</flux:callout.text>
    </flux:callout>
</div>
