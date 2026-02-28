<?php

namespace App\Blocks;

class BenefitsGridBlock extends BaseBlock
{
    public function name(): string
    {
        return 'benefits-grid';
    }

    public function title(): string
    {
        return __('Benefits Grid', 'alma');
    }

    public function description(): string
    {
        return __('A four-column grid highlighting key benefits.', 'alma');
    }

    public function render(array $attributes): string
    {
        return view('blocks.benefits-grid.benefits-grid', ['attributes' => $attributes])->render();
    }
}
