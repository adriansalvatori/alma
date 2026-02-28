<?php

namespace App\Blocks;

class LogoCloudBlock extends BaseBlock
{
    public function name(): string
    {
        return 'logo-cloud';
    }

    public function title(): string
    {
        return __('Logo Cloud', 'alma');
    }

    public function description(): string
    {
        return __('A section displaying partner or client logos.', 'alma');
    }

    public function render(array $attributes): string
    {
        return view('blocks.logo-cloud.logo-cloud', ['attributes' => $attributes])->render();
    }
}
