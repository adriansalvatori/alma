<div class="{{ $block->classes }} relative w-full overflow-hidden">
    @switch($layout)
        @case('centered_dashboard')
            {{-- Centered Dashboard Layout --}}
            <div
                class="py-16 lg:py-24 bg-white dark:bg-zinc-900 border-b border-zinc-200 dark:border-zinc-800 {{ $full_height ? 'min-h-dvh flex flex-col justify-center' : '' }}">
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 text-center">
                    @if (!empty($badge))
                        <div
                            class="inline-flex items-center justify-center px-4 py-1.5 rounded-full text-sm font-semibold text-indigo-700 bg-indigo-100 dark:bg-indigo-900/40 dark:text-indigo-300 ring-1 ring-inset ring-indigo-500/20 mb-8 mt-4">
                            {{ $badge }}
                        </div>
                    @endif

                    <h1
                        class="text-5xl sm:text-6xl lg:text-7xl font-extrabold text-zinc-900 dark:text-white tracking-tight leading-tight mx-auto max-w-4xl">
                        {{ $title }}
                    </h1>

                    <p class="mt-6 text-xl text-zinc-500 dark:text-zinc-400 max-w-2xl mx-auto">
                        {{ $subtitle }}
                    </p>

                    <div
                        class="mt-10 flex flex-wrap items-center justify-center gap-4 [&_.block-editor-block-list__layout]:flex [&_.block-editor-block-list__layout]:flex-wrap [&_.block-editor-block-list__layout]:items-center [&_.block-editor-block-list__layout]:justify-center [&_.block-editor-block-list__layout]:gap-4">
                        {!! '<InnerBlocks />' !!}
                    </div>

                    {{-- Image Mockup --}}
                    @if (!empty($imageUrl))
                        <div class="mt-16 flow-root sm:mt-24">
                            <div
                                class="-m-2 rounded-xl bg-zinc-900/5 p-2 ring-1 ring-inset ring-zinc-900/10 dark:-m-4 dark:rounded-2xl dark:bg-zinc-800/20 dark:p-4 dark:ring-white/10 lg:-m-4 lg:rounded-2xl lg:p-4">
                                <img src="{{ $imageUrl }}" alt="App screenshot" width="2432" height="1442"
                                    class="rounded-md shadow-2xl ring-1 ring-zinc-900/10 dark:ring-white/10 w-full object-cover">
                            </div>
                        </div>
                    @else
                        <div
                            class="mt-16 sm:mt-24 h-96 w-full max-w-5xl mx-auto bg-zinc-100 dark:bg-zinc-800 rounded-2xl border border-zinc-200 dark:border-zinc-700 flex items-center justify-center shadow-xl">
                            <div class="text-zinc-400 dark:text-zinc-500 flex flex-col items-center">
                                <flux:icon.computer-desktop class="w-16 h-16 mb-4" />
                                <span class="text-sm font-medium">Dashboard Mockup Placeholder</span>
                            </div>
                        </div>
                    @endif

                    {{-- Social Proof --}}
                    <div class="mt-12 flex justify-center items-center space-x-4">
                        <div class="flex -space-x-2">
                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-zinc-900"
                                src="https://i.pravatar.cc/100?img=1" alt="" />
                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-zinc-900"
                                src="https://i.pravatar.cc/100?img=2" alt="" />
                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-zinc-900"
                                src="https://i.pravatar.cc/100?img=3" alt="" />
                            <img class="inline-block h-8 w-8 rounded-full ring-2 ring-white dark:ring-zinc-900"
                                src="https://i.pravatar.cc/100?img=4" alt="" />
                        </div>
                        <p class="text-sm text-zinc-600 dark:text-zinc-400 font-medium">{{ $downloadsText }}</p>
                    </div>
                </div>
            </div>
        @break

        @case('centered_glow')
            {{-- Centered Glow Layout (Senses Incorporations style) --}}
            <div
                class="relative py-20 lg:py-32 overflow-hidden bg-white dark:bg-zinc-950 {{ $full_height ? 'min-h-dvh flex flex-col justify-center' : '' }}">
                {{-- Glow Effects --}}
                <div
                    class="absolute top-0 -translate-y-12 left-1/2 -translate-x-1/2 w-[800px] h-[400px] opacity-30 dark:opacity-20 pointer-events-none blur-3xl rounded-full bg-gradient-to-r from-indigo-500 via-purple-500 to-pink-500 mix-blend-multiply dark:mix-blend-screen">
                </div>

                <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    @if (!empty($badge))
                        <div
                            class="inline-flex items-center px-3 py-1 rounded-full border border-zinc-200 dark:border-zinc-800 bg-white/50 dark:bg-zinc-900/50 backdrop-blur-sm text-sm font-medium text-zinc-900 dark:text-zinc-200 mb-8 mx-auto shadow-sm">
                            <span class="flex h-2 w-2 rounded-full bg-indigo-500 mr-2"></span>
                            {{ $badge }}
                        </div>
                    @endif

                    <h1 class="text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight text-zinc-900 dark:text-white mx-auto">
                        {{ $title }}
                    </h1>

                    <p class="mt-6 text-xl text-zinc-600 dark:text-zinc-400 max-w-2xl mx-auto leading-relaxed">
                        {{ $subtitle }}
                    </p>

                    <div
                        class="mt-10 flex flex-wrap items-center justify-center gap-4 [&_.block-editor-block-list__layout]:flex [&_.block-editor-block-list__layout]:flex-wrap [&_.block-editor-block-list__layout]:items-center [&_.block-editor-block-list__layout]:justify-center [&_.block-editor-block-list__layout]:gap-4">
                        {!! '<InnerBlocks />' !!}
                    </div>
                </div>
            </div>
        @break

        @case('cinematic')
            {{-- Cinematic Focus Layout (Verta style) --}}
            <div
                class="relative py-24 sm:py-32 lg:py-40 flex flex-col justify-end {{ $full_height ? 'min-h-dvh' : 'min-h-[60vh] lg:min-h-[80vh]' }}">
                @if (!empty($imageUrl))
                    <div class="absolute inset-0">
                        <img src="{{ $imageUrl }}" alt=""
                            class="h-full w-full object-cover opacity-20 dark:opacity-30 mix-blend-multiply dark:mix-blend-overlay">
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-white via-white/80 to-transparent dark:from-zinc-950 dark:via-zinc-950/60 dark:to-transparent">
                        </div>
                    </div>
                @else
                    <div class="absolute inset-0 bg-zinc-100 dark:bg-zinc-900">
                        <div
                            class="absolute inset-0 bg-gradient-to-tr from-white via-transparent to-white dark:from-zinc-950 dark:via-transparent dark:to-zinc-950">
                        </div>
                    </div>
                @endif

                <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col justify-end min-h-[40vh]">
                    <div class="max-w-3xl">
                        @if (!empty($badge))
                            <p class="text-indigo-600 dark:text-indigo-400 font-semibold tracking-wide uppercase text-sm mb-4">
                                {{ $badge }}
                            </p>
                        @endif

                        <h1
                            class="text-4xl sm:text-5xl lg:text-7xl font-semibold text-zinc-900 dark:text-white tracking-tight leading-[1.1]">
                            {{ $title }}
                        </h1>

                        <p class="mt-6 text-xl text-zinc-600 dark:text-zinc-300 max-w-xl">
                            {{ $subtitle }}
                        </p>

                        <div
                            class="mt-10 flex flex-wrap items-center gap-4 [&_.block-editor-block-list__layout]:flex [&_.block-editor-block-list__layout]:flex-wrap [&_.block-editor-block-list__layout]:items-center [&_.block-editor-block-list__layout]:gap-4">
                            {!! '<InnerBlocks />' !!}
                        </div>
                    </div>
                </div>
            </div>
        @break

        @default
            {{-- Default: Split Right Image --}}
            <div class="py-16 lg:py-24 {{ $full_height ? 'min-h-dvh flex flex-col justify-center' : '' }}">
                <div class="max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 items-center">

                        {{-- Left Column: Content --}}
                        <div class="flex flex-col items-start text-left space-y-6">
                            @if (!empty($badge))
                                <div
                                    class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-zinc-100 text-zinc-800 dark:bg-zinc-800 dark:text-zinc-200">
                                    {{ $badge }}
                                    <flux:icon.arrow-right class="w-4 h-4 ml-2 text-zinc-500" />
                                </div>
                            @endif

                            <h1
                                class="text-4xl sm:text-5xl lg:text-6xl font-black text-zinc-900 dark:text-white tracking-tight leading-tight">
                                {{ $title }}
                            </h1>

                            <p class="text-lg text-zinc-500 dark:text-zinc-400 max-w-xl">
                                {{ $subtitle }}
                            </p>

                            <div
                                class="flex flex-wrap items-center gap-4 pt-2 w-full [&_.block-editor-block-list__layout]:flex [&_.block-editor-block-list__layout]:flex-wrap [&_.block-editor-block-list__layout]:items-center [&_.block-editor-block-list__layout]:gap-4">
                                {!! '<InnerBlocks />' !!}
                            </div>

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
                                        +</div>
                                </div>
                                <span class="text-sm font-medium text-zinc-600 dark:text-zinc-400">
                                    {{ $downloadsText }}
                                </span>
                            </div>
                        </div>

                        {{-- Right Column: Image/Mockup Placeholder --}}
                        <div
                            class="relative w-full h-[500px] lg:h-[600px] flex items-center justify-center bg-zinc-100 dark:bg-zinc-800 rounded-3xl overflow-hidden border border-zinc-200 dark:border-zinc-700">
                            @if (!empty($imageUrl))
                                <img src="{{ $imageUrl }}" alt="App Mockup" class="w-full h-full object-cover">
                            @else
                                <div class="text-zinc-400 flex flex-col items-center">
                                    <flux:icon.device-phone-mobile class="w-24 h-24 mb-4 text-zinc-300 dark:text-zinc-600" />
                                    <span class="text-sm font-medium">App Mockup Placeholder</span>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        @break

    @endswitch
</div>
