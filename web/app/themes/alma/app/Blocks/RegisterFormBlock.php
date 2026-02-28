<?php

namespace App\Blocks;

class RegisterFormBlock extends BaseBlock
{
    public function name(): string
    {
        return 'register-form';
    }

    public function title(): string
    {
        return __('Register Form', 'alma');
    }

    public function description(): string
    {
        return __('A dynamic Livewire-powered registration form.', 'alma');
    }

    public function render(array $attributes, string $content = ""): string
    {
        return view('blocks.register-form.register-form', ['attributes' => $attributes, 'content' => $content])->render();
    }

    public function authorize(): bool
    {
        return !is_user_logged_in();
    }
}
