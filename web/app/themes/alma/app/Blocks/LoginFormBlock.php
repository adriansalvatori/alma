<?php

namespace App\Blocks;

class LoginFormBlock extends BaseBlock
{
    public function name(): string
    {
        return 'login-form';
    }

    public function title(): string
    {
        return __('Login Form', 'alma');
    }

    public function description(): string
    {
        return __('A dynamic Livewire-powered login form.', 'alma');
    }

    public function render(array $attributes): string
    {
        return view('blocks.login-form.login-form', ['attributes' => $attributes])->render();
    }
}
