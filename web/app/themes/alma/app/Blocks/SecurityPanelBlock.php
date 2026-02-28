<?php

namespace App\Blocks;

class SecurityPanelBlock extends BaseBlock
{
    public function name(): string
    {
        return 'security-panel';
    }

    public function title(): string
    {
        return __('Security Panel', 'alma');
    }

    public function description(): string
    {
        return __('A dynamic Livewire-powered security panel.', 'alma');
    }

    public function render(array $attributes, string $content = ""): string
    {
        return view('blocks.security-panel.security-panel', ['attributes' => $attributes, 'content' => $content])->render();
    }

    public function authorize(): bool
    {
        return is_user_logged_in();
    }
}
