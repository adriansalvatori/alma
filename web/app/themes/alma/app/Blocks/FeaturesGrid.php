<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasRepeaterDefaults;
use App\Support\ResponsiveImage;
use Log1x\AcfComposer\Builder;

class FeaturesGrid extends Block
{
    use HasRepeaterDefaults;

    public $name = 'Features Grid';

    public $slug = 'features-grid';

    public $description = 'A heading over a grid of feature cards, each with an icon or image, a title and a short description.';

    public $icon = 'grid-view';

    public $keywords = ['features', 'cards', 'grid', 'services'];

    public const DEFAULTS = [
        'heading' => 'Everything a marketing site needs',
        'intro' => 'Start from a foundation that already solved the hard parts, and spend the project on what makes it yours.',
        'columns' => '3',
        'feature_first' => true,
    ];

    public $example = [
        'heading' => 'Everything a marketing site needs',
    ];

    public static function repeaterDefaults(): array
    {
        return [
            'items' => [
                ['icon' => 'squares-2x2', 'title' => 'Reusable blocks', 'text' => 'Sections editors can compose into pages, with copy defaults that match what the sidebar shows.', 'image' => null],
                ['icon' => 'chat-bubble-left-right', 'title' => 'Lead capture', 'text' => 'Forms that store every enquiry, notify your team and send it on to your CRM.', 'image' => null],
                ['icon' => 'chart-bar', 'title' => 'SEO that holds', 'text' => 'Redirects, real 404s, robots rules and structured settings that survive every deploy.', 'image' => null],
            ],
        ];
    }

    public function with(): array
    {
        return [
            'heading' => $this->value('heading'),
            'intro' => $this->value('intro'),
            'columns' => (int) $this->value('columns'),
            'featureFirst' => (bool) $this->value('feature_first'),
            'items' => array_map(fn (array $item) => [
                ...$item,
                'image' => ResponsiveImage::html($item['image'] ?? null, 'large', ['class' => 'h-full w-full object-cover']),
            ], $this->rows('items')),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('features_grid_block');

        $fields
            ->addText('heading', [
                'label' => 'Heading',
                'default_value' => self::DEFAULTS['heading'],
            ])
            ->addTextarea('intro', [
                'label' => 'Introduction',
                'rows' => 2,
                'default_value' => self::DEFAULTS['intro'],
            ])
            // Numeric keys in pair form: acf-builder drops plain numeric keys and stores the label.
            ->addButtonGroup('columns', [
                'label' => 'Cards per row',
                'choices' => [['2' => 'Two'], ['3' => 'Three']],
                'default_value' => self::DEFAULTS['columns'],
            ])
            ->addTrueFalse('feature_first', [
                'label' => 'Make the first card span the full row',
                'ui' => 1,
                'default_value' => self::DEFAULTS['feature_first'],
            ])
            ->addRepeater('items', [
                'label' => 'Features',
                'layout' => 'block',
                'button_label' => 'Add feature',
            ])
            ->addSelect('icon', [
                'label' => 'Icon',
                'choices' => ['squares-2x2' => 'Blocks'] + self::ICONS,
                'default_value' => 'bolt',
            ])
            ->addText('title', ['label' => 'Title'])
            ->addTextarea('text', ['label' => 'Description', 'rows' => 2])
            ->addImage('image', [
                'label' => 'Image (optional, replaces the icon)',
                'return_format' => 'id',
                'preview_size' => 'thumbnail',
            ])
            ->endRepeater();

        return $fields->build();
    }
}
