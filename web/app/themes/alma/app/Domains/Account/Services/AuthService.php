<?php

namespace App\Domains\Account\Services;

use Illuminate\Validation\ValidationException;
use WP_Error;
use WP_User;

class AuthService
{
    /**
     * Attempt to log the user in.
     *
     * @return array|WP_Error Array with 'requires_2fa' boolean and 'user_id' or WP_User, or WP_Error on failure.
     */
    public function login(array $credentials)
    {
        $username = $credentials['email'] ?? $credentials['username'] ?? '';
        $user = wp_authenticate($username, $credentials['password'] ?? '');

        if (is_wp_error($user)) {
            throw ValidationException::withMessages([
                'email' => $user->get_error_message(),
            ]);
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

        if (is_wp_error($userId)) {
            throw ValidationException::withMessages([
                'email' => $userId->get_error_message(),
            ]);
        }

        return $userId;
    }

    /**
     * Log the current user out.
     */
    public function logout(): void
    {
        wp_logout();
    }

    /**
     * Get the currently authenticated user.
     */
    public function user(): ?WP_User
    {
        $user = wp_get_current_user();

        return $user->exists() ? $user : null;
    }

    /**
     * Check if a user is authenticated.
     */
    public function check(): bool
    {
        return is_user_logged_in();
    }
}
