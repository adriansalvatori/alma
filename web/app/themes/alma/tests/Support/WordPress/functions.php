<?php

/*
 * Fake WordPress functions, backed by Tests\Support\WordPress\WordPress.
 *
 * Loaded before Composer's autoloader so WordPress' signatures win where Laravel defines a helper
 * of the same name (__()), exactly as they do in production. Grouped by area; add what you need.
 */

use Tests\Support\WordPress\WordPress;

// ---------------------------------------------------------------------------------------------
// Hooks
// ---------------------------------------------------------------------------------------------

// Callbacks are typed loosely, like core: WordPress accepts the name of a function that does not
// exist (yet), and code that removes core's own hooks passes exactly such names.

function add_filter(string $hook, callable|string|array $callback, int $priority = 10, int $args = 1): bool
{
    WordPress::$filters[$hook][$priority][] = ['callback' => $callback, 'args' => $args];

    return true;
}

function add_action(string $hook, callable|string|array $callback, int $priority = 10, int $args = 1): bool
{
    return add_filter($hook, $callback, $priority, $args);
}

function remove_filter(string $hook, callable|string|array $callback, int $priority = 10): bool
{
    foreach (WordPress::$filters[$hook][$priority] ?? [] as $i => $entry) {
        if ($entry['callback'] === $callback) {
            unset(WordPress::$filters[$hook][$priority][$i]);

            return true;
        }
    }

    return false;
}

function remove_action(string $hook, callable|string|array $callback, int $priority = 10): bool
{
    return remove_filter($hook, $callback, $priority);
}

function remove_all_filters(string $hook): bool
{
    unset(WordPress::$filters[$hook]);

    return true;
}

function remove_all_actions(string $hook): bool
{
    return remove_all_filters($hook);
}

function has_filter(string $hook, callable|string|array|false $callback = false): bool|int
{
    if ($callback === false) {
        return ! empty(WordPress::$filters[$hook]);
    }

    foreach (WordPress::$filters[$hook] ?? [] as $priority => $entries) {
        foreach ($entries as $entry) {
            if ($entry['callback'] === $callback) {
                return $priority;
            }
        }
    }

    return false;
}

function has_action(string $hook, callable|string|array|false $callback = false): bool|int
{
    return has_filter($hook, $callback);
}

function apply_filters(string $hook, mixed $value = null, mixed ...$args): mixed
{
    return WordPress::run($hook, [$value, ...$args]);
}

function apply_filters_ref_array(string $hook, array $args): mixed
{
    return WordPress::run($hook, $args);
}

function do_action(string $hook, mixed ...$args): void
{
    WordPress::$actionsFired[$hook] = (WordPress::$actionsFired[$hook] ?? 0) + 1;
    WordPress::run($hook, $args === [] ? [''] : $args);
}

function do_action_ref_array(string $hook, array $args): void
{
    do_action($hook, ...$args);
}

function did_action(string $hook): int
{
    return WordPress::$actionsFired[$hook] ?? 0;
}

function doing_action(?string $hook = null): bool
{
    return $hook === null ? WordPress::$currentFilter !== [] : in_array($hook, WordPress::$currentFilter, true);
}

function doing_filter(?string $hook = null): bool
{
    return doing_action($hook);
}

function current_filter(): string
{
    return (string) end(WordPress::$currentFilter);
}

function __return_true(): bool
{
    return true;
}

function __return_false(): bool
{
    return false;
}

function __return_empty_array(): array
{
    return [];
}

function __return_null(): mixed
{
    return null;
}

// ---------------------------------------------------------------------------------------------
// Options, transients, object cache
// ---------------------------------------------------------------------------------------------

function get_option(string $name, mixed $default = false): mixed
{
    return array_key_exists($name, WordPress::$options) ? WordPress::$options[$name] : $default;
}

function update_option(string $name, mixed $value, bool|string|null $autoload = null): bool
{
    WordPress::$options[$name] = $value;

    return true;
}

function add_option(string $name, mixed $value = '', string $deprecated = '', bool|string|null $autoload = null): bool
{
    if (array_key_exists($name, WordPress::$options)) {
        return false;
    }

    WordPress::$options[$name] = $value;

    return true;
}

function delete_option(string $name): bool
{
    $existed = array_key_exists($name, WordPress::$options);
    unset(WordPress::$options[$name]);

    return $existed;
}

