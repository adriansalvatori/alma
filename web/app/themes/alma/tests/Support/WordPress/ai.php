<?php

/*
 * Fakes the AI module's code calls on top of functions.php: the Abilities API, Application
 * Passwords, the block and pattern registries, media sideloading and plugin activation. State
 * lives in WordPress::$options (reset before every test) under "__" keys. Every definition is
 * guarded: another module's fakes may define the same function first.
 */

use Tests\Support\WordPress\WordPress;

if (! defined('OBJECT')) {
    define('OBJECT', 'OBJECT');
}

// ---------------------------------------------------------------------------------------------
// Abilities API
// ---------------------------------------------------------------------------------------------

if (! function_exists('wp_register_ability_category')) {
    function wp_register_ability_category(string $slug, array $args): ?object
    {
        WordPress::$options['__ability_categories'][$slug] = $args;

        return (object) (['slug' => $slug] + $args);
    }
}

if (! function_exists('wp_register_ability')) {
    function wp_register_ability(string $name, array $args): ?object
    {
        WordPress::$options['__abilities'][$name] = $args;

        return (object) ['name' => $name];
    }
}

if (! function_exists('ai_test_ability')) {
    /**
     * The registered arguments of an ability, registering the configured ones on first use.
     */
    function ai_test_ability(string $name): array
    {
        if (! isset(WordPress::$options['__abilities'])) {
            do_action('wp_abilities_api_categories_init');
            do_action('wp_abilities_api_init');
        }

        return WordPress::$options['__abilities'][$name] ?? throw new RuntimeException("Ability {$name} is not registered.");
    }
}

if (! function_exists('ai_test_run_ability')) {
    /**
     * Run an ability the way WP_Ability::execute() does: invoked action, permission check through
     * its filter, the execute callback, then the result filter. Input validation is not modelled.
     */
    function ai_test_run_ability(string $name, mixed $input = null): mixed
    {
        $ability = ai_test_ability($name);
        $input ??= [];

        do_action('wp_ability_invoked', $name, $input, null);

        $permission = apply_filters('wp_ability_permission_result', ($ability['permission_callback'])($input), $name, $input, null);

        if ($permission !== true) {
            return new WP_Error('ability_invalid_permissions', "Ability \"{$name}\" does not have necessary permission.");
        }

        return apply_filters('wp_ability_execute_result', ($ability['execute_callback'])($input), $name, $input, null);
    }
}

// ---------------------------------------------------------------------------------------------
// Application Passwords
// ---------------------------------------------------------------------------------------------

if (! function_exists('wp_is_application_passwords_available')) {
    function wp_is_application_passwords_available(): bool
    {
        return WordPress::$options['__app_passwords_available'] ?? true;
    }
}

if (! class_exists('WP_Application_Passwords')) {
    class WP_Application_Passwords
    {
        public static function create_new_application_password(int $userId, array $args = []): array|WP_Error
        {
            if (empty($args['name'])) {
                return new WP_Error('application_password_empty_name', 'An application name is required.');
            }

            $password = bin2hex(random_bytes(12));
            $item = [
                'uuid' => sprintf('%s-%s', bin2hex(random_bytes(4)), bin2hex(random_bytes(6))),
                'name' => $args['name'],
                'password' => wp_hash_password($password),
                'created' => time(),
                'last_used' => null,
                'last_ip' => null,
            ];

            WordPress::$options['__app_passwords'][$userId][$item['uuid']] = $item;

            return [$password, $item];
        }

        public static function get_user_application_passwords(int $userId): array
        {
            return array_values(WordPress::$options['__app_passwords'][$userId] ?? []);
        }

        public static function delete_application_password(int $userId, string $uuid): bool|WP_Error
        {
            if (! isset(WordPress::$options['__app_passwords'][$userId][$uuid])) {
                return new WP_Error('application_password_not_found', 'Could not find an application password with that id.');
            }

            unset(WordPress::$options['__app_passwords'][$userId][$uuid]);

            return true;
        }
    }
}

// ---------------------------------------------------------------------------------------------
// Posts and attachments
// ---------------------------------------------------------------------------------------------

if (! function_exists('get_posts')) {
    function get_posts(array $args = []): array
    {
        $type = $args['post_type'] ?? 'post';
        $status = $args['post_status'] ?? 'publish';
        $search = strtolower((string) ($args['s'] ?? ''));
        $mime = (string) ($args['post_mime_type'] ?? '');

        $posts = array_values(array_filter(WordPress::$posts, function (WP_Post $post) use ($type, $status, $search, $mime) {
            if (! in_array($post->post_type, (array) $type, true)) {
                return false;
            }

            if ($status !== 'any' && ! in_array($post->post_status, (array) $status, true)) {
                return false;
            }

            if ($status === 'any' && in_array($post->post_status, ['trash', 'auto-draft'], true)) {
                return false;
            }

            if ($search !== '' && ! str_contains(strtolower($post->post_title.' '.$post->post_content), $search)) {
                return false;
            }

            return $mime === '' || str_starts_with((string) get_post_mime_type($post), $mime);
        }));

        if (($args['orderby'] ?? '') === 'title') {
            usort($posts, fn (WP_Post $a, WP_Post $b) => strcmp($a->post_title, $b->post_title));
        } elseif (($args['orderby'] ?? '') === 'date') {
            usort($posts, fn (WP_Post $a, WP_Post $b) => $b->ID <=> $a->ID);
        }

        $limit = (int) ($args['numberposts'] ?? 5);

        return $limit > 0 ? array_slice($posts, 0, $limit) : $posts;
    }
}

