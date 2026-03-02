{{--
{
    "name": "alma/logo-cloud",
    "title": "Logo Cloud",
    "description": "A grid of partner or client logos.",
    "category": "alma",
    "icon": "grid-view",
    "supports": {
        "align": true,
        "multiple": true,
        "jsx": true,
        "color": {
            "background": true,
            "text": true,
            "gradient": true
        }
    },
    "attributes": {
        "text": {
            "type": "string",
            "control": "TextControl",
            "label": "Heading Text",
            "default": "WE ARE PARTNERED WITH MORE THAN 50+ COMPANIES AROUND THE GLOBE"
        },
        "logo1": { "type": "string", "control": "ImageControl", "label": "Logo 1" },
        "logo2": { "type": "string", "control": "ImageControl", "label": "Logo 2" },
        "logo3": { "type": "string", "control": "ImageControl", "label": "Logo 3" },
        "logo4": { "type": "string", "control": "ImageControl", "label": "Logo 4" },
        "logo5": { "type": "string", "control": "ImageControl", "label": "Logo 5" },
        "logo6": { "type": "string", "control": "ImageControl", "label": "Logo 6" }
    }
}
--}}

@php
    $text = $attributes['text'] ?? 'WE ARE PARTNERED WITH MORE THAN 50+ COMPANIES AROUND THE GLOBE';
    $logos = [];
    for ($i = 1; $i <= 6; $i++) {
        $logo = $attributes["logo{$i}"] ?? '';
        if ($logo) {
            $logos[] = $logo;
        }
    }
@endphp

<div class="{{ $block->classes ?? 'wp-block-alma-logo-cloud' }} py-12 bg-white dark:bg-zinc-900">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-8">
            <div class="md:w-1/3">
                <p class="text-sm font-semibold text-zinc-500 uppercase tracking-wide">
                    {{ $text }}
                </p>
            </div>
            <div
                class="md:w-2/3 grid grid-cols-2 gap-8 md:grid-cols-4 items-center opacity-60 grayscale hover:grayscale-0 transition duration-300">
                @if (!empty($logos))
                    @foreach ($logos as $logo)
                        <div class="col-span-1 flex justify-center md:justify-start">
                            <img class="h-8 object-contain dark:invert" src="{{ $logo }}" alt="Partner Logo">
                        </div>
                    @endforeach
                @else
                    {{-- Default placeholders --}}
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
                            src="https://tailwindui.com/plus/img/logos/158x48/savvycal-logo-gray-900.svg"
                            alt="SavvyCal">
                    </div>
                    <div class="col-span-1 flex justify-center md:justify-start">
                        <img class="h-8 object-contain dark:invert"
                            src="https://tailwindui.com/plus/img/logos/158x48/statamic-logo-gray-900.svg"
                            alt="Statamic">
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
