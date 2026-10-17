<?php

namespace App\Blocks;

use App\Support\ResponsiveImage;
use Log1x\AcfComposer\Builder;

class Hero extends Block
{
    public $name = 'Hero';

    public $slug = 'hero';

    public $description = 'The opening section of a page: headline, supporting line, two calls to action and an optional image.';

    public $icon = 'cover-image';

    public $keywords = ['hero', 'header', 'banner', 'intro'];

    public const DEFAULTS = [
        'layout' => 'split',
        'eyebrow' => 'Built on Alma',
        'headline' => 'Launch a site that converts from day one',
        'subheadline' => 'A tested foundation for marketing websites: reusable blocks, lead capture, SEO and one-command deploys, on any host that runs PHP.',
        'primary_cta_text' => 'Get started',
        'primary_cta_url' => '#contact',
        'secondary_cta_text' => 'See what is included',
        'secondary_cta_url' => '#features',
        'proof_text' => '',
        'full_height' => false,
    ];

    public $example = [
        'layout' => 'centered',
        'headline' => 'Launch a site that converts from day one',
    ];

    public function with(): array
    {
        return [
            'layout' => $this->value('layout'),
            'eyebrow' => $this->value('eyebrow'),
            'headline' => $this->value('headline'),
            'subheadline' => $this->value('subheadline'),
            'primaryCta' => ['text' => $this->value('primary_cta_text'), 'url' => $this->value('primary_cta_url')],
            'secondaryCta' => ['text' => $this->value('secondary_cta_text'), 'url' => $this->value('secondary_cta_url')],
            'proofText' => $this->value('proof_text'),
            'fullHeight' => (bool) $this->value('full_height'),
            // The hero image is the page's largest paint: load it eagerly, at high priority.
            'image' => ResponsiveImage::html(get_field('image'), 'full', [
                'class' => 'h-full w-full object-cover',
                'loading' => 'eager',
                'fetchpriority' => 'high',
                'sizes' => '(min-width: 1024px) 50vw, 100vw',
            ]),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('hero_block');

        $fields
            ->addSelect('layout', [
                'label' => 'Layout',
                'choices' => [
                    'split' => 'Split: copy left, image right',
                    'centered' => 'Centered',
                    'glow' => 'Centered with a colour glow',
                    'cinematic' => 'Copy over a full-width image',
                ],
                'default_value' => self::DEFAULTS['layout'],
            ])
            ->addText('eyebrow', [
                'label' => 'Eyebrow',
                'instructions' => 'A short line above the headline. Leave empty for none.',
                'default_value' => self::DEFAULTS['eyebrow'],
            ])
            ->addTextarea('headline', [
                'label' => 'Headline',
                'rows' => 2,
                'default_value' => self::DEFAULTS['headline'],
            ])
            ->addTextarea('subheadline', [
                'label' => 'Supporting line',
                'rows' => 3,
                'default_value' => self::DEFAULTS['subheadline'],
            ])
            ->addText('primary_cta_text', [
                'label' => 'Main button text',
                'default_value' => self::DEFAULTS['primary_cta_text'],
            ])
            // Text, not URL: ACF's URL field rejects a relative path or #anchor.
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
                'label' => 'Image',
                'return_format' => 'id',
                'preview_size' => 'medium',
            ])
            ->addText('proof_text', [
                'label' => 'Social proof line',
                'instructions' => 'For example "Trusted by 2,000 teams". Leave empty for none.',
                'default_value' => self::DEFAULTS['proof_text'],
            ])
            ->addTrueFalse('full_height', [
                'label' => 'Fill the screen height',
                'ui' => 1,
                'default_value' => self::DEFAULTS['full_height'],
            ]);

        return $fields->build();
    }
}
