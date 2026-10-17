<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasRepeaterDefaults;
use App\Support\ResponsiveImage;
use Log1x\AcfComposer\Builder;

class LogoCloud extends Block
{
    use HasRepeaterDefaults;

    public $name = 'Logo Cloud';

    public $slug = 'logo-cloud';

    public $description = 'A strip of client or partner logos with a short line of social proof.';

    public $icon = 'images-alt2';

    public $keywords = ['logos', 'clients', 'partners', 'trust', 'social proof'];

    public const DEFAULTS = [
        'text' => 'Trusted by teams at companies of every size',
        'grayscale' => true,
    ];

    public $example = [
        'text' => 'Trusted by teams at companies of every size',
    ];

    /**
     * Placeholder names, rendered as wordmarks until real logos are uploaded. No image is
     * hotlinked from a third party.
     */
    public static function repeaterDefaults(): array
    {
        return [
            'logos' => [
                ['name' => 'Northwind', 'image' => null, 'url' => ''],
                ['name' => 'Contoso', 'image' => null, 'url' => ''],
                ['name' => 'Globex', 'image' => null, 'url' => ''],
                ['name' => 'Initech', 'image' => null, 'url' => ''],
            ],
        ];
    }

    public function with(): array
    {
        return [
            'text' => $this->value('text'),
            'grayscale' => (bool) $this->value('grayscale'),
            'logos' => array_map(fn (array $logo) => [
                ...$logo,
                'image' => ResponsiveImage::html($logo['image'] ?? null, 'medium', [
                    'class' => 'h-8 w-auto object-contain dark:invert',
                    'alt' => $logo['name'] ?? '',
                ]),
            ], $this->rows('logos')),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('logo_cloud_block');

        $fields
            ->addText('text', [
                'label' => 'Line of text',
                'default_value' => self::DEFAULTS['text'],
            ])
            ->addTrueFalse('grayscale', [
                'label' => 'Show logos in greyscale until hovered',
                'ui' => 1,
                'default_value' => self::DEFAULTS['grayscale'],
            ])
            ->addRepeater('logos', [
                'label' => 'Logos',
                'layout' => 'table',
                'button_label' => 'Add logo',
            ])
            ->addText('name', ['label' => 'Company name', 'instructions' => 'Used as the alt text.'])
            ->addImage('image', ['label' => 'Logo', 'return_format' => 'id', 'preview_size' => 'thumbnail'])
            ->addText('url', ['label' => 'Link (optional)'])
            ->endRepeater();

        return $fields->build();
    }
}
