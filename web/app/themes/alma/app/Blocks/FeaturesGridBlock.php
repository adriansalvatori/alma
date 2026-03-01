<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class FeaturesGridBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Features Grid';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A bento box style grid highlighting 3 main features.';

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
    public $keywords = ['features', 'grid', 'bento'];

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
            'sectionTitle' => get_field('sectionTitle') ?: __('Features Section', 'alma'),

            'feature1Title' => get_field('feature1Title') ?: __('Highlighted Feature 1', 'alma'),
            'feature1Desc' => get_field('feature1Desc') ?: __('Use main feature cards with supporting visuals to quickly show how your app solves real problems.', 'alma'),

            'feature2Title' => get_field('feature2Title') ?: __('Highlighted Feature 2', 'alma'),
            'feature2Desc' => get_field('feature2Desc') ?: __('Brief explanation of the secondary feature.', 'alma'),

            'feature3Title' => get_field('feature3Title') ?: __('Highlighted Feature 3', 'alma'),
            'feature3Desc' => get_field('feature3Desc') ?: __('Brief explanation of the tertiary feature.', 'alma'),
        ];
    }

    /**
     * The block field group.
     *
     * @return array
     */
    public function fields()
    {
        $featuresGrid = new FieldsBuilder('features_grid');

        $featuresGrid
            ->addText('sectionTitle', [
                'label' => 'Section Title',
                'default_value' => 'Features Section',
            ])

            ->addAccordion('Feature 1', ['open' => 0])
            ->addText('feature1Title', [
                'label' => 'Feature 1 Title',
                'default_value' => 'Highlighted Feature 1',
            ])
            ->addTextarea('feature1Desc', [
                'label' => 'Feature 1 Description',
                'rows' => 3,
                'default_value' => 'Use main feature cards with supporting visuals to quickly show how your app solves real problems.',
            ])

            ->addAccordion('Feature 2', ['open' => 0])
            ->addText('feature2Title', [
                'label' => 'Feature 2 Title',
                'default_value' => 'Highlighted Feature 2',
            ])
            ->addTextarea('feature2Desc', [
                'label' => 'Feature 2 Description',
                'rows' => 3,
                'default_value' => 'Brief explanation of the secondary feature.',
            ])

            ->addAccordion('Feature 3', ['open' => 0])
            ->addText('feature3Title', [
                'label' => 'Feature 3 Title',
                'default_value' => 'Highlighted Feature 3',
            ])
            ->addTextarea('feature3Desc', [
                'label' => 'Feature 3 Description',
                'rows' => 3,
                'default_value' => 'Brief explanation of the tertiary feature.',
            ]);

        return $featuresGrid->build();
    }
}
