<?php

namespace App\Domains\Content\Editor;

/**
 * A short, curated block inserter on pages.
 *
 * Pages are built from the theme's ACF blocks plus a few core blocks for running copy. Offering
 * all ~90 core blocks invites one-off layouts the design system does not cover. Posts keep the
 * full inserter: long-form writing needs it.
 *
 * Blocks are hidden by setting supports.inserter = false in a blocks.registerBlockType filter,
 * NOT with allowed_block_types_all: a disallowed block type makes the editor refuse to recover an
 * invalid block, so a page with one stuck block could not be repaired. The filter runs as an
 * inline script right after wp-blocks loads, before any block registers.
 */
class PageChrome
{
    public function register(): void
    {
        add_action('enqueue_block_editor_assets', [$this, 'enqueue']);
    }

    public function enqueue(): void
    {
        $screen = function_exists('get_current_screen') ? get_current_screen() : null;

        if (! $screen || $screen->post_type !== 'page') {
            return;
        }

        wp_add_inline_script('wp-blocks', self::script(self::allowed()), 'after');
    }

    /**
     * Core blocks offered on pages, in addition to every acf/* block.
     *
     * @return list<string>
     */
    public static function allowed(): array
    {
        return array_values((array) config('content.page_inserter', []));
    }

    /**
     * @param  list<string>  $allowed
     */
    public static function script(array $allowed): string
    {
        $list = wp_json_encode($allowed, JSON_UNESCAPED_SLASHES);

        return <<<JS
            wp.hooks.addFilter('blocks.registerBlockType', 'alma/page-chrome', function (settings, name) {
              var allowed = {$list};
              if (name.indexOf('acf/') === 0 || allowed.indexOf(name) !== -1) {
                return settings;
              }
              return Object.assign({}, settings, { supports: Object.assign({}, settings.supports, { inserter: false }) });
            });
            JS;
    }
}
