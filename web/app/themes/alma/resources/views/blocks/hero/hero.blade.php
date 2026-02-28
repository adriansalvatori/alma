<section class="relative w-full overflow-hidden {{ $attributes['align'] ?? '' }} py-16 lg:py-24">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

            {{-- Left Column: Content --}}
            <div class="flex flex-col items-start text-left space-y-6">

                {{-- Badge --}}
                @if (!empty($attributes['badge']))
                    <div
                        class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                        {{ $attributes['badge'] }}
                        <flux:icon.arrow-right class="w-4 h-4 ml-2 text-zinc-500" />
                    </div>
                @endif

                {{-- Heading --}}
                <h1
                    class="text-4xl sm:text-5xl lg:text-6xl font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                    {{ $attributes['title'] ?? 'High Converting Heading Comes Here' }}
                </h1>

                {{-- Subtitle --}}
                <p class="text-lg text-zinc-500 dark:text-zinc-400 max-w-xl">
                    {{ $attributes['subtitle'] ?? 'Use a clear headline, value prop, and app store buttons — give them a reason to scroll or download right away.' }}
                </p>

                {{-- Buttons --}}
                <div class="flex flex-wrap items-center gap-4 pt-2">
                    @if (!empty(trim($content ?? '')))
                        {!! $content !!}
                    @else
                        <flux:button variant="primary" icon="apple">
                            {{ $attributes['primaryButtonText'] ?? 'Download App' }}
                        </flux:button>
                        <flux:button variant="primary" icon="play">
                            {{ $attributes['secondaryButtonText'] ?? 'Download App' }}
                        </flux:button>
                    @endif
                </div>

                {{-- Social Proof / Downloads --}}
                <div class="flex items-center space-x-4 pt-4">
                    <div class="flex -space-x-2">
                        <img class="w-8 h-8 rounded-full border-2 border-white dark:border-zinc-900"
                            src="https://i.pravatar.cc/100?img=1" alt="User">
                        <img class="w-8 h-8 rounded-full border-2 border-white dark:border-zinc-900"
                            src="https://i.pravatar.cc/100?img=2" alt="User">
                        <img class="w-8 h-8 rounded-full border-2 border-white dark:border-zinc-900"
                            src="https://i.pravatar.cc/100?img=3" alt="User">
                        <div
                            class="w-8 h-8 rounded-full border-2 border-white dark:border-zinc-900 bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center text-xs font-medium text-zinc-600 dark:text-zinc-300">
                            +
                        </div>
                    </div>
                    <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                        {{ $attributes['downloadsText'] ?? '200K+ Downloads' }}
                    </span>
                </div>
            </div>

            {{-- Right Column: Image/Mockup Placeholder --}}
            <div
                class="relative w-full h-[500px] lg:h-[600px] flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 rounded-3xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
                @if (!empty($attributes['imageUrl']))
                    <img src="{{ $attributes['imageUrl'] }}" alt="App Mockup" class="w-full h-full object-cover">
                @else
                    <div class="text-zinc-400 flex flex-col items-center">
                        <flux:icon.device-phone-mobile class="w-24 h-24 mb-4 text-zinc-300 dark:text-zinc-600" />
                        <span class="text-sm font-medium">App Mockup Placeholder</span>
                    </div>
                @endif
            </div>

        </div>
    </div>
</section>
