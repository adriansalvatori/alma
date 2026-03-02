{{--
{
    "name": "alma/testimonials",
    "title": "Testimonials",
    "description": "A grid of user reviews or testimonials.",
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
        "sectionTitle": { "type": "string", "control": "TextControl", "label": "Section Title", "default": "Review Section" },
        "description": { "type": "string", "control": "TextareaControl", "label": "Description", "default": "Let happy users convince the rest." },
        "r1_name": { "type": "string", "control": "TextControl", "label": "Reviewer 1 Name", "default": "Alex Johnson" },
        "r1_loc": { "type": "string", "control": "TextControl", "label": "Reviewer 1 Location", "default": "United States" },
        "r1_rating": { "type": "number", "control": "SelectControl", "label": "Reviewer 1 Rating", "default": 5, "options": [ {"label": "5 Stars", "value": 5}, {"label": "4 Stars", "value": 4}, {"label": "3 Stars", "value": 3}, {"label": "2 Stars", "value": 2}, {"label": "1 Star", "value": 1} ] },
        "r1_comment": { "type": "string", "control": "TextareaControl", "label": "Reviewer 1 Comment", "default": "Testimonials with names, ratings, and short blurbs help build authenticity and trust." },
        "r1_avatar": { "type": "string", "control": "ImageControl", "label": "Reviewer 1 Avatar", "default": "https://i.pravatar.cc/150?u=a042581f4e29026024d" },
        "r2_name": { "type": "string", "control": "TextControl", "label": "Reviewer 2 Name", "default": "Maria Garcia" },
        "r2_loc": { "type": "string", "control": "TextControl", "label": "Reviewer 2 Location", "default": "Spain" },
        "r2_rating": { "type": "number", "control": "SelectControl", "label": "Reviewer 2 Rating", "default": 5, "options": [ {"label": "5 Stars", "value": 5}, {"label": "4 Stars", "value": 4}, {"label": "3 Stars", "value": 3}, {"label": "2 Stars", "value": 2}, {"label": "1 Star", "value": 1} ] },
        "r2_comment": { "type": "string", "control": "TextareaControl", "label": "Reviewer 2 Comment", "default": "Incredibly fast and beautifully designed." },
        "r2_avatar": { "type": "string", "control": "ImageControl", "label": "Reviewer 2 Avatar", "default": "https://i.pravatar.cc/150?u=a04258a2462d826712d" },
        "r3_name": { "type": "string", "control": "TextControl", "label": "Reviewer 3 Name", "default": "David Chen" },
        "r3_loc": { "type": "string", "control": "TextControl", "label": "Reviewer 3 Location", "default": "Canada" },
        "r3_rating": { "type": "number", "control": "SelectControl", "label": "Reviewer 3 Rating", "default": 4, "options": [ {"label": "5 Stars", "value": 5}, {"label": "4 Stars", "value": 4}, {"label": "3 Stars", "value": 3}, {"label": "2 Stars", "value": 2}, {"label": "1 Star", "value": 1} ] },
        "r3_comment": { "type": "string", "control": "TextareaControl", "label": "Reviewer 3 Comment", "default": "We integrated this into our daily operations and saw a 40% increase in productivity." },
        "r3_avatar": { "type": "string", "control": "ImageControl", "label": "Reviewer 3 Avatar", "default": "https://i.pravatar.cc/150?u=a04258114e29026702d" },
        "r4_name": { "type": "string", "control": "TextControl", "label": "Reviewer 4 Name", "default": "Sarah Williams" },
        "r4_loc": { "type": "string", "control": "TextControl", "label": "Reviewer 4 Location", "default": "UK" },
        "r4_rating": { "type": "number", "control": "SelectControl", "label": "Reviewer 4 Rating", "default": 5, "options": [ {"label": "5 Stars", "value": 5}, {"label": "4 Stars", "value": 4}, {"label": "3 Stars", "value": 3}, {"label": "2 Stars", "value": 2}, {"label": "1 Star", "value": 1} ] },
        "r4_comment": { "type": "string", "control": "TextareaControl", "label": "Reviewer 4 Comment", "default": "The support is fantastic and the features exactly match what we need." },
        "r4_avatar": { "type": "string", "control": "ImageControl", "label": "Reviewer 4 Avatar", "default": "https://i.pravatar.cc/150?u=a048581f4e29026701d" }
    }
}
--}}

@php
    $sectionTitle = $attributes['sectionTitle'] ?? 'Review Section';
    $description = $attributes['description'] ?? 'Let happy users...';
    $reviews = [];
    for ($i = 1; $i <= 4; $i++) {
        $name = $attributes["r{$i}_name"] ?? '';
        if ($name) {
            $reviews[] = [
                'name' => $name,
                'loc' => $attributes["r{$i}_loc"] ?? '',
                'rating' => (int) ($attributes["r{$i}_rating"] ?? 5),
                'comment' => $attributes["r{$i}_comment"] ?? '',
                'avatar' => $attributes["r{$i}_avatar"] ?? '',
            ];
        }
    }
@endphp

<div class="{{ $block->classes ?? 'wp-block-alma-testimonials' }} py-24 bg-zinc-50 dark:bg-zinc-900/50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

        <div class="text-center mb-16">
            <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl">
                {{ $sectionTitle }}
            </h2>
            <p class="mt-4 text-lg text-zinc-600 dark:text-zinc-400">
                {{ $description }}
            </p>
            <div class="mt-6 h-1 w-20 bg-indigo-500 mx-auto rounded"></div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            @foreach ($reviews as $review)
                <div
                    class="bg-white dark:bg-zinc-800 p-8 rounded-2xl shadow-sm border border-zinc-200 dark:border-zinc-700 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center space-x-1 text-yellow-400 mb-4">
                            @for ($s = 1; $s <= 5; $s++)
                                <flux:icon.star :variant="$s <= $review['rating'] ? 'solid' : 'outline'"
                                    class="w-5 h-5" />
                            @endfor
                        </div>
                        <p class="text-zinc-600 dark:text-zinc-300 italic mb-6">
                            "{!! $review['comment'] !!}"
                        </p>
                    </div>
                    <div class="flex items-center space-x-4">
                        <img class="w-10 h-10 rounded-full" src="{{ $review['avatar'] }}" alt="User Avatar">
                        <div>
                            <h4 class="text-sm font-bold text-zinc-900 dark:text-white">{{ $review['name'] }}</h4>
                            <span class="text-xs text-zinc-500 dark:text-zinc-400">{{ $review['loc'] }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
