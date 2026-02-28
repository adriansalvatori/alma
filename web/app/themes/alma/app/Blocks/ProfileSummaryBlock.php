<?php

namespace App\Blocks;

class ProfileSummaryBlock extends BaseBlock
{
    public function name(): string
    {
        return 'profile-summary';
    }

    public function title(): string
    {
        return __('Profile Summary', 'alma');
    }

    public function description(): string
    {
        return __('A dynamic Livewire-powered user profile summary.', 'alma');
    }

    public function render(array $attributes): string
    {
        return view('blocks.profile-summary.profile-summary', ['attributes' => $attributes])->render();
    }

    public function authorize(): bool
    {
        return is_user_logged_in();
    }
}
