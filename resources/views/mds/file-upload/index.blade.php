@props([
    'name' => null,
    'multiple' => false,
    'accept' => null,
    'label' => null,
    'description' => null,
    'error' => null,
    'invalid' => false,
    'disabled' => false,
    'fa' => null,
])

@php
$fa ??= config('mds.persian_digits', true);

// An explicit :error wins; otherwise fall back to the validation bag for this name...
if (blank($error) && $name && isset($errors)) {
    $error = $errors->first($name) ?: $errors->first($name.'.*') ?: null;
}

$invalid = $invalid || filled($error);
@endphp

@include('mds::partials.digits')

@once
<script>
document.addEventListener('alpine:init', () => {
    Alpine.data('mdsFileUpload', (config = {}) => ({
        dragging: false,
        loading: false,
        focused: false,
        progress: 0,
        disabled: config.disabled ?? false,
        fa: config.fa ?? true,

        init() {
            const input = this.$refs.input

            // Livewire dispatches these on the input bound with wire:model...
            input.addEventListener('livewire-upload-start', () => { this.loading = true; this.progress = 0 })
            input.addEventListener('livewire-upload-progress', (e) => { this.progress = e.detail.progress })
            input.addEventListener('livewire-upload-finish', () => { this.loading = false; this.progress = 100 })
            input.addEventListener('livewire-upload-cancel', () => { this.loading = false; this.progress = 0 })
            input.addEventListener('livewire-upload-error', () => { this.loading = false; this.progress = 0 })
        },

        // Display percent — Persian digits and ٪ when fa is on...
        get percent() {
            const n = String(Math.round(this.progress))

            return window.mds.digits(n, this.fa) + (this.fa ? '٪' : '%')
        },

        // Exposed as CSS custom properties for custom progress UIs...
        get styles() {
            return {
                '--mds-file-upload-progress': Math.round(this.progress) + '%',
                '--mds-file-upload-progress-as-string': "'" + this.percent + "'",
            }
        },

        enter() {
            if (! this.disabled) this.dragging = true
        },

        leave(event) {
            if (! this.$root.contains(event.relatedTarget)) this.dragging = false
        },

        drop(event) {
            this.dragging = false

            if (this.disabled) return

            const input = this.$refs.input
            const dropped = [...(event.dataTransfer?.files ?? [])]

            if (! dropped.length) return

            // Assigning a DataTransfer list is the only way to write input.files...
            const transfer = new DataTransfer()

            ;(input.multiple ? dropped : dropped.slice(0, 1)).forEach(file => transfer.items.add(file))

            input.files = transfer.files
            input.dispatchEvent(new Event('change', { bubbles: true }))
            input.dispatchEvent(new Event('input', { bubbles: true }))
        },
    }))
})
</script>
@endonce

<flux:field>
    @if ($label)
        <flux:label>{{ $label }}</flux:label>
    @endif

    <div
        {{ $attributes->whereDoesntStartWith('wire:model')->class('relative') }}
        x-data="mdsFileUpload({ disabled: @js((bool) $disabled), fa: @js((bool) $fa) })"
        x-bind:style="styles"
        x-bind:data-dragging="dragging ? '' : false"
        x-bind:data-loading="loading ? '' : false"
        x-on:dragenter.prevent="enter()"
        x-on:dragover.prevent="enter()"
        x-on:dragleave="leave($event)"
        x-on:drop.prevent="drop($event)"
        x-on:focusin="focused = $event.target.matches(':focus-visible')"
        x-on:focusout="focused = false"
        data-mds-file-upload
    >
        <label @class(['block', $disabled ? 'cursor-not-allowed opacity-50' : 'cursor-pointer'])>
            <input
                type="file"
                class="sr-only"
                x-ref="input"
                @if ($name) name="{{ $name }}{{ $multiple ? '[]' : '' }}" @endif
                @if ($multiple) multiple @endif
                @if ($accept) accept="{{ $accept }}" @endif
                @if ($disabled) disabled @endif
                @if ($invalid) aria-invalid="true" @endif
                data-flux-control
                {{ $attributes->whereStartsWith('wire:model') }}
            >

            {{ $slot }}
        </label>
    </div>

    @if ($description)
        <flux:description>{{ $description }}</flux:description>
    @endif

    @if (filled($error))
        {{-- Same markup as flux:error, without its dependency on the session error bag... --}}
        <div role="alert" aria-live="polite" aria-atomic="true" class="mt-3 text-sm font-medium text-red-500 dark:text-red-400" data-flux-error>
            <mds:icon icon="exclamation-triangle" variant="mini" class="inline size-4" />
            {{ $error }}
        </div>
    @endif
</flux:field>
