<?php

use PHPUnit\Framework\Assert;
use Tests\TestCase;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| Every test runs against a freshly created application (bootstrap/application.php)
| with a reset fake WordPress and the network closed. See tests/TestCase.php.
|
*/

pest()->extend(TestCase::class)->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

/**
 * Skip locally, fail in CI. For tests that need something CI always provides (a built asset
 * manifest, for example), so a missing prerequisite there cannot pass silently as a skip.
 */
function skipUnlessCi(string $reason): never
{
    if (getenv('CI')) {
        Assert::fail($reason.' CI must provide it, so this fails rather than skips.');
    }

    Assert::markTestSkipped($reason);
}

/**
 * The theme's root directory, for tests that scan source files.
 */
function theme_path(string $path = ''): string
{
    return dirname(__DIR__).($path !== '' ? '/'.ltrim($path, '/') : '');
}