function get_transient(string $name): mixed
{
    $entry = WordPress::$transients[$name] ?? null;

    if ($entry === null || ($entry['expires'] > 0 && $entry['expires'] < time())) {
        unset(WordPress::$transients[$name]);

        return false;
    }

    return $entry['value'];
}

function set_transient(string $name, mixed $value, int $expiration = 0): bool
{
    WordPress::$transients[$name] = ['value' => $value, 'expires' => $expiration > 0 ? time() + $expiration : 0];

    return true;
}

function delete_transient(string $name): bool
{
    $existed = isset(WordPress::$transients[$name]);
    unset(WordPress::$transients[$name]);

    return $existed;
}

function wp_cache_get(int|string $key, string $group = '', bool $force = false, ?bool &$found = null): mixed
{
    $found = array_key_exists("{$group}:{$key}", WordPress::$cache);

    return $found ? WordPress::$cache["{$group}:{$key}"] : false;
}

function wp_cache_set(int|string $key, mixed $data, string $group = '', int $expire = 0): bool
{
    WordPress::$cache["{$group}:{$key}"] = $data;

    return true;
}

function wp_cache_add(int|string $key, mixed $data, string $group = '', int $expire = 0): bool
{
    if (array_key_exists("{$group}:{$key}", WordPress::$cache)) {
        return false;
    }

    return wp_cache_set($key, $data, $group, $expire);
}

function wp_cache_delete(int|string $key, string $group = ''): bool
{
    unset(WordPress::$cache["{$group}:{$key}"]);

    return true;
}

function wp_cache_flush(): bool
{
    WordPress::$cache = [];

    return true;
}

function wp_using_ext_object_cache(?bool $using = null): bool
{
    return false;
}

// ---------------------------------------------------------------------------------------------
// Users, authentication, capabilities
// ---------------------------------------------------------------------------------------------

function get_user_by(string $field, int|string $value): WP_User|false
{
    foreach (WordPress::$users as $user) {
        $match = match ($field) {
            'id', 'ID' => $user->ID === (int) $value,
            'email' => strcasecmp($user->data->user_email, (string) $value) === 0,
            'login' => $user->data->user_login === (string) $value,
            'slug' => sanitize_title($user->data->user_login) === (string) $value,
            default => false,
        };

        if ($match) {
            return $user;
        }
    }

    return false;
}

function get_userdata(int $id): WP_User|false
{
    return get_user_by('id', $id);
}

function wp_get_current_user(): WP_User
{
    return WordPress::$users[WordPress::$currentUserId] ?? new WP_User(0);
}

function get_current_user_id(): int
{
    return wp_get_current_user()->ID;
}

function wp_set_current_user(int $id, string $name = ''): WP_User
{
    WordPress::$currentUserId = $id;

    return wp_get_current_user();
}

function is_user_logged_in(): bool
{
    return wp_get_current_user()->exists();
}

function username_exists(string $login): int|false
{
    return ($user = get_user_by('login', $login)) ? $user->ID : false;
}

function email_exists(string $email): int|false
{
    return ($user = get_user_by('email', $email)) ? $user->ID : false;
}

function validate_username(string $login): bool
{
    return $login !== '' && sanitize_user($login, true) === $login;
}

function wp_insert_user(array|object $data): int|WP_Error
{
    $data = (array) $data;

    if (empty($data['user_login'])) {
        return new WP_Error('empty_user_login', 'Cannot create a user with an empty login name.');
    }

    if (username_exists($data['user_login'])) {
        return new WP_Error('existing_user_login', 'Sorry, that username already exists!');
    }

    if (! empty($data['user_email']) && email_exists($data['user_email'])) {
        return new WP_Error('existing_user_email', 'Sorry, that email address is already used!');
    }

    $user = WordPress::user([
        'user_login' => $data['user_login'],
        'user_email' => $data['user_email'] ?? '',
        'user_pass' => $data['user_pass'] ?? wp_generate_password(),
        'display_name' => $data['display_name'] ?? $data['user_login'],
        'first_name' => $data['first_name'] ?? '',
        'last_name' => $data['last_name'] ?? '',
        'role' => $data['role'] ?? get_option('default_role', 'subscriber'),
    ]);

    do_action('user_register', $user->ID, $data);

    return $user->ID;
}

