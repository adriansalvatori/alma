<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        // Initialize WordPress test environment
        if (!defined('ABSPATH')) {
            define('ABSPATH', dirname(__DIR__, 2) . '/web/wp/');
        }

        // Load WordPress
        if (!function_exists('wp_get_current_user')) {
            require_once ABSPATH . 'wp-includes/pluggable.php';
        }
    }

    protected function tearDown(): void
    {
        // Clean up test data
        parent::tearDown();
    }

    /**
     * Create a test user
     */
    protected function createUser(array $args = []): int
    {
        $defaults = [
            'user_login' => 'testuser_' . uniqid(),
            'user_email' => 'test_' . uniqid() . '@example.com',
            'user_pass' => 'password',
            'role' => 'subscriber',
        ];

        return wp_insert_user(array_merge($defaults, $args));
    }

    /**
     * Delete a test user
     */
    protected function deleteUser(int $userId): void
    {
        wp_delete_user($userId, true);
    }

    /**
     * Create a test post
     */
    protected function createPost(array $args = []): int
    {
        $defaults = [
            'post_title' => 'Test Post',
            'post_content' => 'Test content',
            'post_status' => 'publish',
            'post_type' => 'post',
        ];

        return wp_insert_post(array_merge($defaults, $args));
    }

    /**
     * Delete a test post
     */
    protected function deletePost(int $postId): void
    {
        wp_delete_post($postId, true);
    }

    /**
     * Mock WordPress function
     */
    protected function mockWpFunction(string $function, callable $implementation): void
    {
        if (function_exists('Brain\Monkey\Functions\when')) {
            \Brain\Monkey\Functions\when($function)->justReturn($implementation());
        }
    }
}