if (! function_exists('get_post_mime_type')) {
    /**
     * Mime types live in WordPress::$options['__mime'][$id]: the WP_Post fake has no
     * post_mime_type property.
     */
    function get_post_mime_type(int|WP_Post|null $post = null): string|false
    {
        $post = get_post($post);

        return $post ? (WordPress::$options['__mime'][$post->ID] ?? false) : false;
    }
}

if (! function_exists('wp_get_attachment_url')) {
    function wp_get_attachment_url(int $id = 0): string|false
    {
        $post = get_post($id);

        return $post ? home_url('app/uploads/'.$post->post_name) : false;
    }
}

if (! function_exists('wp_tempnam')) {
    function wp_tempnam(string $filename = '', string $dir = ''): string
    {
        return (string) tempnam(sys_get_temp_dir(), 'alma-ai-');
    }
}

if (! function_exists('download_url')) {
    /**
     * Downloads come from WordPress::$options['__downloads'][$url]; anything else fails like an
     * unreachable host.
     */
    function download_url(string $url, int $timeout = 300, bool $signatureVerification = false): string|WP_Error
    {
        if (! isset(WordPress::$options['__downloads'][$url])) {
            return new WP_Error('http_request_failed', 'A valid URL was not provided.');
        }

        $tmp = wp_tempnam($url);
        file_put_contents($tmp, WordPress::$options['__downloads'][$url]);

        return $tmp;
    }
}

if (! function_exists('wp_check_filetype_and_ext')) {
    /**
     * Like core: the extension must be allowed, and for files PHP can sniff, the contents must
     * agree with it.
     */
    function wp_check_filetype_and_ext(string $file, string $filename, ?array $mimes = null): array
    {
        $none = ['ext' => false, 'type' => false, 'proper_filename' => false];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $type = false;

        foreach ((array) $mimes as $exts => $mime) {
            if (in_array($ext, explode('|', $exts), true)) {
                $type = $mime;
            }
        }

        if ($type === false) {
            return $none;
        }

        $real = (new finfo(FILEINFO_MIME_TYPE))->file($file);

        return $real === $type ? ['ext' => $ext, 'type' => $type, 'proper_filename' => false] : $none;
    }
}

if (! function_exists('media_handle_sideload')) {
    function media_handle_sideload(array $file, int $postId = 0, ?string $desc = null, array $postData = []): int|WP_Error
    {
        if (! is_file($file['tmp_name'] ?? '')) {
            return new WP_Error('upload_error', 'The file is missing.');
        }

        $post = WordPress::post([
            'post_type' => 'attachment',
            'post_status' => 'inherit',
            'post_title' => $desc ?? pathinfo($file['name'], PATHINFO_FILENAME),
            'post_name' => $file['name'],
        ]);

        WordPress::$options['__mime'][$post->ID] = (new finfo(FILEINFO_MIME_TYPE))->file($file['tmp_name']);

        WordPress::$options['__uploaded'][$post->ID] = filesize($file['tmp_name']);

        return $post->ID;
    }
}

// ---------------------------------------------------------------------------------------------
// Block and pattern registries
// ---------------------------------------------------------------------------------------------

if (! class_exists('WP_Block_Patterns_Registry')) {
    class WP_Block_Patterns_Registry
    {
        public static function get_instance(): self
        {
            return new self;
        }

        public function get_all_registered(bool $outsideInitOnly = false): array
        {
            $patterns = [];

            foreach (WordPress::$blockPatterns as $name => $pattern) {
                $patterns[] = ['name' => $name] + $pattern;
            }

            return $patterns;
        }

        public function get_registered(string $name): ?array
        {
            return isset(WordPress::$blockPatterns[$name]) ? ['name' => $name] + WordPress::$blockPatterns[$name] : null;
        }

        public function is_registered(?string $name): bool
        {
            return isset(WordPress::$blockPatterns[(string) $name]);
        }
    }
}

if (! class_exists('WP_Block_Type_Registry')) {
    /**
     * Block types come from WordPress::$options['__block_types'][$name] = ['title' => .., 'description' => ..].
     */
    class WP_Block_Type_Registry
    {
        public static function get_instance(): self
        {
            return new self;
        }

        public function get_all_registered(): array
        {
            return array_map(
                fn (array $type) => (object) ($type + ['title' => '', 'description' => '']),
                WordPress::$options['__block_types'] ?? [],
            );
        }
    }
}

if (! function_exists('acf_get_field_groups')) {
    /**
     * Field groups come from WordPress::$options['__acf_field_groups'][$block] = [fields].
     */
    function acf_get_field_groups(array $filter = []): array
    {
        $block = $filter['block'] ?? null;

        return isset(WordPress::$options['__acf_field_groups'][$block]) ? [['key' => 'group_'.$block, 'block' => $block]] : [];
    }
}

if (! function_exists('acf_get_fields')) {
    function acf_get_fields(array|string|int $group): array
    {
        $block = is_array($group) ? ($group['block'] ?? null) : null;

        return WordPress::$options['__acf_field_groups'][$block] ?? [];
    }
}

// ---------------------------------------------------------------------------------------------
// Plugins and REST routes
// ---------------------------------------------------------------------------------------------

if (! function_exists('is_plugin_active')) {
    function is_plugin_active(string $plugin): bool
    {
        return in_array($plugin, (array) get_option('active_plugins', []), true);
    }
}

if (! function_exists('activate_plugin')) {
    function activate_plugin(string $plugin, string $redirect = '', bool $networkWide = false, bool $silent = false): ?WP_Error
    {
        $active = (array) get_option('active_plugins', []);
        $active[] = $plugin;
        update_option('active_plugins', array_values(array_unique($active)));

        return null;
    }
}

if (! function_exists('register_rest_route')) {
    function register_rest_route(string $namespace, string $route, array $args = [], bool $override = false): bool
    {
        WordPress::$options['__rest_routes'][$namespace.$route] = $args;

        return true;
    }
}
