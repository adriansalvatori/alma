<?php

namespace App\Blocks;

class DashboardStatsBlock extends BaseBlock
{
    public function name(): string
    {
        return 'dashboard-stats';
    }

    public function title(): string
    {
        return __('Dashboard Stats', 'alma');
    }

    public function description(): string
    {
        return __('A dynamic Livewire-powered dashboard statistics panel.', 'alma');
    }

    public function render(array $attributes): string
    {
        return view('blocks.dashboard-stats.dashboard-stats', ['attributes' => $attributes])->render();
    }

    public function authorize(): bool
    {
        return is_user_logged_in();
    }
}
