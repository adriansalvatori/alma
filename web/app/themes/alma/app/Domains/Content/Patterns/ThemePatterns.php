<?php

namespace App\Domains\Content\Patterns;

use Illuminate\Support\Facades\Log;
use Throwable;
use WP_Block_Patterns_Registry;

/**
 * Registers the theme's block patterns from patterns/, so one broken file cannot take the site
 * down with it.
 *
 * Every pattern file is included at init, on every request, to capture its markup. A bare loop
 * would let one file that throws (a typo'd field name, a helper that does not exist yet) take
 * down every page and every `wp` command. Remote Leverage had it happen twice in one day. Here
 * each file loads on its own: a file that throws is logged (and reaches Sentry) and skipped, and
 * every other pattern still registers. A page referencing the skipped pattern renders without it.
 *
 * What it cannot catch: fatals that are not Throwables (a function declared twice, memory or time
 * exhausted, exit). CI runs `php -l` over patterns/ for the syntax half of that.
 *
 * Core's _register_theme_block_patterns() scans patterns/ too, reading only headers at boot and
 * including the file later, the first time something asks for its content, with nothing around
 * that include. Registering here replaces core's entry for every slug loaded; a skipped slug is
 * unregistered as well, so core never includes the broken file on a later request.
 */
class ThemePatterns
{
    /** The header fields read from each file, in get_file_data()'s shape. */
    private const HEADERS = [
        'title' => 'Title',
        'slug' => 'Slug',
        'categories' => 'Categories',
        'description' => 'Description',
        'inserter' => 'Inserter',
    ];

    /** Whole pages: never offered in the inserter (inserting one copies it, and it stops getting fixes). */
    public const PAGES_CATEGORY = 'alma-pages';

    /** Sections an editor may insert into any page. */
    public const SECTIONS_CATEGORY = 'alma-sections';

    public function register(): void
    {
        add_action('init', function () {
            register_block_pattern_category(self::PAGES_CATEGORY, ['label' => __('Pages', 'alma')]);
            register_block_pattern_category(self::SECTIONS_CATEGORY, ['label' => __('Sections', 'alma')]);

            $this->load(get_theme_file_path('patterns'));
        }, 20);

        add_filter('doing_it_wrong_trigger_error', [self::class, 'quietStaleCoreCache'], 10, 2);
    }

    /**
     * Register every pattern file in $directory.
     *
     * @return array<string, string> The files that were skipped, each mapped to its error.
     */
    public function load(string $directory): array
    {
        $skipped = [];

        foreach (glob(rtrim($directory, '/').'/*.php') ?: [] as $file) {
            $slug = null;

            try {
                $headers = get_file_data($file, self::HEADERS);
                $slug = trim((string) ($headers['slug'] ?? ''));

                if ($slug === '' || empty($headers['title'])) {
                    continue;
                }

                $this->registerPattern($slug, $headers, $this->include($file));
            } catch (Throwable $e) {
                $skipped[$file] = $e->getMessage();
                $this->skip($file, $slug, $e);
            }
        }

        return $skipped;
    }

    /**
     * doing_it_wrong_trigger_error: keep core's notice about a renamed pattern file from becoming
     * an exception.
     *
     * Core caches pattern file names for 30 minutes. After a pattern is renamed it finds a cached
     * name missing, calls _doing_it_wrong(), and only then clears the cache. With WP_DEBUG on,
     * Acorn turns that notice into an exception first, so the cache is never cleared and every
     * request fails until it expires (Remote Leverage, 2026-10-08). Nothing is lost: load() reads
     * every file from disk anyway.
     */
    public static function quietStaleCoreCache(mixed $trigger, mixed $function): mixed
    {
        return $function === '_register_theme_block_patterns' ? false : $trigger;
    }

    /**
     * @param  array<string, string>  $headers
     */
    private function registerPattern(string $slug, array $headers, string $content): void
    {
        $categories = ! empty($headers['categories'])
            ? array_map('trim', explode(',', $headers['categories']))
            : [self::SECTIONS_CATEGORY];

        register_block_pattern($slug, [
            'title' => $headers['title'],
            'content' => $content,
            'categories' => $categories,
            'description' => $headers['description'] ?? '',
            'inserter' => match (strtolower(trim((string) ($headers['inserter'] ?? '')))) {
                'yes', 'true' => true,
                'no', 'false' => false,
                default => ! in_array(self::PAGES_CATEGORY, $categories, true),
            },
        ]);
    }

    /**
     * Include a pattern file and return what it printed. The include gets its own scope, and a
     * file that throws partway has its half-printed output discarded, not left at the top of
     * whatever page is rendering.
     */
    private function include(string $file): string
    {
        $level = ob_get_level();
        ob_start();

        try {
            (static function (string $__file): void {
                include $__file;
            })($file);

            return (string) ob_get_clean();
        } catch (Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }

            throw $e;
        }
    }

    private function skip(string $file, ?string $slug, Throwable $e): void
    {
        try {
            Log::error('A pattern file failed to load and was skipped.', [
                'file' => $file,
                'slug' => $slug,
                'error' => $e->getMessage(),
                'at' => $e->getFile().':'.$e->getLine(),
            ]);
        } catch (Throwable) {
            // Logging must not become the new way for boot to fail.
            error_log('ThemePatterns: skipped '.$file.': '.$e->getMessage());
        }

        if ($slug !== null && $slug !== '') {
            $this->unregisterFromCore($slug);
        }
    }

    protected function unregisterFromCore(string $slug): void
    {
        if (! class_exists(WP_Block_Patterns_Registry::class)) {
            return;
        }

        $registry = WP_Block_Patterns_Registry::get_instance();

        if ($registry->is_registered($slug)) {
            $registry->unregister($slug);
        }
    }
}
