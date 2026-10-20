{{-- Arrow link: an arrow slides in from the left on hover (kit.css). Follows wire:navigate when it's on. --}}
@props(['href'])

<a href="{{ $href }}" @navigate {{ $attributes->class(['kit-arrow-link']) }}>
    <svg class="kit-arrow" viewBox="0 0 45 45" fill="none" aria-hidden="true">
        <path d="M12.85 31.8 31.18 13.47M12.85 13.47h18.33V31.8" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
    </svg>
    <span>{{ $slot }}</span>
</a>
