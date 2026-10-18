<?php

/*
 * Fakes for the admin skin (App\Infrastructure\WordPress\Admin): the toolbar and its nodes.
 * Toggle the toolbar with WordPress::$conditionals['is_admin_bar_showing'] = true.
 */

use Tests\Support\WordPress\WordPress;

if (! function_exists('is_admin_bar_showing')) {
    function is_admin_bar_showing(): bool
    {
        return WordPress::$conditionals['is_admin_bar_showing'] ?? false;
    }
}

if (! function_exists('_wp_admin_bar_init')) {
    function _wp_admin_bar_init(): bool
    {
        $GLOBALS['wp_admin_bar'] = new WP_Admin_Bar;

        return true;
    }
}

if (! class_exists('WP_Admin_Bar')) {
    /**
     * Nodes keyed by id, merged on re-add like core's add_node().
     */
    class WP_Admin_Bar
    {
        /** @var array<string, object> */
        public array $nodes = [];

        public function add_node(array $args): void
        {
            $existing = isset($this->nodes[$args['id']]) ? get_object_vars($this->nodes[$args['id']]) : ['title' => false, 'href' => false, 'parent' => false, 'meta' => []];

            $this->nodes[$args['id']] = (object) array_merge($existing, $args);
        }

        public function add_menu(array $args): void
        {
            $this->add_node($args);
        }

        public function get_node(string $id): ?object
        {
            return $this->nodes[$id] ?? null;
        }

        public function remove_node(string $id): void
        {
            unset($this->nodes[$id]);
        }

        public function remove_menu(string $id): void
        {
            $this->remove_node($id);
        }
    }
}
