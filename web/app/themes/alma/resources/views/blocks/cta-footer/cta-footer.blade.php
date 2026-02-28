<section class="py-16 {{ $attributes['align'] ?? '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div
            class="bg-zinc-100 dark:bg-zinc-800 rounded-3xl overflow-hidden shadow-sm border border-zinc-200 dark:border-zinc-700">
            <div class="grid grid-cols-1 lg:grid-cols-2">

                {{-- Left Side: CTA Content --}}
                <div class="p-10 sm:p-16 flex flex-col justify-center text-center lg:text-left">
                    <h2
                        class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl lg:text-5xl mb-6">
                        {{ $attributes['title'] ?? 'CTA Heading' }}
                    </h2>
                    <p class="text-lg text-zinc-600 dark:text-zinc-400 mb-8 max-w-xl mx-auto lg:mx-0">
                        {{ $attributes['description'] ?? 'Reinforce the download offer, repeat your app\'s value, and include the app buttons again for one final push.' }}
                    </p>

                    {{-- Buttons --}}
                    <div class="flex flex-wrap items-center justify-center lg:justify-start gap-4">
                        <flux:button variant="primary" icon="apple">
                            {{ $attributes['primaryButtonText'] ?? 'Download App' }}
                        </flux:button>
                        <flux:button variant="primary" icon="play">
                            {{ $attributes['secondaryButtonText'] ?? 'Download App' }}
                        </flux:button>
                    </div>
                </div>

                {{-- Right Side: Illustration Placeholder --}}
                <div class="hidden lg:flex items-center justify-center bg-zinc-200 dark:bg-zinc-700 p-12">
                    @if (!empty($attributes['imageUrl']))
                        <img src="{{ $attributes['imageUrl'] }}" alt="CTA Illustration"
                            class="w-full h-auto max-h-96 object-contain">
                    @else
                        <div class="text-zinc-400 dark:text-zinc-500 flex flex-col items-center">
                            <flux:icon.device-phone-mobile class="w-32 h-32 mb-4" />
                            <span class="text-sm font-medium">Phone Mockup or Illustration</span>
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>
</section>