function wp_update_user(array|object $data): int|WP_Error
{
    $data = (array) $data;
    $user = get_userdata((int) ($data['ID'] ?? 0));

    if (! $user) {
        return new WP_Error('invalid_user_id', 'Invalid user ID.');
    }

    foreach ($data as $key => $value) {
        if ($key === 'ID') {
            continue;
        }

        if ($key === 'user_pass') {
            $value = wp_hash_password($value);
        }

        if ($key === 'role') {
            $user->set_role($value);

            continue;
        }

        $user->data->{$key} = $value;
    }

    return $user->ID;
}

function wp_delete_user(int $id, ?int $reassign = null): bool
{
    unset(WordPress::$users[$id], WordPress::$userMeta[$id]);

    return true;
}

function wp_hash_password(string $password): string
{
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 4]);
}

function wp_check_password(string $password, string $hash, int|string $userId = ''): bool
{
    return password_verify($password, $hash);
}

function wp_set_password(string $password, int $userId): void
{
    if ($user = get_userdata($userId)) {
        $user->data->user_pass = wp_hash_password($password);
    }
}

/**
 * Runs the `authenticate` filter chain like core: core's own username/email checks first (at
 * priority 20 and 30 in core, collapsed into one step here), then anything the theme added.
 */
function wp_authenticate(string $username, string $password): WP_User|WP_Error
{
    $user = null;

    if ($username === '' || $password === '') {
        $user = new WP_Error('empty_username', 'The username or password field is empty.');
    } else {
        $candidate = str_contains($username, '@') ? get_user_by('email', $username) : get_user_by('login', $username);

        $user = $candidate && wp_check_password($password, $candidate->data->user_pass, $candidate->ID)
            ? $candidate
            : new WP_Error('incorrect_password', 'The username or password you entered is incorrect.');
    }

    $user = apply_filters('authenticate', $user, $username, $password);

    if ($user instanceof WP_Error) {
        do_action('wp_login_failed', $username, $user);
    }

    return $user;
}

function wp_set_auth_cookie(int $userId, bool $remember = false, bool|string $secure = '', string $token = ''): void
{
    WordPress::$authCookies[] = ['user_id' => $userId, 'remember' => $remember];
    do_action('set_logged_in_cookie', '', 0, 0, $userId, 'logged_in', $token);
}

function wp_clear_auth_cookie(): void
{
    WordPress::$authCookies = [];
    do_action('clear_auth_cookie');
}

function wp_logout(): void
{
    $userId = get_current_user_id();
    wp_clear_auth_cookie();
    wp_set_current_user(0);
    do_action('wp_logout', $userId);
}

function user_can(int|WP_User $user, string $capability, mixed ...$args): bool
{
    $user = $user instanceof WP_User ? $user : get_userdata($user);

    return $user && $user->has_cap($capability, ...$args);
}

function current_user_can(string $capability, mixed ...$args): bool
{
    return user_can(wp_get_current_user(), $capability, ...$args);
}

function get_user_meta(int $userId, string $key = '', bool $single = false): mixed
{
    if ($key === '') {
        return WordPress::$userMeta[$userId] ?? [];
    }

    $values = WordPress::$userMeta[$userId][$key] ?? [];

    return $single ? ($values[0] ?? '') : $values;
}

function update_user_meta(int $userId, string $key, mixed $value, mixed $previous = ''): bool
{
    WordPress::$userMeta[$userId][$key] = [$value];

    return true;
}

function add_user_meta(int $userId, string $key, mixed $value, bool $unique = false): int|false
{
    if ($unique && isset(WordPress::$userMeta[$userId][$key])) {
        return false;
    }

    WordPress::$userMeta[$userId][$key][] = $value;

    return WordPress::$nextId++;
}

function delete_user_meta(int $userId, string $key, mixed $value = ''): bool
{
    unset(WordPress::$userMeta[$userId][$key]);

    return true;
}

function get_users(array $args = []): array
{
    return array_values(array_filter(
        WordPress::$users,
        fn (WP_User $user) => empty($args['role']) || in_array($args['role'], $user->roles, true),
    ));
}

// ---------------------------------------------------------------------------------------------
// Posts and post meta
// ---------------------------------------------------------------------------------------------

function get_post(int|WP_Post|null $post = null): ?WP_Post
{
    if ($post instanceof WP_Post) {
        return $post;
    }

    return WordPress::$posts[(int) $post] ?? null;
}

function get_post_field(string $field, int|WP_Post|null $post = null): mixed
{
    return get_post($post)?->{$field} ?? '';
}

function get_post_status(int|WP_Post|null $post = null): string|false
{
    return get_post($post)?->post_status ?? false;
}

