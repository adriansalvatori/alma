<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasRepeaterDefaults;
use Log1x\AcfComposer\Builder;

class BenefitsGrid extends Block
{
    use HasRepeaterDefaults;

    public $name = 'Benefits';

    public $slug = 'benefits-grid';

    public $description = 'A row of short benefits, each an icon, a title and one sentence.';

    public $icon = 'yes-alt';

    public $keywords = ['benefits', 'why us', 'icons', 'reasons'];

    public const DEFAULTS = [
        'heading' => 'Why teams choose us',
        'intro' => '',
    ];

    public $example = [
        'heading' => 'Why teams choose us',
    ];

    public static function repeaterDefaults(): array
    {
        return [
            'items' => [
                ['icon' => 'rocket-launch', 'title' => 'Fast', 'text' => 'Pages that score well on Core Web Vitals out of the box.'],
                ['icon' => 'shield-check', 'title' => 'Secure', 'text' => 'Hardened defaults and two-factor sign-in for every account.'],
                ['icon' => 'device-phone-mobile', 'title' => 'Mobile first', 'text' => 'Every section is designed for the small screen first.'],
                ['icon' => 'chat-bubble-left-right', 'title' => 'Supported', 'text' => 'Documented decisions, so the next developer is never guessing.'],
            ],
        ];
    }

    public function with(): array
    {
        return [
            'heading' => $this->value('heading'),
            'intro' => $this->value('intro'),
            'items' => $this->rows('items'),
        ];
    }

    public function fields(): array
    {
        $fields = Builder::make('benefits_grid_block');

        $fields
            ->addText('heading', [
                'label' => 'Heading',
                'default_value' => self::DEFAULTS['heading'],
            ])
            ->addTextarea('intro', [
                'label' => 'Introduction',
                'instructions' => 'Leave empty for none.',
                'rows' => 2,
                'default_value' => self::DEFAULTS['intro'],
            ])
            ->addRepeater('items', [
                'label' => 'Benefits',
                'layout' => 'block',
                'max' => 8,
                'button_label' => 'Add benefit',
            ])
            ->addSelect('icon', [
                'label' => 'Icon',
                'choices' => self::ICONS,
                'default_value' => 'bolt',
            ])
            ->addText('title', ['label' => 'Title'])
            ->addTextarea('text', ['label' => 'Sentence', 'rows' => 2])
            ->endRepeater();

        return $fields->build();
    }
}
