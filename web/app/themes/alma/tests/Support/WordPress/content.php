<?php

/*
 * Fakes for the Content module: file headers, block parsing helpers, menus and attachments.
 * Guarded, because another module's fakes may define the same function first.
 */

use Tests\Support\WordPress\WordPress;

if (! function_exists('get_file_data')) {
    /**
     * Core's header parser: "Name: value" lines in the first 8KB of the file.
     */
    function get_file_data(string $file, array $headers, string $context = ''): array
    {
        $data = (string) file_get_contents($file, false, null, 0, 8192);
        $values = [];

        foreach ($headers as $key => $header) {
            $values[$key] = preg_match('/^(?:[ \t]*<\?php)?[ \t\/*#@]*'.preg_quote($header, '/').':(.*)$/mi', $data, $m)
                ? trim(preg_replace('/\s*(?:\*\/|\?>).*/', '', $m[1]))
                : '';
        }

        return $values;
    }
}

if (! function_exists('get_post_types')) {
    function get_post_types(array|string $args = [], string $output = 'names', string $operator = 'and'): array
    {
        return ['post' => 'post', 'page' => 'page'];
    }
}

if (! function_exists('wp_get_attachment_image')) {
    /**
     * Attachments are seeded as WordPress::$posts with post_type 'attachment'; the fake renders a
     * recognisable tag with the attributes it was given.
     */
    function wp_get_attachment_image(int $id, string|array $size = 'thumbnail', bool $icon = false, string|array $attr = ''): string
    {
        $post = WordPress::$posts[$id] ?? null;

        if (! $post || $post->post_type !== 'attachment') {
            return '';
        }

        $html = '<img src="https://example.test/app/uploads/'.$post->post_name.'.jpg" width="1200" height="800"';

        foreach ((array) $attr as $name => $value) {
            $html .= ' '.$name.'="'.esc_attr((string) $value).'"';
        }

        return $html.' />';
    }
}

if (! function_exists('has_block')) {
    function has_block(string $name, mixed $post = null): bool
    {
        $content = is_string($post) ? $post : (string) (get_post($post)?->post_content ?? '');

        return str_contains($content, '<!-- wp:'.$name.' ') || str_contains($content, '<!-- wp:'.$name.' /');
    }
}

if (! function_exists('do_blocks')) {
    function do_blocks(string $content): string
    {
        return $content;
    }
}

if (! function_exists('strip_shortcodes')) {
    function strip_shortcodes(string $content): string
    {
        return preg_replace('/\[[^\]]+\]/', '', $content);
    }
}

if (! function_exists('get_nav_menu_locations')) {
    function get_nav_menu_locations(): array
    {
        return [];
    }
}

if (! function_exists('wp_get_nav_menu_items')) {
    function wp_get_nav_menu_items(int|string|object $menu, array $args = []): array|false
    {
        return [];
    }
}

if (! function_exists('get_search_query')) {
    function get_search_query(bool $escaped = true): string
    {
        return (string) ($_GET['s'] ?? '');
    }
}

if (! function_exists('esc_attr_x')) {
    function esc_attr_x(string $text, string $context, string $domain = 'default'): string
    {
        return esc_attr($text);
    }
}

/*
 * Core functions FrontEndCleanup unhooks by name. They must exist (be callable) for remove_action()
 * to accept them; their bodies never run in the suite.
 */
if (! function_exists('print_emoji_detection_script')) {
    function print_emoji_detection_script(): void {}
}

if (! function_exists('print_emoji_styles')) {
    function print_emoji_styles(): void {}
}
