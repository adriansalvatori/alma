<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class TestimonialsBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Testimonials';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A grid of user reviews or testimonials.';

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
    public $icon = 'grid-view';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = ['testimonials', 'reviews'];

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
            'sectionTitle' => get_field('sectionTitle') ?: __('Review Section', 'alma'),
            'description' => get_field('description') ?: __('Let happy users convince the rest.', 'alma'),
        ];
    }

    /**
     * The block field group.
     *
     * @return array
     */
    public function fields()
    {
        $testimonials = new FieldsBuilder('testimonials');

        $testimonials
            ->addText('sectionTitle', [
                'label' => 'Section Title',
                'default_value' => 'Review Section',
            ])
            ->addText('description', [
                'label' => 'Description',
                'default_value' => 'Let happy users convince the rest.',
            ]);

        return $testimonials->build();
    }
}
