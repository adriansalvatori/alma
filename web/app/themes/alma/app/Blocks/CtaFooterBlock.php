<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class CtaFooterBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'CTA Footer';

    /**
     * The block slug.
     *
     * @var string
     */
    public $slug = 'cta-footer';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'A strong call to action block typically placed above the footer.';

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
    public $icon = 'megaphone';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = ['cta', 'call to action', 'footer'];

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
            'title' => get_field('title') ?: __('CTA Heading', 'alma'),
            'description' => get_field('description') ?: __('Reinforce the download offer, repeat your app\'s value, and include the app buttons again for one final push.', 'alma'),
            'imageUrl' => get_field('image_url') ?: '',
        ];
    }

    /**
     * The block field group.
     *
     * @return array
     */
    public function fields()
    {
        $ctaFooter = new FieldsBuilder('cta_footer');

        $ctaFooter
            ->addText('title', [
                'label' => 'Title',
                'default_value' => 'CTA Heading',
            ])
            ->addTextarea('description', [
                'label' => 'Description',
                'rows' => 3,
                'default_value' => 'Reinforce the download offer, repeat your app\'s value, and include the app buttons again for one final push.',
            ])
            ->addImage('image_url', [
                'label' => 'Image',
                'return_format' => 'url',
            ]);

        return $ctaFooter->build();
    }
}
