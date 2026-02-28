<?php

namespace App\Blocks;

class CtaFooterBlock extends BaseBlock
{
    public function name(): string
    {
        return 'cta-footer';
    }

    public function title(): string
    {
        return __('CTA Footer', 'alma');
    }

    public function description(): string
    {
        return __('A call to action section designed for the bottom of pages.', 'alma');
    }

    public function render(array $attributes, string $content = ""): string
    {
        return view('blocks.cta-footer.cta-footer', ['attributes' => $attributes, 'content' => $content])->render();
    }
}
