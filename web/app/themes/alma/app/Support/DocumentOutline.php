<?php

namespace App\Support;

/**
 * Heading anchors and a table of contents for long-form content.
 *
 * Every h2 gets an id (kept when it already has one), so sections can be linked to, and the
 * same pass returns the list for a table of contents. Done at render time, so nothing is written
 * into post content.
 */
class DocumentOutline
{
    /**
     * @return array{content: string, headings: list<array{id: string, text: string}>}
     */
    public static function build(string $content): array
    {
        $headings = [];
        $used = [];

        $content = (string) preg_replace_callback('#<h2([^>]*)>(.*?)</h2>#is', function (array $match) use (&$headings, &$used) {
            [$full, $attributes, $inner] = $match;
            $text = trim(wp_strip_all_tags($inner));

            if ($text === '') {
                return $full;
            }

            if (preg_match('/\bid=(["\'])(.*?)\1/i', $attributes, $existing)) {
                $id = $existing[2];
            } else {
                $base = sanitize_title($text) ?: 'section';
                $id = $base;

                for ($n = 2; isset($used[$id]); $n++) {
                    $id = "{$base}-{$n}";
                }

                $attributes .= ' id="'.esc_attr($id).'"';
            }

            $used[$id] = true;
            $headings[] = ['id' => $id, 'text' => $text];

            return "<h2{$attributes}>{$inner}</h2>";
        }, $content);

        return ['content' => $content, 'headings' => $headings];
    }
}
