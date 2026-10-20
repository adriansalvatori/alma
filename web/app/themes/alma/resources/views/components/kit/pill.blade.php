{{-- Pill link: the kit's button. Variants: solid, brand, outline. Sizes: md, lg. Follows wire:navigate when it's on. --}}
@props(['href', 'variant' => 'solid', 'size' => 'md', 'arrow' => true])

@php
    $styles = match ($variant) {
        'brand' => 'bg-brand-500 text-white hover:bg-brand-600',
        'outline' => 'border border-current hover:bg-zinc-950 hover:text-white hover:border-zinc-950 dark:hover:bg-white dark:hover:text-zinc-950 dark:hover:border-white',
        default => 'bg-zinc-950 text-white hover:bg-brand-500 dark:bg-white dark:text-zinc-950 dark:hover:bg-brand-500 dark:hover:text-white',
    };
@endphp

<a href="{{ $href }}" @navigate {{ $attributes->class([
    'type-eyebrow inline-flex items-center justify-center gap-3 rounded-full transition-colors duration-300',
    $size === 'lg' ? 'px-8 py-5 md:px-10 md:py-6' : 'px-5 py-3',
    $styles,
]) }}>{{ $slot }}@if ($arrow)<span aria-hidden="true">→</span>@endif</a>
