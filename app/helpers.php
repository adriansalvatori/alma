<?php

namespace App;

/**
 * Check hot reload status
 *
 * @return boolean
 */
function hmr_enabled(): bool
{
    return env('HMR_ENABLED') ?: !file_exists(get_theme_file_path('/public/manifest.json'));
}

/**
 * Build assets hmr uri
 *
 * @param string $asset
 *
 * @return string
 */
function hmr_assets(string $asset): string
{
    $entrypoint = env('HMR_ENTRYPOINT') ?: 'http://localhost:5173';

    return $entrypoint ? "{$entrypoint}/{$asset}" : asset($asset);
}

collect(['add_to_cart', 'mailer', 'breadcrumbs'])
    ->each(function ($file) {
        if (! locate_template($file = "app/Helpers/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'sage'), $file)
            );
        }
    });