<?php

/*
 * Minimal stand-ins for the WordPress classes the theme type-hints or constructs. Only the
 * members the code uses are implemented.
 */

if (! class_exists('WP_Error')) {
    class WP_Error
    {
        /** @var array<string, list<string>> */
        public array $errors = [];

        /** @var array<string, mixed> */
        public array $error_data = [];

        public function __construct(string $code = '', string $message = '', mixed $data = '')
        {
            if ($code !== '') {
                $this->add($code, $message, $data);
            }
        }

        public function add(string $code, string $message, mixed $data = ''): void
        {
            $this->errors[$code][] = $message;

            if ($data !== '') {
                $this->error_data[$code] = $data;
            }
        }

        public function get_error_code(): string
        {
            return (string) (array_key_first($this->errors) ?? '');
        }

        public function get_error_codes(): array
        {
            return array_keys($this->errors);
        }

        public function get_error_message(string $code = ''): string
        {
            $code = $code ?: $this->get_error_code();

            return $this->errors[$code][0] ?? '';
        }

        public function get_error_data(string $code = ''): mixed
        {
            return $this->error_data[$code ?: $this->get_error_code()] ?? null;
        }

        public function has_errors(): bool
        {
            return $this->errors !== [];
        }
    }
}

if (! class_exists('WP_User')) {
    class WP_User
    {
        public int $ID;

        public object $data;

        /** @var list<string> */
        public array $roles = [];

        /** @var array<string, bool> */
        public array $caps = [];

        /** @var array<string, bool> */
        public array $allcaps = [];

        /**
         * Capabilities granted by each role, enough for the checks the theme makes.
         *
         * @var array<string, list<string>>
         */
        public const ROLE_CAPS = [
            'administrator' => ['read', 'edit_posts', 'edit_pages', 'edit_others_pages', 'edit_published_pages', 'publish_pages', 'upload_files', 'manage_options', 'list_users', 'edit_users', 'create_users'],
            'editor' => ['read', 'edit_posts', 'edit_pages', 'edit_others_pages', 'edit_published_pages', 'publish_pages', 'upload_files'],
            'author' => ['read', 'edit_posts', 'upload_files'],
            'contributor' => ['read', 'edit_posts'],
            'subscriber' => ['read'],
        ];

        /**
         * @param  array<string, mixed>  $data
         * @param  list<string>  $roles
         */
        public function __construct(int $id = 0, array $data = [], array $roles = [])
        {
            $this->ID = $id;
            $this->data = (object) array_merge(['ID' => $id, 'user_login' => '', 'user_email' => '', 'user_pass' => '', 'display_name' => ''], $data);

            foreach ($roles as $role) {
                $this->add_role($role);
            }
        }

        public function __get(string $name): mixed
        {
            return $this->data->{$name} ?? get_user_meta($this->ID, $name, true);
        }

        public function __set(string $name, mixed $value): void
        {
            $this->data->{$name} = $value;
        }

        public function __isset(string $name): bool
        {
            return isset($this->data->{$name});
        }

        public function exists(): bool
        {
            return $this->ID > 0;
        }

        public function has_cap(string $cap, mixed ...$args): bool
        {
            return ! empty($this->allcaps[$cap]);
        }

        public function add_cap(string $cap, bool $grant = true): void
        {
            $this->caps[$cap] = $grant;
            $this->allcaps[$cap] = $grant;
        }

        public function remove_cap(string $cap): void
        {
            unset($this->caps[$cap], $this->allcaps[$cap]);
        }

        public function add_role(string $role): void
        {
            if (! in_array($role, $this->roles, true)) {
                $this->roles[] = $role;
            }

            foreach (self::ROLE_CAPS[$role] ?? [] as $cap) {
                $this->allcaps[$cap] = true;
            }

            $this->allcaps[$role] = true;
        }

        public function set_role(string $role): void
        {
            $this->roles = [];
            $this->allcaps = $this->caps;
            $this->add_role($role);
        }
    }
}

if (! class_exists('WP_Post')) {
    class WP_Post
    {
        public int $ID = 0;

        public string $post_title = '';

        public string $post_name = '';

        public string $post_content = '';

        public string $post_excerpt = '';

        public string $post_status = 'publish';

        public string $post_type = 'post';

        public string $post_mime_type = '';

        public int|string $post_author = 0;

        public int $post_parent = 0;

        public string $post_date = '';

        public string $post_modified = '';

        public function __construct(object $post)
        {
            foreach (get_object_vars($post) as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }
}

if (! class_exists('wpdb')) {
    /**
     * Only the properties Acorn's database config reads when it builds the "wordpress"
     * connection. Queries go through Eloquent on SQLite, never through this object.
     */
    class wpdb
    {
        public string $prefix = 'wp_';

        public string $base_prefix = 'wp_';

        public string $collate = 'utf8mb4_unicode_ci';

        public string $charset = 'utf8mb4';

        public string $posts = 'wp_posts';

        public string $postmeta = 'wp_postmeta';

        public string $options = 'wp_options';

        public string $users = 'wp_users';

        public string $usermeta = 'wp_usermeta';
    }
}