function get_post_type(int|WP_Post|null $post = null): string|false
{
    return get_post($post)?->post_type ?? false;
}

function get_the_title(int|WP_Post $post = 0): string
{
    return get_post($post)?->post_title ?? '';
}

function get_permalink(int|WP_Post $post = 0): string|false
{
    $post = get_post($post);

    return $post ? home_url('/'.$post->post_name.'/') : false;
}

function get_page_by_path(string $path, string $output = 'OBJECT', string|array $type = 'page'): ?WP_Post
{
    foreach (WordPress::$posts as $post) {
        if ($post->post_name === trim($path, '/') && in_array($post->post_type, (array) $type, true)) {
            return $post;
        }
    }

    return null;
}

function wp_insert_post(array $data, bool $wpError = false): int|WP_Error
{
    if (! empty($data['ID']) && isset(WordPress::$posts[(int) $data['ID']])) {
        $post = WordPress::$posts[(int) $data['ID']];

        foreach ($data as $key => $value) {
            $post->{$key} = $value;
        }

        return $post->ID;
    }

    return WordPress::post($data)->ID;
}

function wp_update_post(array|object $data, bool $wpError = false): int|WP_Error
{
    return wp_insert_post((array) $data, $wpError);
}

function get_post_meta(int $postId, string $key = '', bool $single = false): mixed
{
    if ($key === '') {
        return WordPress::$postMeta[$postId] ?? [];
    }

    $values = WordPress::$postMeta[$postId][$key] ?? [];

    return $single ? ($values[0] ?? '') : $values;
}

function update_post_meta(int $postId, string $key, mixed $value, mixed $previous = ''): bool
{
    WordPress::$postMeta[$postId][$key] = [$value];

    return true;
}

function add_post_meta(int $postId, string $key, mixed $value, bool $unique = false): int|false
{
    if ($unique && isset(WordPress::$postMeta[$postId][$key])) {
        return false;
    }

    WordPress::$postMeta[$postId][$key][] = $value;

    return WordPress::$nextId++;
}

function delete_post_meta(int $postId, string $key, mixed $value = ''): bool
{
    unset(WordPress::$postMeta[$postId][$key]);

    return true;
}

function wp_slash(mixed $value): mixed
{
    return is_array($value) ? array_map('wp_slash', $value) : (is_string($value) ? addslashes($value) : $value);
}

function wp_unslash(mixed $value): mixed
{
    return stripslashes_deep($value);
}

function stripslashes_deep(mixed $value): mixed
{
    return is_array($value) ? array_map('stripslashes_deep', $value) : (is_string($value) ? stripslashes($value) : $value);
}

// ---------------------------------------------------------------------------------------------
// URLs, paths, environment
// ---------------------------------------------------------------------------------------------

function home_url(string $path = '', ?string $scheme = null): string
{
    return rtrim(WordPress::$homeUrl, '/').($path !== '' ? '/'.ltrim($path, '/') : '');
}

function site_url(string $path = '', ?string $scheme = null): string
{
    return rtrim(WordPress::$homeUrl, '/').'/wp'.($path !== '' ? '/'.ltrim($path, '/') : '');
}

function admin_url(string $path = '', string $scheme = 'admin'): string
{
    return site_url('wp-admin/'.ltrim($path, '/'));
}

function rest_url(string $path = '', string $scheme = 'rest'): string
{
    return home_url('wp-json/'.ltrim($path, '/'));
}

function content_url(string $path = ''): string
{
    return home_url('app/'.ltrim($path, '/'));
}

function wp_login_url(string $redirect = '', bool $forceReauth = false): string
{
    return site_url('wp-login.php').($redirect !== '' ? '?redirect_to='.rawurlencode($redirect) : '');
}

function wp_logout_url(string $redirect = ''): string
{
    return site_url('wp-login.php?action=logout&_wpnonce='.wp_create_nonce('log-out')).($redirect !== '' ? '&redirect_to='.rawurlencode($redirect) : '');
}

function wp_lostpassword_url(string $redirect = ''): string
{
    return site_url('wp-login.php?action=lostpassword');
}

function wp_registration_url(): string
{
    return site_url('wp-login.php?action=register');
}

function get_theme_file_path(string $file = ''): string
{
    return dirname(__DIR__, 3).($file !== '' ? '/'.ltrim($file, '/') : '');
}

function get_theme_file_uri(string $file = ''): string
{
    return home_url('app/themes/alma'.($file !== '' ? '/'.ltrim($file, '/') : ''));
}

