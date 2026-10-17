<?php

namespace App\Support;

/**
 * Estimated reading time for a post, at an average adult reading speed.
 */
class ReadingTime
{
    public const WORDS_PER_MINUTE = 220;

    /**
     * Whole minutes, never less than one.
     */
    public static function minutes(string $content): int
    {
        if (function_exists('strip_shortcodes')) {
            $content = strip_shortcodes($content);
        }

        $words = str_word_count(wp_strip_all_tags($content));

        return max(1, (int) ceil($words / self::WORDS_PER_MINUTE));
    }
}
