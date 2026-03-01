<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class HeroBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Hero';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A highly customizable hero section.';

    /**
     * The block category.
     *
     * @var string
     */
    public $category = 'alma';

    /**
     * The block icon.
     *
     * @var string|array
     */
    public $icon = 'cover-image';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = ['hero', 'header'];


    /**
     * The supported block features.
     *
     * @var array
     */
    public $supports = [
        'align' => true,
        'align_text' => false,
        'align_content' => false,
        'full_height' => false,
        'anchor' => false,
        'mode' => false,
        'multiple' => true,
        'jsx' => true,
        'color' => [
            'background' => true,
            'text' => true,
            'gradient' => true,
        ],
    ];

    /**
     * Data to be passed to the block before rendering.
     *
     * @return array
     */
    public function with()
    {
        return [
            'layout' => get_field('layout') ?: 'split_right_image',
            'full_height' => get_field('full_height') ?: false,
            'badge' => get_field('badge') ?: '',
            'title' => get_field('title') ?: __('High Converting Heading Comes Here', 'alma'),
            'subtitle' => get_field('subtitle') ?: __('Use a clear headline, value prop, and app store buttons...', 'alma'),
            'imageUrl' => get_field('image_url') ?: '',
            'downloadsText' => get_field('downloads_text') ?: '200K+ Downloads',
        ];
    }

    /**
     * The block field group.
     *
     * @return array
     */
    public function fields()
    {
        $hero = new FieldsBuilder('hero');

        $hero
            ->addSelect('layout', [
                'label' => 'Hero Layout Style',
                'instructions' => 'Choose the visual presentation of this hero section.',
                'choices' => [
                    'split_right_image' => 'Split (Right Image)',
                    'centered_dashboard' => 'Centered App Dashboard',
                    'centered_glow' => 'Centered Modern Glow',
                    'cinematic' => 'Cinematic Dark Focus',
                ],
                'default_value' => 'split_right_image',
                'return_format' => 'value',
            ])
            ->addTrueFalse('full_height', [
                'label' => 'Full Height',
                'instructions' => 'Make the hero section take up the full height of the screen.',
                'ui' => 1,
            ])
            ->addText('badge', [
                'label' => 'Badge Text',
                'instructions' => 'Optional small badge above title.',
            ])
            ->addText('title', [
                'label' => 'Title',
                'required' => 1,
            ])
            ->addTextarea('subtitle', [
                'label' => 'Subtitle',
                'rows' => 3,
            ])
            ->addImage('image_url', [
                'label' => 'Image',
                'return_format' => 'url',
            ])
            ->addText('downloads_text', [
                'label' => 'Downloads Text',
                'default_value' => '200K+ Downloads',
            ]);

        return $hero->build();
    }
}