function get_template_directory(): string
{
    return get_theme_file_path();
}

function get_stylesheet_directory(): string
{
    return get_theme_file_path();
}

function get_template_directory_uri(): string
{
    return get_theme_file_uri();
}

function get_stylesheet_directory_uri(): string
{
    return get_theme_file_uri();
}

function get_stylesheet(): string
{
    return 'alma';
}

function get_template(): string
{
    return 'alma';
}

function locate_template(string|array $names, bool $load = false, bool $once = true, array $args = []): string
{
    foreach ((array) $names as $name) {
        if (is_file($path = get_theme_file_path($name))) {
            return $path;
        }
    }

    return '';
}

function wp_upload_dir(?string $time = null, bool $createDir = true, bool $refresh = false): array
{
    $base = WP_CONTENT_DIR.'/uploads';

    return ['path' => $base, 'url' => home_url('app/uploads'), 'subdir' => '', 'basedir' => $base, 'baseurl' => home_url('app/uploads'), 'error' => false];
}

function wp_normalize_path(string $path): string
{
    return str_replace('\\', '/', $path);
}

function trailingslashit(string $value): string
{
    return rtrim($value, '/\\').'/';
}

function untrailingslashit(string $value): string
{
    return rtrim($value, '/\\');
}

function wp_get_environment_type(): string
{
    return WordPress::$environment;
}

function is_admin(): bool
{
    return WordPress::$isAdmin;
}

function is_ssl(): bool
{
    return WordPress::$isSsl;
}

function wp_doing_ajax(): bool
{
    return false;
}

function wp_doing_cron(): bool
{
    return false;
}

function wp_is_json_request(): bool
{
    return false;
}

function is_multisite(): bool
{
    return false;
}

function get_current_blog_id(): int
{
    return 1;
}

function wp_is_block_theme(): bool
{
    return false;
}

function get_bloginfo(string $show = '', string $filter = 'raw'): string
{
    return match ($show) {
        'name' => (string) get_option('blogname'),
        'description' => (string) get_option('blogdescription'),
        'url', 'wpurl' => home_url(),
        'admin_email' => (string) get_option('admin_email'),
        'charset' => 'UTF-8',
        'language' => 'en-US',
        'version' => '7.1.3',
        default => '',
    };
}

function get_locale(): string
{
    return 'en_US';
}

function determine_locale(): string
{
    return 'en_US';
}

function wp_timezone(): DateTimeZone
{
    return new DateTimeZone('UTC');
}

function wp_timezone_string(): string
{
    return 'UTC';
}

function current_time(string $type, bool $gmt = false): int|string
{
    return $type === 'timestamp' || $type === 'U' ? time() : gmdate($type === 'mysql' ? 'Y-m-d H:i:s' : $type);
}

// ---------------------------------------------------------------------------------------------
// Conditionals (seed WordPress::$conditionals['is_front_page'] = true, and so on)
// ---------------------------------------------------------------------------------------------

function is_front_page(): bool
{
    return WordPress::$conditionals['is_front_page'] ?? false;
}

function is_home(): bool
{
    return WordPress::$conditionals['is_home'] ?? false;
}

function is_404(): bool
{
    return WordPress::$conditionals['is_404'] ?? false;
}

function is_search(): bool
{
    return WordPress::$conditionals['is_search'] ?? false;
}

function is_singular(string|array $types = ''): bool
{
    return WordPress::$conditionals['is_singular'] ?? false;
}

function is_page(mixed $page = ''): bool
{
    return WordPress::$conditionals['is_page'] ?? false;
}

function is_single(mixed $post = ''): bool
{
    return WordPress::$conditionals['is_single'] ?? false;
}

function is_archive(): bool
{
    return WordPress::$conditionals['is_archive'] ?? false;
}

function is_feed(string|array $feeds = ''): bool
{
    return WordPress::$conditionals['is_feed'] ?? false;
}

function is_robots(): bool
{
    return WordPress::$conditionals['is_robots'] ?? false;
}

function get_queried_object(): mixed
{
    return WordPress::$conditionals['queried_object'] ?? null;
}

function get_queried_object_id(): int
{
    $object = get_queried_object();

    return $object->ID ?? 0;
}

// ---------------------------------------------------------------------------------------------
// Escaping, sanitising, formatting
// ---------------------------------------------------------------------------------------------

