<?php

/*
 * Fakes the Lead module's code calls on top of functions.php. Every definition is guarded:
 * another module's fakes may define the same function first.
 */

use Tests\Support\WordPress\WordPress;

if (! function_exists('add_query_arg')) {
    function add_query_arg(array|string $args, mixed $value = null, mixed $url = null): string
    {
        if (is_array($args)) {
            $url = is_string($value) ? $value : '';
        } else {
            $args = [$args => $value];
            $url = is_string($url) ? $url : '';
        }

        [$base, $query] = array_pad(explode('?', $url, 2), 2, '');
        parse_str($query, $existing);

        return $base.'?'.http_build_query(array_merge($existing, $args));
    }
}

if (! function_exists('wp_nonce_url')) {
    function wp_nonce_url(string $url, int|string $action = -1, string $name = '_wpnonce'): string
    {
        return add_query_arg($name, wp_create_nonce($action), $url);
    }
}

if (! class_exists('WP_Role')) {
    class WP_Role
    {
        /**
         * @param  array<string, bool>  $capabilities
         */
        public function __construct(public string $name, public array $capabilities = []) {}

        public function add_cap(string $cap, bool $grant = true): void
        {
            $this->capabilities[$cap] = $grant;
        }

        public function remove_cap(string $cap): void
        {
            unset($this->capabilities[$cap]);
        }

        public function has_cap(string $cap): bool
        {
            return ! empty($this->capabilities[$cap]);
        }
    }
}

if (! function_exists('get_role')) {
    /**
     * Roles live in WordPress::$options['__roles'], created on first use for the core roles.
     */
    function get_role(string $role): ?WP_Role
    {
        if (! array_key_exists($role, WP_User::ROLE_CAPS)) {
            return null;
        }

        $roles = WordPress::$options['__roles'] ?? [];

        if (! isset($roles[$role])) {
            $roles[$role] = new WP_Role($role, array_fill_keys(WP_User::ROLE_CAPS[$role], true));
            WordPress::$options['__roles'] = $roles;
        }

        return $roles[$role];
    }
}
