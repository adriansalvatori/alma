<?php

namespace App\Blocks;

use Log1x\AcfComposer\Block;
use StoutLogic\AcfBuilder\FieldsBuilder;

class FaqAccordionBlock extends Block
{
    /**
     * The block name.
     *
     * @var string
     */
    public $name = 'Faq Accordion';

    /**
     * The block description.
     *
     * @var string
     */
    public $description = 'An accordion list of frequently asked questions.';

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
    public $icon = 'editor-help';

    /**
     * The block keywords.
     *
     * @var array
     */
    public $keywords = ['faq', 'accordion', 'questions'];

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
        // For the repeater, map the values or return defaults
        $faqs = get_field('faqs');
        if (empty($faqs)) {
            $faqs = [
                [
                    'question' => 'Is this app free to use?',
                    'answer' => 'Yes, there is a comprehensive free tier available for all new users. Premium features can be unlocked via the Pro subscription.',
                ],
                [
                    'question' => 'How does the 30-day money back guarantee work?',
                    'answer' => 'If you\'re not completely satisfied with our service within the first 30 days, simply contact our support team to receive a full refund, no questions asked.',
                ],
                [
                    'question' => 'What platforms do you support?',
                    'answer' => 'Our application is fully supported on iOS, Android, macOS, Windows, and modern web browsers.',
                ],
                [
                    'question' => 'Can I cancel my subscription anytime?',
                    'answer' => 'Absolutely. You can cancel or pause your membership directly from your account dashboard with zero hidden fees.',
                ],
            ];
        }

        return [
            'sectionTitle' => get_field('sectionTitle') ?: __('FAQ Section', 'alma'),
            'description' => get_field('description') ?: __('Reduce hesitation with smart answers. Use collapsible questions to address common concerns without overwhelming the layout.', 'alma'),
            'faqs' => $faqs,
        ];
    }

    /**
     * The block field group.
     *
     * @return array
     */
    public function fields()
    {
        $faqAccordion = new FieldsBuilder('faq_accordion');

        $faqAccordion
            ->addText('sectionTitle', [
                'label' => 'Section Title',
                'default_value' => 'FAQ Section',
            ])
            ->addTextarea('description', [
                'label' => 'Description',
                'rows' => 3,
                'default_value' => 'Reduce hesitation with smart answers. Use collapsible questions to address common concerns without overwhelming the layout.',
            ])
            ->addRepeater('faqs', [
                'label' => 'FAQs',
                'min' => 1,
                'layout' => 'block',
            ])
            ->addText('question', [
                'label' => 'Question',
                'required' => 1,
            ])
            ->addTextarea('answer', [
                'label' => 'Answer',
                'required' => 1,
                'rows' => 3,
            ])
            ->endRepeater();

        return $faqAccordion->build();
    }
}
