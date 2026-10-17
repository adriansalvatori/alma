<?php

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our theme. We will simply require it into the script here so that we
| don't have to worry about manually loading any of our classes later on.
|
*/

if (! file_exists($composer = __DIR__.'/vendor/autoload.php')) {
    wp_die(__('Error locating autoloader. Please run <code>composer install</code>.', 'alma'));
}

require $composer;

/*
|--------------------------------------------------------------------------
| Boot The Application
|--------------------------------------------------------------------------
|
| The builder lives in bootstrap/application.php so the test suite and Larastan can
| create the exact same application without booting it inside WordPress.
|
*/

(require __DIR__.'/bootstrap/application.php')->boot();

/*
|--------------------------------------------------------------------------
| Register Theme Files
|--------------------------------------------------------------------------
|
| Plain WordPress theme setup (supports, menus, sidebars, assets). Anything
| with behaviour belongs in a class registered by a module provider instead.
|
*/

collect(['setup', 'filters'])
    ->each(function ($file) {
        if (! locate_template($file = "app/{$file}.php", true, true)) {
            wp_die(
                /* translators: %s is replaced with the relative file path */
                sprintf(__('Error locating <code>%s</code> for inclusion.', 'alma'), $file),
            );
        }
    });
