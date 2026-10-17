<?php

/**
 * Configuration overrides for WP_ENV === 'production'
 */

use Roots\WPConfig\Config;

use function Env\env;

/**
 * Production is indexable unless told otherwise. Set DISALLOW_INDEXING=true for a production
 * install that is not public yet (a pre-launch site on its final host).
 */
Config::define('DISALLOW_INDEXING', (bool) env('DISALLOW_INDEXING'));
