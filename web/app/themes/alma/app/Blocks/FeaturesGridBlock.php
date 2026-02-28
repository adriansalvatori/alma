<?php

namespace App\Blocks;

class FeaturesGridBlock extends BaseBlock
{
    public function name(): string
    {
        return 'features-grid';
    }

    public function title(): string
    {
        return __('Features Grid', 'alma');
    }

    public function description(): string
    {
        return __('A bento-style grid of highlighted features.', 'alma');
    }

    public function render(array $attributes): string
    {
        return view('blocks.features-grid.features-grid', ['attributes' => $attributes])->render();
    }
}
