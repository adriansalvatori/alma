<?php

namespace App\Domains\Content\Hooks;

use Illuminate\Support\Facades\Blade;

/**
 * Render core Buttons blocks as Flux buttons on the front end, so a button typed in the editor
 * looks like every other button on the site.
 *
 * Editor content is data, never template source. The previous version interpolated the label and
 * link into a Blade string and compiled it, which let anyone who could edit a post run PHP by
 * typing `@php ... @endphp` into a button label. Values are passed as variables instead.
 */
class CoreButtons
{
    public function register(): void
    {
        add_filter('render_block_core/buttons', [$this, 'buttons'], 10, 2);
        add_filter('render_block_core/button', [$this, 'button'], 10, 2);
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public function buttons(string $content, array $block): string
    {
        if (is_admin()) {
            return $content;
        }

        return '<div class="flex flex-wrap items-center gap-3">'.$content.'</div>';
    }

    /**
     * @param  array<string, mixed>  $block
     */
    public function button(string $content, array $block): string
    {
        if (is_admin()) {
            return $content;
        }

        $html = (string) ($block['innerHTML'] ?? $content);
        $class = (string) ($block['attrs']['className'] ?? '');

        $variant = match (true) {
            str_contains($class, 'is-style-outline') => 'outline',
            str_contains($class, 'is-style-ghost') => 'ghost',
            default => 'primary',
        };

        $href = preg_match('/href="([^"]*)"/', $html, $m) ? html_entity_decode($m[1]) : '#';
        $text = preg_match('/<a[^>]*>(.*?)<\/a>/su', $html, $m) ? $m[1] : $html;

        return Blade::render(
            '<flux:button :href="$href" :variant="$variant">{{ $text }}</flux:button>',
            [
                'href' => $href !== '' ? $href : '#',
                'variant' => $variant,
                'text' => trim(html_entity_decode(wp_strip_all_tags($text))),
            ],
        );
    }
}
