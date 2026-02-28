<?php

namespace App\Blocks;

class TestimonialsBlock extends BaseBlock
{
    public function name(): string
    {
        return 'testimonials';
    }

    public function title(): string
    {
        return __('Testimonials', 'alma');
    }

    public function description(): string
    {
        return __('A section displaying user reviews.', 'alma');
    }

    public function render(array $attributes, string $content = ""): string
    {
        return view('blocks.testimonials.testimonials', ['attributes' => $attributes, 'content' => $content])->render();
    }
}
