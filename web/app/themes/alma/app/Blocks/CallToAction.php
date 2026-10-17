<?php

namespace App\Blocks;

use App\Support\ResponsiveImage;
use Log1x\AcfComposer\Builder;

class CallToAction extends Block
{
    public $name = 'Call to Action';

    public $slug = 'call-to-action';

    public $description = 'A closing panel that repeats the offer: heading, one sentence, up to two buttons and an optional image.';

    public $icon = 'megaphone';

    public $keywords = ['cta', 'call to action', 'banner', 'offer'];

    public const DEFAULTS = [
        'style' => 'panel',
        'heading' => 'Ready to start your next site?',
        'text' => 'Spin up a new project on a foundation that has already been through production.',
        'primary_cta_text' => 'Start a project',
        'primary_cta_url' => '#contact',
        'secondary_cta_text' => '',
        'secondary_cta_url' => '',
    ];

    public $example = [
        'heading' => 'Ready to start your next site?',
    ];

    public function with(): array
    {
        return [
            'style' => $this->value('style'),
            'heading' => $this->value('heading'),
            'text' => $this->value('text'),
            'primaryCta' => ['text' => $this->value('primary_cta_text'), 'url' => $this->value('primary_cta_url')],
            'secondaryCta' => ['text' => $this->value('secondary_cta_text'), 'url' => $this->value('secondary_cta_url')],
            'image' => ResponsiveImage::html(get_field('image'), 'large', [
                'class' => 'h-full w-full object-cover',
                'sizes' => '(min-width: 1024px) 40vw, 100vw',
            ]),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('call_to_action_block');

        $fields
            ->addButtonGroup('style', [
                'label' => 'Style',
                'choices' => [
                    'panel' => 'Neutral panel',
                    'brand' => 'Brand colour',
                ],
                'default_value' => self::DEFAULTS['style'],
            ])
            ->addText('heading', [
                'label' => 'Heading',
                'default_value' => self::DEFAULTS['heading'],
            ])
            ->addTextarea('text', [
                'label' => 'Sentence',
                'rows' => 2,
                'default_value' => self::DEFAULTS['text'],
            ])
            ->addText('primary_cta_text', [
                'label' => 'Main button text',
                'default_value' => self::DEFAULTS['primary_cta_text'],
            ])
            ->addText('primary_cta_url', [
                'label' => 'Main button link',
                'instructions' => 'A full URL, a path like /pricing/, or #contact.',
                'default_value' => self::DEFAULTS['primary_cta_url'],
            ])
            ->addText('secondary_cta_text', [
                'label' => 'Second button text',
                'instructions' => 'Leave empty to show one button.',
                'default_value' => self::DEFAULTS['secondary_cta_text'],
            ])
            ->addText('secondary_cta_url', [
                'label' => 'Second button link',
                'default_value' => self::DEFAULTS['secondary_cta_url'],
            ])
            ->addImage('image', [
                'label' => 'Image (optional)',
                'return_format' => 'id',
                'preview_size' => 'medium',
            ]);

        return $fields->build();
    }
}
