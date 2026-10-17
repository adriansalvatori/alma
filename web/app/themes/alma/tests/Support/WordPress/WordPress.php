<?php

namespace Tests\Support\WordPress;

/**
 * In-memory state behind the fake WordPress functions in functions.php.
 *
 * The suite never loads WordPress. Instead, the WordPress functions the theme calls are defined
 * against this store, and TestCase resets it before every test. Tests seed it directly
 * (WordPress::$options['x'] = ...) or through the normal functions (update_option()).
 *
 * Add a function to functions.php when the code under test needs one; keep its behaviour as
 * close to core as the tests need, and no closer.
 */
final class WordPress
{
    /** @var array<string, array<int, list<array{callback: callable|string|array, args: int}>>> */
    public static array $filters = [];

    /** @var array<string, int> */
    public static array $actionsFired = [];

    /** @var list<string> */
    public static array $currentFilter = [];

    /** @var array<string, mixed> */
    public static array $options = [];

    /** @var array<string, array{value: mixed, expires: int}> */
    public static array $transients = [];

    /** @var array<string, mixed> */
    public static array $cache = [];

    /** @var array<int, \WP_User> */
    public static array $users = [];

    /** @var array<int, array<string, list<mixed>>> */
    public static array $userMeta = [];

    /** @var array<int, \WP_Post> */
    public static array $posts = [];

    /** @var array<int, array<string, list<mixed>>> */
    public static array $postMeta = [];

    public static int $currentUserId = 0;

    /** @var list<array{user_id: int, remember: bool}> */
    public static array $authCookies = [];

    /** @var list<array{to: string|array, subject: string, message: string, headers: string|array}> */
    public static array $mails = [];

    public static bool $mailSucceeds = true;

    /** @var list<array{location: string, status: int}> */
    public static array $redirects = [];

    /** @var list<array{url: string, args: array}> */
    public static array $httpRequests = [];

    /** @var list<array|\WP_Error> */
    public static array $httpResponses = [];

    /** @var array<string, array{timestamp: int, recurrence: string|false, args: array}> */
    public static array $cron = [];

    /** @var array<string, array<string, mixed>> */
    public static array $blockPatterns = [];

    /** @var array<string, bool> */
    public static array $conditionals = [];

    /**
     * ACF field values, keyed by post ID (or 'option', or 'block'), then field name.
     *
     * @var array<int|string, array<string, mixed>>
     */
    public static array $acf = [];

    public static bool $isAdmin = false;

    public static bool $isSsl = true;

    public static string $environment = 'local';

    public static string $homeUrl = 'https://example.test';

    public static int $nextId = 1;

    public static function reset(): void
    {
        self::$filters = [];
        self::$actionsFired = [];
        self::$currentFilter = [];
        self::$options = [
            'blogname' => 'Alma',
            'blogdescription' => 'A WordPress boilerplate',
            'admin_email' => 'admin@example.test',
            'users_can_register' => '1',
            'default_role' => 'subscriber',
            'permalink_structure' => '/%postname%/',
            'date_format' => 'F j, Y',
            'time_format' => 'g:i a',
        ];
        self::$transients = [];
        self::$cache = [];
        self::$users = [];
        self::$userMeta = [];
        self::$posts = [];
        self::$postMeta = [];
        self::$currentUserId = 0;
        self::$authCookies = [];
        self::$mails = [];
        self::$mailSucceeds = true;
        self::$redirects = [];
        self::$httpRequests = [];
        self::$httpResponses = [];
        self::$cron = [];
        self::$blockPatterns = [];
        self::$conditionals = [];
        self::$acf = [];
        self::$isAdmin = false;
        self::$isSsl = true;
        self::$environment = 'local';
        self::$homeUrl = 'https://example.test';
        self::$nextId = 1;
    }

    /**
     * Create a user and return it. Roles map to the capabilities the code checks.
     *
     * @param  array<string, mixed>  $attributes
     */
    public static function user(array $attributes = []): \WP_User
    {
        $id = self::$nextId++;

        $user = new \WP_User($id, [
            'user_login' => $attributes['user_login'] ?? "user{$id}",
            'user_email' => $attributes['user_email'] ?? "user{$id}@example.test",
            'user_pass' => wp_hash_password($attributes['user_pass'] ?? 'password'),
            'display_name' => $attributes['display_name'] ?? ($attributes['user_login'] ?? "User {$id}"),
            'first_name' => $attributes['first_name'] ?? '',
            'last_name' => $attributes['last_name'] ?? '',
        ], (array) ($attributes['role'] ?? 'subscriber'));

        self::$users[$id] = $user;

        return $user;
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function post(array $attributes = []): \WP_Post
    {
        $id = self::$nextId++;

        $post = new \WP_Post((object) array_merge([
            'ID' => $id,
            'post_title' => "Post {$id}",
            'post_name' => "post-{$id}",
            'post_content' => '',
            'post_excerpt' => '',
            'post_status' => 'publish',
            'post_type' => 'page',
            'post_author' => 0,
            'post_parent' => 0,
            'post_date' => '2026-01-01 00:00:00',
            'post_modified' => '2026-01-01 00:00:00',
        ], $attributes, ['ID' => $id]));

        self::$posts[$id] = $post;

        return $post;
    }

    /**
     * Queue a response for the next wp_remote_* call.
     *
     * @param  array<string, string>  $headers
     */
    public static function respondWith(int $status, string $body = '', array $headers = []): void
    {
        self::$httpResponses[] = [
            'headers' => $headers,
            'body' => $body,
            'response' => ['code' => $status, 'message' => ''],
        ];
    }

    /**
     * Run a filter chain. Shared by apply_filters() and do_action().
     *
     * @param  array<int, mixed>  $args
     */
    public static function run(string $hook, array $args): mixed
    {
        $value = $args[0] ?? null;

        if (! isset(self::$filters[$hook])) {
            return $value;
        }

        self::$currentFilter[] = $hook;
        ksort(self::$filters[$hook]);

        foreach (self::$filters[$hook] as $callbacks) {
            foreach ($callbacks as $entry) {
                $args[0] = $value;
                $value = call_user_func_array($entry['callback'], array_slice($args, 0, max(0, $entry['args'])));
            }
        }

        array_pop(self::$currentFilter);

        return $value;
    }
}
