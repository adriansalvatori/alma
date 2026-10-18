<?php

namespace App\Infrastructure\WordPress\Admin;

use Illuminate\Support\Facades\Vite;
use Throwable;

/**
 * Enqueues a built stylesheet from the Vite manifest, or nothing.
 *
 * The admin must keep working when the build is missing (a fresh clone before `npm run build`, a
 * deploy that lost public/build): without its manifest there is simply no skin, never an error
 * on every wp-admin screen.
 */
class AdminStyles
{
    /**
     * The built file's URL (the dev server's while `npm run dev` runs), or null without a build.
     */
    public function url(string $entry): ?string
    {
        try {
            return Vite::asset($entry);
        } catch (Throwable) {
            return null;
        }
    }

    /**
     * Enqueue $entry as a stylesheet after $dependencies. A real <link> rather than inline CSS:
     * the files are hashed, so the browser caches them across admin screens.
     *
     * @param  list<string>  $dependencies
     */
    public function enqueue(string $handle, string $entry, array $dependencies = []): bool
    {
        $url = $this->url($entry);

        if ($url === null) {
            return false;
        }

        wp_enqueue_style($handle, $url, $dependencies, null);

        return true;
    }
}
