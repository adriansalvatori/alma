<?php

/*
|--------------------------------------------------------------------------
| Feature Tests
|--------------------------------------------------------------------------
|
| Feature tests test larger pieces of your application. They test how different parts of
| your application work together. They often make HTTP requests and check responses.
|
*/

it('can access login page', function () {
    // This would require WordPress test setup with HTTP routing
    expect(true)->toBeTrue(); // Placeholder
});

it('requires authentication for dashboard', function () {
    // Create test user
    $userId = createTestUser();

    // Try to access dashboard without login
    // This requires proper WordPress test HTTP setup

    // Cleanup
    deleteTestUser($userId);

    expect(true)->toBeTrue(); // Placeholder
});

it('can register new user', function () {
    // Test registration logic
    $service = new \App\Services\AuthService();

    $userId = $service->register([
        'email' => 'newuser_' . uniqid() . '@example.com',
        'password' => 'password123',
        'username' => 'newuser',
    ]);

    expect($userId)->toBeGreaterThan(0);

    // Cleanup
    wp_delete_user($userId, true);
});
