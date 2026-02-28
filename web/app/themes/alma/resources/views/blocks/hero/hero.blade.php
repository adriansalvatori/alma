<div class="relative bg-white dark:bg-zinc-900 overflow-hidden {{ $attributes['align'] ?? '' }}">
    <div class="max-w-7xl mx-auto">
        <div
            class="relative z-10 pb-8 bg-white dark:bg-zinc-900 sm:pb-16 md:pb-20 lg:max-w-2xl lg:w-full lg:pb-28 xl:pb-32">
            <main class="mt-10 mx-auto max-w-7xl px-4 sm:mt-12 sm:px-6 md:mt-16 lg:mt-20 lg:px-8 xl:mt-28">
                <div class="sm:text-center lg:text-left">
                    <h1
                        class="text-4xl tracking-tight font-extrabold text-zinc-900 dark:text-white sm:text-5xl md:text-6xl">
                        <span class="block xl:inline">{{ $attributes['title'] ?? 'Welcome to Alma' }}</span>
                    </h1>
                    <p
                        class="mt-3 text-base text-zinc-500 dark:text-zinc-400 sm:mt-5 sm:text-lg sm:max-w-xl sm:mx-auto md:mt-5 md:text-xl lg:mx-0">
                        {{ $attributes['subtitle'] ?? 'The next generation WordPress application toolkit.' }}
                    </p>
                    <div class="mt-5 sm:mt-8 sm:flex sm:justify-center lg:justify-start">
                        <div class="rounded-md shadow">
                            <flux:button variant="primary" href="/register">Get started</flux:button>
                        </div>
                        <div class="mt-3 sm:mt-0 sm:ml-3">
                            <flux:button variant="subtle" href="/login">Log in</flux:button>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</div>
