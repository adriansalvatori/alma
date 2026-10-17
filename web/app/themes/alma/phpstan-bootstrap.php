<?php

use Illuminate\Support\Facades\Facade;
use Roots\Acorn\Application;

/*
 * Larastan bootstrap. Builds a bare Acorn application so facades, config and the container
 * resolve during analysis. WordPress itself is described by the phpstan-wordpress stubs.
 *
 * This file sits inside the web root, so it does nothing unless PHPStan is the caller.
 */

if (! defined('__PHPSTAN_RUNNING__')) {
    return;
}

$app = new Application(__DIR__);

Facade::setFacadeApplication($app);

if (! defined('LARAVEL_VERSION')) {
    define('LARAVEL_VERSION', $app->version());
}
