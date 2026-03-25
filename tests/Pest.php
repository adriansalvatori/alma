<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses(Tests\TestCase::class)->in('Unit', 'Feature');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| top-level functions to use in your tests, just like you would with regular test functions.
|
*/

function createTestUser(array $args = []): int
{
    $defaults = [
        'user_login' => 'testuser_' . uniqid(),
        'user_email' => 'test_' . uniqid() . '@example.com',
        'user_pass' => 'password',
        'role' => 'subscriber',
    ];

    return wp_insert_user(array_merge($defaults, $args));
}

function deleteTestUser(int $userId): void
{
    wp_delete_user($userId, true);
}

function createTestPost(array $args = []): int
{
    $defaults = [
        'post_title' => 'Test Post',
        'post_content' => 'Test content',
        'post_status' => 'publish',
        'post_type' => 'post',
    ];

    return wp_insert_post(array_merge($defaults, $args));
}

function deleteTestPost(int $postId): void
{
    wp_delete_post($postId, true);
}
