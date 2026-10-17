<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasRepeaterDefaults;
use Log1x\AcfComposer\Builder;

class Faq extends Block
{
    use HasRepeaterDefaults;

    public $name = 'FAQ';

    public $slug = 'faq';

    public $description = 'Frequently asked questions as an accordion, with an optional contact prompt and FAQPage structured data.';

    public $icon = 'editor-help';

    public $keywords = ['faq', 'questions', 'accordion', 'answers'];

    public const DEFAULTS = [
        'heading' => 'Frequently asked questions',
        'intro' => 'Answers to what people ask before they get in touch.',
        'contact_text' => 'Still have questions? Talk to us',
        'contact_url' => '#contact',
        'schema' => true,
    ];

    public $example = [
        'heading' => 'Frequently asked questions',
    ];

    public static function repeaterDefaults(): array
    {
        return [
            'items' => [
                ['question' => 'How long does a project take?', 'answer' => 'Most marketing sites launch in four to eight weeks, depending on how much content is ready.'],
                ['question' => 'Can we edit the site ourselves?', 'answer' => 'Yes. Every section is a block with its copy in the sidebar, so changes never need a developer.'],
                ['question' => 'Where can it be hosted?', 'answer' => 'Anywhere that runs PHP 8.4 and MySQL, including shared hosting with SSH access.'],
                ['question' => 'What happens to the enquiries the site collects?', 'answer' => 'They are stored in WordPress, emailed to your team and can be forwarded to your CRM.'],
            ],
        ];
    }

    public function with(): array
    {
        $items = array_values(array_filter(
            $this->rows('items'),
            fn (array $item) => trim((string) ($item['question'] ?? '')) !== '',
        ));

        return [
            'heading' => $this->value('heading'),
            'intro' => $this->value('intro'),
            'contact' => ['text' => $this->value('contact_text'), 'url' => $this->value('contact_url')],
            'items' => $items,
            // Not printed in the editor preview: the canvas is not a page search engines read.
            'schema' => $this->value('schema') && ! $this->preview && $items !== [] ? $this->schema($items) : null,
        ];
    }

    /**
     * FAQPage structured data, which search engines can show as expandable answers.
     *
     * @param  list<array<string, mixed>>  $items
     */
    public function schema(array $items): string
    {
        return (string) wp_json_encode([
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_map(fn (array $item) => [
                '@type' => 'Question',
                'name' => wp_strip_all_tags((string) $item['question']),
                'acceptedAnswer' => ['@type' => 'Answer', 'text' => wp_strip_all_tags((string) $item['answer'])],
            ], $items),
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);
    }

    public function fields(): array
    {
        $fields = Builder::make('faq_block');

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
            ->addRepeater('items', [
                'label' => 'Questions',
                'layout' => 'block',
                'button_label' => 'Add question',
            ])
            ->addText('question', ['label' => 'Question'])
            ->addTextarea('answer', ['label' => 'Answer', 'rows' => 3])
            ->endRepeater()
            ->addText('contact_text', [
                'label' => 'Contact link text',
                'instructions' => 'Leave empty for no contact link.',
                'default_value' => self::DEFAULTS['contact_text'],
            ])
            ->addText('contact_url', [
                'label' => 'Contact link',
                'default_value' => self::DEFAULTS['contact_url'],
            ])
            ->addTrueFalse('schema', [
                'label' => 'Add FAQ structured data for search engines',
                'instructions' => 'Turn off if another FAQ on the same page already provides it.',
                'ui' => 1,
                'default_value' => self::DEFAULTS['schema'],
            ]);

        return $fields->build();
    }
}