function esc_html(mixed $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function esc_attr(mixed $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function esc_textarea(mixed $text): string
{
    return htmlspecialchars((string) $text, ENT_QUOTES, 'UTF-8');
}

function esc_js(mixed $text): string
{
    return addslashes((string) $text);
}

function esc_url(mixed $url, ?array $protocols = null, string $context = 'display'): string
{
    $url = trim((string) $url);

    if ($url === '' || preg_match('#^(javascript|data|vbscript):#i', $url)) {
        return '';
    }

    return $context === 'display' ? str_replace('&', '&#038;', $url) : $url;
}

function esc_url_raw(mixed $url, ?array $protocols = null): string
{
    return esc_url($url, $protocols, 'db');
}

function sanitize_text_field(mixed $value): string
{
    return trim(preg_replace('/[\r\n\t ]+/', ' ', strip_tags((string) $value)));
}

function sanitize_textarea_field(mixed $value): string
{
    return trim(strip_tags((string) $value));
}

function sanitize_email(mixed $email): string
{
    return filter_var(trim((string) $email), FILTER_SANITIZE_EMAIL) ?: '';
}

function is_email(mixed $email): string|false
{
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? (string) $email : false;
}

function sanitize_key(mixed $key): string
{
    return preg_replace('/[^a-z0-9_\-]/', '', strtolower((string) $key));
}

function sanitize_title(mixed $title, string $fallback = '', string $context = 'save'): string
{
    $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9]+/', '-', (string) $title), '-'));

    return $slug !== '' ? $slug : $fallback;
}

function sanitize_user(mixed $username, bool $strict = false): string
{
    $username = strip_tags((string) $username);

    return trim($strict ? preg_replace('/[^a-z0-9 _.\-@]/i', '', $username) : $username);
}

function sanitize_file_name(string $name): string
{
    return preg_replace('/[^A-Za-z0-9._\-]/', '-', $name);
}

function wp_strip_all_tags(mixed $text, bool $removeBreaks = false): string
{
    $text = strip_tags(preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $text));

    return trim($removeBreaks ? preg_replace('/[\r\n\t ]+/', ' ', $text) : $text);
}

function wp_kses_post(mixed $content): string
{
    return preg_replace('@<(script|style)[^>]*?>.*?</\\1>@si', '', (string) $content);
}

function wp_kses(mixed $content, mixed $allowed = [], array $protocols = []): string
{
    return wp_kses_post($content);
}

function absint(mixed $value): int
{
    return abs((int) $value);
}

function wp_json_encode(mixed $data, int $options = 0, int $depth = 512): string|false
{
    return json_encode($data, $options, $depth);
}

function wp_parse_args(string|array|object $args, array $defaults = []): array
{
    if (is_string($args)) {
        parse_str($args, $args);
    }

    return array_merge($defaults, (array) $args);
}

function wp_generate_password(int $length = 12, bool $special = true, bool $extraSpecial = false): string
{
    return substr(bin2hex(random_bytes($length)), 0, $length);
}

function wp_salt(string $scheme = 'auth'): string
{
    return 'test-salt-'.$scheme;
}

function wp_hash(string $data, string $scheme = 'auth', string $algo = 'md5'): string
{
    return hash_hmac($algo, $data, wp_salt($scheme));
}

function wp_rand(int $min = 0, int $max = 0): int
{
    return random_int($min, $max ?: PHP_INT_MAX);
}

function wpautop(string $text, bool $br = true): string
{
    return $text === '' ? '' : '<p>'.implode('</p><p>', array_map('trim', preg_split('/\n\s*\n/', trim($text)))).'</p>';
}

function number_format_i18n(float $number, int $decimals = 0): string
{
    return number_format($number, $decimals);
}

function date_i18n(string $format, int|bool $timestamp = false, bool $gmt = false): string
{
    return gmdate($format, $timestamp === false ? time() : (int) $timestamp);
}

function wp_date(string $format, ?int $timestamp = null, ?DateTimeZone $timezone = null): string
{
    return gmdate($format, $timestamp ?? time());
}

function human_time_diff(int $from, int $to = 0): string
{
    return (string) abs(($to ?: time()) - $from).' secs';
}

function mysql2date(string $format, string $date, bool $translate = true): string
{
    return gmdate($format, strtotime($date));
}

function size_format(int|string $bytes, int $decimals = 0): string
{
    return $bytes.' B';
}

// ---------------------------------------------------------------------------------------------
// Translation
// ---------------------------------------------------------------------------------------------

