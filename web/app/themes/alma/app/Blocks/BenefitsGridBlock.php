<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class BenefitsGridBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Benefits Grid';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A grid highlighting the main benefits.';

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
    public $keywords = ['benefits', 'grid'];

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
            'sectionTitle' => get_field('sectionTitle') ?: __('Why Choose Us Section', 'alma'),
        ];
    }

    /**
     * The block field group.
     *
     * @return array
     */
    public function fields()
    {
        $benefitsGrid = new FieldsBuilder('benefits_grid');

        $benefitsGrid
            ->addText('sectionTitle', [
                'label' => 'Section Title',
                'default_value' => 'Why Choose Us Section',
            ]);

        return $benefitsGrid->build();
    }
}
