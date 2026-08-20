# Using Majid DS in a Laravel app

Minimal integration example for a real Laravel 11/12 + Livewire 3 app.
(For a runnable showcase of every component, use the workbench demo instead:
`npm run demo:css && npm run demo:serve` from the package root.)

## 1. Install

```bash
composer require mahdimajidzadeh/ds
```

## 2. resources/css/app.css

```css
@import 'tailwindcss';
@import '../../vendor/livewire/flux/dist/flux.css';
@import '../../vendor/mahdimajidzadeh/ds/resources/css/mds.css';

@custom-variant dark (&:where(.dark, .dark *));
```

## 3. resources/views/components/layouts/app.blade.php

```blade
<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    @mdsFonts
    @vite('resources/css/app.css')
    @fluxAppearance
</head>
<body class="min-h-screen bg-zinc-50 font-sans antialiased dark:bg-zinc-900">
    {{ $slot }}

    @fluxScripts
</body>
</html>
```

## 4. A product page fragment

```blade
<div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
    @foreach ($products as $product)
        <mds:product-card
            :title="$product->title"
            :image="$product->image_url"
            :amount="$product->price"
            :original="$product->compare_at_price"
            :rating="$product->rating"
            :reviews="$product->reviews_count"
            :href="route('products.show', $product)"
            :unavailable="! $product->in_stock"
        >
            <flux:button
                variant="primary" size="sm" class="w-full"
                wire:click="addToCart({{ $product->id }})"
            >افزودن به سبد</flux:button>
        </mds:product-card>
    @endforeach
</div>
```

## 5. A cart line with Livewire binding

```blade
<div class="flex items-center justify-between gap-4">
    <mds:quantity wire:model.live="items.{{ $item->id }}.qty" :min="1" :max="$item->stock" />
    <mds:price :amount="$item->total" />
</div>

<mds:stepper :steps="['سبد خرید', 'آدرس و زمان ارسال', 'پرداخت', 'تأیید نهایی']" :current="1" class="w-full" />
```
