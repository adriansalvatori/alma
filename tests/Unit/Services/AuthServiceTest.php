<?php

/*
|--------------------------------------------------------------------------
| Unit Tests
|--------------------------------------------------------------------------
|
| Unit tests are the smallest tests you can write. They test individual units of code in
| isolation. You can use unit tests to test a single method or function.
|
*/

it('can instantiate service', function () {
    $service = new \App\Services\AuthService();
    expect($service)->toBeInstanceOf(\App\Services\AuthService::class);
});

it('can check authentication status', function () {
    $service = new \App\Services\AuthService();

    $result = $service->check();
    expect($result)->toBe(is_user_logged_in());
});

it('can get current user', function () {
    $service = new \App\Services\AuthService();

    $user = $service->user();

    if (is_user_logged_in()) {
        expect($user)->not->toBeNull();
    } else {
        expect($user)->toBeNull();
    }
});
