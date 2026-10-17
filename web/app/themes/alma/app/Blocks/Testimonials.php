<?php

namespace App\Blocks;

use App\Blocks\Concerns\HasRepeaterDefaults;
use App\Support\ResponsiveImage;
use Log1x\AcfComposer\Builder;

class Testimonials extends Block
{
    use HasRepeaterDefaults;

    public $name = 'Testimonials';

    public $slug = 'testimonials';

    public $description = 'Customer quotes with names, roles, star ratings and optional photos.';

    public $icon = 'format-quote';

    public $keywords = ['testimonials', 'reviews', 'quotes', 'social proof'];

    public const DEFAULTS = [
        'heading' => 'What our clients say',
        'intro' => 'Real words from the teams we work with.',
        'show_ratings' => true,
    ];

    public $example = [
        'heading' => 'What our clients say',
    ];

    public static function repeaterDefaults(): array
    {
        return [
            'items' => [
                ['quote' => 'We launched in five weeks and the enquiries started the same day.', 'name' => 'Alex Johnson', 'role' => 'Marketing Director', 'rating' => '5', 'photo' => null],
                ['quote' => 'Our team edits every page without waiting on a developer.', 'name' => 'Maria Garcia', 'role' => 'Head of Content', 'rating' => '5', 'photo' => null],
                ['quote' => 'Fast, secure and easy to hand over. Exactly what we needed.', 'name' => 'David Chen', 'role' => 'Founder', 'rating' => '4', 'photo' => null],
            ],
        ];
    }

    public function with(): array
    {
        return [
            'heading' => $this->value('heading'),
            'intro' => $this->value('intro'),
            'showRatings' => (bool) $this->value('show_ratings'),
            'items' => array_map(fn (array $item) => [
                ...$item,
                'rating' => max(0, min(5, (int) ($item['rating'] ?? 0))),
                'initials' => self::initials((string) ($item['name'] ?? '')),
                'photo' => ResponsiveImage::html($item['photo'] ?? null, 'thumbnail', [
                    'class' => 'h-10 w-10 rounded-full object-cover',
                    'alt' => '',
                ]),
            ], $this->rows('items')),
        ];
    }

    public static function initials(string $name): string
    {
        $words = preg_split('/\s+/', trim($name), -1, PREG_SPLIT_NO_EMPTY) ?: [];

        return strtoupper(implode('', array_map(fn ($word) => mb_substr($word, 0, 1), array_slice($words, 0, 2))));
    }

    public function fields(): array
    {
        $fields = Builder::make('testimonials_block');

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
            ->addTrueFalse('show_ratings', [
                'label' => 'Show star ratings',
                'ui' => 1,
                'default_value' => self::DEFAULTS['show_ratings'],
            ])
            ->addRepeater('items', [
                'label' => 'Testimonials',
                'layout' => 'block',
                'button_label' => 'Add testimonial',
            ])
            ->addTextarea('quote', ['label' => 'Quote', 'rows' => 3])
            ->addText('name', ['label' => 'Name'])
            ->addText('role', ['label' => 'Role or company'])
            // Numeric keys in pair form: acf-builder drops plain numeric keys.
            ->addSelect('rating', [
                'label' => 'Rating',
                'choices' => [['5' => '5 stars'], ['4' => '4 stars'], ['3' => '3 stars'], ['2' => '2 stars'], ['1' => '1 star']],
                'default_value' => '5',
            ])
            ->addImage('photo', ['label' => 'Photo (optional)', 'return_format' => 'id', 'preview_size' => 'thumbnail'])
            ->endRepeater();

        return $fields->build();
    }
}
