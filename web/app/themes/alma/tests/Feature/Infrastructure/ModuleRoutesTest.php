<?php

use App\Infrastructure\Providers\ModuleServiceProvider;
use Illuminate\Support\Facades\Route;

it('loads a module\'s web and api route files with the right groups', function () {
    $provider = new class(app()) extends ModuleServiceProvider
    {
        protected ?string $webRoutes = __DIR__.'/Fixtures/web.php';

        protected ?string $apiRoutes = __DIR__.'/Fixtures/api.php';
    };

    app()->register($provider);
    Route::getRoutes()->refreshNameLookups();

    expect(Route::getRoutes()->getByName('fixture.web')->gatherMiddleware())->toContain('web')
        ->and(Route::getRoutes()->getByName('fixture.api')->uri())->toBe('api/fixture')
        ->and(Route::getRoutes()->getByName('fixture.api')->gatherMiddleware())->toContain('api');
});
