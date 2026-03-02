---
name: sage-acf-blocks
description: "Develops custom Gutenberg blocks using Log1x/AcfComposer and StoutLogic/AcfBuilder in the Sage/Acorn WordPress ecosystem. Activates when creating, updating, or styling Gutenberg blocks, creating editable WordPress dashboard components, or when the user mentions building a block, ACF block, Hero block, or Gutenberg editable area."
license: MIT
metadata:
  author: roots
---
# Sage ACF Blocks Development

## When to Apply

Activate this skill when:

- Creating new editable Gutenberg blocks for the WordPress editor
- Modifying existing `App\Blocks` (e.g., HeroBlock, TestimonialsBlock)
- Adjusting ACF Builder fields (`StoutLogic\AcfBuilder\FieldsBuilder`)
- Changing block Blade templates (`resources/views/blocks/`)

## Architecture Rules

This project uses **Log1x/AcfComposer** to build dynamic Gutenberg blocks programmatically. A block consists of TWO required pieces:

1. **The PHP Class:** Defines block metadata and ACF fields using StoutLogic's PHP builder.
   - Location: `web/app/themes/alma/app/Blocks/`
   - Namespace: `App\Blocks`
   - Extension: Must inherit from `Log1x\AcfComposer\Block` (or a base block like `App\Blocks\BaseBlock`)
2. **The Blade Template:** Handles the rendering logic using Flux UI/Tailwind.
   - Location: `web/app/themes/alma/resources/views/blocks/` (matched by slug)

## Creating a new Block

If requested to create a block (e.g., "Feature Grid"), you must create the PHP logic and the Blade view.

### 1. The PHP Class (`app/Blocks/FeatureGrid.php`)

```php
<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class FeatureGrid extends Block
{
    /**
     * Define Block Metadata
     */
    public $name = 'Feature Grid';
    public $description = 'A responsive grid of features.';
    public $category = 'alma'; // Custom namespace for this theme
    public $icon = 'grid-view';
    public $supports = [
        'align' => true,
        'jsx' => true, // CRITICAL: Enables InnerBlocks (nested blocks)
    ];

    /**
     * Map ACF values to Blade variables accessible via $data array.
     */
    public function with()
    {
        return [
            'headline' => get_field('headline') ?: 'Default Headline',
            'features' => get_field('features') ?: [],
        ];
    }

    /**
     * Define ACF Fields via StoutLogic Builder
     */
    public function fields()
    {
        $featureGrid = new FieldsBuilder('feature_grid');

        $featureGrid
            ->addText('headline', ['label' => 'Main Headline'])
            ->addRepeater('features', ['label' => 'Features Grid'])
                ->addText('title', ['label' => 'Title'])
                ->addTextarea('description', ['label' => 'Description', 'rows' => 3])
                ->addImage('icon', ['return_format' => 'url'])
            ->endRepeater();

        return $featureGrid->build();
    }
}
```

### 2. The Blade Template (`resources/views/blocks/feature-grid.blade.php`)

The Blade template automatically receives variables mapped in the `with()` method.

```blade
<div class="{{ $block->classes }} py-12">
    <h2 class="text-3xl font-bold">{{ $headline }}</h2>

    @if(!empty($features))
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-8">
            @foreach($features as $feature)
                <div class="p-6 bg-white rounded-xl shadow-sm">
                    @if($feature['icon'])
                        <img src="{{ $feature['icon'] }}" class="w-12 h-12 mb-4" alt="Icon">
                    @endif
                    <h3 class="text-xl font-semibold">{{ $feature['title'] }}</h3>
                    <p class="text-zinc-600 mt-2">{{ $feature['description'] }}</p>
                </div>
            @endforeach
        </div>
    @endif
    
    {{-- Render InnerBlocks if JSX is true --}}
    <div class="mt-8">
        <InnerBlocks />
    </div>
</div>
```

## Critical Pitfalls & Rules

* **Do not use `wp-cli` to register raw blocks** - Let `Log1x\AcfComposer` auto-discover the `App\Blocks` directory.
* **JSX Support:** Always set `'jsx' => true` in the `$supports` array if you want the user to drop other blocks (buttons, paragraphs) inside this block via `<InnerBlocks />`.
* **Flux UI:** Whenever building user-facing templates for blocks, continue using Flux UI for buttons and standard Tailwind for layout (avoid raw HTML inputs if Flux provides them).
* **Return Arrays:** Be mindful that `get_field` returns false if empty. In the `with()` mapping, always provide a sensible fallback (e.g. `get_field('images') ?: []`) so your Blade loops (`@foreach`) do not crash.
