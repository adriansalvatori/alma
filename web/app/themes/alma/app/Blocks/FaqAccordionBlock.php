<?php

namespace App\Blocks;

class FaqAccordionBlock extends BaseBlock
{
    public function name(): string
    {
        return 'faq-accordion';
    }

    public function title(): string
    {
        return __('FAQ Accordion', 'alma');
    }

    public function description(): string
    {
        return __('A frequently asked questions accordion.', 'alma');
    }

    public function render(array $attributes): string
    {
        return view('blocks.faq-accordion.faq-accordion', ['attributes' => $attributes])->render();
    }
}
