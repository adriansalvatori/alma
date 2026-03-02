{{--
{
    "name": "alma/test-subagent-block",
    "title": "Test Subagent Block",
    "description": "A custom Test Subagent Block block.",
    "category": "alma",
    "icon": "block-default",
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
        "title": {
            "type": "string",
            "control": "TextControl",
            "label": "Title",
            "default": "Test Subagent Block"
        }
    }
}
--}}

@php
    $title = $attributes['title'] ?? 'Test Subagent Block';
@endphp

<section class="py-12 {{ $block->classes ?? '' }}">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="text-3xl font-bold tracking-tight text-zinc-900 dark:text-white sm:text-4xl text-center">
            {{ $title }}
        </h2>
    </div>
</section>