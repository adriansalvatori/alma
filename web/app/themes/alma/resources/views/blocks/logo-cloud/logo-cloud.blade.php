<section class="py-12 bg-white dark:bg-zinc-900 {{ $attributes['align'] ?? '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
            <div class="md:w-1/3">
                <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wide">
                    {{ $attributes['text'] ?? 'WE ARE PARTNERED WITH MORE THAN 50+ COMPANIES AROUND THE GLOBE' }}
                </p>
            </div>
            <div
                class="md:w-2/3 grid grid-cols-2 gap-8 md:grid-cols-4 items-center opacity-60 grayscale hover:grayscale-0 transition duration-300">
                <div class="col-span-1 flex justify-center md:justify-start">
                    <img class="h-8 object-contain dark:invert"
                        src="https://tailwindui.com/plus/img/logos/158x48/tuple-logo-gray-900.svg" alt="Tuple">
                </div>
                <div class="col-span-1 flex justify-center md:justify-start">
                    <img class="h-8 object-contain dark:invert"
                        src="https://tailwindui.com/plus/img/logos/158x48/reform-logo-gray-900.svg" alt="Reform">
                </div>
                <div class="col-span-1 flex justify-center md:justify-start">
                    <img class="h-8 object-contain dark:invert"
                        src="https://tailwindui.com/plus/img/logos/158x48/savvycal-logo-gray-900.svg" alt="SavvyCal">
                </div>
                <div class="col-span-1 flex justify-center md:justify-start">
                    <img class="h-8 object-contain dark:invert"
                        src="https://tailwindui.com/plus/img/logos/158x48/statamic-logo-gray-900.svg" alt="Statamic">
                </div>
            </div>
        </div>
    </div>
</section>
