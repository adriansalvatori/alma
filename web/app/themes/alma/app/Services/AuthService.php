<?php

namespace App\Services;

use WP_User;
use WP_Error;

class AuthService
{
    /**
     * Attempt to log the user in.
     *
     * @param array $credentials
     * @return array|WP_Error Array with 'requires_2fa' boolean and 'user_id' or WP_User, or WP_Error on failure.
     */
    public function login(array $credentials)
    {
        $username = $credentials['email'] ?? $credentials['username'] ?? '';
        $user = wp_authenticate($username, $credentials['password'] ?? '');

        if (is_wp_error($user)) {
            return $user;
        }

        $is2faEnabled = get_user_meta($user->ID, 'two_factor_enabled', true);

        if ($is2faEnabled) {
            return [
                'requires_2fa' => true,
                'user_id' => $user->ID,
            ];
        }

        $this->completeLogin($user, $credentials['remember'] ?? false);

        return [
            'requires_2fa' => false,
            'user' => $user,
        ];
    }

    /**
     * Complete the login process by setting cookies.
     *
     * @param WP_User $user
     * @param bool $remember
     * @return void
     */
    public function completeLogin(WP_User $user, bool $remember = false): void
    {
        wp_set_current_user($user->ID);
        wp_set_auth_cookie($user->ID, $remember);
        do_action('wp_login', $user->user_login, $user);
    }

    /**
     * Register a new user.
     *
     * @param array $data
     * @return int|WP_Error
     */
    public function register(array $data)
    {
        $userId = wp_insert_user([
            'user_pass' => $data['password'] ?? '',
            'user_login' => $data['username'] ?? current(explode('@', $data['email'] ?? '')),
            'user_email' => $data['email'] ?? '',
            'role' => 'subscriber',
        ]);

        return $userId;
    }

    /**
     * Log the current user out.
     *
     * @return void
     */
    public function logout(): void
    {
        wp_logout();
    }

    /**
     * Get the currently authenticated user.
     *
     * @return WP_User|null
     */
    public function user(): ?WP_User
    {
        $user = wp_get_current_user();

        return $user->exists() ? $user : null;
    }

    /**
     * Check if a user is authenticated.
     *
     * @return bool
     */
    public function check(): bool
    {
        return is_user_logged_in();
    }
}