function __(string $text, string $domain = 'default'): string
{
    return $text;
}

function _e(string $text, string $domain = 'default'): void
{
    echo $text;
}

function _x(string $text, string $context, string $domain = 'default'): string
{
    return $text;
}

function _n(string $single, string $plural, int $number, string $domain = 'default'): string
{
    return $number === 1 ? $single : $plural;
}

function esc_html__(string $text, string $domain = 'default'): string
{
    return esc_html($text);
}

function esc_attr__(string $text, string $domain = 'default'): string
{
    return esc_attr($text);
}

function esc_html_e(string $text, string $domain = 'default'): void
{
    echo esc_html($text);
}

function esc_attr_e(string $text, string $domain = 'default'): void
{
    echo esc_attr($text);
}

// ---------------------------------------------------------------------------------------------
// Nonces
// ---------------------------------------------------------------------------------------------

function wp_create_nonce(string|int $action = -1): string
{
    return substr(wp_hash($action.'|'.get_current_user_id(), 'nonce'), 0, 10);
}

function wp_verify_nonce(?string $nonce, string|int $action = -1): int|false
{
    return is_string($nonce) && hash_equals(wp_create_nonce($action), $nonce) ? 1 : false;
}

function wp_nonce_field(string|int $action = -1, string $name = '_wpnonce', bool $referer = true, bool $display = true): string
{
    $field = '<input type="hidden" name="'.esc_attr($name).'" value="'.wp_create_nonce($action).'" />';

    if ($display) {
        echo $field;
    }

    return $field;
}

function check_admin_referer(string|int $action = -1, string $name = '_wpnonce'): int|false
{
    $result = wp_verify_nonce($_REQUEST[$name] ?? null, $action);

    if ($result === false) {
        wp_die('The link you followed has expired.', '', ['response' => 403]);
    }

    return $result;
}

function check_ajax_referer(string|int $action = -1, string|false $name = false, bool $stop = true): int|false
{
    return check_admin_referer($action, $name ?: '_wpnonce');
}

// ---------------------------------------------------------------------------------------------
// Mail, redirects, responses
// ---------------------------------------------------------------------------------------------

function wp_mail(string|array $to, string $subject, string $message, string|array $headers = '', string|array $attachments = []): bool
{
    WordPress::$mails[] = compact('to', 'subject', 'message', 'headers');

    return WordPress::$mailSucceeds;
}

function wp_redirect(string $location, int $status = 302, string|false $by = 'WordPress'): bool
{
    WordPress::$redirects[] = ['location' => $location, 'status' => $status];

    return true;
}

function wp_safe_redirect(string $location, int $status = 302, string|false $by = 'WordPress'): bool
{
    return wp_redirect(wp_validate_redirect($location, home_url('/')), $status, $by);
}

function wp_validate_redirect(string $location, string $fallback = ''): string
{
    $host = parse_url($location, PHP_URL_HOST);

    return $host === null || $host === parse_url(home_url(), PHP_URL_HOST) ? $location : $fallback;
}

function status_header(int $code, string $description = ''): void
{
    WordPress::$options['__status_header'] = $code;
}

function nocache_headers(): void {}

function wp_die(string|WP_Error $message = '', string|int $title = '', string|array|int $args = []): never
{
    throw new RuntimeException('wp_die: '.($message instanceof WP_Error ? $message->get_error_message() : $message));
}

function is_wp_error(mixed $thing): bool
{
    return $thing instanceof WP_Error;
}

// ---------------------------------------------------------------------------------------------
// Cron
// ---------------------------------------------------------------------------------------------

function wp_next_scheduled(string $hook, array $args = []): int|false
{
    return WordPress::$cron[$hook]['timestamp'] ?? false;
}

function wp_schedule_event(int $timestamp, string $recurrence, string $hook, array $args = [], bool $wpError = false): bool
{
    WordPress::$cron[$hook] = ['timestamp' => $timestamp, 'recurrence' => $recurrence, 'args' => $args];

    return true;
}

function wp_schedule_single_event(int $timestamp, string $hook, array $args = [], bool $wpError = false): bool
{
    WordPress::$cron[$hook] = ['timestamp' => $timestamp, 'recurrence' => false, 'args' => $args];

    return true;
}

function wp_unschedule_event(int $timestamp, string $hook, array $args = [], bool $wpError = false): bool
{
    unset(WordPress::$cron[$hook]);

    return true;
}

