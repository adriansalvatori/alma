<?php

/*
 * Fakes for the SEO module and core ops infrastructure. Every function is guarded: another
 * module's fake file may define the same one first.
 */

use Tests\Support\WordPress\WordPress;

if (! function_exists('url_to_postid')) {
    function url_to_postid(string $url): int
    {
        $path = trim((string) parse_url($url, PHP_URL_PATH), '/');

        foreach (WordPress::$posts as $post) {
            if ($post->post_name === $path) {
                return $post->ID;
            }
        }

        return 0;
    }
}

if (! function_exists('add_query_arg')) {
    function add_query_arg(mixed ...$args): string
    {
        if (is_array($args[0])) {
            [$params, $url] = [$args[0], $args[1] ?? ''];
        } else {
            [$params, $url] = [[$args[0] => $args[1]], $args[2] ?? ''];
        }

        $query = (string) parse_url((string) $url, PHP_URL_QUERY);
        parse_str($query, $existing);
        $base = strtok((string) $url, '?');

        return $base.'?'.http_build_query(array_merge($existing, $params));
    }
}

if (! function_exists('add_management_page')) {
    function add_management_page(string $pageTitle, string $menuTitle, string $capability, string $slug, ?callable $callback = null): string|false
    {
        WordPress::$options['__menu_pages'][$slug] = compact('pageTitle', 'menuTitle', 'capability');

        return 'tools_page_'.$slug;
    }
}

if (! function_exists('submit_button')) {
    function submit_button(?string $text = null, string $type = 'primary', string $name = 'submit', bool $wrap = true, mixed $other = ''): void
    {
        echo '<button type="submit" class="button button-'.esc_attr($type).'">'.esc_html($text ?? 'Save Changes').'</button>';
    }
}

if (! function_exists('selected')) {
    function selected(mixed $selected, mixed $current = true, bool $display = true): string
    {
        $result = (string) $selected === (string) $current ? ' selected="selected"' : '';

        if ($display) {
            echo $result;
        }

        return $result;
    }
}
