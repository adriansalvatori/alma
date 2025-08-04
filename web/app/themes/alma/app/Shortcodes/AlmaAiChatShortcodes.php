<?php

namespace App\Shortcodes;

use App\Livewire\AlmaAI;
use Livewire\Livewire;

class AlmaAiChatShortcode
{
    public function register()
    {
        add_shortcode('alma_ai_chat', function ($atts) {
            $atts = shortcode_atts(['agent' => 'galena'], $atts);
            return Livewire::make(AlmaAI::class)->mount($atts['agent'])->render();
        });
    }
}