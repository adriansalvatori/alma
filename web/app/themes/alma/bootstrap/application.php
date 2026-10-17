<?php

use App\Providers\ThemeServiceProvider;
use Roots\Acorn\Application;

/*
|--------------------------------------------------------------------------
| The Application Builder
|--------------------------------------------------------------------------
|
| One definition of the application, used in two places:
|
| - functions.php boots it inside WordPress.
| - tests/TestCase.php creates it without booting, for the Pest suite.
| (Larastan builds its own bare app in phpstan-bootstrap.php: it would treat a
| bootstrap/app.php as an Application and fail on a builder.)
|
| Keep this file free of direct WordPress calls so all three can load it. Modules are
| registered by ThemeServiceProvider from config/alma.php, not here.
|
*/

$base = dirname(__DIR__);

return Application::configure($base)
    ->withProviders([
        ThemeServiceProvider::class,
    ])
    ->withRouting(
        web: $base.'/routes/web.php',
        api: $base.'/routes/api.php',
    );