function wp_clear_scheduled_hook(string $hook, array $args = [], bool $wpError = false): int
{
    $existed = isset(WordPress::$cron[$hook]);
    unset(WordPress::$cron[$hook]);

    return (int) $existed;
}

function _get_cron_array(): array
{
    $cron = [];

    foreach (WordPress::$cron as $hook => $event) {
        $cron[$event['timestamp']][$hook][md5(serialize($event['args']))] = ['schedule' => $event['recurrence'], 'args' => $event['args']];
    }

    ksort($cron);

    return $cron;
}

// ---------------------------------------------------------------------------------------------
// HTTP (queue responses with WordPress::respondWith())
// ---------------------------------------------------------------------------------------------

function wp_remote_request(string $url, array $args = []): array|WP_Error
{
    WordPress::$httpRequests[] = compact('url', 'args');

    return array_shift(WordPress::$httpResponses) ?? new WP_Error('http_request_failed', 'No fake response queued for '.$url);
}

function wp_remote_post(string $url, array $args = []): array|WP_Error
{
    return wp_remote_request($url, ['method' => 'POST'] + $args);
}

function wp_remote_get(string $url, array $args = []): array|WP_Error
{
    return wp_remote_request($url, ['method' => 'GET'] + $args);
}

function wp_safe_remote_post(string $url, array $args = []): array|WP_Error
{
    return wp_remote_post($url, $args);
}

function wp_safe_remote_get(string $url, array $args = []): array|WP_Error
{
    return wp_remote_get($url, $args);
}

function wp_remote_retrieve_response_code(array|WP_Error $response): int|string
{
    return is_array($response) ? $response['response']['code'] : '';
}

function wp_remote_retrieve_body(array|WP_Error $response): string
{
    return is_array($response) ? $response['body'] : '';
}

function wp_remote_retrieve_header(array|WP_Error $response, string $header): string
{
    return is_array($response) ? ($response['headers'][strtolower($header)] ?? '') : '';
}

// ---------------------------------------------------------------------------------------------
// Blocks and patterns
// ---------------------------------------------------------------------------------------------

function register_block_pattern(string $name, array $properties): bool
{
    WordPress::$blockPatterns[$name] = $properties;

    return true;
}

function unregister_block_pattern(string $name): bool
{
    unset(WordPress::$blockPatterns[$name]);

    return true;
}

function register_block_pattern_category(string $name, array $properties): bool
{
    return true;
}

// ---------------------------------------------------------------------------------------------
// Theme output helpers the layouts call
// ---------------------------------------------------------------------------------------------

function wp_head(): void
{
    do_action('wp_head');
}

function wp_footer(): void
{
    do_action('wp_footer');
}

function wp_body_open(): void
{
    do_action('wp_body_open');
}

function language_attributes(string $doctype = 'html'): void
{
    echo 'lang="en-US"';
}

function body_class(string|array $class = ''): void
{
    echo 'class="'.esc_attr(implode(' ', (array) $class)).'"';
}

function get_body_class(string|array $class = ''): array
{
    return (array) $class;
}

function wp_robots(): void {}

function has_nav_menu(string $location): bool
{
    return false;
}

function wp_nav_menu(array $args = []): string|false|null
{
    return '';
}

function get_search_form(array $args = []): ?string
{
    return '';
}

function is_active_sidebar(string|int $index): bool
{
    return false;
}

function dynamic_sidebar(string|int $index = 1): bool
{
    return false;
}

function wp_enqueue_script(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, array|bool $args = []): void {}

function wp_enqueue_style(string $handle, string $src = '', array $deps = [], string|bool|null $ver = false, string $media = 'all'): void {}

function wp_dequeue_style(string $handle): void {}

function wp_dequeue_script(string $handle): void {}

function wp_add_inline_script(string $handle, string $data, string $position = 'after'): bool
{
    return true;
}

function wp_localize_script(string $handle, string $objectName, array $l10n): bool
{
    return true;
}

function wp_set_script_translations(string $handle, string $domain = 'default', string $path = ''): bool
{
    return true;
}

function wp_script_is(string $handle, string $status = 'enqueued'): bool
{
    return false;
}

function get_current_screen(): ?object
{
    return null;
}

function get_parent_theme_file_path(string $file = ''): string
{
    return get_theme_file_path($file);
}

function get_parent_theme_file_uri(string $file = ''): string
{
    return get_theme_file_uri($file);
}
