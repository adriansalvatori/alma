<?php

use Illuminate\Filesystem\Filesystem;

/*
|--------------------------------------------------------------------------
| Test bootstrap
|--------------------------------------------------------------------------
|
| The suite never loads WordPress. It defines the constants and functions the
| theme and Acorn read (tests/Support/WordPress), then each test creates the
| real application from bootstrap/application.php on an in-memory SQLite database.
|
| The fake functions load before Composer's autoloader, so WordPress' __()
| wins over Laravel's, as it does in production.
|
*/

$storage = sys_get_temp_dir().'/alma-tests-'.getmypid();

define('ABSPATH', $storage.'/wordpress/');
define('WP_CONTENT_DIR', $storage.'/app');
define('WPINC', 'wp-includes');
define('WP_PLUGIN_DIR', WP_CONTENT_DIR.'/plugins');
define('WPMU_PLUGIN_DIR', WP_CONTENT_DIR.'/mu-plugins');
define('WP_HOME', 'https://example.test');
define('DB_HOST', '127.0.0.1');
define('DB_NAME', 'alma_tests');
define('DB_USER', 'alma');
define('DB_PASSWORD', '');
define('DB_CHARSET', 'utf8mb4');
define('WP_DEBUG', false);
define('WP_DEBUG_DISPLAY', false);
define('WP_ENV', 'testing');
define('COOKIEPATH', '/');
define('SITECOOKIEPATH', '/');
define('COOKIE_DOMAIN', '');
define('MINUTE_IN_SECONDS', 60);
define('HOUR_IN_SECONDS', 3600);
define('DAY_IN_SECONDS', 86400);
define('WEEK_IN_SECONDS', 604800);
define('MONTH_IN_SECONDS', 2592000);
define('YEAR_IN_SECONDS', 31536000);

require __DIR__.'/Support/WordPress/classes.php';
require __DIR__.'/Support/WordPress/functions.php';

$GLOBALS['wpdb'] = new wpdb;

require dirname(__DIR__).'/vendor/autoload.php';

register_shutdown_function(static function () use ($storage) {
    (new Filesystem)->deleteDirectory($storage);
});
