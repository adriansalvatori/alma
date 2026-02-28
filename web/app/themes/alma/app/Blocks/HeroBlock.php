<?php

namespace App\Blocks;

class HeroBlock extends BaseBlock
{
    /**
     * Get the name of the block.
     *
     * @return string
     */
    public function name(): string
    {
        return 'hero';
    }

    /**
     * Get the title of the block.
     *
     * @return string
     */
    public function title(): string
    {
        return __('Hero', 'alma');
    }

    /**
     * Get the description of the block.
     *
     * @return string
     */
    public function description(): string
    {
        return __('A highly customizable hero section.', 'alma');
    }

    /**
     * Render the block.
     *
     * @param array $attributes
     * @return string
     */
    public function render(array $attributes, string $content = ""): string
    {
        return view('blocks.hero.hero', ['attributes' => $attributes, 'content' => $content])->render();
    }
}
